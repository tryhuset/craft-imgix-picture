<?php
/**
 * Craft Imgix Picture plugin for Craft CMS 3.x
 *
 * Create responsive image tags and json objects from config file, using the imgIX service.
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2021 thomas@apt.no
 */

namespace apt\craftimgixpicture;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\services\Plugins;
use craft\services\Assets;
use craft\services\Elements;
use craft\events\PluginEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\ElementEvent;
use craft\events\RegisterElementActionsEvent;
use craft\events\ReplaceAssetEvent;
use craft\elements\Asset;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use Exception;
use yii\base\Event;

use apt\craftimgixpicture\actions\ImgixPurgeAction;
use apt\craftimgixpicture\models\Settings;
use apt\craftimgixpicture\services\Service as ServiceService;
use apt\craftimgixpicture\services\Imgix as ImgixService;
use apt\craftimgixpicture\variables\CraftImgixPictureVariable;
use apt\craftimgixpicture\twigextensions\CraftImgixPictureTwigExtension;

use apt\craftimgixpicture\gql\resolvers\ImgixResolver;
use apt\craftimgixpicture\gql\arguments\ImgixTransformQueryArguments;
use apt\craftimgixpicture\gql\interfaces\ImgixTransformedImageInterface;

/**
 * Class CraftImgixPicture
 *
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 *
 * @property  ServiceService $service
 * @property  ImgixService $imgix
 */
class CraftImgixPicture extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var CraftImgixPicture
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $settings = $this->getSettings();
        if (!$settings->validate()) {
            foreach ($settings->errors as $error) {
                throw new Exception("CraftImgixPicture: Settings: {$error[0]}");
            }
        }

        Craft::$app->view->registerTwigExtension(new CraftImgixPictureTwigExtension());

        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) use($settings) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set($settings->variableName, CraftImgixPictureVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        $this->registerGraphQL();

        Event::on(
            Elements::class,
            Elements::EVENT_BEFORE_SAVE_ELEMENT,
            function (ElementEvent $event) {
                Craft::debug(
                    'Elements::EVENT_BEFORE_SAVE_ELEMENT',
                    __METHOD__
                );

                /** @var Element $element */
                $element      = $event->element;
                $isNewElement = $event->isNew;

                if ($element instanceof Asset && !$isNewElement) {
                    CraftImgixPicture::$plugin->imgix->onSaveAsset($element);
                }
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_BEFORE_DELETE_ELEMENT,
            function (ElementEvent $event) {
                Craft::debug(
                    'Elements::EVENT_BEFORE_DELETE_ELEMENT',
                    __METHOD__
                );

                /** @var Element $element */
                $element      = $event->element;
                $isNewElement = $event->isNew;

                if ($element instanceof Asset) {
                    CraftImgixPicture::$plugin->imgixService->onDeleteAsset($element);
                }
            }
        );

        Event::on(
            Assets::class,
            Assets::EVENT_BEFORE_REPLACE_ASSET,
            function (ReplaceAssetEvent $event) {
                Craft::debug(
                    'Assets::EVENT_BEFORE_REPLACE_ASSET',
                    __METHOD__
                );
                /** @var Asset $element */
                $element = $event->asset;

                CraftImgixPicture::$plugin->imgixService->onSaveAsset($element);
            }
        );

        Event::on(
            Asset::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = new ImgixPurgeAction();
            }
        );

        Craft::info(
            Craft::t(
                'craft-imgix-picture',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Register GraphQL event listeners
     */
    private function registerGraphQL(): void
    {
        Event::on(
            \craft\gql\TypeManager::class,
            \craft\gql\TypeManager::EVENT_DEFINE_GQL_TYPE_FIELDS,
            static function (\craft\events\DefineGqlTypeFieldsEvent $event) {
                if ($event->typeName !== 'AssetInterface') {
                    return;
                }
                $event->fields['imgixPicture'] = [
                    'name' => 'imgixPicture',
                    'type' => ImgixTransformedImageInterface::getType(),
                    'args' => ImgixTransformQueryArguments::getArguments(),
                    'resolve' => ImgixResolver::class . '::resolve',
                    'description' => 'Returns a list of images produced from the named CraftImgixPicture transform.',
                ];
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Settings
    {
        return new Settings();
    }

}
