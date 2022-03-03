<?php
namespace verbb\expandedsingles\base;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\services\SinglesList;

use Craft;

use yii\log\Logger;

use verbb\base\BaseHelper;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    public static ExpandedSingles $plugin;


    // Public Methods
    // =========================================================================

    public function getSinglesList(): SinglesList
    {
        return $this->get('singlesList');
    }

    public static function log($message): void
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'expanded-singles');
    }

    public static function error($message): void
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'expanded-singles');
    }


    // Private Methods
    // =========================================================================

    private function _setPluginComponents(): void
    {
        $this->setComponents([
            'singlesList' => SinglesList::class,
        ]);

        BaseHelper::registerModule();
    }

    private function _setLogging(): void
    {
        BaseHelper::setFileLogging('expanded-singles');
    }

}