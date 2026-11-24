import type { ShowDialoguePayload } from '@/game/events';
import type { WorldStateSnapshot } from '@/game/worldState';

export const DIALOGUE_CATALOG: Record<string, ShowDialoguePayload> = {
    'mentor-ada': {
        speaker: 'Mentor Ada',
        kind: 'npc',
        lines: [
            'Welcome to Localhost Town, the central hub of The Stack Region!',
            'New to PHP? Step inside your Starter House to use the Localhost Academy terminal for 8 zero-knowledge Foundations steps.',
            'Ready to explore? Roads are open East to Algorithm Woods, North to Artisan City, and West to Legacy Harbor!',
        ],
    },
    'center-guide': {
        speaker: 'Lab Coordinator',
        kind: 'npc',
        lines: [
            'Welcome to the Localhost Town Challenge Center!',
            'Start with Foundations (Zero-Knowledge) or jump straight into Programmer, Developer, and Maintainer trials.',
            'Interact with the glowing Challenge Terminal on the east side to browse all 23 Stack Region challenges.',
        ],
    },
    'sign-localhost-town': {
        speaker: 'Town Sign',
        kind: 'sign',
        lines: [
            'LOCALHOST TOWN — "Where every great stack begins."',
            'West: Starter House  |  North-East: Challenge Center',
        ],
    },
    'sign-algorithm-woods': {
        speaker: 'East Road Sign',
        kind: 'sign',
        lines: [
            'EAST → ALGORITHM WOODS (Programmer’s Path)',
            'Follow the trail east to reach the Stepping-Stone Bridge and Logic Pavilion.',
        ],
    },
    'sign-artisan-city': {
        speaker: 'North Road Sign',
        kind: 'sign',
        lines: [
            'NORTH → ARTISAN CITY (Developer’s Path)',
            'Head north to reach the API Gateway checkpoint and the Billing Guild.',
        ],
    },
    'sign-legacy-harbor': {
        speaker: 'West Road Sign',
        kind: 'sign',
        lines: [
            'WEST → LEGACY HARBOR (Maintainer’s Path)',
            'Head west to inspect the Dock Crane lock and enter the Security Warehouse.',
        ],
    },
    'sign-woods-welcome': {
        speaker: 'Algorithm Woods',
        kind: 'sign',
        lines: [
            'ALGORITHM WOODS — Programmer’s Path',
            'Solve the Code-Locked Terminal by the river to deploy the Stepping-Stone Bridge to the Logic Pavilion.',
        ],
    },
    'sign-city-welcome': {
        speaker: 'Artisan City',
        kind: 'sign',
        lines: [
            'ARTISAN CITY — Developer’s Path',
            'Solve the API Gateway Terminal to open the avenue north to the Billing Guild.',
        ],
    },
    'sign-harbor-welcome': {
        speaker: 'Legacy Harbor',
        kind: 'sign',
        lines: [
            'LEGACY HARBOR — Maintainer’s Path',
            'Diagnose the Dock Crane Terminal to clear the pier west toward the Security Warehouse.',
        ],
    },
    'sign-gate-software-architect': {
        speaker: 'Future Expansion Gate',
        kind: 'sign',
        lines: [
            '[SEALED BORDER GATE] Future Specialization Frontier: Software Architect & DevOps Engineer.',
            'System design, distributed scaling, and CI/CD infrastructure trials lie beyond in a future expansion.',
        ],
    },
    'sign-gate-database-engineer': {
        speaker: 'Future Expansion Gate',
        kind: 'sign',
        lines: [
            '[SEALED BORDER GATE] Future Specialization Frontier: Database Engineer.',
            'SQL query planning, indexing, and transaction isolation trials await beyond this northern gate.',
        ],
    },
    'sign-gate-security-engineer': {
        speaker: 'Future Expansion Gate',
        kind: 'sign',
        lines: [
            '[SEALED BORDER GATE] Future Specialization Frontier: Security Engineer.',
            'Threat modeling, hardened authentication, and vulnerability defense trials await beyond this western pier.',
        ],
    },
    'terminal-challenge-center': {
        speaker: 'Challenge Terminal',
        kind: 'terminal',
        lines: ['[TERMINAL ONLINE] Opening Challenge Center workspace...'],
    },
};

export function resolveDialogue(
    dialogueKey: string,
    worldState?: WorldStateSnapshot,
): ShowDialoguePayload {
    if (dialogueKey === 'starter-progress-board' && worldState) {
        const badgeList =
            worldState.unlockedBadges.length > 0
                ? worldState.unlockedBadges.join(', ')
                : 'None yet';

        return {
            speaker: 'Starter House Trophy Board',
            kind: 'sign',
            lines: [
                `Title: ${worldState.equippedTitle}  •  Level ${worldState.level} (${worldState.totalXp} XP)`,
                `Solved Challenges: ${worldState.completedChallenges} / ${worldState.totalChallenges} across The Stack Region.`,
                `Unlocked Badges: ${badgeList}.`,
            ],
        };
    }

    if (dialogueKey === 'guild-programmer' && worldState) {
        const prog = worldState.disciplineRanks.programmer;

        return {
            speaker: 'Logic Pavilion Master',
            kind: 'npc',
            lines: [
                'Welcome to the Logic Pavilion of Algorithm Woods!',
                `Programmer’s Path Rank: ${prog?.rank ?? 'Novice'} (${prog?.xp ?? 0} XP • ${prog?.completed ?? 0}/${prog?.total ?? 5} solved).`,
                'Use the pavilion terminal to sharpen your algorithms and data structures.',
            ],
        };
    }

    if (dialogueKey === 'guild-developer' && worldState) {
        const dev = worldState.disciplineRanks.developer;

        return {
            speaker: 'Billing Guild Lead',
            kind: 'npc',
            lines: [
                'Welcome to Artisan City’s Billing Guild!',
                `Developer’s Path Rank: ${dev?.rank ?? 'Novice'} (${dev?.xp ?? 0} XP • ${dev?.completed ?? 0}/${dev?.total ?? 5} solved).`,
                'Use our studio terminal to build APIs, ledgers, and webhook guards.',
            ],
        };
    }

    if (dialogueKey === 'guild-maintainer' && worldState) {
        const maint = worldState.disciplineRanks.maintainer;

        return {
            speaker: 'Security Warehouse Chief',
            kind: 'npc',
            lines: [
                'Welcome to the Security Warehouse of Legacy Harbor!',
                `Maintainer’s Path Rank: ${maint?.rank ?? 'Novice'} (${maint?.xp ?? 0} XP • ${maint?.completed ?? 0}/${maint?.total ?? 5} solved).`,
                'Use the warehouse terminal to squash regressions, audit SQL, and fix race conditions.',
            ],
        };
    }

    return (
        DIALOGUE_CATALOG[dialogueKey] ?? {
            kind: 'sign',
            lines: ['There is nothing written here yet.'],
        }
    );
}
