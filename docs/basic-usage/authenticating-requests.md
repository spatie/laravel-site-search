---
title: Authenticating requests
weight: 6
---

Meilisearch exposes its API to create indexes, update settings, and retrieve results on HTTP port 7700. If you publicly expose that port, we highly recommend using [Meilisearch built-in authentication features](https://docs.meilisearch.com/reference/features/authentication.html#key-types) to prevent unauthorized persons making request against your Meilisearch installation.

In the Meilisearch docs you'll find [how to start Meilisearch with a master password](https://docs.meilisearch.com/reference/features/authentication.html#key-types) and how to retrieve the API Keys.

You can specify the API Key (can be public or private key) that this package should use by adding a `meilisearch.apiKey` JSON value to the `extra` attribute in the `site_search_configs` table. Here's how that would look like:

```json
{"meilisearch":{"apiKey":"your-private-or-public-api-key"}}
```
