<?php
namespace apt\craftimgixpicture\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use apt\craftimgixpicture\gql\interfaces\ImgixTransformedImageInterface;

class ImgixType extends ObjectType
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            ImgixTransformedImageInterface::getType(),
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        $fieldName = $resolveInfo->fieldName;
        
        if (isset($source[$fieldName])) {
            return $source[$fieldName];
        }
        
        return null;
    }
}
