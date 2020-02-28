<?php
namespace verbb\expandedsingles\services;

use verbb\expandedsingles\ExpandedSingles;

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
            $entry = null;
            $siteUrls = [];
            
            // If this is a multi-site, we need to grab the first site-enabled entry
            // Kind of annoying we can't query multiple site ids, or _all_ sites
            // https://github.com/craftcms/cms/issues/2854
            if (Craft::$app->getIsMultiSite()) {
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
            } else {
                $entry = Entry::find()
                    ->status(null)
                    ->sectionId($single->id)
                    ->one();
            }

            $singles = [];

            if (($entry || $siteUrls) && Craft::$app->getUser()->checkPermission('editEntries:' . $single->uid)) {
                $url = $entry && !$siteUrls ? $entry->getCpEditUrl() : '';

                $siteIds = $entry ? ArrayHelper::getColumn($entry->getSupportedSites(), 'siteId') : array_keys($siteUrls);;

                $singles[] = [
                    'key' => 'single:' . $single->uid,
                    'label' => Craft::t('site', $single->name),
                    'sites' => $single->getSiteIds(),
                    'data' => [
                        'url' => $url,
                        'handle' => $single->handle,
                        'sites' => implode(',', $siteIds),
                        'siteUrls' => json_encode($siteUrls),
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
            $css = '.cp-nav-link-mask {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                z-index: 10;
            };';

            Craft::$app->view->registerCss($css);

            $js = <<<'JS'
$(function() {
    var $siteMenuBtn = $('#page-container').find('.sitemenubtn:first');
    var storedSiteId = Craft.getLocalStorage('BaseElementIndex.siteId');

    var updateSingleUrls = function(siteId = null) {
        $("#main-content #sidebar nav a[data-url]").each(function(i, e) {
            var url = siteId != null && $(this).data("siteurls")[siteId] ? $(this).data("siteurls")[siteId] : $(this).data("url");

            if (!url) return;

            // Update if overlay link already exists, create and append if not
            var $link = $(this).parent().find('a.cp-nav-link-mask');
            if ($link.length) {
                $link.attr('href', url);
            } else {
                $link = $("<a class=\"cp-nav-link-mask\" href=" + url + ">" + $(this).text() + "</a>");
                $(this).parent().append($link);
            }
        });
    }

    var onSelect = function(ev) {
        var $option = $(ev.selectedOption);
        updateSingleUrls($option.data('site-id'));
    }

    // If we have a site menu
    if ($siteMenuBtn.length) {
        // Set links to stored siteId
        updateSingleUrls(storedSiteId);

        // Add listener when selecting site
        this.siteMenu = $siteMenuBtn.menubtn().data('menubtn').menu;
        this.siteMenu.on('optionselect', onSelect);
    } else {
        // Set links to default link
        updateSingleUrls();
    }
});
JS;

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
        $sections = Craft::$app->getSections()->getAllSections();

        $sites = Craft::$app->getSites()->getAllSites();

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
