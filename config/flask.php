<?php

return [
    'base_url' => env('FLASK_BASE_URL', 'http://localhost:5000'),

    'predict_endpoint' => env('FLASK_PREDICT_ENDPOINT', '/predict'),
    'health_endpoint' => env('FLASK_HEALTH_ENDPOINT', '/health'),
    'jurusan_endpoint' => env('FLASK_JURUSAN_ENDPOINT', '/jurusan'),
    'dashboard_stats_endpoint' => env('FLASK_DASHBOARD_STATS_ENDPOINT', '/dashboard-stats'),
    'info_model_endpoint' => env('FLASK_INFO_MODEL_ENDPOINT', '/info-model'),
    'feature_importance_endpoint' => env('FLASK_FEATURE_IMPORTANCE_ENDPOINT', '/feature-importance'),
    'evaluation_endpoint' => env('FLASK_EVALUATION_ENDPOINT', '/evaluation'),

    'timeout' => env('FLASK_TIMEOUT', 30),
];