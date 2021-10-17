---
title: Introduction
weight: 1
---

**THIS PACKAGE IS STILL IN DEVELOPMENT, DO NOT USE IN PRODUCTION (YET)**

This package can crawl and index your entire site. You can think of it as a private Google search. What gets crawled and indexed can be highly customized. Under the hood, Meilisearch is used to provide blazing fast search speeds.

When crawling your site, multiple concurrent connections are used to speed up the crawling process.

## How does this package differ from Laravel Scout?

[Laravel Scout](https://laravel.com/docs/8.x/scout) is an excellent package to add search capabilities for Eloquent models. In most cases, this is very useful if you want to provide a structured search. For example, if you have a `Product` model, Scout can help to build up a search index to search the properties of these products.

Our laravel-site-search package is not tied to Eloquent models. Like Google, it will crawl your entire site and index all content that is there.

## How does this package differ from Algolia Docsearch?

[Algolia Docsearch](https://laravel.com/docs/8.x/scout) is an awesome solution for adding search capabilities to open-source documentation. 

Our laravel-site-search package may be used to index non-open-source stuff as well. Where Docsearch makes basic assumptions on how the content is structured, our package tries to make a best effort to index all kinds of content.

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-site-search/releases"><img src="https://img.shields.io/github/release/spatie/laravel-site-search.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-site-search/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://packagist.org/packages/spatie/laravel-site-search"><img src="https://img.shields.io/packagist/dt/spatie/laravel-site-search.svg?style=flat-square" alt="Total Downloads"></a>
</section>
