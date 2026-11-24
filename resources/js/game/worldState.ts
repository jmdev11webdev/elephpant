export interface DisciplineRankSnapshot {
    rank: string;
    xp: number;
    completed: number;
    total: number;
}

export interface ResumeStateSnapshot {
    zone: string;
    x: number;
    y: number;
    facing: 'up' | 'down' | 'left' | 'right';
    playtimeSeconds: number;
    fromSlot: number | null;
}

export interface WorldStateSnapshot {
    unlockedTriggers: string[];
    equippedTitle: string;
    level: number;
    totalXp: number;
    completedChallenges: number;
    totalChallenges: number;
    disciplineRanks: Record<string, DisciplineRankSnapshot>;
    unlockedBadges: string[];
    resume?: ResumeStateSnapshot;
}

export const DEFAULT_WORLD_STATE: WorldStateSnapshot = {
    unlockedTriggers: [],
    equippedTitle: 'Localhost Apprentice',
    level: 1,
    totalXp: 0,
    completedChallenges: 0,
    totalChallenges: 23,
    disciplineRanks: {
        programmer: { rank: 'Novice', xp: 0, completed: 0, total: 5 },
        developer: { rank: 'Novice', xp: 0, completed: 0, total: 5 },
        maintainer: { rank: 'Novice', xp: 0, completed: 0, total: 5 },
    },
    unlockedBadges: ['Localhost Pioneer'],
    resume: {
        zone: 'foundations-hub',
        x: 24,
        y: 19,
        facing: 'down',
        playtimeSeconds: 0,
        fromSlot: null,
    },
};

export const DISTRICT_SPAWNS: Record<
    string,
    {
        mapKey: string;
        x: number;
        y: number;
        facing: 'up' | 'down' | 'left' | 'right';
    }
> = {
    'foundations-hub': {
        mapKey: 'localhost-town',
        x: 24,
        y: 19,
        facing: 'down',
    },
    'localhost-town': {
        mapKey: 'localhost-town',
        x: 24,
        y: 19,
        facing: 'down',
    },
    'algorithm-woods': {
        mapKey: 'algorithm-woods',
        x: 2,
        y: 17,
        facing: 'right',
    },
    'artisan-city': {
        mapKey: 'artisan-city',
        x: 23,
        y: 33,
        facing: 'up',
    },
    'legacy-harbor': {
        mapKey: 'legacy-harbor',
        x: 45,
        y: 17,
        facing: 'left',
    },
};

export function zoneToMapKey(zone: string): string {
    return zone === 'foundations-hub' ? 'localhost-town' : zone;
}

export function mapKeyToZone(mapKey: string): string {
    return mapKey === 'localhost-town' ? 'foundations-hub' : mapKey;
}
