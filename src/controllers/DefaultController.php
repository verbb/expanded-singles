<?php
namespace verbb\expandedsingles\controllers;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\models\Settings;

use craft\web\Controller;

use yii\web\Response;

class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings(): Response
    {
        /* @var Settings $settings */
        $settings = ExpandedSingles::$plugin->getSettings();

        return $this->renderTemplate('expanded-singles/settings', [
            'settings' => $settings,
        ]);
    }

}
