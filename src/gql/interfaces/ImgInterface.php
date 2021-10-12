<?php
namespace apt\craftimgixpicture\gql\interfaces;

use apt\craftimgixpicture\gql\types\generators\ImgGenerator;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\TypeLoader;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

class ImgInterface extends BaseInterfaceType
{
    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return ImgGenerator::class;
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
                return GqlEntityRegistry::getEntity(ImgGenerator::getName());
            },
        ]));

        foreach (ImgGenerator::generateTypes() as $typeName => $generatedType) {
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
        return 'ImgInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return array_merge(parent::getFieldDefinitions(), [
            'sizes' => [
                'name' => 'sizes',
                'type' => Type::string(),
                'description' => 'The alternative text of the image.',
            ],
            'srcSet' => [
                'name' => 'srcSet',
                'type' => Type::string(),
                'description' => 'The alternative text of the image.',
            ],
            'src' => [
                'name' => 'src',
                'type' => Type::string(),
                'description' => 'The alternative text of the image.',
            ],
            'alt' => [
                'name' => 'alt',
                'type' => Type::string(),
                'description' => 'The alternative text of the image.',
            ],
            // 'className' => [
            //     'name' => 'className',
            //     'type' => Type::string(),
            //     'description' => 'The alternative text of the image.',
            // ]
        ]);
    }
}
