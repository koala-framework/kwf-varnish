= Varnish Cache Integration for Koala Framework

Adds support for Caching asset and media urls thru a varnish cache.

Those urls will use a different domain (varnish.domain). Proxying the whole page thru varnish is currently not supported.

== Installation

- add to config.ini:

    `eventSubscribers.varnish = KwfVarnish_Events`
    `clearCacheTypes.varnishAssets = KwfVarnish_ClearCacheTypeAssets`

- configure varnish.domain baseProperty:
    - one cdn domain: `varnish.domain = cdn.example.com`
    - multi domain web with different cdn domains: `kwc.domains.com.varnish.domain = cdn.example.com`
