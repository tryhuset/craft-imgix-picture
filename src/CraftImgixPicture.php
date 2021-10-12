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

use apt\craftimgixpicture\models\Settings;
use apt\craftimgixpicture\services\Service as ServiceService;
use apt\craftimgixpicture\variables\CraftImgixPictureVariable;
use apt\craftimgixpicture\twigextensions\CraftImgixPictureTwigExtension;
use apt\craftimgixpicture\gql\directives\Imgix as ImgixDirective;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\services\Gql;
use craft\events\PluginEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterGqlDirectivesEvent;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use Exception;
use yii\base\Event;

/**
 * Class CraftImgixPicture
 *
 * @author    thomas@apt.no
 * @package   CraftImgixPicture
 * @since     1.0.0
 *
 * @property  ServiceService $service
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
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

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

        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_DIRECTIVES,
            function (RegisterGqlDirectivesEvent $event) {
                $event->directives[] = ImgixDirective::class;
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

    protected function createSettingsModel()
    {
        return new Settings();
    }
}
