<?php
namespace apt\craftimgixpicture\gql\interfaces;

use craft\elements\Asset;
use craft\gql\interfaces\Element;

class Imgix extends Element
{
    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return Asset::class;
    }
}