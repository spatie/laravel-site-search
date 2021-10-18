---
title: Customizing Meilisearch settings
weight: 4
---

When using Meilisearch, sane defaults are used to connect and create an index. These defaults can be customized.

Most customizations can be done by adding values to the `extra` field of a row of the `site_search_configs` table. This field must be either `null` or contain valid JSON.  

## Using an alternative URL

When using the Meilisearch driver, we'll try to connect to `http://localhost:7700` by default. 

You can customize this by adding a `meilisearch.url` JSON value to the `extra` attribute in the `site_search_configs` table. Here's how that would look like:

```json
{"meilisearch":{"url":"https:\/\/your-custom-domain-and-port.com:1234"}}
```

### Modifying index settings

A Meilisearch index has [various interesting settings](https://docs.meilisearch.com/reference/features/settings.html#settings) that allow you [which fields are searchable](https://docs.meilisearch.com/reference/features/settings.html#searchable-attributes), [specify ranking rules](https://docs.meilisearch.com/reference/features/settings.html#ranking-rules), and even [add synonyms](https://docs.meilisearch.com/reference/features/settings.html#synonyms).

Every time a site is crawled, a new index is created. You can customize the settings that are used for this index in two ways.

The first way would be by adding a `meilisearch.indexSettings` JSON value to the `extra` attribute in the `site_search_configs`. In `meilisearch.indexSettings` you can put any of [the list settings that Meilisearch provides](https://docs.meilisearch.com/reference/features/settings.html#settings).

Here's how that value you would put in `extra` if you only want results based on the `url` and `description` fields in the index.

```json
{
    "meilisearch": {
        "indexSettings": {
            "searchableAttributes": [
                "url",
                "description"
            ]
        }
    }
}
```

Here's another example where we are going to add a synonym for the word "computer". You can read more about [how synonyms can be configured](https://docs.meilisearch.com/reference/features/synonyms.html) in the Meilisearch docs.

```json
{
   "meilisearch":{
      "indexSettings":{
         "synonyms":{
            "Macintosh":[
               "computer"
            ]
         }
      }
   }
}
```

The second way to customize index settings would be by leveraging the `Spatie\SiteSearch\Events\NewIndexCreatedEvent`. This event is fired whenever a new index is created. It has two properties:

- the name of the created Meilisearch object
- an instance of `Spatie\SiteSearch\Models\SiteSearchConfig`

You can use these properties to make an API call of your own to Meilisearch to customize [any of the available settings](https://docs.meilisearch.com/reference/features/settings.html#settings).
