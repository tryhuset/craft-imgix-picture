<?php
namespace apt\craftimgixpicture\gql\arguments;

use craft\gql\base\Arguments;
use GraphQL\Type\Definition\Type;

class ImgixTransformQueryArguments extends Arguments
{
    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int(),
                'description' => 'The asset id to transform.'
            ],
            'url' => [
                'name' => 'url',
                'type' => Type::string(),
                'description' => 'The asset url to transform.'
            ],
            'style' => [
                'name' => 'style',
                'type' => Type::string(),
                'description' => 'The handle of the named transform you want to generate.'
            ],
        ];
    }
}
