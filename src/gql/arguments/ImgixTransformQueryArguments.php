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
            'style' => [
                'name' => 'style',
                'type' => Type::string(),
                'description' => 'The handle of the named transform you want to generate.'
            ],
        ];
    }
}
