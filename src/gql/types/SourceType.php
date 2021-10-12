<?php
namespace apt\craftimgixpicture\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use apt\craftimgixpicture\gql\interfaces\SourceInterface;

class SourceType extends ObjectType
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            SourceInterface::getType(),
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        $fieldName = $resolveInfo->fieldName;

        if (!isset($source[$fieldName])) {
            return null;
        }

        return $source[$fieldName];
    }
}
