<?php

use Jaybizzle\CrawlerDetect\CrawlerDetect;

if (!\function_exists('app_get_crawler_detect')) {
    function app_get_crawler_detect(): CrawlerDetect
    {
        static $crawler;

        if (null === $crawler) {
            $crawler = new CrawlerDetect();
        }

        return $crawler;
    }
}
