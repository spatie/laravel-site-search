---
title: Using the Meilisearch driver
weight: 6
---

The Meilisearch driver provides blazing fast search with advanced features like synonyms and custom ranking rules. It requires a running Meilisearch instance.

## Setting up the Meilisearch driver

First, update your `config/site-search.php` to use the Meilisearch driver:

```php
'default_driver' => Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
```

Or set the `driver_class` on a specific `SiteSearchConfig` model to use Meilisearch for individual indexes.

Next, require the Meilisearch PHP client:

```bash
composer require meilisearch/meilisearch-php
```

Then, install Meilisearch. Head over to [the Meilisearch docs](https://docs.meilisearch.com/learn/getting_started/installation.html#download-and-launch) to learn how to install it on your system.

Here are the steps for installing it on a Forge provisioned server. You must first download the stable release:

```bash
curl -L https://install.meilisearch.com | sh
```

Next, you must change the ownership and modify permission:

```bash
chmod 755 meilisearch
chown root:root meilisearch
```

After that, move the binary to a system-wide available path:

```bash
sudo mv meilisearch /usr/bin/
```

Finally, you can run the binary and make sure it keeps running. In the Forge Dashboard, click on "Daemons" under "Server Details". Fill out the following for a new daemon:

- Command: `meilisearch --master-key=SOME_MASTER_KEY --env=production --http-addr 0.0.0.0:7700 --db-path ./home/forge/meilifiles`
- User: `forge`
- Directory: leave blank
- Processes: `1`

These instructions were taken from [this gist](https://gist.github.com/josecanhelp/126d627ef125538943f33253d16fc882) by Jose Soto.

## Authenticating requests

Meilisearch exposes its API to create indexes, update settings, and retrieve results on HTTP port 7700. If you publicly expose that port, we highly recommend using [Meilisearch built-in authentication features](https://docs.meilisearch.com/reference/features/authentication.html#key-types) to prevent unauthorized persons making requests against your Meilisearch installation.

To avoid unauthorized access, either block Meilisearch's default port (7700) in your firewall, or use authentication.

In the Meilisearch docs you'll find [how to start Meilisearch with a master password](https://docs.meilisearch.com/reference/features/authentication.html#key-types) and how to retrieve the API Keys.

Here's how you can create a new API key for a given master key:

```bash
curl -X POST 'http://localhost:7700/keys' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <MASTER-KEY>' \
  --data-binary '{
    "description": "Site search key",
    "actions": ["*"],
    "indexes": ["*"],
    "expiresAt": null
  }'
```

You can view all previously created keys with this command:

```bash
curl -X GET 'http://localhost:7700/keys' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <MASTER-KEY>'
```

You can specify the API Key (can be public or private key) that this package should use by adding a `meilisearch.apiKey` JSON value to the `extra` attribute in the `site_search_configs` table:

```json
{"meilisearch": {"apiKey": "your-private-or-public-api-key"}}
```

## Customizing the connection URL

By default, the Meilisearch driver connects to `http://localhost:7700`.

You can customize this by adding a `meilisearch.url` JSON value to the `extra` attribute in the `site_search_configs` table:

```json
{"meilisearch": {"url": "https://your-custom-domain-and-port.com:1234"}}
```

## Customizing index settings

A Meilisearch index has [various interesting settings](https://www.meilisearch.com/docs/learn/configuration/settings) that allow you to specify [which fields are searchable](https://www.meilisearch.com/docs/reference/api/settings#searchable-attributes), [ranking rules](https://www.meilisearch.com/docs/reference/api/settings#ranking-rules), and even [synonyms](https://www.meilisearch.com/docs/learn/configuration/synonyms).

Every time a site is crawled, a new index is created. You can customize the settings that are used for this index in two ways.

The first way would be by adding a `meilisearch.indexSettings` JSON value to the `extra` attribute in the `site_search_configs`. In `meilisearch.indexSettings` you can put any of [the list settings that Meilisearch provides](https://www.meilisearch.com/docs/learn/configuration/settings).

Here's the value you would put in `extra` if you only want results based on the `url` and `description` fields in the index.

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

Here's another example where we are going to add a synonym for the word "computer". You can read more about [how synonyms can be configured](https://www.meilisearch.com/docs/learn/configuration/synonyms) in the Meilisearch docs.

```json
{
    "meilisearch": {
        "indexSettings": {
            "synonyms": {
                "Macintosh": [
                    "computer"
                ]
            }
        }
    }
}
```

The second way to customize index settings would be by leveraging the `Spatie\SiteSearch\Events\NewIndexCreatedEvent`. This event is fired whenever a new index is created. It has two properties:

- the name of the created search index
- an instance of `Spatie\SiteSearch\Models\SiteSearchConfig`

You can use these properties to make an API call of your own to Meilisearch to customize [any of the available settings](https://www.meilisearch.com/docs/learn/configuration/settings).
