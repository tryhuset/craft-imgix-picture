<?php

/**
 * Imgix plugin for Craft CMS 3.x
 *
 * Use Imgix with Craft
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace apt\craftimgixpicture\services;

use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\helpers\Assets as AssetsHelper;
use GuzzleHttp\Exception\RequestException;
use Imgix\UrlBuilder;
use apt\craftimgixpicture\CraftImgixPicture;

use Craft;
use craft\base\Component;
use apt\craftimgixpicture\jobs\PurgeUrlsJob;
use apt\craftimgixpicture\models\ImgixModel;
use apt\craftimgixpicture\models\Settings;

/**
 * @author    Superbig
 * @package   Imgix
 * @since     2.0.0
 */
class Imgix extends Component
{
    // Public Methods
    // =========================================================================

    const IMGIX_PURGE_ENDPOINT_OLD = 'https://api.imgix.com/v2/image/purger';
    const IMGIX_PURGE_ENDPOINT = 'https://api.imgix.com/api/v1/purge';

    protected $builder;

    /**
     * @var Settings
     */
    private $settings;

    public function init() : void
    {
        parent::init();

        $this->settings = CraftImgixPicture::$plugin->getSettings();
    }

    /**
     * @param null  $asset
     * @param null  $transforms
     * @param array $defaultOptions
     *
     * @return null|ImgixModel
     */
    public function transformImage($asset = null, $transforms = null, $defaultOptions = [])
    {
        if (!$asset) {
            return null;
        }

        $options = $defaultOptions;

        unset($options['fm']);

        $pathsModel = new ImgixModel($asset, $transforms, $options);

        return $pathsModel;
    }

    /**
     * @param null  $asset
     * @param null  $transforms
     * @param array $defaultOptions
     *
     * @return null|ImgixModel
     */
    public function transformVideo($asset = null, $style = [], $imgixOptions = [], $options = [])
    {
        if (!$asset) {
            return null;
        }

        $options = array_merge($imgixOptions, $style, $options);
        $imgixOptionsDownload = $imgixOptions['download'] ?? [];
        $styleDownload = $style['download'] ?? [];
        $optionsDownload = $options['download'] ?? [];

        if (isset($options['download'])) {
            $options['download'] = array_merge($imgixOptionsDownload, $styleDownload, $optionsDownload);
        }

        $imgixOptionsStoryboard = $imgixOptions['storyboard'] ?? [];
        $styleStoryboard = $style['storyboard'] ?? [];
        $optionsStoryboard = $options['storyboard'] ?? [];

        if (isset($options['storyboard'])) {
            $options['storyboard'] = array_merge($imgixOptionsStoryboard, $styleStoryboard, $optionsStoryboard);
        }

        $imgixOptionsGif = $imgixOptions['gif'] ?? [];
        $styleGif = $style['gif'] ?? [];
        $optionsGif = $options['gif'] ?? [];

        if (isset($options['gif'])) {
            $options['gif'] = array_merge($imgixOptionsGif, $styleGif, $optionsGif);
        }

        $pathsModel = new ImgixModel($asset, null, $options);

        return $pathsModel;
    }

    /**
     * @param Asset $asset
     */
    public function onSaveAsset(Asset $asset)
    {
        $url = $this->getImgixUrl($asset);

        Craft::debug(
            'Getting url: ' . $url,
            __METHOD__
        );

        if ($url) {
            $job = new PurgeUrlsJob();
            $job->urls = [$this->getImgixUrl($asset)];

            Craft::$app->getQueue()->push($job);
        }
    }

    /**
     * @param Asset $asset
     */
    public function onDeleteAsset(Asset $asset)
    {
        $url = $this->getImgixUrl($asset);

        if ($url) {
            $job = new PurgeUrlsJob();
            $job->urls = [$this->getImgixUrl($asset)];

            Craft::$app->getQueue()->push($job);
        }
    }

    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function purge(Asset $asset)
    {
        $url = $this->getImgixUrl($asset);

        Craft::debug(
            Craft::t(
                'craft-imgix-picture',
                'Purging asset #{id}: {url}',
                ['id' => $asset->id, 'url' => $url]
            ),
            'craft-imgix-picture'
        );

        return $this->purgeUrl($url);
    }

    /**
     * @param null $url
     *
     * @return bool
     */
    public function purgeUrl($url = null)
    {
        $apiKey = $this->settings->getApiKey();
        $isOldKey = strlen($apiKey) < 50;

        Craft::debug(
            Craft::t(
                'craft-imgix-picture',
                'Purging asset: {url}',
                ['url' => $url]
            ),
            'craft-imgix-picture'
        );

        try {
            $client = Craft::createGuzzleClient(['timeout' => 30, 'connect_timeout' => 30]);
            $endpoint = $isOldKey ? self::IMGIX_PURGE_ENDPOINT_OLD : self::IMGIX_PURGE_ENDPOINT;
            $config = $isOldKey ? [
                'auth' => [
                    $apiKey, '',
                ],
                'form_params' => [
                    'url' => $url,
                ],
            ] : [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                ],
                'json' => [
                    'data' => [
                        'attributes' => [
                            'url' => $url,
                        ],
                        'type' => 'purges',
                    ],
                ],
            ];
            $response = $client->post($endpoint, $config);

            Craft::debug(
                Craft::t(
                    'craft-imgix-picture',
                    'Purged asset: {url} - Status code {statusCode}',
                    [
                        'url' => $url,
                        'statusCode' => $response->getStatusCode(),
                    ]
                ),
                'craft-imgix-picture'
            );

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 400;
        } catch (RequestException $e) {
            Craft::error(
                Craft::t(
                    'craft-imgix-picture',
                    'Failed to purge {url}: {statusCode} {error}',
                    [
                        'url' => $url,
                        'error' => $e->getMessage(),
                        'statusCode' => $e->getResponse()->getStatusCode(),
                    ]
                ),
                'craft-imgix-picture'
            );

            return false;
        }
    }

    /**
     * @param Asset $asset
     *
     * @return null|string
     */
    public function getImgixUrl(Asset $asset)
    {
        $url = null;
        $domains = $this->settings->domains;
        $volume = $asset->getVolume();

        $assetUrl = AssetsHelper::generateUrl($asset->fs, $asset);

        $assetUri = parse_url($assetUrl, PHP_URL_PATH);

        if (isset($domains[$volume->handle])) {
            $builder = new UrlBuilder($domains[$volume->handle]);

            $builder->setUseHttps(true);

            if ($token = $this->settings->signedToken)
                $builder->setSignKey($token);
            $url = UrlHelper::stripQueryString($builder->createURL($assetUri));
        }


        return $url;
    }
}
