<?php
namespace verbb\expandedsingles\services;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\web\assets\cp\ExpandedSinglesAsset;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\models\Section;
use craft\events\RegisterElementSourcesEvent;

class SinglesList extends Component
{
    // Properties
    // =========================================================================

    private array $singles = [];


    // Public Methods
    // =========================================================================

    public function createSinglesList(RegisterElementSourcesEvent $event): void
    {
        $singles = [];

        if (!$this->singles) {
            $singles[] = ['heading' => Craft::t('app', 'Singles')];

            // Grab all the Singles
            $singleSections = Craft::$app->getEntries()->getSectionsByType(Section::TYPE_SINGLE);

            // Keep both the entry query and rendered source metadata within the user's editable sites.
            $sitesService = Craft::$app->getSites();
            $siteIds = array_values(array_intersect(
                $sitesService->getAllSiteIds(),
                $sitesService->getEditableSiteIds(),
            ));

            // Fetch all single entries for their IDs (direct db call for performance)
            $singleEntries = $this->_getSingleEntries($singleSections, $siteIds);

            // Create list of Singles
            foreach ($singleSections as $single) {
                $siteUrls = [];
                $singleSiteIds = array_values(array_intersect($single->getSiteIds(), $siteIds));

                foreach ($singleSiteIds as $siteId) {
                    // Don't do an element query here, which hurts performance. We just want the cpEditUrl.
                    // https://github.com/verbb/expanded-singles/issues/34
                    $siteEntry = $singleEntries[$single->id . ':' . $siteId] ?? null;

                    if ($siteEntry) {
                        $siteUrls[$siteId] = $siteEntry->getCpEditUrl();
                    }
                }

                if ($siteUrls && Craft::$app->getUser()->checkPermission('viewEntries:' . $single->uid)) {
                    $singles[] = [
                        'key' => 'single:' . $single->uid,
                        'label' => Craft::t('site', $single->name),
                        'data' => [
                            'cp-nav' => true,
                            'handle' => $single->handle,
                            'sites' => implode(',', $singleSiteIds),
                            'site-urls' => Json::encode($siteUrls),
                        ],
                        'criteria' => [
                            'sectionId' => $single->id,
                        ],
                    ];
                }
            }

            $this->singles = $singles;
        }

        // Replace original Singles link with new singles list
        array_splice($event->sources, 1, 1, $this->singles);

        // Insert some JS to go straight to single page when clicked - rather than listing in Index Table
        if (ExpandedSingles::$plugin->getSettings()->redirectToEntry) {
            // Only output this for CP-requests, as this can be called from the front-end.
            if (Craft::$app->getRequest()->getIsCpRequest()) {
                Craft::$app->getView()->registerAssetBundle(ExpandedSinglesAsset::class);
            }
        }
    }

    /**
     * Create a new singles list and replace the old one with it. This is a slightly modified and shorthand version
     * of `createSinglesList`, and is used for a Redactor field. This uses a simple array, and outputs an array of
     * section:id combinations. This is because Redactor shows entries grouped by channels.
     */
    public function createSectionedSinglesList(array $sources): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();

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


    // Private Methods
    // =========================================================================

    private function _getSingleEntries(array $singleSections, array $siteIds): array
    {
        $singles = [];

        // An empty siteId criterion falls back to the current site, so stop before querying.
        if (!$siteIds) {
            return $singles;
        }

        $singleEntries = Entry::find()
            ->sectionId(ArrayHelper::getColumn($singleSections, 'id'))
            ->siteId($siteIds)
            ->status(null)
            ->all();

        foreach ($singleEntries as $singleEntry) {
            $singles[$singleEntry->sectionId . ':' . $singleEntry->siteId] = $singleEntry;
        }

        return $singles;
    }
}
