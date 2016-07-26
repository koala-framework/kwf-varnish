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
            'event' => 'Kwf_Events_Event_CreateAssetUrls',
            'callback' => 'onCreateAssetUrls'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_Media_Changed',
            'callback' => 'onMediaChanged'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_FetchClearCacheTypes',
            'callback' => 'onFetchClearCacheTypes'
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

        $varnishDomain = $ev->component->getBaseProperty('varnishDomain');
        if ($varnishDomain) {
            $ev->url = '://'.$varnishDomain.$ev->url;
        }
    }

    public function onCreateAssetUrls(Kwf_Events_Event_CreateAssetUrls $ev)
    {
        $varnishDomain = $ev->subroot->getBaseProperty('varnishDomain');
        if ($varnishDomain) {
            $ev->prefix = '://'.$varnishDomain;
        }
    }

    public function onMediaChanged(Kwf_Events_Event_Media_Changed $ev)
    {
        $varnishDomain = $ev->component->getBaseProperty('varnishDomain');
        if ($varnishDomain) {
            $url = 'http://'.$varnishDomain.'/media/'.$ev->class.'/'.$ev->component->componentId.'/*';
            KwfVarnish_Purge::purge($url);
        }
    }

    public function onFetchClearCacheTypes(Kwf_Events_Event_FetchClearCacheTypes $ev)
    {
        $ev->addType(new KwfVarnish_ClearCacheTypeAssets());
    }

    public function onMediaClearAll(Kwf_Events_Event_Media_ClearAll $ev)
    {
        foreach (KwfVarnish_Purge::getVarnishDomains() as $domain) {
            KwfVarnish_Purge::purge('http://'.$domain.'/media/*');
        }
    }
}
