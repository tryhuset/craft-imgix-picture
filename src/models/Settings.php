<?php
namespace apt\craftimgixpicture\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $variableName = 'craftImgixPicture';
    public $imageStyles = [];

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

    public function validateImageStyles($attribute)
    {
        foreach ($this->$attribute as $key => $style) {
            $this->validateStyle($attribute, $key, $style);
        }
    }

    public function rules()
    {
        return [
            [['variableName', 'imageStyles'], 'required'],
            ['imageStyles', 'validateImageStyles']
        ];
    }
}
