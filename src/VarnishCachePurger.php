<?php

namespace statikbe\blitzvarnish;

use putyourlightson\blitz\drivers\purgers\BaseCachePurger;

class VarnishCachePurger extends BaseCachePurger
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('blitz', 'Varnish Cache purger');
    }
}