<?php

namespace statikbe\blitzvarnish;

use Craft;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\drivers\purgers\BaseCachePurger;
use putyourlightson\blitz\events\RefreshCacheEvent;
use putyourlightson\blitz\helpers\CachePurgerHelper;
use putyourlightson\blitz\helpers\SiteUriHelper;
use yii\log\Logger;

class VarnishCachePurger extends BaseCachePurger
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('blitz', 'Varnish Cache purger');
    }

    /**
     * @inheritdoc
     */
    public function purgeUris(array $siteUris, callable $setProgressHandler = null, bool $queue = true): void
    {
        $event = new RefreshCacheEvent(['siteUris' => $siteUris]);
        $this->trigger(self::EVENT_BEFORE_PURGE_CACHE, $event);

        if (!$event->isValid) {
            return;
        }

        if ($queue) {
            CachePurgerHelper::addPurgerJob($siteUris, 'purgeUrisWithProgress');
        } else {
            $this->purgeUrisWithProgress($siteUris, $setProgressHandler);
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_PURGE_CACHE)) {
            $this->trigger(self::EVENT_AFTER_PURGE_CACHE, $event);
        }
    }

    /**
     * @inheritdoc
     */
    public function purgeSite(int $siteId, callable $setProgressHandler = null, bool $queue = true): void
    {
        $this->purgeUris(SiteUriHelper::getSiteUrisForSite($siteId), $setProgressHandler, $queue);
    }

    /**
     * @inheritdoc
     */
    public function purgeAll(callable $setProgressHandler = null, bool $queue = true): void
    {
        $event = new RefreshCacheEvent();
        $this->trigger(self::EVENT_BEFORE_PURGE_ALL_CACHE, $event);

        if (!$event->isValid) {
            return;
        }

        $sites = Craft::$app->getSites()->getAllSites();

        foreach ($sites as $site) {
            $this->purgeSite($site->id, $setProgressHandler, $queue);
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_PURGE_ALL_CACHE)) {
            $this->trigger(self::EVENT_AFTER_PURGE_ALL_CACHE, $event);
        }
    }

    /**
     * @inheritdoc
     */
    public function purgeUrisWithProgress(array $siteUris, callable $setProgressHandler = null): void
    {
        $count = 0;
        $total = count($siteUris);
        $label = 'Purging {total} pages.';

        if (is_callable($setProgressHandler)) {
            $progressLabel = Craft::t('blitz', $label, ['total' => $total]);
            call_user_func($setProgressHandler, $count, $total, $progressLabel);
        }

        $groupedSiteUris = SiteUriHelper::getSiteUrisGroupedBySite($siteUris);

        foreach ($groupedSiteUris as $siteId => $siteUriGroup) {
            $this->_sendRequest('PURGE', $siteId,
                SiteUriHelper::getUrlsFromSiteUris($siteUriGroup)
            );

            $count = $count + count($groupedSiteUris);

            if (is_callable($setProgressHandler)) {
                $progressLabel = Craft::t('blitz', $label, ['total' => $total]);
                call_user_func($setProgressHandler, $count, $total, $progressLabel);
            }
        }
    }

    private function _sendRequest($method = "PURGE", $siteId, $urls = [])
    {
        if (!empty($urls)) {
            $batches = array_chunk($urls, 25);

            foreach ($batches as $batch) {
                foreach ($batch as $uri) {
                    $requests[] = new Request($method, $uri, []);
                }
            }
        }

        $site = \Craft::$app->getSites()->getSiteById($siteId);
        $client = Craft::createGuzzleClient([
            'base_uri' => $site->getBaseUrl(),
        ]);


        // Create a pool of requests
        $pool = new Pool($client, $requests, [
            'fulfilled' => function () use (&$response) {
                $response = true;
            },
            'rejected' => function ($reason) {
                if ($reason instanceof RequestException) {
                    /** RequestException $reason */
                    preg_match('/^(.*?)\R/', $reason->getMessage(), $matches);
                    if (!empty($matches[1])) {
                        Blitz::$plugin->log(trim($matches[1], ':'), [], Logger::LEVEL_ERROR);
                    }
                }
            },
        ]);

        // Initiate the transfers and wait for the pool of requests to complete
        $pool->promise()->wait();

        return $response;
    }
}