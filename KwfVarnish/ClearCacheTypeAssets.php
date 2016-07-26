<?php
class KwfVarnish_ClearCacheTypeAssets extends Kwf_Util_ClearCache_Types_Abstract
{
    protected function _clearCache($options)
    {
        foreach (KwfVarnish_Purge::getVarnishDomains() as $domain) {
            KwfVarnish_Purge::purge('http://'.$domain.'/assets/*');
        }
    }

    public function getTypeName()
    {
        return 'assetsVarnish';
    }

    public function doesRefresh() { return false; }
    public function doesClear() { return true; }
}
