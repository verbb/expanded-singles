<?php
namespace Craft;

class ExpandedSinglesPlugin extends BasePlugin
{
    /* --------------------------------------------------------------
    * PLUGIN INFO
    * ------------------------------------------------------------ */

    public function getName()
    {
        return Craft::t('Expanded Singles');
    }

    public function getVersion()
    {
        return '0.1';
    }

    public function getDeveloper()
    {
        return 'S. Group';
    }

    public function getDeveloperUrl()
    {
        return 'http://sgroup.com.au';
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

    public function createSinglesList(&$sources, $context)
    {
        $singles[] = array('heading' => 'Singles');

        // Grab all the Singles
        $singleSections = craft()->sections->getSectionsByType(SectionType::Single);

        // Create list of Singles
        foreach ($singleSections as $single) {
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->sectionId = $single->id;
            $entry = $criteria->first();

            if ($entry) {
                $url = $entry->getCpEditUrl();

                $singles['single:'.$single->id] = array(
                    'label'     => $single->name,
                    'data'      => array('url' => $url),
                    'criteria'  => array('section' => $single),
                );
            }
        }

        // Insert it right after 'All Entries'
        if ($context == 'index') {
            array_splice($sources, 1, 0, $singles);

            // Remove original Singles links
            unset($sources['singles']);
        }

        // Insert some JS to go straight to single page when clicked - rather than listing in Index Table
        if ($this->getSettings()->redirectToEntry) {
            $js = '$(function() {' .
                '$(document).on("click", ".content.has-sidebar #sidebar nav a[data-url]", function(e) {' .
                    'e.preventDefault();' .
                    '$(this).removeClass("sel");' .
                    'location.href = $(this).attr("data-url")' .
                '});' .
            '});';

            craft()->templates->includeJs($js);
        }
    }



    /* --------------------------------------------------------------
    * HOOKS
    * ------------------------------------------------------------ */

    public function modifyEntrySources(&$sources, $context)
    {
        if ($this->getSettings()->expandSingles) {

            // Are there any Singles at all?
            if (array_key_exists('singles', $sources)) {
                $this->createSinglesList($sources, $context);
            }
        }
    }
 
}
