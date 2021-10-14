<?php

$router->get('/', fn () => view('chain/1'));
$router->get('2', fn () => view('invalidHtml'));
$router->get('3', fn () => view('chain/3'));
