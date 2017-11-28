<?php
namespace verbb\expandedsingles;

use verbb\expandedsingles\services\SinglesList;
use verbb\expandedsingles\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementSourcesEvent;

use yii\base\Event;

/**
 * @property  SinglesList $singlesList
 * @property  Settings    $settings
 * @method    Settings getSettings()
 */
class ExpandedSingles extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ExpandedSingles
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        // Register Components (Services)
        $this->setComponents([
            'singlesList' => SinglesList::class,
        ]);

        // Modified the entry index sources
        Event::on(Entry::class, Element::EVENT_REGISTER_SOURCES, function(RegisterElementSourcesEvent $event) {

            // Are we in the context of index?
            if ($this->getSettings()->expandSingles && $event->context == 'index') {

                // Are there any Singles at all?
                foreach ($event->sources as $source) {
                    if (array_key_exists('key', $source) && $source['key'] === 'singles') {
                        $this->singlesList->createSinglesList($event);
                    }
                }
            }
        });
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return Settings
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate('expanded-singles/settings', [
            'settings' => $this->getSettings(),
        ]);
    }
}
