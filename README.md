= Varnish Cache Integration for Koala Framework

Adds support for Caching asset and media urls thru a varnish cache.

Those urls will use a different domain (varnishDomain). Proxying the whole page thru varnish is currently not supported.

== Installation

- add to config.ini: `eventSubscribers.varnish = KwfVarnish_Events`
- configure varnishDomain baseProperty, eg: `kwc.domains.com.varnishDomain = cdn.example.com`
