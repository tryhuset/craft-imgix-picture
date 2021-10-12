<?php
namespace apt\craftimgixpicture\gql\directives;

use craft\gql\base\Directive;
use craft\gql\GqlEntityRegistry;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive as GqlDirective;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\Type;

use apt\craftimgixpicture\CraftImgixPicture;

class Imgix extends Directive
{
    const DEFAULT_KEY = null;
    const DEFAULT_ALT = '';

    public static function create(): GqlDirective
    {
        if ($type = GqlEntityRegistry::getEntity(self::name())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(static::name(), new self([
            'name' => static::name(),
            'locations' => [
                DirectiveLocation::FIELD,
            ],
            'description' => 'Replace `foo` with `bar`.',
            'args' => [
                new FieldArgument([
                    'name' => 'style',
                    'type' => Type::string(),
                    'defaultValue' => self::DEFAULT_KEY,
                    'description' => '',
                ]),
                new FieldArgument([
                    'name' => 'alt',
                    'type' => Type::string(),
                    'defaultValue' => self::DEFAULT_ALT,
                    'description' => '',
                ]),
            ],
        ]));

        return $type;
    }

    public static function name(): string
    {
        return 'imgix';
    }

    public static function apply($source, $value, array $arguments, ResolveInfo $resolveInfo)
    {
        $style = $arguments['style'];

        if (!$style) {
            return null;
        }

        unset($arguments['style']);

        $response = CraftImgixPicture::getInstance()->service->getImageArray($source, $style, $arguments);

        if(isset($response['srcSet'])) {
            return $response['srcSet'];
        }

        return 'success';
        // var_dump($source);
        // exit;
        // $asset = 
        // echo get_class($value); 
        // $onAssetElement = $value instanceof Asset;
        // if ($onAssetElement) {
        //     die('HEPP');
        //     return new Asset();
        //     # code...
        // }
        
        // var_dump($source);
        // return array_map(function($asset) use ($arguments) {
        //     return new ImgixElement(array_merge($arguments, [
        //         'asset' => $asset,
        //     ]));
        // }, $value);

        // $asset = array_shift($value);
        // if (!$asset) {
        //     return null;
        // }

        // return [];
        // // if (condition) {
        // //     # code...
        // // }
        // // var_dump($value);
        // return ['id' => 'bar'];
        return $value;
        // // return str_replace('foo', 'bar', (string)$value);
    }
}
