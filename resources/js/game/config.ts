import Phaser from 'phaser';
import { InteriorScene } from '@/game/scenes/InteriorScene';
import { MainScene } from '@/game/scenes/MainScene';
import { OverworldScene } from '@/game/scenes/OverworldScene';

export const GAME_WIDTH = 960;
export const GAME_HEIGHT = 540;

export const gameConfig: Phaser.Types.Core.GameConfig = {
    type: Phaser.AUTO,
    width: GAME_WIDTH,
    height: GAME_HEIGHT,
    backgroundColor: '#0f172a',
    pixelArt: true,
    antialias: false,
    roundPixels: true,
    scale: {
        mode: Phaser.Scale.FIT,
        autoCenter: Phaser.Scale.CENTER_BOTH,
        width: GAME_WIDTH,
        height: GAME_HEIGHT,
    },
    scene: [MainScene, OverworldScene, InteriorScene],
};

export function createGameConfig(
    parent: HTMLElement,
): Phaser.Types.Core.GameConfig {
    return {
        ...gameConfig,
        parent,
    };
}
