/** Seed the section mix shown in the Expanded Singles feature screenshot. */

use craft\elements\Entry;
use craft\helpers\Json;
use craft\models\EntryType;
use craft\models\Section;
use craft\models\Section_SiteSettings;

$entries = Craft::$app->getEntries();
$site = Craft::$app->getSites()->getPrimarySite();

$definitions = [
    [Section::TYPE_SINGLE, 'About Us', 'aboutUs'],
    [Section::TYPE_SINGLE, 'Contact', 'contact'],
    [Section::TYPE_SINGLE, 'Homepage', 'homepage'],
    [Section::TYPE_SINGLE, 'Landing Page', 'landingPage'],
    [Section::TYPE_SINGLE, 'News Index', 'newsIndex'],
    [Section::TYPE_CHANNEL, 'News', 'news'],
    [Section::TYPE_CHANNEL, 'Events', 'events'],
    [Section::TYPE_STRUCTURE, 'Pages', 'pages'],
];

foreach ($definitions as [$type, $name, $handle]) {
    if ($entries->getSectionByHandle($handle)) {
        continue;
    }

    $entryType = new EntryType([
        'name' => $name,
        'handle' => $handle . 'Type',
        'hasTitleField' => true,
    ]);

    if (!$entries->saveEntryType($entryType)) {
        throw new RuntimeException('Unable to save entry type: ' . Json::encode($entryType->getErrors()));
    }

    $section = new Section([
        'name' => $name,
        'handle' => $handle,
        'type' => $type,
    ]);
    $section->setEntryTypes([$entryType]);
    $section->setSiteSettings([
        new Section_SiteSettings([
            'siteId' => $site->id,
            'enabledByDefault' => true,
            'hasUrls' => true,
            'uriFormat' => $type === Section::TYPE_SINGLE ? $handle : $handle . '/{slug}',
            'template' => '_screenshot/entry',
        ]),
    ]);

    if ($type === Section::TYPE_STRUCTURE) {
        $section->maxLevels = null;
    }

    if (!$entries->saveSection($section)) {
        throw new RuntimeException('Unable to save section: ' . Json::encode($section->getErrors()));
    }
}

$postDate = new DateTime('2025-04-15 12:00:00', new DateTimeZone('UTC'));

foreach ($entries->getSectionsByType(Section::TYPE_SINGLE) as $section) {
    $entry = Entry::find()
        ->sectionId($section->id)
        ->siteId($site->id)
        ->status(null)
        ->one();

    if (!$entry) {
        throw new RuntimeException("Single entry for `{$section->handle}` was not created.");
    }

    $entry->postDate = clone $postDate;

    if (!Craft::$app->getElements()->saveElement($entry)) {
        throw new RuntimeException('Unable to update Single entry: ' . Json::encode($entry->getErrors()));
    }
}

echo Json::encode([
    'singles' => count($entries->getSectionsByType(Section::TYPE_SINGLE)),
    'channels' => count($entries->getSectionsByType(Section::TYPE_CHANNEL)),
    'structures' => count($entries->getSectionsByType(Section::TYPE_STRUCTURE)),
], JSON_THROW_ON_ERROR);
