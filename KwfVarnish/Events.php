<?php
class KwfVarnish_Events extends Kwf_Events_Subscriber
{
    public function getListeners()
    {
        $ret = parent::getListeners();
        if (!Kwf_Config::getValue('varnish.mode')) {
            throw new Kwf_Exception("varnish.mode setting is required");
        }
        if (Kwf_Config::getValue('varnish.mode') == 'assetsMedia') {
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
        }
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

        if (Kwf_Config::getValue('varnish.mode') == 'full') {
            $ret[] = array(
                'class' => null,
                'event' => 'Kwf_Component_Event_ViewCache_ClearFullPage',
                'callback' => 'onClearFullPage'
            );
        }
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
        if (Kwf_Config::getValue('varnish.mode') == 'assetsMedia') {
            $varnishDomain = $ev->component->getBaseProperty('varnish.domain');
            if ($varnishDomain) {
                $url = 'http://'.$varnishDomain.'/media/'.rawurlencode($ev->class).'/'.$ev->component->componentId.'/*';
                KwfVarnish_Purge::purge($url);
            }
        } else {
            $url = '/media/'.rawurlencode($ev->class).'/'.$ev->component->componentId.'/*';
            KwfVarnish_Purge::purgeMediaAssets($url);
        }
    }

    public function onMediaClearAll(Kwf_Events_Event_Media_ClearAll $ev)
    {
        KwfVarnish_Purge::purgeMediaAssets('/media/*');
    }

    public function onClearFullPage(Kwf_Component_Event_ViewCache_ClearFullPage $ev)
    {
        if (!$ev->domainComponentId) return;
        $domainCmp = Kwf_Component_Data_Root::getInstance()
            ->getComponentById($ev->domainComponentId, array('ignoreVisible'=>true));
        $domain = $domainCmp->getDomain();
        foreach ($ev->urls as $url) {
            KwfVarnish_Purge::purge('http://'.$domain.$url);
        }
    }

}
