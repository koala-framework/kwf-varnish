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
            'event' => 'Kwf_Events_Event_FetchClearCacheTypes',
            'callback' => 'onFetchClearCacheTypes'
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
        if ($ev->subroot) {
            $varnishDomain = $ev->subroot->getBaseProperty('varnish.domain');
            if ($varnishDomain) {
                $ev->prefix = '//'.$varnishDomain;
            }
        }
    }

    public function onMediaChanged(Kwf_Events_Event_Media_Changed $ev)
    {
        if (Kwf_Config::getValue('varnish.mode') == 'assetsMedia') {
            $varnishDomain = $ev->component->getBaseProperty('varnish.domain');
            if ($varnishDomain) {
                $url = 'http://'.$varnishDomain.'/media/'.$ev->class.'/'.$ev->component->componentId.'/*';
                KwfVarnish_Purge::purge($url);
            }
        } else {
            $domainCmp = $ev->component->getDomainComponent();
            $url = 'http://'.$domainCmp->getDomain().'/media/'.$ev->class.'/'.$ev->component->componentId.'/*';
            KwfVarnish_Purge::purge($url);

            $preliminaryDomain = $domainCmp->getBaseProperty('preliminaryDomain');
            if ($preliminaryDomain) {
                $url = 'http://'.$preliminaryDomain.'/media/'.$ev->class.'/'.$ev->component->componentId.'/*';
                KwfVarnish_Purge::purge($url);
            }
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

    public function onClearFullPage(Kwf_Component_Event_ViewCache_ClearFullPage $ev)
    {
        if (!$ev->domainComponentId) return;
        $domainCmp = Kwf_Component_Data_Root::getInstance()
            ->getComponentById($ev->domainComponentId, array('ignoreVisible'=>true));
        $domain = $domainCmp->getDomain();
        $preliminaryDomain = $domainCmp->getBaseProperty('preliminaryDomain');
        foreach ($ev->urls as $url) {
            KwfVarnish_Purge::purge('http://'.$domain.$url);
            if ($preliminaryDomain) {
                KwfVarnish_Purge::purge('http://'.$preliminaryDomain.$url);
            }
        }
    }
}
