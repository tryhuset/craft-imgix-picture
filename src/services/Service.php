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
use superbig\imgix\Imgix;

/**
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 */
class Service extends Component
{
    protected $styles;
    protected $imgix;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->styles = CraftImgixPicture::getInstance()->settings->imageStyles;
        $this->imgix = Imgix::$plugin->imgixService;
        // $this->validateStyles();
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

        d($style);
        if (array_key_exists('img', $style)) {
            $result['img'] = $this->getImageArray($asset, $style['img']);
        }

        return $result;
    }

    /*
     * @return mixed
     */
    public function getTag(Asset $asset = null, $key = 'default', array $options = [])
    {
        return null;
    }

    // protected function validateStyle($key, $style)
    // {

    // }

    // protected function validateStyles()
    // {
    //     foreach ($this->styles as $key => $style) {
    //         if (array_key_exists('sources', $style)) {
    //             foreach ($style['sources'] as $i => $style) {
    //                 # code...
    //             }
    //         }

    //         if (array_key_exists('img', $style)) {

    //         }
    //     }
    // }

    protected function getSrcSet(Asset $asset, $style)
    {
        // if (!array_key_exists('widths', $style)) {
        //     throw new \Exception(Craft::t('craft-imgix-picture', "CraftImgixPicture: Your image style named", ['key' => $key]));
        // }

        d($style);
        $srcSet = [];
        if (array_key_exists('aspectRatio', $style)) {

        } else {

        }

        return implode(', ', $srcSet);
    }

    protected function getImageArray(Asset $asset, $style)
    {
        $test = $this->getSrcSet($asset, $style);
        dd($test);
    }
}
