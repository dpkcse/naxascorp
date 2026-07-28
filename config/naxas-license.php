<?php

return [
    'enabled' => env('NAXAS_LICENSE_ENABLED', true),
    'server_url' => env('NAXAS_LICENSE_SERVER_URL', 'https://license.example.com'),
    'product' => env('NAXAS_LICENSE_PRODUCT', 'naxora-cms'),
    'license_type' => env('NAXAS_LICENSE_TYPE', 'single_site'),
    'public_key_path' => env('NAXAS_LICENSE_PUBLIC_KEY_PATH'),
    'trusted_keys' => [],
    'timeout_seconds' => (int) env('NAXAS_LICENSE_TIMEOUT_SECONDS', 10),
    'connect_timeout_seconds' => (int) env('NAXAS_LICENSE_CONNECT_TIMEOUT_SECONDS', 5),
    'max_response_bytes' => (int) env('NAXAS_LICENSE_MAX_RESPONSE_BYTES', 131072),
    'allow_local_http' => (bool) env('NAXAS_LICENSE_ALLOW_LOCAL_HTTP', false),
    'trusted_local_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('NAXAS_LICENSE_TRUSTED_LOCAL_HOSTS', '127.0.0.1,localhost,::1'))))),
];
