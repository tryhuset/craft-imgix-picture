<?php

namespace apt\craftimgixpicture\gql\resolvers;

use Craft;
use craft\elements\Asset;
use craft\gql\base\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

use apt\craftimgixpicture\CraftImgixPicture;
use apt\craftimgixpicture\services\Service as ImgixService;

class ImgixResolver extends Resolver
{
    /**
     * @inheritDoc
     */
    public static function resolve($source, array $arguments, $context, ResolveInfo $resolveInfo)
    {
        $service = CraftImgixPicture::getInstance()->service;
        $asset = null;
        $style = $arguments['style'];

        if ($source instanceof Asset) {
            // If our source is an Asset, use it directly
            $asset = $source;
        } else {
            // Otherwise query for assets based on submitted id or url argument
            $id = $arguments['id'] ?? null;
            $url = $arguments['url'] ?? null;

            if ($id && $url) {
                Craft::error('Both `id` and `url` was submitted to GraphQL query, these are mutually exclusive. `id` will be used.', __METHOD__);
            }

            if (!empty($id)) {
                $query = Asset::find()
                    ->id($id)
                    ->kind('image')
                    ->limit(null);

                $asset = $query->one();
            } elseif (!empty($url)) {
                $asset = $url;
            }
        }

        if ($asset instanceof Asset) {
            if ($asset->kind !== 'image' || !\in_array(strtolower($asset->getExtension()), ImgixService::$SAFE_FILEFORMATS, true)) {
                return null;
            }
        }

        if ($asset !== null) {
            try {
                return $service->getArray($asset, $style);
            } catch (\Throwable $th) {
                return null;
            }
        }

        return null;
    }
}
