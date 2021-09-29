<?php

return [
    'external_cache_clear' => [
        'path' => '/fr_myra_cloud/external/clear_cache_page',
        'target' => Fr\MyraCloud\Controller\ExternalClearCacheController::class . '::clearPageCache',
    ]
];