<?php
namespace apt\craftimgixpicture\gql\arguments;

use craft\gql\base\Arguments;
use GraphQL\Type\Definition\Type;

class _ImgTransformQueryArguments extends Arguments
{
    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        return [
            // 'img' => [
            //     'name' => 'img',
            //     'type' => Type::int(),
            //     'description' => 'The asset id to transform.'
            // ],
        ];
    }
}
