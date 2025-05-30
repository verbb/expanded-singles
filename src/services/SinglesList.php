<?php
namespace verbb\expandedsingles\services;

use verbb\expandedsingles\ExpandedSingles;
use verbb\expandedsingles\assetbundles\ExpandedSinglesAsset;

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

            // Fetch all single entries for their IDs (direct db call for performance)
            $singleEntries = $this->_getSingleEntries($singleSections);

            // Create list of Singles
            foreach ($singleSections as $single) {
                $siteUrls = [];

                foreach (Craft::$app->getSites()->getAllSiteIds() as $siteId) {
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
                            'sites' => implode(',', $single->getSiteIds()),
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

    private function _getSingleEntries($singleSections): array
    {
        $singles = [];

        $singleEntries = Entry::find()
            ->sectionId(ArrayHelper::getColumn($singleSections, 'id'))
            ->siteId('*')
            ->status(null)
            ->all();

        foreach ($singleEntries as $singleEntry) {
            $singles[$singleEntry->sectionId . ':' . $singleEntry->siteId] = $singleEntry;
        }

        return $singles;
    }
}
