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

/**
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 */
class Service extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }
}
