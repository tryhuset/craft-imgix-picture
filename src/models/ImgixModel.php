<?php

/**
 * Imgix plugin for Craft CMS 3.x
 *
 * Use Imgix with Craft
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace apt\craftimgixpicture\models;

use craft\elements\Asset;
use craft\helpers\Template;
use Imgix\UrlBuilder;
use apt\craftimgixpicture\CraftImgixPicture;
use craft\helpers\Assets;

use Craft;
use craft\base\Model;
use yii\base\Exception;

/**
 * @author    Superbig
 * @package   Imgix
 * @since     2.0.0
 */
class ImgixModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $transformed = [];

    // Protected Properties
    // =========================================================================

    protected $supportedAttributes = [
        'bri',
        'con',
        'exp',
        'gam',
        'high',
        'hue',
        'invert',
        'sat',
        'shad',
        'sharp',
        'usm',
        'usmrad',
        'vib',
        'auto',
        'bg',
        'blend',
        'ba',
        'balph',
        'bc',
        'bf',
        'bh',
        'bm',
        'bp',
        'bs',
        'bw',
        'bx',
        'by',
        'border',
        'border-radius-inner',
        'border-radius',
        'pad',
        'prefix',
        'palette',
        'colors',
        'dpr',
        'faceindex',
        'facepad',
        'faces',
        'fp-debug',
        'fp-z',
        'fp-x',
        'fp-y',
        'chromasub',
        'ch',
        'colorquant',
        'cs',
        'dpi',
        'dl',
        'lossless',
        'fm',
        'q',
        'corner-radius',
        'maskbg',
        'mask',
        'nr',
        'nrs',
        'page',
        'flip',
        'or',
        'rot',
        'crop',
        'h',
        'w',
        'max-h',
        'max-w',
        'min-h',
        'min-w',
        'fit',
        'rect',
        'blur',
        'htn',
        'mono',
        'px',
        'sepia',
        'txtalign',
        'txtclip',
        'txtclr',
        'txtfit',
        'txtfont',
        'txtsize',
        'txtlig',
        'txtline',
        'txtlineclr',
        'txtpad',
        'txtshad',
        'txt',
        'txtwidth',
        'trimcolor',
        'trim',
        'trimmd',
        'trimsd',
        'trimtol',
        'txtlead',
        'txttrack',
        '~text',
        'markalign',
        'markalpha',
        'markbase',
        'markfit',
        'markh',
        'mark',
        'markpad',
        'markscale',
        'markw',
        'markx',
        'marky',
    ];
    protected $attributesTranslate = [
        'width'      => 'w',
        'height'     => 'h',
        'min-width'  => 'min-w',
        'max-width'  => 'max-w',
        'min-height' => 'min-h',
        'max-height' => 'max-h',
        'x'          => 'fp-x',
        'y'          => 'fp-y',
    ];
    protected $transforms;
    protected $imagePath;
    protected $builder;
    protected $defaultOptions;
    protected $lazyLoadPrefix;

    protected $kind = Asset::KIND_IMAGE;

    // Public Methods
    // =========================================================================


    /**
     * Constructor
     *
     * @param $image
     *
     * @throws Exception
     */
    public function __construct($image, $transforms = null, $defaultOptions = [])
    {
        parent::__construct();
        $this->lazyLoadPrefix = CraftImgixPicture::$plugin->getSettings()->lazyLoadPrefix ?: 'data-';

        /** @var null|Asset $image */
        if ($image instanceof Asset) {
            $this->kind = $image->kind;
            $source       = $image->getVolume();
            $sourceHandle = $source->handle;
            $focalPoint   = $image->getFocalPoint();

            $domains = CraftImgixPicture::$plugin->getSettings()->domains;
            $domain  = array_key_exists($sourceHandle, $domains) ? $domains[$sourceHandle] : null;
            $domainParts = [];
            if ($domain !== null) {
                $domainParts = explode('/', $domain, 2);
                $domain = $domainParts[0];
            }

            $this->builder = new UrlBuilder($domain);
            $this->builder->setUseHttps(true);

            if ($token = CraftImgixPicture::$plugin->getSettings()->signedToken) {
                $this->builder->setSignKey($token);
            }

            $imagePath = '';
            if (count($domainParts) === 2) {
                $imagePath = rtrim($domainParts[1], '/') . '/';
            }
            $imagePath .= $image->getPath();

            $this->imagePath  = $imagePath;
            $this->transforms = $transforms;

            if (!empty($focalPoint)) {
                $defaultOptions['x'] = $focalPoint['x'];
                $defaultOptions['y'] = $focalPoint['y'];
            }

            $this->defaultOptions = $defaultOptions;

            $this->transform($transforms);
        } elseif (gettype($image) === 'string') {
            $domains     = CraftImgixPicture::$plugin->getSettings()->domains;

            reset($domains);
            $firstHandle = array_key_first($domains);
            $domain      = $domains[$firstHandle];
            $domainParts = [];
            if ($domain !== null) {
                $domainParts = explode('/', $domain, 2);
                $domain = $domainParts[0];
            }

            $this->builder = new UrlBuilder($domain);
            $this->builder->setUseHttps(true);

            if ($token = CraftImgixPicture::$plugin->getSettings()->signedToken)
                $this->builder->setSignKey($token);

            $imagePath = '';
            if (count($domainParts) === 2) {
                $imagePath = rtrim($domainParts[1], '/') . '/';
            }
            $imagePath .= $image;

            $this->imagePath      = $imagePath;
            $this->transforms     = $transforms;
            $this->defaultOptions = $defaultOptions;
            $this->kind = Assets::getFileKindByExtension($this->imagePath);
            $this->transform($transforms);
        } else {
            throw new Exception(Craft::t('craft-imgix-picture', 'An unknown image object was used.'));
        }
    }

    /**
     * @return mixed|null
     */
    public function getUrl()
    {
        if ($image = $this->transformed) {
            if ($image && isset($image['url'])) {
                return $image['url'];
            }
        }

        return null;
    }

    protected function getProps($keys = [], $array = []) {
         return array_intersect_key($array, array_fill_keys($keys, null));
    }

    protected function getVideoPlayerProps()
    {
        $videoProps =  $this->getProps(['controls', 'autoplay', 'loop', 'muted', 'playsinline'], $this->defaultOptions);

        if (!$videoProps) {
            return null;
        }

        return $videoProps;
    }

    protected function getVideoSrcProps()
    {
        $srcConfig =  $this->getProps(['fm', 'res'], $this->defaultOptions);

        if (!$srcConfig) {
            return [];
        }

        return $srcConfig;
    }

    protected function getVideoPosterProps()
    {
        $posterConfig = $this->defaultOptions['poster'] ?? null;

        if (!$posterConfig) {
            return null;
        }

        $posterConfig =  $this->getProps(['time'], $posterConfig);

        if (!$posterConfig) {
            return null;
        }

        $posterConfig['video-generate'] = 'thumbnail';

        $time = $posterConfig['time'] ?? null;

        unset($posterConfig['time']);

        if ($time !== null) {
            $posterConfig['video-thumbnail-time'] = $time;
        } else {
            $posterConfig['video-thumbnail-time'] = 0;
        }

        return $posterConfig;
    }

    protected function getVideoDownloadProps()
    {
        $srcConfig =  $this->getProps(['fm', 'res', 'download', 'downloadClass'], $this->defaultOptions);
        $extra = [];

        $downloadClass = $srcConfig['downloadClass'] ?? null;

        if ($downloadClass) {
            $extra['class'] = $downloadClass;
        }

        $download = $srcConfig['download'] ?? null;

        if (!$download) {
            return null;
        }

        unset($srcConfig['downloadClass']);
        unset($srcConfig['download']);

        if ($download) {
            $extention = $srcConfig['fm'];
            $pathParts = pathinfo($this->imagePath);

            $href = $srcConfig;

            $default = [
                'title' => $pathParts['filename'],
                'name' => $pathParts['filename'],
                'text' => $pathParts['filename'],
            ];

            if (is_string($download)) {
                $download = [
                    'title' => $download,
                    'name' => $download,
                    'text' => $download,
                ];
            } else if (is_array($download)) {
                $props = $this->getProps(['title', 'name', 'text', 'class'], $download);

                $download = array_merge($default, $props, $extra);
            } else {
                $download = $default;
            }

            $download['download'] = "{$download['name']}.{$extention}";

            $href['vdl'] = $download['name'];

            unset($download['name']);

            $srcConfig = array_merge([ 'href' => $href ],$download);
        }

        if (!$srcConfig) {
            return null;
        }

        return $srcConfig;
    }

    protected function getVideoStoryboardProps()
    {
        $srcConfig =  $this->getProps(['storyboardClass'], $this->defaultOptions);
        $extra = [];

        $storyboarClass = $srcConfig['storyboardClass'] ?? null;

        if ($storyboarClass) {
            $extra['class'] = $storyboarClass;
        }

        $storyboarConfig = $this->defaultOptions['storyboard'] ?? null;

        if (!$storyboarConfig) {
            return null;
        }

        $storyboarConfig =  $this->getProps(['format'], $storyboarConfig);

        $storyboarConfig['video-generate'] = 'storyboard';

        $format = $storyboarConfig['format'] ?? 'vtt';

        unset($storyboarConfig['format']);

        $storyboarConfig['video-storyboard-format'] = $format;

        return array_merge($storyboarConfig, $extra);
    }

    protected function getVideoGifProps()
    {
        $gifConfig = $this->defaultOptions['gif'] ?? null;

        if (!$gifConfig) {
            return null;
        }

        $gifConfig =  $this->getProps(['start', 'end', 'fps', 'quality', 'interval', 'loop', 'reverse', 'skip'], $gifConfig);

        $gifConfig['video-generate'] = 'gif';

        $start = $gifConfig['start'] ?? null;
        $end = $gifConfig['end'] ?? null;
        $fps = $gifConfig['fps'] ?? null;
        $quality = $gifConfig['quality'] ?? null;

        unset($gifConfig['start']);
        unset($gifConfig['end']);
        unset($gifConfig['fps']);
        unset($gifConfig['quality']);

        if ($start !== null) {
            $gifConfig['video-gif-time-start'] = $start;
        }

        if ($end !== null) {
            $gifConfig['video-gif-time-end'] = $end;
        }

        if ($fps !== null) {
            $gifConfig['video-gif-fps'] = $fps;
        }

        if ($quality !== null) {
            $gifConfig['gif-q'] = $quality;
        }

        return $gifConfig;
    }

    /**
     * @param $transforms
     *
     * @return null
     */
    protected function transform($transforms = [])
    {
        if ($this->kind === Asset::KIND_VIDEO) {

             $gifProps = $this->getVideoGifProps();

            if ($gifProps) {
                $src = $this->buildTransform($this->imagePath, $gifProps);
            } else {
                $videoSrcProps = $this->getVideoSrcProps();

                $src = $this->buildTransform($this->imagePath, $videoSrcProps);
            }

            $videoProps =  $this->getVideoPlayerProps();


            $transformed = [
                'video' => array_merge($videoProps, [
                    'src' => $src,
                ]),
            ];


            $posterProps = $this->getVideoPosterProps();

            if ($posterProps) {
                $poster = $this->buildTransform($this->imagePath, $posterProps);

                if (isset($transformed['video'])) {
                    $transformed['video'] = array_merge($transformed['video'], [
                        'poster' => $poster,
                    ]);
                }
            }

             $downloadProps = $this->getVideoDownloadProps();

            if ($downloadProps) {
                $href = $this->buildTransform($this->imagePath, $downloadProps['href']);

                $transformed = array_merge($transformed, [
                    'download' => array_merge($downloadProps, [
                        'href' => $href,
                    ]),
                 ]);
            }


            $storyboardProps = $this->getVideoStoryboardProps();

            if ($storyboardProps) {
                $storyboard = $this->buildTransform($this->imagePath, $storyboardProps);

                 $transformed = array_merge($transformed, [
                    'storyboard' => $storyboard,
                 ]);
            }

            $this->transformed = $transformed;
        }

        if (!$transforms) {
            return null;
        }
        if (isset($transforms[0])) {
            $images = [];

            foreach ($transforms as $transform) {
                $transform = array_merge($this->defaultOptions, $transform);
                $transform = $this->calculateTargetSizeFromRatio($transform);
                $url       = $this->buildTransform($this->imagePath, $transform);
                $images[]  = array_merge($transform, ['url' => $url]);
            }

            $this->transformed = $images;
        } else {
            $transforms        = array_merge($this->defaultOptions, $transforms);
            $transforms        = $this->calculateTargetSizeFromRatio($transforms);
            $url               = $this->buildTransform($this->imagePath, $transforms);
            $image             = array_merge($transforms, ['url' => $url]);
            $this->transformed = $image;
        }
    }

    /**
     * @param $filename
     * @param $transform
     *
     * @return string
     */
    private function buildTransform($filename, $transform)
    {
        $parameters = $this->translateAttributes($transform);

        return $this->builder->createURL($filename, $parameters);
    }

    /**
     * @param $attributes
     *
     * @return array
     */
    private function translateAttributes($attributes)
    {
        $translatedAttributes = [];

        foreach ($attributes as $key => $setting) {
            if (array_key_exists($key, $this->attributesTranslate)) {
                $key = $this->attributesTranslate[$key];
            }

            $translatedAttributes[$key] = $setting;
        }

        return $translatedAttributes;
    }

    /**
     * @param $transform
     *
     * @return mixed
     */
    protected function calculateTargetSizeFromRatio($transform)
    {
        if (!isset($transform['ratio'])) {
            return $transform;
        }

        $ratio = (float)$transform['ratio'];
        $w     = isset($transform['w']) ? $transform['w'] : null;
        $h     = isset($transform['h']) ? $transform['h'] : null;

        // If both sizes and ratio is specified, let ratio take control based on width
        if ($w and $h) {
            $transform['h'] = round($w / $ratio);
        } else {
            if ($w) {
                $transform['h'] = round($w / $ratio);
            } elseif ($h) {
                $transform['w'] = round($h * $ratio);
            } else {
                // TODO: log that neither w nor h is specified with ratio
                // no idea what to do, return
                return $transform;
            }
        }

        unset($transform['ratio']); // remove the ratio setting so that it doesn't gets processed in the URL

        return $transform;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['transformed', 'array'],
            ['transformed', 'default', 'value' => []],
        ];
    }
}
