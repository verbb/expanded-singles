<?php
namespace verbb\expandedsingles\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

use verbb\base\assetbundles\CpAsset as VerbbCpAsset;

class ExpandedSinglesAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $this->sourcePath = "@verbb/expandedsingles/resources/dist";

        $this->depends = [
            VerbbCpAsset::class,
            CpAsset::class,
        ];

        $this->css = [
            'css/expanded-singles.css',
        ];

        $this->js = [
            'js/expanded-singles.js',
        ];

        parent::init();
    }
}
