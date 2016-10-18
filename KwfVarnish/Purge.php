<?php
class KwfVarnish_Purge
{
    public static function purgeMediaAssets($relativeUrl)
    {
        if (Kwf_Config::getValue('varnish.purge.assetsMediaIgnoreHost')) {
            self::purge('http://'.Kwf_Config::getValue('server.domain').$relativeUrl);
        } else {
            foreach (KwfVarnish_Purge::getVarnishDomains() as $domain) {
                self::purge('http://'.$domain.$relativeUrl);
            }
        }
    }
    public static function purge($url)
    {
        if (!Kwf_Config::getValue('varnish.purge.method')) {
            throw new Kwf_Exception('varnish.purge.method is not set');
        }
        if (Kwf_Config::getValue('varnish.purge.method') == 'url') {
            $url = parse_url($url);
            $url = $url['scheme'].'://'.$url['host'].(isset($url['port']) ? ':'.$url['port'] : '')
                .'/purge-url'.$url['path'].(isset($url['query']) ? '?'.$url['query'] : '');
        }
        $headers = array();
        if (Kwf_Config::getValue('varnish.purge.host')) {
            $url = parse_url($url);
            $headers['Host'] = $url['host'].(isset($url['port']) ? ':'.$url['port'] : '');
            $url = 'http://'.Kwf_Config::getValue('varnish.purge.host').(Kwf_Config::getValue('varnish.purge.port') ? ':'.Kwf_Config::getValue('varnish.purge.port') : '')
                .$url['path'].(isset($url['query']) ? '?'.$url['query'] : '');
        }
        $config = array();
        if (Kwf_Config::getValue('http.proxy.host')) {
            $config['proxy_host'] = Kwf_Config::getValue('http.proxy.host');
            $config['proxy_port'] = Kwf_Config::getValue('http.proxy.port');
        }
        $c = new Zend_Http_Client($url, $config);
        $c->setHeaders($headers);
        if (Kwf_Config::getValue('varnish.purge.method') == 'purge') {
            $c->setMethod('PURGE');
        }
        if (Kwf_Config::getValue('varnish.purge.user')) {
            $c->setAuth(Kwf_Config::getValue('varnish.purge.user'), Kwf_Config::getValue('varnish.purge.password'));
        }
        $response = $c->request();
        if ($response->isError()) {
            throw new Kwf_Exception('purge failed: '.$response->getStatus().' '.substr($response->getBody(), 0, 150));
        }
    }

    public static function getVarnishDomains()
    {
        if (Kwf_Config::getValue('varnish.mode') == 'assetsMedia') {
            $domains = array();
            if (Kwf_Config::getValue('varnish.domain')) {
                $domains[] = Kwf_Config::getValue('varnish.domain');
            }
            foreach (Kwf_Config::getValueArray('kwc.domains') as $i) {
                if (isset($i['varnish']['domain']) && $i['varnish']['domain'] && !in_array($i['varnish']['domain'], $domains)) {
                    $domains[] = $i['varnish']['domain'];
                }
            }
            return $domains;
        } else {
            $domains = array();
            if (Kwf_Config::getValue('server.domain')) {
                $domains[] = Kwf_Config::getValue('server.domain');
            }
            if (Kwf_Config::getValue('server.preliminaryDomain')) {
                $domains[] = Kwf_Config::getValue('server.preliminaryDomain');
            }
            foreach (Kwf_Config::getValueArray('kwc.domains') as $i) {
                if (isset($i['domain']) && $i['domain'] && !in_array($i['domain'], $domains)) {
                    $domains[] = $i['domain'];
                }
                if (isset($i['preliminaryDomain']) && $i['preliminaryDomain'] && !in_array($i['preliminaryDomain'], $domains)) {
                    $domains[] = $i['preliminaryDomain'];
                }
            }
            return $domains;
        }
    }
}
