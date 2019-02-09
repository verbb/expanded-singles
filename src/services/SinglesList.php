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
            $entry = null;
            
            // If this is a multi-site, we need to grab the first site-enabled entry
            // Kind of annoying we can't query multiple site ids, or _all_ sites
            // https://github.com/craftcms/cms/issues/2854
            if (Craft::$app->getIsMultiSite()) {
                foreach (Craft::$app->getSites()->getAllSiteIds() as $key => $siteId) {
                    $entry = Entry::find()
                        ->siteId($siteId)
                        ->status(null)
                        ->sectionId($single->id)
                        ->one();

                    if ($entry) {
                        break;
                    }
                }
            } else {
                $entry = Entry::find()
                    ->status(null)
                    ->sectionId($single->id)
                    ->one();
            }

            if ($entry && Craft::$app->getUser()->checkPermission('editEntries:' . $single->uid)) {
                $url = $entry->getCpEditUrl();

                $singles[] = [
                    'key' => 'single:' . $single->uid,
                    'label' => Craft::t('site', $single->name),
                    'sites' => $single->getSiteIds(),
                    'data' => [
                        'url' => $url,
                        'handle' => $single->handle
                    ],
                    'criteria' => [
                        'sectionId' => $single->id,
                        'editable' => false,
                    ]
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

        // Update our element indexes to use the same columns as the original singles items
        // $newSettings = [];
        // $settings = Craft::$app->getElementIndexes()->getSettings(Entry::class);

        // // Get the singles index info - if none exists, then no need to go further
        // $singlesSettings = $settings['sources']['singles'] ?? null;

        // if (!$singlesSettings) {
        //     return;
        // }

        // foreach ($singles as $key => $single) {
        //     if (isset($single['key'])) {
        //         $newSettings[$single['key']] = $singlesSettings;
        //     }
        // }

        // Craft::$app->getElementIndexes()->saveSettings(Entry::class, $newSettings);
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
        $singles = [];
        foreach ($singleSections as $single) {
            $entry = Entry::find()
                ->status(null)
                ->sectionId($single->id)
                ->one();

            if ($entry && Craft::$app->getUser()->checkPermission('editEntries:' . $single->uid)) {
                $url = $entry->getCpEditUrl();

                $singles[] = 'single:' . $single->uid;
            }
        }

        // Replace original Singles link with new singles list
        array_splice($sources, 0, 1, $singles);

        return $sources;
    }
}
