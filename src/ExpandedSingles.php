<?php
namespace verbb\expandedsingles;

use verbb\expandedsingles\services\SinglesList;
use verbb\expandedsingles\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementSourcesEvent;

use craft\redactor\events\RegisterLinkOptionsEvent;
use craft\redactor\Field as RedactorField;

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

        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        // Register Components (Services)
        $this->setComponents([
            'singlesList' => SinglesList::class,
        ]);

        // Modified the entry index sources
        Event::on(Entry::class, Element::EVENT_REGISTER_SOURCES, function(RegisterElementSourcesEvent $event) {
            
            // Have we enabled the plugin?
            if ($this->getSettings()->expandSingles) {

                // Are there any Singles at all?
                foreach ($event->sources as $source) {
                    if (array_key_exists('key', $source) && $source['key'] === 'singles') {
                        $this->singlesList->createSinglesList($event);
                    }
                }
            }
        });

        // Hook onto a special hook from Redactor - it handles singles a little differently!
        if (class_exists(RedactorField::class)) {
            Event::on(RedactorField::class, RedactorField::EVENT_REGISTER_LINK_OPTIONS, function(RegisterLinkOptionsEvent $event) {
                
                // Have we enabled the plugin?
                if ($this->getSettings()->expandSingles) {

                    foreach ($event->linkOptions as $i => $linkOption) {

                        // Only apply this for entries, and if there are any singles
                        if ($linkOption['refHandle'] === 'entry') {
                            if (in_array('singles', $linkOption['sources'])) {
                                $modifiedSources = $this->singlesList->createSectionedSinglesList($linkOption['sources']);

                                if ($modifiedSources) {
                                    $event->linkOptions[$i]['sources'] = $modifiedSources;
                                }
                            }
                        }
                    }
                }
            });
        }
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
