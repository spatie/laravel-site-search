<?php

$router->get(
    '/',
    fn () =>
<<<HTML
        Here is the homepage
        <a href="/page?with=query">Next page</a>
    HTML
);
$router->get('/page', fn () => response(
    'Page with query',
));
