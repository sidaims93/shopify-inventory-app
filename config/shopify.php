<?php 

return [
    'client_id' => env('SHOPIFY_CLIENT_ID'),
    'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
    'api_version' => env('SHOPIFY_API_VERSION', '2023-07'),
    'api_scopes' => [
        'write_assigned_fulfillment_orders',
        'write_checkouts',
        'write_customers',
        'read_draft_orders',
        'write_draft_orders',
        'read_fulfillments',
        'write_fulfillments',
        'read_inventory',
        'write_inventory',
        'read_locations',
        'write_orders',
        'write_price_rules',
        'write_products',
        'read_product_listings',
        'write_reports',
        'read_shipping',
        'read_themes',
        'read_third_party_fulfillment_orders',
        'write_third_party_fulfillment_orders'
    ]
];