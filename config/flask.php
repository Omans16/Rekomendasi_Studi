<?php
return [
    'base_url' => env('FLASK_BASE_URL', 'http://localhost:5000'),
    'predict_endpoint' => env('FLASK_PREDICT_ENDPOINT', '/predict'),
    'health_endpoint' => env('FLASK_HEALTH_ENDPOINT', '/health'),
    'timeout' => env('FLASK_TIMEOUT', 30),
];
