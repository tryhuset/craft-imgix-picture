<?php

namespace apt\craftimgixpicture\gql\resolvers;

use Craft;
use craft\elements\Asset;
use craft\gql\base\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

class SourceResolver extends Resolver
{
    /**
     * @inheritDoc
     */
    public static function resolve($source, array $arguments, $context, ResolveInfo $resolveInfo)
    {
        return $source;
    }

    // private static function prepResults(array $transformedImages): array
    // {
    //     $r = [];

    //     foreach ($transformedImages as $transformedImage) {
    //         $r[] = (array)$transformedImage;
    //     }

    //     return $r;
    // }
}
