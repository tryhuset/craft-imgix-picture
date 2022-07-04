<?php

/**
 * Craft Imgix Picture plugin for Craft CMS 3.x
 *
 * Create responsive image tags and json objects from config file, using the imgIX service.
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2021 thomas@apt.no
 */

namespace apt\craftimgixpicture\services;

use apt\craftimgixpicture\CraftImgixPicture;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\web\View;
use craft\helpers\Template;

use superbig\imgix\Imgix;

/**
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 */
class Service extends Component
{
    public static $SAFE_FILEFORMATS = ['jpg', 'jpeg', 'gif', 'png'];

    protected $styles;
    protected $defaultImgixOptions = [];
    protected $imgix;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->styles = CraftImgixPicture::getInstance()->settings->imageStyles;
        $this->defaultImgixOptions = CraftImgixPicture::getInstance()->settings->imgix;
        $this->imgix = Imgix::$plugin->imgixService;
    }

    // Public Methods
    // =========================================================================

    public function getStyle($key)
    {
        if (!isset($this->styles[$key])) {
            throw new \Exception(Craft::t('craft-imgix-picture', "CraftImgixPicture: Style named \"{key}\" does not exist.\n Please add it to your config/craft-imgix-picture.", ['key' => $key]));
        }

        return $this->styles[$key];
    }

    /*
     * @return mixed
     */
    public function getArray(Asset $asset = null, $key = 'default', array $options = [])
    {
        if (!$asset) {
            return null;
        }

        $result = [];
        $style = $this->getStyle($key);
        $imgixOptions = $this->defaultImgixOptions;

        $pictureClass = null;

        if (array_key_exists('pictureClass', $options)) {
            $pictureClass = $options['pictureClass'];
            unset($options['pictureClass']);
        }

        if (array_key_exists('sources', $style)) {
            $result['sources'] = $this->getSourcesArray($asset, $style['sources'], $imgixOptions, $options);
        }

        if (array_key_exists('img', $style)) {
            $result['img'] = $this->getImageArray($asset, $style['img'], $imgixOptions, $options);

            unset($options['imgix']);

            $result['img'] = array_merge($result['img'], $options);


            // Rename class to className for react
            if (array_key_exists('class', $result['img'])) {
                if (!empty($result['img']['class'])) {
                    $result['img']['className'] = $result['img']['class'];
                }
                unset($result['img']['class']);
            }

            if ($pictureClass) {
                $result['class'] = $pictureClass;
            }

            return $result;
        }

        unset($options['imgix']);

        if ($pictureClass) {
            $options['class'] = $pictureClass;
        }

        return array_merge($result, $options);
    }

    protected function normalizeAttributes($tag)
    {
        $tag = array_filter($tag, function ($attr) {
            if (is_array($attr)) {
                return false;
            }
            if (is_object($attr)) {
                return false;
            }

            return true;
        });

        $tag = array_map(function ($key, $value) {
            $key = strtolower($key);
            if ($key === 'classname') {
                return "class=\"{$value}\"";
            }
            return "{$key}=\"{$value}\"";
        }, array_keys($tag), array_values($tag));

        return implode(' ', $tag);
    }

    /*
     * @return mixed
     */
    public function getTag(Asset $asset = null, $key = 'default', array $options = [])
    {
        if (!$asset) {
            return null;
        }

        $data = $this->getArray($asset, $key, $options);

        if (array_key_exists('sources', $data)) {
            $data['sources'] = array_map(function ($source) {
                return $this->normalizeAttributes($source);
            }, $data['sources']);
        }

        if (array_key_exists('img', $data)) {
            $data['img'] = $this->normalizeAttributes($data['img']);
        }

        $oldMode = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        $template = array_key_exists('sources', $data) ? 'craft-imgix-picture/picture' : 'craft-imgix-picture/img';

        $html =  Craft::$app->view->renderTemplate(
            $template,
            $data
        );

        Craft::$app->view->setTemplateMode($oldMode);
        return Template::raw($html);
    }

    public function getPreload(Asset $asset = null, $key = 'default', array $options = [])
    {
        if (!$asset) {
            return null;
        }

        $data = $this->getArray($asset, $key, $options);

        $oldMode = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        $html =  Craft::$app->view->renderTemplate(
            'craft-imgix-picture/preload',
            $data
        );

        Craft::$app->view->setTemplateMode($oldMode);
        return Template::raw($html);
    }

    protected function getSrcSet(Asset $asset, $style, $imgixOptions = [])
    {
        if (count($style['widths']) < 1) {
            return null;
        }

        $srcSet = [];
        $aspectRatio = $style['aspectRatio'] ?? null;

        $transforms = [];

        foreach ($style['widths'] as $width) {
            $transform = [
                'width' => $width,
            ];

            if ($aspectRatio) {
                $transform['height'] = intval($width / $aspectRatio);
            }
            $transforms[] = $transform;
        }

        $image = $this->imgix->transformImage($asset, $transforms, $imgixOptions)->toArray();

        if (!$image) {
            return null;
        }

        if (!$image['transformed']) {
            return null;
        }

        usort($image['transformed'], function ($a, $b) {
            if ($a['width'] == $b['width']) {
                return 0;
            }
            return ($a['width'] < $b['width']) ? -1 : 1;
        });

        $srcSet = array_map(function ($item) {
            return "{$item['url']} {$item['width']}w";
        }, $image['transformed']);

        return $srcSet;
    }

    protected function getSourcesArray(Asset $asset, $sources, $imgixOptions = [], $options = [])
    {
        return array_map(function ($style) use ($asset, $imgixOptions, $options) {
            if (array_key_exists('imgix', $style)) {
                $imgixOptions = array_merge($imgixOptions, $style['imgix']);
                unset($style['imgix']);
            }

            if (array_key_exists('imgix', $options)) {
                $imgixOptions = array_merge($imgixOptions, $options['imgix']);
                unset($options['imgix']);
            }

            $srcSet = $this->getSrcSet($asset, $style, $imgixOptions);

            unset($style['aspectRatio']);
            unset($style['widths']);

            return array_merge($style, [
                'srcSet' => implode(', ', $srcSet),
            ]);
        }, $sources);
    }

    protected function getImageArray(Asset $asset, $style, $imgixOptions = [], $options = [])
    {
        $alt = $asset->alt ?? $asset->title ?? '';
        $alt = $style['alt'] ?? $alt;

        if (array_key_exists('imgix', $style)) {
            $imgixOptions = array_merge($imgixOptions, $style['imgix']);
            unset($style['imgix']);
        }

        if (array_key_exists('imgix', $options)) {
            $imgixOptions = array_merge($imgixOptions, $options['imgix']);
            unset($options['imgix']);
        }


        if (array_key_exists('alt', $options)) {
            $alt = $options['alt'];
        }

        $srcSet = $this->getSrcSet($asset, $style, $imgixOptions);
        $src = array_shift($srcSet);

        $src = preg_replace('/(.*) (\d*w$)/', '$1', $src);

        unset($style['aspectRatio']);
        unset($style['widths']);

        if (count($srcSet)) {
            return array_merge($style, [
                'srcSet' => implode(', ', $srcSet),
                'src' => $src,
                'alt' => $alt,
            ]);
        }

        return array_merge($style, [
            'src' => $src,
            'alt' => $alt,
        ]);
    }
}
