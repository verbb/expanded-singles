<?php
namespace verbb\expandedsingles;

use verbb\expandedsingles\base\PluginTrait;
use verbb\expandedsingles\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementSourcesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;

use craft\redactor\events\RegisterLinkOptionsEvent;
use craft\redactor\Field as RedactorField;

use yii\base\Event;

class ExpandedSingles extends Plugin
{
    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.0';
    public $hasCpSettings = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_setLogging();
        $this->_registerCpRoutes();

        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

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

    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('expanded-singles/settings'));
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'expanded-singles/settings' => 'expanded-singles/default/settings',
            ]);
        });
    }
}
