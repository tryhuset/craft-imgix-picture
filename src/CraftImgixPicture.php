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

use apt\craftimgixpicture\services\Service as ServiceService;
use apt\craftimgixpicture\variables\CraftImgixPictureVariable;
use apt\craftimgixpicture\twigextensions\CraftImgixPictureTwigExtension;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;

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

        Craft::$app->view->registerTwigExtension(new CraftImgixPictureTwigExtension());

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('craftImgixPicture', CraftImgixPictureVariable::class);
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

}
