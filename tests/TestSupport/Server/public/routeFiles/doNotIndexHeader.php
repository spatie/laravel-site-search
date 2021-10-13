<?php

$router->get('/', fn () =>
<<<HTML
        Here is the homepage
        <a href="/do-not-index">Next page</a>
    HTML
);
$router->get('/do-not-index', fn () => response(
    'Here is the next page',
    headers: ['site-search-do-not-index' => '']
));
