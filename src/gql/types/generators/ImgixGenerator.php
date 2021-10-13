<?php

namespace apt\craftimgixpicture\gql\types\generators;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;

use apt\craftimgixpicture\gql\arguments\ImgixTransformQueryArguments;
use apt\craftimgixpicture\gql\interfaces\ImgixTransformedImageInterface;
use apt\craftimgixpicture\gql\types\ImgixType;

class ImgixGenerator implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        $fields = ImgixTransformedImageInterface::getFieldDefinitions();
        $args = ImgixTransformQueryArguments::getArguments();
        $typeName = self::getName();

        $type = GqlEntityRegistry::getEntity($typeName)
            ?: GqlEntityRegistry::createEntity($typeName, new ImgixType([
                'name' => $typeName,
                'args' => function () use ($args) {
                    return $args;
                },
                'fields' => function () use ($fields) {
                    return $fields;
                },
                'description' => 'This entity has all the CraftImgixPicture imgix interface fields.',
            ]));

        TypeLoader::registerType($typeName, function () use ($type) {
            return $type;
        });

        return [$type];
    }

    /**
     * @inheritdoc
     */
    public static function getName($context = null): string
    {
        return 'imgix';
    }
}
