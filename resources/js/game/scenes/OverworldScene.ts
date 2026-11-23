import { BaseMapScene } from '@/game/scenes/BaseMapScene';

export class OverworldScene extends BaseMapScene {
    protected readonly defaultMapKey = 'localhost-town';
    protected readonly defaultSpawnId = 'default';

    public constructor() {
        super({ key: 'OverworldScene' });
    }
}
