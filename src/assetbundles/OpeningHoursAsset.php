<?php

namespace brikdigital\craftopeninghours\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

class OpeningHoursAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@brikdigital/craftopeninghours/web/assets/dist";

        $this->depends = [
            CpAsset::class,
            VueAsset::class
        ];

        parent::init();
    }
}