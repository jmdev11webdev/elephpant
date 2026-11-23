import type { ShowDialoguePayload } from '@/game/events';

export const DIALOGUE_CATALOG: Record<string, ShowDialoguePayload> = {
    'mentor-ada': {
        speaker: 'Mentor Ada',
        kind: 'npc',
        lines: [
            'Welcome to Localhost Town, the heart of the Stack Region!',
            'Step inside the indigo Challenge Center to the northeast to inspect the coding terminal.',
            'Soon, roads to Algorithm Woods, Artisan City, and Legacy Harbor will open for your adventure.',
        ],
    },
    'center-guide': {
        speaker: 'Lab Coordinator',
        kind: 'npc',
        lines: [
            'Welcome to the Localhost Town Challenge Center!',
            'Every discipline—Programmer, Developer, and Maintainer—starts right here.',
            'Check out the glowing Challenge Terminal on the east side of the room when you are ready.',
        ],
    },
    'sign-localhost-town': {
        speaker: 'Town Sign',
        kind: 'sign',
        lines: [
            'LOCALHOST TOWN — "Where every great stack begins."',
            'North-East: Challenge Center  |  West: Starter House',
        ],
    },
    'sign-algorithm-woods': {
        speaker: 'East Road Sign',
        kind: 'sign',
        lines: [
            'EAST -> ALGORITHM WOODS (Programmer’s Path)',
            'Gate sealed for now. Logic, data structures, and computational trials await ahead.',
        ],
    },
    'sign-artisan-city': {
        speaker: 'North Road Sign',
        kind: 'sign',
        lines: [
            'NORTH -> ARTISAN CITY (Developer’s Path)',
            'Gate sealed for now. Full-stack guilds, APIs, and architecture landmarks lie beyond.',
        ],
    },
    'sign-legacy-harbor': {
        speaker: 'West Road Sign',
        kind: 'sign',
        lines: [
            'WEST -> LEGACY HARBOR (Maintainer’s Path)',
            'Gate sealed for now. Refactoring docks, debugging yards, and QA vaults await.',
        ],
    },
    'terminal-challenge-center': {
        speaker: 'Challenge Terminal',
        kind: 'terminal',
        lines: [
            '[TERMINAL ONLINE] Challenge Center Core v0.2 — Localhost Node.',
            'The Developer Challenge System and embedded code workspace will unlock in Milestone 4.',
            'Explore Localhost Town in the meantime and prepare for your first trials!',
        ],
    },
};

export function resolveDialogue(dialogueKey: string): ShowDialoguePayload {
    return (
        DIALOGUE_CATALOG[dialogueKey] ?? {
            kind: 'sign',
            lines: ['There is nothing written here yet.'],
        }
    );
}
