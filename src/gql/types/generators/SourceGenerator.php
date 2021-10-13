<?php

namespace apt\craftimgixpicture\gql\types\generators;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;

use apt\craftimgixpicture\gql\interfaces\SourceInterface;
use apt\craftimgixpicture\gql\types\SourceType;

class SourceGenerator implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        $fields = SourceInterface::getFieldDefinitions();
        $typeName = self::getName();

        $type = GqlEntityRegistry::getEntity($typeName)
            ?: GqlEntityRegistry::createEntity($typeName, new SourceType([
                'name' => $typeName,
                'args' => [],
                'fields' => function () use ($fields) {
                    return $fields;
                },
                'description' => 'This entity has all the CraftImgixPicture source interface fields.',
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
        return 'source';
    }
}
