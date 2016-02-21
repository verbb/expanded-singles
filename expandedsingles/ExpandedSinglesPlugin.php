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
        return '0.2.3';
    }

    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    public function getDeveloper()
    {
        return 'S. Group';
    }

    public function getDeveloperUrl()
    {
        return 'http://sgroup.com.au';
    }

    public function getPluginUrl()
    {
        return 'https://github.com/engram-design/ExpandedSingles';
    }

    public function getDocumentationUrl()
    {
        return $this->getPluginUrl() . '/blob/master/README.md';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/engram-design/ExpandedSingles/master/changelog.json';
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
        if ($this->getSettings()->expandSingles) {

            // Are there any Singles at all?
            if (array_key_exists('singles', $sources)) {
                craft()->expandedSingles->createSinglesList($context, $sources);
            }
        }
    }
 
}
