import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import type { ScreenshotSetupContext } from '@verbb/craft-screenshots/types';

const supportDir = dirname(fileURLToPath(import.meta.url));
const seedScript = readFileSync(join(supportDir, 'seed', 'seed-entry-sections.php'), 'utf8');

/** Seed a recognisable mix of Singles, Channels and Structures for the Entries navigation. */
export async function seedExpandedSinglesFixture(context: ScreenshotSetupContext): Promise<void> {
    const output = await context.runCraftScript(seedScript, { label: 'seed-expanded-singles-sections' });
    const fixture = JSON.parse(output.trim()) as { singles?: number; channels?: number; structures?: number };

    if (fixture.singles !== 5 || fixture.channels !== 2 || fixture.structures !== 1) {
        throw new Error(`Invalid Expanded Singles fixture payload: ${output}`);
    }
}
