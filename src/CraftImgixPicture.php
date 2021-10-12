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
use craft\services\Plugins;
use craft\services\Gql;
use craft\events\PluginEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlDirectivesEvent;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use Exception;
use yii\base\Event;

use apt\craftimgixpicture\models\Settings;
use apt\craftimgixpicture\services\Service as ServiceService;
use apt\craftimgixpicture\variables\CraftImgixPictureVariable;
use apt\craftimgixpicture\twigextensions\CraftImgixPictureTwigExtension;

use apt\craftimgixpicture\gql\resolvers\ImgixResolver;
use apt\craftimgixpicture\gql\directives\ImgixTransform;
// use apt\craftimgixpicture\gql\directives\ImagerSrcset;
use apt\craftimgixpicture\gql\interfaces\ImgixTransformedImageInterface;
use apt\craftimgixpicture\gql\queries\ImgixQuery;
// use apt\craftimgixpicture\gql\directives\Imgix as ImgixDirective;

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

        // Event::on(
        //     Gql::class,
        //     Gql::EVENT_REGISTER_GQL_DIRECTIVES,
        //     function (RegisterGqlDirectivesEvent $event) {
        //         $event->directives[] = ImgixDirective::class;
        //     }
        // );

        $this->registerGraphQL();

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
        // Register types
        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_TYPES,
            static function (RegisterGqlTypesEvent $event) {
                Craft::debug(
                    'Gql::EVENT_REGISTER_GQL_TYPES',
                    __METHOD__
                );
                $event->types[] = ImgixTransformedImageInterface::class;
            }
        );

        // Register query
        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_QUERIES,
            static function (RegisterGqlQueriesEvent $event) {
                $queries = ImgixQuery::getQueries();
                foreach ($queries as $key => $value) {
                    $event->queries[$key] = $value;
                }
            }
        );

        // Register directives
        // Event::on(
        //     Gql::class,
        //     Gql::EVENT_REGISTER_GQL_DIRECTIVES,
        //     static function (RegisterGqlDirectivesEvent $event) {
        //         $event->directives[] = ImagerTransform::class;
        //         $event->directives[] = ImagerSrcset::class;
        //     }
        // );

        Event::on(
            \craft\gql\TypeManager::class,
            \craft\gql\TypeManager::EVENT_DEFINE_GQL_TYPE_FIELDS,
            static function (\craft\events\DefineGqlTypeFieldsEvent $event) {
                if ($event->typeName !== 'AssetInterface') {
                    return;
                }
                $event->fields['imgixTransform'] = [
                    'name' => 'imgixTransform',
                    'type' => \GraphQL\Type\Definition\Type::listOf(ImgixTransformedImageInterface::getType()),
                    'args' => [
                        'style' => [
                            'name' => 'style',
                            'type' => \GraphQL\Type\Definition\Type::string(),
                            'description' => 'The handle of the named transform you want to generate.'
                        ],
                    ],
                    'resolve' => ImgixResolver::class . '::resolve',
                    'description' => 'Returns a list of images produced from the named CraftImgixPicture transform.',
                ];
            }
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }
}
