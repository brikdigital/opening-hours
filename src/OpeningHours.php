<?php

namespace brikdigital\craftopeninghours;

use brikdigital\craftopeninghours\web\assets\openinghours\OpeningHoursAsset;
use Craft;
use brikdigital\craftopeninghours\fields\OpeningHoursField;
use nystudio107\pluginvite\services\VitePluginService;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\web\View;
use yii\base\Event;

/**
 * opening hours plugin
 *
 * @method static OpeningHours getInstance()
 * @author Brik.digital <service@brik.digital>
 * @copyright Brik.digital
 * @license MIT
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
                    'devServerPublic' => 'http://localhost:3001',
                    'serverPublic' => 'http://localhost:8000',
                    'errorEntry' => 'src/js/openinghours.js',
                    'devServerInternal' => 'http://opening-hours-v4-buildchain-dev:3001',
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
    }
}
