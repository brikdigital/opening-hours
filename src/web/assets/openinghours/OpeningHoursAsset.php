<?php

namespace brikdigital\craftopeninghours\web\assets\openinghours;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

/**
 * Opening Hours asset bundle
 */
class OpeningHoursAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';
    public $depends = [];
    public $js = [];
    public $css = [
        'css/opening-hours.css'
    ];
}
