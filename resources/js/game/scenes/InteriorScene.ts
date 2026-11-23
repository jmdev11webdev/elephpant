import { BaseMapScene } from '@/game/scenes/BaseMapScene';

export class InteriorScene extends BaseMapScene {
    protected readonly defaultMapKey = 'challenge-center';
    protected readonly defaultSpawnId = 'entrance';

    public constructor() {
        super({ key: 'InteriorScene' });
    }
}
