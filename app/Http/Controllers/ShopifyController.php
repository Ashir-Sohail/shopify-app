<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Shop;


use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    public function install(Request $request)
    {
        $shop = $request->query('shop');
        if (!$shop) {
            return response('Missing shop parameter', 400);
        }
        $state = bin2hex(random_bytes(16));

        session(['shopify_oauth_state' => $state]);
        $query = [
            'client_id' => env('SHOPIFY_API_KEY'),
            'scope' => env('SHOPIFY_SCOPES'),
            'redirect_uri' => env('SHOPIFY_REDIRECT_URI'),
            'state' => $state,

        ];

        $installUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query($query);

        return redirect($installUrl);
    }

    public function callback(Request $request)
    {
        $shop = $request->query('shop');
        $code = $request->query('code');
        if (!$shop || !str_ends_with($shop, '.myshopify.com')) {
            abort(400, 'Invalid shop domain');
        }
        // 1. Verify HMAC (Crucial for security!)
        // You must verify the 'hmac' query parameter using your API Secret.
        abort_unless($this->verifyHmac($request), 403, 'HMAC failed');
        abort_unless(
            $request->query('state') === session('shopify_oauth_state'),
            403,
            'Invalid state'
        );

        // 2. Exchange Code for Access Token
        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id'     => env('SHOPIFY_API_KEY'),
            'client_secret' => env('SHOPIFY_API_SECRET'),
            'code'          => $code,
        ]);

        if (!$response->successful()) {
            Log::error('Token exchange failed', ['response' => $response->body()]);
            abort(500, 'Failed to get access token');
        }

        $data = $response->json();

        $accessToken = $data['access_token'] ?? null;

        if (!$accessToken) {
            abort(500, 'No access token returned');
        }

        // 3. Save the token
        // Store $accessToken in your database linked to this $shop name.
        Shop::updateOrCreate(
            ['shop_domain' => $shop],
            [
                'access_token' => $accessToken,
                'scope' => $data['scope'] ?? null,
            ]
        );
        Log::info('after updateorcreate',[
            'shop' => $shop,
            'access_token' => $accessToken,
            'scope' => $data['scope'] ?? null,
        ]);

        return view('shopify-success', ['shop' => $shop]);
    }

    private function verifyHmac(Request $request): bool
    {
        $params = $request->query();

        $hmac = $params['hmac'] ?? '';

        unset($params['hmac'], $params['signature']);

        ksort($params);

        $queryString = urldecode(http_build_query($params));

        $computed = hash_hmac(
            'sha256',
            $queryString,
            env('SHOPIFY_API_SECRET')
        );
        Log::info('HMAC Debug', [
            'computed' => $computed,
            'received' => $hmac,
            'query' => $queryString
        ]);

        return hash_equals($computed, $hmac);
    }
    public function index(Request $request)
    {
        // Retrieve the shop name from the URL parameters
        $shop = $request->query('shop');
        // Skip HMAC if no params (manual access)
        if ($request->has('hmac') && !$this->verifyHmac($request)) {
            abort(403, 'Unauthorized action.');
        }

        return view('dummy-dashboard', ['shop' => $shop]);
    }


    public function products($shop)
    {
        $shopData = Shop::where('shop_domain', $shop)->first();

        if (!$shopData) {
            abort(404, 'Shop not found');
        }

        $query = '
                {
                    products(first: 10) {
                        edges {
                        node {
                            id
                            title
                            handle
                        }
                        }
                    }
                }';

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopData->access_token,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2024-01/graphql.json", [
            'query' => $query
        ]);

        $products = $response->json();

        return view('products', compact('products', 'shop'));
    }
}
