<?php
namespace apt\craftimgixpicture\gql\elements;

// use craft\elements\Asset;
// use craft\base\Element;
use craft\gql\types\elements\Asset;

class Imgix extends Asset
{
    // public $key = '';
    // public $asset = null;

    public function getSrcSet()
    {
        return '/hepp';
    }
}