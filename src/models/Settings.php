<?php
namespace apt\craftimgixpicture\models;


use Craft;
use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    public $variableName = 'craftImgixPicture';

    public $apiKey = '';

    public $domains = [];

    public $signedToken = '';

    public $lazyLoadPrefix = '';

    public $options = [];

    public $imageStyles = [];

    public $exclude = [];

    public function init() : void
    {
        parent::init();

        if (!empty($this->apiKey) && strlen($this->apiKey) < 50) {
            \Craft::$app->deprecator->log(__METHOD__, 'You appear to be using an API key for v1 of the Imgix API. v1 has been deprecated. You need to generate a new one from https://dashboard.imgix.com/api-keys/new, with permissions to purge, and replace the old one. See https://blog.imgix.com/2020/10/16/api-deprecation for more information.');
        }
    }

    protected function validateSource($attribute, $key, $source)
    {
        if (!is_array($source)) {
            $this->addError($attribute, Craft::t('craft-imgix-picture', '{key} is an invalid array.', ['key' => $key]));
        } else {
            if (!array_key_exists('widths', $source)) {
                $this->addError($attribute, Craft::t('craft-imgix-picture', '{key} is is missing widths.', ['key' => $key]));
            } else {
                if (!is_array($source['widths'])) {
                    $this->addError($attribute, Craft::t('craft-imgix-picture', '{key} widths must be an array.', ['key' => $key]));
                }
            }
        }
    }

    protected function validateStyle($attribute, $key, $style)
    {
        if (array_key_exists('sources', $style)) {
            foreach ($style['sources'] as $index => $source) {
                $this->validateSource($attribute, "{$key}[sources][{$index}]", $source);
            }
        }
        if (array_key_exists('img', $style)) {
            if (!is_array($style['img'])) {
                $this->addError($attribute, Craft::t('craft-imgix-picture', '{key}[img] must be an array.', ['key' => $key]));
            } else {
                if (array_key_exists('widths', $style['img'])) {
                    if (!is_array($style['img']['widths'])) {
                        $this->addError($attribute, Craft::t('craft-imgix-picture', '{key}[img][widths] must be an array.', ['key' => $key]));
                    }
                } else {
                    $this->addError($attribute, Craft::t('craft-imgix-picture', '{key}[img][widths] is required.', ['key' => $key]));
                }
            }
        }
    }

    public function getApiKey()
    {
        $apiKey = App::parseEnv($this->apiKey);

        if (!empty($apiKey) && strlen($apiKey) < 50) {
            \Craft::$app->deprecator->log(__METHOD__, 'You appear to be using an deprecated API key for th eImgix API. You need to generate a new one from https://dashboard.imgix.com/api-keys/new, with permissions to purge, and replace the old one. See https://blog.imgix.com/2020/10/16/api-deprecation for more information.');
        }

        return $apiKey;
    }

    public function validateImageStyles($attribute)
    {
        foreach ($this->$attribute as $key => $style) {
            $this->validateStyle($attribute, $key, $style);
        }
    }

    public function validateArray($attribute) {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, Craft::t('craft-imgix-picture', '{attribute} must be an array.', ['attribute' => $attribute]));
        }
    }

    public function rules(): array
    {
        return [
            ['domains', 'default', 'value' => []],
            ['signedToken', 'string'],
            ['signedToken', 'default', 'value' => ''],
            ['lazyLoadPrefix', 'string'],
            ['lazyLoadPrefix', 'default', 'value' => ''],
            ['variableName', 'required'],
            [['domains', 'options'], 'validateArray'],
            ['imageStyles', 'validateImageStyles'],
            ['exclude', 'default', 'value' => []],
        ];
    }
}
