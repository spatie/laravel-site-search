<?php

return [
    '/' => ['body' => 'Here is the homepage <a href="/do-not-index">Next page</a>'],
    '/do-not-index' => [
        'body' => 'Here is the next page',
        'headers' => ['site-search-do-not-index' => ''],
    ],
];
