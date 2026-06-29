<?php

return [
    'secret' => env('HMAC_SECRET', 'default_secret'),
    'algo' => env('HMAC_ALGO', 'sha256'),
    'header_signature' => env('HMAC_HEADER_SIGNATURE', 'X-HMAC'),
];

