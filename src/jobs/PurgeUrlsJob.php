<?php

/**
 * Imgix plugin for Craft CMS 3.x
 *
 * Use Imgix with Craft
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace apt\craftimgixpicture\jobs;

use craft\elements\Asset;
use craft\queue\BaseJob;
use Imgix\UrlBuilder;
use apt\craftimgixpicture\CraftImgixPicture;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Imgix
 * @since     2.0.0
 */
class PurgeUrlsJob extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * URLs to purge
     *
     * @var array
     */
    public $urls = [];

    public function execute($queue): void
    {
        $totalSteps = count($this->urls);

        for ($step = 0; $step < $totalSteps; $step++) {
            $this->setProgress($queue, $step / $totalSteps);
            $url = $this->urls[$step];

            CraftImgixPicture::$plugin->imgix->purgeUrl($url);
        }
    }

    protected function defaultDescription(): string
    {
        return count($this->urls) > 1 ? 'Purging images' : 'Purging image';
    }
}
