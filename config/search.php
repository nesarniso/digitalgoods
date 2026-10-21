<?php

return [

    // 'database' works on both MySQL and sqlite via query builder LIKE
    // clauses — no external service required to run this engine. Swap to
    // 'meilisearch' or 'elasticsearch' later by adding a driver class and
    // changing this value; nothing in Domain/Catalog should change.
    'driver' => env('SEARCH_DRIVER', 'database'),

];
