<?php
namespace verbb\expandedsingles\services;

use verbb\expandedsingles\ExpandedSingles;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\models\Section;
use craft\events\RegisterElementSourcesEvent;

class SinglesList extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Create a new singles list and replace the old one with it
     *
     * @param RegisterElementSourcesEvent $event
     *
     * @return void
     */
    public function createSinglesList(RegisterElementSourcesEvent $event)
    {
        $singles[] = ['heading' => Craft::t('app', 'Singles')];

        // Grab all the Singles
        $singleSections = Craft::$app->sections->getSectionsByType(Section::TYPE_SINGLE);

        // Create list of Singles
        foreach ($singleSections as $single) {
            $entry = Entry::find()
                ->status(null)
                ->sectionId($single->id)
                ->one();

            if ($entry && Craft::$app->getUser()->checkPermission('editEntries:' . $single->id)) {
                $url = $entry->getCpEditUrl();

                $singles[] = [
                    'key'      => 'single:' . $single->id,
                    'label'    => $single->name,
                    'data'     => ['url' => $url],
                    'criteria' => ['section' => $single],
                ];
            }
        }

        // Replace original Singles link with new singles list
        array_splice($event->sources, 1, 1, $singles);

        // Insert some JS to go straight to single page when clicked - rather than listing in Index Table
        if (ExpandedSingles::$plugin->getSettings()->redirectToEntry) {
            $js = '$(function() {' .
                '$("#main-content #sidebar nav a[data-url]").each(function(i, e) {' .
                    'var link = "<a href=" + $(this).data("url") + ">" + $(this).text() + "</a>";' .
                        '$(this).replaceWith($(link));' .
                    '});' .
                '});';

            Craft::$app->view->registerJs($js);
        }
    }

    /**
     * Create a new singles list and replace the old one with it. This is a slightly modified and short-hand version
     * of `createSinglesList`, and is used for a Redactor field. This uses a simple array, and outputs an array of
     * section:id combinations. This is because Redactor shows entries grouped by channels.
     *
     * @param array $sources
     *
     * @return array
     */
    public function createSectionedSinglesList(array $sources)
    {
        // Grab all the Singles
        $singleSections = Craft::$app->sections->getSectionsByType(Section::TYPE_SINGLE);

        // Create list of Singles
        foreach ($singleSections as $single) {
            $entry = Entry::find()
                ->status(null)
                ->sectionId($single->id)
                ->one();

            if ($entry && Craft::$app->getUser()->checkPermission('editEntries:' . $single->id)) {
                $url = $entry->getCpEditUrl();

                $singles[] = 'single:' . $single->id;
            }
        }

        // Replace original Singles link with new singles list
        array_splice($sources, 0, 1, $singles);

        return $sources;
    }
}
