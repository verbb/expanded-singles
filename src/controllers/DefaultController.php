<?php
namespace verbb\expandedsingles\controllers;

use verbb\expandedsingles\ExpandedSingles;

use Craft;
use craft\web\Controller;

class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings()
    {
        $settings = ExpandedSingles::$plugin->getSettings();

        return $this->renderTemplate('expanded-singles/settings', [
            'settings' => $settings,
        ]);
    }

}
