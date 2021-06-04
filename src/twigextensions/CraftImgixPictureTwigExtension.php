<?php
/**
 * Craft Imgix Picture plugin for Craft CMS 3.x
 *
 * Create responsive image tags and json objects from config file, using the imgIX service.
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2021 thomas@apt.no
 */

namespace apt\craftimgixpicture\twigextensions;

use apt\craftimgixpicture\CraftImgixPicture;

use Craft;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 */
class CraftImgixPictureTwigExtension extends AbstractExtension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'CraftImgixPicture';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            // new TwigFilter('someFilter', [$this, 'someInternalFunction']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            // new TwigFunction('someFunction', [$this, 'someInternalFunction']),
        ];
    }

    // /**
    //  * @param null $text
    //  *
    //  * @return string
    //  */
    // public function someInternalFunction($text = null)
    // {
    //     $result = $text . " in the way";

    //     return $result;
    // }
}
