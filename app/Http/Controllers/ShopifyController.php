<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Shop;
use App\Services\ShopifyService;


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
        $host = $request->query('host');

        if (!$host) {
            return redirect()->route('shopify.install', ['shop' => $shop]);
        }

        if (!$shop || !str_ends_with($shop, '.myshopify.com')) {
            abort(400, 'Invalid shop domain');
        }

        // 1. Verify HMAC (Crucial for security!)
        // You must verify the 'hmac' query parameter using your API Secret.
        abort_unless($this->verifyHmac($request), 403, 'HMAC failed');
   
        // In callback()
        Log::info('OAuth State Check', [
            'session_state' => session('shopify_oauth_state'),
            'request_state' => $request->query('state')
        ]);

        // If session is empty (common in iframes), you may need to rely on HMAC only for the dev phase
        if (!session()->has('shopify_oauth_state') && app()->environment('local')) {
            Log::warning('Session state missing, bypassing for local development.');
        } else {
            abort_unless(
                $request->query('state') === session('shopify_oauth_state'),
                403,
                'Invalid state'
            );
        }

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
        Log::info('after updateorcreate', [
            'shop' => $shop,
            'access_token' => $accessToken,
            'scope' => $data['scope'] ?? null,
        ]);

        // return view('shopify-success', ['shop' => $shop]);
        $host = $request->query('host');

        return redirect()->route('home', [
            'shop' => $shop,
            'host' => $host
        ]);
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
        $shop = $request->query('shop');
        $host = $request->query('host'); // Capture the host

        if (!$shop) {
            $shop = Shop::latest()->value('shop_domain');
        }

        if ($request->has('hmac') && !$this->verifyHmac($request)) {
            abort(403, 'Unauthorized action.');
        }

        // Pass both shop and host to the view
        return response()
            ->view('index', [
                'shop' => $shop,
                'host' => $host
            ])
            ->header('Content-Security-Policy', "frame-ancestors https://admin.shopify.com https://{$shop} https://*.myshopify.com;");
    }


    public function products($shop)
    {
        $shopData = Shop::where('shop_domain', $shop)->first();

        if (!$shopData) {
            abort(404, 'Shop not found');
        }

        $query = '{
            products(first: 15, sortKey: CREATED_AT, reverse: true) {
                edges {
                    node {
                        id
                        title
                        handle
                        status
                        createdAt
                    }
                }
            }
       }';

        $response = ShopifyService::query($shop, $shopData->access_token, $query);
        $result = $response->json();

        // ERROR HANDLING: Don't use back() here if it's a GET request
        if (!isset($result['data'])) {
            Log::error('Shopify API Error', ['response' => $result]);
            // If it fails, show an error on a specific view or abort
            return response("Shopify API Error: " . ($result['errors'][0]['message'] ?? 'Unknown'), 500);
        }

        // Assign the correct variable for the view
        $products = $result['data']['products'];

        return view('products', compact('products', 'shop'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'shop' => 'required'
        ]);

        $shop = $request->shop;

        $shopData = Shop::where('shop_domain', $shop)->first();

        if (!$shopData) {
            return back()->with('error', 'Shop not found');
        }

        $mutation = '
            mutation productCreate($input: ProductInput!) {
            productCreate(input: $input) {
                product {
                id
                title
                }
                userErrors {
                field
                message
                }
            }
            }';

        $variables = [
            "input" => [
                "title" => $request->title,
                "descriptionHtml" => $request->description,
                "status" => "ACTIVE"
            ]
        ];

        $response = ShopifyService::query($request->shop, $shopData->access_token, $mutation, $variables);
        $result = $response->json();

        // Check for GraphQL-specific UserErrors
        if (!empty($result['data']['productCreate']['userErrors'])) {
            return back()->withErrors($result['data']['productCreate']['userErrors']);
        }

        return back()->with('success', 'Product created successfully');
    }
}
