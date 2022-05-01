<?php
namespace verbb\expandedsingles\base;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\services\SinglesList;
use verbb\base\BaseHelper;

use Craft;

use yii\log\Logger;

trait PluginTrait
{
    // Properties
    // =========================================================================

    public static ExpandedSingles $plugin;


    // Static Methods
    // =========================================================================

    public static function log(string $message, array $params = []): void
    {
        $message = Craft::t('expanded-singles', $message, $params);

        Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'expanded-singles');
    }

    public static function error(string $message, array $params = []): void
    {
        $message = Craft::t('expanded-singles', $message, $params);

        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'expanded-singles');
    }


    // Public Methods
    // =========================================================================

    public function getSinglesList(): SinglesList
    {
        return $this->get('singlesList');
    }


    // Private Methods
    // =========================================================================

    private function _registerComponents(): void
    {
        $this->setComponents([
            'singlesList' => SinglesList::class,
        ]);

        BaseHelper::registerModule();
    }

    private function _registerLogTarget(): void
    {
        BaseHelper::setFileLogging('expanded-singles');
    }

}