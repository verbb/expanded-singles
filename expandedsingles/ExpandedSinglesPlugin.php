<?php
namespace Craft;

class ExpandedSinglesPlugin extends BasePlugin
{
    // =========================================================================
    // PLUGIN INFO
    // =========================================================================

    public function getName()
    {
        return Craft::t('Expanded Singles');
    }

    public function getVersion()
    {
        return '0.2.5';
    }

    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    public function getDeveloper()
    {
        return 'Verbb';
    }

    public function getDeveloperUrl()
    {
        return 'https://verbb.io';
    }

    public function getPluginUrl()
    {
        return 'https://github.com/verbb/expanded-singles';
    }

    public function getDocumentationUrl()
    {
        return 'https://verbb.io/craft-plugins/expanded-singles/docs';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/verbb/expanded-singles/craft-2/changelog.json';
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render( 'expandedsingles/settings', array(
            'settings' => $this->getSettings(),
        ) );
    }

    protected function defineSettings()
    {
        return array(
            'expandSingles' => array( AttributeType::Bool, 'default' => true ),
            'redirectToEntry' => array( AttributeType::Bool, 'default' => false ),
        );
    }


    // =========================================================================
    // HOOKS
    // =========================================================================

    public function modifyEntrySources(&$sources, $context)
    {
        // Are we in the context of index?
        if ($this->getSettings()->expandSingles && $context == 'index') {

            // Are there any Singles at all?
            if (array_key_exists('singles', $sources)) {
                craft()->expandedSingles->createSinglesList($context, $sources);
            }
        }
    }
 
}
