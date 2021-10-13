<?php

/**
 * Imager X plugin for Craft CMS
 *
 * Ninja powered image transforms.
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2020 André Elvan
 */

namespace apt\craftimgixpicture\gql\interfaces;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\TypeLoader;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

use apt\craftimgixpicture\gql\types\generators\ImgixGenerator;
use apt\craftimgixpicture\gql\interfaces\SourceInterface;
use apt\craftimgixpicture\gql\resolvers\SourceResolver;

class ImgixTransformedImageInterface extends BaseInterfaceType
{
    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return ImgixGenerator::class;
    }

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::class, new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by CraftImgixPicture.',
            'resolveType' => function (array $value) {
                return GqlEntityRegistry::getEntity(ImgixGenerator::getName());
            },
        ]));

        foreach (ImgixGenerator::generateTypes() as $typeName => $generatedType) {
            TypeLoader::registerType($typeName, function () use ($generatedType) {
                return $generatedType;
            });
        }

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'ImgixTransformedImageInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return array_merge(parent::getFieldDefinitions(), [
            'img' => [
                'name' => 'img',
                'type' => SourceInterface::getType(),
                'resolve' => function($source) {
                    if (isset($source['img'])) {
                        return $source['img'];
                    }
                    return null;
                },
                'description' => 'img def 1',
            ],
            'sources' => [
                'name' => 'sources',
                'type' => Type::listOf(SourceInterface::getType()),
                'resolve' => function ($source) {
                    if (isset($source['sources'])) {
                        return $source['sources'];
                    }
                    return null;
                },
                'description' => 'sources def ',
            ],
        ]);
    }
}