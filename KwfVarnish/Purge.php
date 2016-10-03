<?php
class KwfVarnish_Purge
{
    public static function purge($url)
    {
        $url = parse_url($url);
        $url = $url['scheme'].'://'.$url['host'].'/purge-url'.$url['path'].($url['path'] ? '?'.$url['path'] : '');
        $config = array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl'
        );
        if (Kwf_Config::getValue('http.proxy.host')) {
            $config['proxy_host'] = Kwf_Config::getValue('http.proxy.host');
            $config['proxy_port'] = Kwf_Config::getValue('http.proxy.port');
        }
        $c = new Zend_Http_Client($url, $config);
        if (Kwf_Config::getValue('varnish.purge.user')) {
            $c->setAuth(Kwf_Config::getValue('varnish.purge.user'), Kwf_Config::getValue('varnish.purge.password'));
        }
        $response = $c->request();
        if ($response->isError()) {
            throw new Kwf_Exception('purge failed: '.$response->getBody());
        }
    }

    public static function getVarnishDomains()
    {
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
    }

}
