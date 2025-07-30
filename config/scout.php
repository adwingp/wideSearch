<?php

return [
    'driver' => env('SCOUT_DRIVER', 'database'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => true,
    'after_commit' => false,
    'soft_delete' => false,
    'database' => [
        'indexer' => env('SCOUT_DATABASE_INDEXER', 'mysql'),
        'min_search_length' => 1,
        'min_fulltext_search_length' => 1,
    ],
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
    ],
];
