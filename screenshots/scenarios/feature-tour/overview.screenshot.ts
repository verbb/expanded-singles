import { defineScreenshotScenario } from '@verbb/craft-screenshots/api';

import { seedExpandedSinglesFixture } from '../../support/fixtures';

export default defineScreenshotScenario({
    id: 'expanded-singles-feature-tour-overview',
    output: 'feature-tour/expanded-singles-craft5-sidebar.png',
    route: '/admin/content/entries',
    viewport: {
        width: 1280,
        height: 720,
        deviceScaleFactor: 2,
    },
    expectedOutput: {
        width: 1460,
        height: 1220,
    },
    setup: seedExpandedSinglesFixture,
    waitFor: [
        { type: 'loadState', state: 'networkidle' },
        { type: 'text', text: 'About Us' },
        { type: 'text', text: 'Pages' },
        { type: 'selector', selector: '#elements', state: 'visible' },
    ],
    preSteps: [
        {
            type: 'evaluate',
            expression: `
                (() => {
                    if (document.activeElement instanceof HTMLElement) {
                        document.activeElement.blur();
                    }

                    window.scrollTo(0, 0);
                })();
            `,
        },
        { type: 'wait', waitFor: { type: 'timeout', ms: 200 } },
    ],
    target: {
        type: 'clip',
        x: 0,
        y: 0,
        width: 730,
        height: 610,
    },
    caption: 'Craft 5’s Entries index with each Single shown directly in the source navigation.',
    intent: 'Focuses the current Craft 5 Entries workflow on the global navigation, expanded source sidebar and visible Single rows.',
});
