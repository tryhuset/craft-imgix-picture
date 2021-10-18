<?php
namespace apt\craftimgixpicture\gql\interfaces;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\TypeLoader;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

use apt\craftimgixpicture\gql\types\generators\SourceGenerator;

class SourceInterface extends BaseInterfaceType
{
    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return SourceGenerator::class;
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
                return GqlEntityRegistry::getEntity(SourceGenerator::getName());
            },
        ]));
        
        foreach (SourceGenerator::generateTypes() as $typeName => $generatedType) {
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
        return 'SourceInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array 
    {
        return array_merge(parent::getFieldDefinitions(), [
            'media' => [
                'name' => 'media',
                'type' => Type::string(),
                'description' => 'The alternative text of the image.',
            ],
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
            'className' => [
                'name' => 'className',
                'type' => Type::string(),
                'description' => 'The alternative text of the image.',
            ],
            'loading' => [
                'name' => 'loading',
                'type' => Type::string(),
                'description' => 'Indicates how the browser should load the image.',
            ]
        ]);
    }
}
