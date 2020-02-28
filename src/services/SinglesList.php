<?php
namespace verbb\expandedsingles\services;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\assetbundles\ExpandedSinglesAsset;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
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
            $siteUrls = [];
            
            foreach (Craft::$app->getSites()->getAllSiteIds() as $key => $siteId) {
                $siteEntry = Entry::find()
                    ->siteId($siteId)
                    ->status(null)
                    ->sectionId($single->id)
                    ->one();

                if ($siteEntry) {
                    $siteUrls[$siteId] = $siteEntry->getCpEditUrl();
                }
            }

            if ($siteUrls && Craft::$app->getUser()->checkPermission('editEntries:' . $single->uid)) {
                $singles[] = [
                    'key' => 'single:' . $single->uid,
                    'label' => Craft::t('site', $single->name),
                    'data' => [
                        'cp-nav' => true,
                        'handle' => $single->handle,
                        'sites' => implode(',', $single->getSiteIds()),
                        'site-urls' => json_encode($siteUrls),
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
            Craft::$app->getView()->registerAssetBundle(ExpandedSinglesAsset::class);
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
        $sections = Craft::$app->getSections()->getAllSections();

        $sites = Craft::$app->getSites()->getAllSites();

        $singles = [];

        foreach ($sections as $section) {
            if ($section->type === Section::TYPE_SINGLE) {
                $sectionSiteSettings = $section->getSiteSettings();
                
                foreach ($sites as $site) {
                    if (isset($sectionSiteSettings[$site->id]) && $sectionSiteSettings[$site->id]->hasUrls) {
                        $singles[] = 'single:' . $section->uid;
                    }
                }
            }
        }

        // Replace original Singles link with new singles list
        array_splice($sources, 0, 1, $singles);

        return $sources;
    }
}
