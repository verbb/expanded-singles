<?php
namespace Craft;

class ExpandedSinglesService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    public function getPlugin()
    {
        return craft()->plugins->getPlugin('expandedSingles');
    }

    public function getSettings()
    {
        return $this->getPlugin()->getSettings();
    }

    public function createSinglesList($context, &$sources)
    {
        $singles[] = array('heading' => 'Singles');

        // Grab all the Singles
        $singleSections = craft()->sections->getSectionsByType(SectionType::Single);

        // Create list of Singles
        foreach ($singleSections as $single) {
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->status = null;
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
            // array_splice() doesn't preserve the array keys, which is a must!
            $this->_array_splice_preserve_keys($sources, 1, 0, $singles);
        
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


    // Private Methods
    // =========================================================================

    private function _array_splice_preserve_keys(&$input, $offset, $length = null, $replacement = array()) {
        if (empty($replacement)) {
            return array_splice($input, $offset, $length);
        }

        $part_before  = array_slice($input, 0, $offset, true);
        $part_removed = array_slice($input, $offset, $length, true);
        $part_after   = array_slice($input, $offset+$length, null, true);

        $input = array_merge($part_before, $replacement, $part_after);

        return $part_removed;
    }
}
