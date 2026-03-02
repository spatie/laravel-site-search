<?php

return [
    '/docs' => ['body' => "Here is the docs page\n\n<a href=\"/docs/sub-page\">Continue</a>\n<a href=\"/\">Back to home</a>\n<a href=\"/support\">Support</a>"],
    '/docs/sub-page' => ['body' => 'Here is a sub page of the docs'],
    '/' => ['body' => 'Here is the homepage'],
    '/support' => ['body' => 'Here is the support page'],
];
