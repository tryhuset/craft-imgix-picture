<?php
/**
 * Imager X plugin for Craft CMS
 *
 * Ninja powered image transforms.
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2020 André Elvan
 */

namespace apt\craftimgixpicture\gql\queries;

use craft\gql\base\Query;

use apt\craftimgixpicture\gql\arguments\ImgixTransformQueryArguments;
use apt\craftimgixpicture\gql\interfaces\ImgixTransformedImageInterface;
use apt\craftimgixpicture\gql\resolvers\ImgixResolver;

class ImgixQuery extends Query
{
    /**
     * @inheritdoc
     */
    public static function getQueries($checkToken = true): array
    {
        return [
            'imgxTransform' => [
                'type' => ImgixTransformedImageInterface::getType(),
                'args' => ImgixTransformQueryArguments::getArguments(),
                'resolve' => ImgixResolver::class . '::resolve',
                'description' => 'This query is used to query for Imager X transforms.'
            ],
        ];
    }
}