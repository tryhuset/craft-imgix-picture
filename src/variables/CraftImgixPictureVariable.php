<?php
/**
 * Craft Imgix Picture plugin for Craft CMS 3.x
 *
 * Create responsive image tags and json objects from config file, using the imgIX service.
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2021 thomas@apt.no
 */

namespace apt\craftimgixpicture\variables;

use apt\craftimgixpicture\CraftImgixPicture;

use Craft;
use craft\elements\Asset;

/**
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 */
class CraftImgixPictureVariable
{
    // Public Methods
    // =========================================================================


    public function getArray(Asset $asset = null, $style = 'default', array $options = [])
    {
        $data = CraftImgixPicture::getInstance()->service->getArray($asset, $style, $options);
        
        if ($data && array_key_exists('img', $data) && array_key_exists('attr', $data['img'])) {
            $data['img'] = array_merge($data['img'], $data['img']['attr']);
            unset($data['img']['attr']);
            
        }
        return $data;
    }

    public function getTag(Asset $asset = null, $style = 'default', array $options = [])
    {
        return CraftImgixPicture::getInstance()->service->getTag($asset, $style, $options);
    }
}
