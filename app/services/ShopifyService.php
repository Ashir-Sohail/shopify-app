<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShopifyService
{
    public static function query($shop, $token, $query, $variables = [])
    {
        $payload = ['query' => $query];

        // ONLY add variables if they are actually provided
        if (!empty($variables)) {
            $payload['variables'] = $variables;
        }

        return Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2024-04/graphql.json", $payload);
    }
}
