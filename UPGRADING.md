# Upgrading

## From v1 to v2

The only breaking changes is the upgrade from crawler v7 and v8. In v8 of crawler, the observer classes gained a `linkText` parameter. If you have extended the `SearchProfileCrawlObserver` class, you will need to add this parameter to your methods.
