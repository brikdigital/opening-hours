<?php

namespace brikdigital\craftopeninghours;

use brikdigital\craftopeninghours\variables\ViteVariable;
use brikdigital\craftopeninghours\assetbundles\OpeningHoursAsset;
use Craft;
use brikdigital\craftopeninghours\fields\OpeningHoursField;
use craft\web\twig\variables\CraftVariable;
use nystudio107\pluginvite\services\VitePluginService;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\web\View;
use yii\base\Event;

/**
 * Craft plugin for specifying opening times
 *
 * @author Brik.digital <service@brik.digital>
 * @author Alyxia Sother <alyxia@riseup.net>
 * @license MIT
 * @copyright 2023+ Brik.digital
 * @method static OpeningHours getInstance()
 * @property-read VitePluginService $vite
 */
class OpeningHours extends Plugin
{
    public string $schemaVersion = '0.0.1';

    public static ?OpeningHours $plugin;
    public static ?View $view = null;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
                'vite' => [
                    'class' => VitePluginService::class,
                    'assetClass' => OpeningHoursAsset::class,
                    'useDevServer' => true,
                    'devServerPublic' => 'http://localhost:3004',
                    'serverPublic' => 'http://localhost:8000',
                    'errorEntry' => 'src/js/main.ts',
                    'devServerInternal' => 'http://opening-hours-v4-buildchain-dev:3004',
                    'checkDevServer' => true,
                ],
            ],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$view = Craft::$app->getView();
        self::$plugin = $this;

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = OpeningHoursField::class;
        });

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
//                $variable->set('vite', [
//                    'class' => ViteVariable::class,
//                    'viteService' => $this->vite
//                ]);
            }
        );
    }
}
