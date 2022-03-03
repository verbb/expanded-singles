<?php
namespace verbb\expandedsingles\controllers;

use verbb\expandedsingles\ExpandedSingles;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings(): Response
    {
        $settings = ExpandedSingles::$plugin->getSettings();

        return $this->renderTemplate('expanded-singles/settings', [
            'settings' => $settings,
        ]);
    }

}
