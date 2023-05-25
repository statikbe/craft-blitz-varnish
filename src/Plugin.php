<?php

namespace statikbe\blitzvarnish;

use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use putyourlightson\blitz\helpers\CachePurgerHelper;
use yii\base\Event;

class Plugin extends BasePlugin
{
    public function init(): void
    {
        parent::init();

        Event::on(
            CachePurgerHelper::class,
            CachePurgerHelper::EVENT_REGISTER_PURGER_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = VarnishCachePurger::class;
            }
        );
    }
}