---
title: Preventing content from being indexed
weight: 4
---

Your site probably displays a lot of information that should not be indexed, such as your menu structure or footer. Or maybe entire pages do not need to be indexed.

## Using CSS selectors

In the `ignore_content_by_css_selector` key of the `site-search` config file, you can specify CSS selectors of elements that should not be indexed. By default, the content of a `nav` element will not be put in the index (but all URLs inside it will still be crawled).

Additionally, any elements with a `data-no-index` will not get indexed. In the following example, the sentence "This will not be indexed", will not be indexed.

```html
<html>
    <head>
        <title>This is my page</title>
        <meta name="description" content="This is the description">
    </head>
    <body>
        <nav>
            This is the content of the nav. It should not be indexed.
        </nav>

        <h1>This is the H1</h1>

        This is the content

        <div data-no-index>
            This will not be indexed.
        </div>
    </body>
</html>
```

## Using the config file

In the `ignore_content_on_urls` key of the `site-search` config file, you may specify URLs whose content should not be indexed. All links on these URLs will still be followed and crawled.

## Using headers

If your site's response contains a header whose name is in the `do_not_index_content_headers` of the `site-search` config file, then that page will not be indexed.

## Using a search profile

A search profile is a class that determines what pages get crawled and what content gets indexed. Learn more about search profiles [here](/docs/laravel-site-search/v1/basic-usage/using-a-search-profile).
