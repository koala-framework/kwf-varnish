= Varnish Cache Integration for Koala Framework

Adds support for varnish reverse proxy cache.

== Config

Config settings

- `eventSubscribers.varnish = KwfVarnish_Events`
- `varnish.mode = full` or `assetsMedia` or `false`
- `varnish.purge.assetsMediaIgnoreHost = false`
- `varnish.purge.method = url` (for GET /purge-url/$url) or `method` (for PURGE /$url)
- `varnish.purge.user = example` (optional)
- `varnish.purge.password = example` (optional)
- `varnish.purge.host = varnish.example.com` (optional)
- `varnish.purge.port = 80` (optional)


== Mode `assetsMedia`

Cache asset and media urls thru a varnish cache using a different domain, also called cdn domain.

=== Installation

- configure varnish.domain baseProperty:
    - one cdn domain: `varnish.domain = cdn.example.com`
    - multi domain web with different cdn domains: `kwc.domains.com.varnish.domain = cdn.example.com`

== Mode `full`

Proxy the whole page including all assets media and html thru varnish. The webserver isn't accessible, only varnish.

== assetsMediaIgnoreHost

Speeds up clearing cache of assets and media urls.

Requires the following vcl_hash implementation

    sub vcl_hash {
        hash_data(req.url);

        if (req.url !~ "^/(assets|media)/") { #don't hash host for assets and media urls
            if (req.http.host) {
                hash_data(req.http.host);
            } else {
                hash_data(server.ip);
            }
        }

        return (lookup);
    }
