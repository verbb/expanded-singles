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

        // Get logged-in user
        $user = craft()->userSession->getUser();

        // Create list of Singles
        foreach ($singleSections as $single) {
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->locale = craft()->i18n->getPrimarySiteLocale()->id;
            $criteria->status = null;
            $criteria->sectionId = $single->id;
            $entry = $criteria->first();

            if ($entry && $user->can('editEntries:'.$single->id)) {
                $url = $entry->getCpEditUrl();

                $singles['single:'.$single->id] = array(
                    'label'     => $single->name,
                    'data'      => array('url' => $url),
                    'criteria'  => array('section' => $single),
                );
            }
        }

        // array_splice() doesn't preserve the array keys, which is a must!
        $this->_array_splice_preserve_keys($sources, 1, 0, $singles);
    
        // Remove original Singles links
        unset($sources['singles']);

        // Insert some JS to go straight to single page when clicked - rather than listing in Index Table
        if ($this->getSettings()->redirectToEntry) {
            $js = '$(function() {' .
                '$(".content.has-sidebar #sidebar nav a[data-url]").each(function(i, e) {' .
                    'var link = "<a href=" + $(this).data("url") + ">" + $(this).text() + "</a>";' .

                    '$(this).replaceWith($(link));' .
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
