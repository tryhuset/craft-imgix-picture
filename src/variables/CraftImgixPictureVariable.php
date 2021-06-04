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

/**
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 */
class CraftImgixPictureVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
