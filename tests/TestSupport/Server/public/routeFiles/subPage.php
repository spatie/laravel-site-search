<?php

$router->get(
    '/docs',
    fn () =>
    <<<HTML
        Here is the docs page

        <a href="/docs/sub-page">Continue</a>
        <a href="/">Back to home</a>
        <a href="/support">Support</a>
    HTML
);

$router->get('/docs/sub-page', fn () => 'Here is a sub page of the docs');
$router->get('/', fn () => 'Here is the homepage');
$router->get('/support', fn () => 'Here is the support page');
