<?php
class KwfVarnish_Events extends Kwf_Events_Subscriber
{
    public function getListeners()
    {
        $ret = parent::getListeners();
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Component_Event_CreateMediaUrl',
            'callback' => 'onCreateMediaUrl'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_CreateAssetsPackageUrls',
            'callback' => 'onCreateAssetsPackageUrls'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_CreateAssetUrl',
            'callback' => 'onCreateAssetUrl'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_Media_Changed',
            'callback' => 'onMediaChanged'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_Media_ClearAll',
            'callback' => 'onMediaClearAll'
        );
        return $ret;
    }

    public function onCreateMediaUrl(Kwf_Component_Event_CreateMediaUrl $ev)
    {
        $varnishDomain = $ev->component->getBaseProperty('varnish.domain');
        if ($varnishDomain && $ev->component->isVisible()) {
            $ev->url = '//'.$varnishDomain.$ev->url;
        }
    }

    public function onCreateAssetUrl(Kwf_Events_Event_CreateAssetUrl $ev)
    {
        if ($ev->subroot) {
            $varnishDomain = $ev->subroot->getBaseProperty('varnish.domain');
            if ($varnishDomain) {
                $ev->url = '//'.$varnishDomain.$ev->url;
            }
        }
    }

    public function onCreateAssetsPackageUrls(Kwf_Events_Event_CreateAssetsPackageUrls $ev)
    {
        $varnishDomain = null;
        if ($ev->subroot) {
            $varnishDomain = $ev->subroot->getBaseProperty('varnish.domain');
        } else {
            $varnishDomain = Kwf_Config::getValue('varnish.domain');
        }
        if ($varnishDomain) {
            $ev->prefix = '//'.$varnishDomain;
        }
    }

    public function onMediaChanged(Kwf_Events_Event_Media_Changed $ev)
    {
        $varnishDomain = $ev->component->getBaseProperty('varnish.domain');
        if ($varnishDomain) {
            $url = 'http://'.$varnishDomain.'/media/'.rawurlencode($ev->class).'/'.$ev->component->componentId.'/*';
            KwfVarnish_Purge::purge($url);
        }
    }

    public function onMediaClearAll(Kwf_Events_Event_Media_ClearAll $ev)
    {
        foreach (KwfVarnish_Purge::getVarnishDomains() as $domain) {
            KwfVarnish_Purge::purge('http://'.$domain.'/media/*');
        }
    }
}
