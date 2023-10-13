<?php
namespace verbb\expandedsingles\base;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\services\SinglesList;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

trait PluginTrait
{
    // Properties
    // =========================================================================

    public static ?ExpandedSingles $plugin = null;


    // Traits
    // =========================================================================

    use LogTrait;
    

    // Static Methods
    // =========================================================================

    public static function config(): array
    {
        Plugin::bootstrapPlugin('expanded-singles');

        return [
            'components' => [
                'singlesList' => SinglesList::class,
            ],
        ];
    }


    // Public Methods
    // =========================================================================

    public function getSinglesList(): SinglesList
    {
        return $this->get('singlesList');
    }

}