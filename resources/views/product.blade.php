<h2>Shopify Products</h2>

@foreach($products['data']['products']['edges'] as $item)
    <p>{{ $item['node']['title'] }}</p>
@endforeach