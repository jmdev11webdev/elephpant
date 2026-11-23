import Phaser from 'phaser';
import type { MapSceneData } from '@/game/scenes/BaseMapScene';

export class MainScene extends Phaser.Scene {
    private hasStarted = false;

    public constructor() {
        super({ key: 'MainScene' });
    }

    public preload(): void {
        this.load.image(
            'stack-region-tiles',
            '/assets/tilesets/stack-region-tiles.png',
        );
        this.load.spritesheet(
            'player-elephpant',
            '/assets/tilesets/player-elephpant.png',
            {
                frameWidth: 16,
                frameHeight: 16,
            },
        );
        this.load.spritesheet('npcs', '/assets/tilesets/npcs.png', {
            frameWidth: 16,
            frameHeight: 16,
        });
        this.load.tilemapTiledJSON(
            'localhost-town',
            '/assets/maps/localhost-town.json',
        );
        this.load.tilemapTiledJSON(
            'challenge-center',
            '/assets/maps/challenge-center.json',
        );
    }

    public create(): void {
        this.hasStarted = false;
        this.registerPlayerAnimations();

        const { width, height } = this.scale;
        const centerX = Math.round(width / 2);
        const centerY = Math.round(height / 2);

        this.cameras.main.setBackgroundColor('#0f172a');

        const frame = this.add.graphics();
        frame.lineStyle(2, 0x334155, 1);
        frame.strokeRect(24, 24, width - 48, height - 48);
        frame.lineStyle(2, 0x6366f1, 1);
        frame.strokeRect(32, 32, width - 64, height - 64);

        const mascot = this.add.sprite(
            centerX,
            centerY - 96,
            'player-elephpant',
            0,
        );
        mascot.setScale(4);

        this.add
            .text(centerX, centerY - 12, 'elePHPant', {
                fontFamily: 'monospace',
                fontSize: '48px',
                fontStyle: 'bold',
                color: '#f8fafc',
                align: 'center',
            })
            .setOrigin(0.5, 0.5);

        const promptText = this.add
            .text(
                centerX,
                centerY + 46,
                'Press Enter, Space, or Click to Start',
                {
                    fontFamily: 'monospace',
                    fontSize: '18px',
                    color: '#a5b4fc',
                    align: 'center',
                },
            )
            .setOrigin(0.5, 0.5);

        this.tweens.add({
            targets: promptText,
            alpha: 0.35,
            duration: 750,
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut',
        });

        this.add
            .text(
                centerX,
                height - 56,
                'Move: WASD / Arrows  •  Interact: E / Space / Enter',
                {
                    fontFamily: 'monospace',
                    fontSize: '13px',
                    color: '#64748b',
                    align: 'center',
                },
            )
            .setOrigin(0.5, 0.5);

        this.input.keyboard?.addCapture([Phaser.Input.Keyboard.KeyCodes.SPACE]);
        this.input.keyboard?.once('keydown-ENTER', () => {
            this.startGame();
        });
        this.input.keyboard?.once('keydown-SPACE', () => {
            this.startGame();
        });
        this.input.once('pointerdown', () => {
            this.startGame();
        });
    }

    private registerPlayerAnimations(): void {
        const directions: Array<{ key: string; start: number; end: number }> = [
            { key: 'player-walk-down', start: 0, end: 2 },
            { key: 'player-walk-up', start: 3, end: 5 },
            { key: 'player-walk-left', start: 6, end: 8 },
            { key: 'player-walk-right', start: 9, end: 11 },
        ];

        for (const anim of directions) {
            if (!this.anims.exists(anim.key)) {
                this.anims.create({
                    key: anim.key,
                    frames: this.anims.generateFrameNumbers(
                        'player-elephpant',
                        {
                            start: anim.start,
                            end: anim.end,
                        },
                    ),
                    frameRate: 8,
                    repeat: -1,
                });
            }
        }
    }

    private startGame(): void {
        if (this.hasStarted) {
            return;
        }

        this.hasStarted = true;
        this.cameras.main.once(
            Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE,
            () => {
                this.scene.start('OverworldScene', {
                    mapKey: 'localhost-town',
                    spawnId: 'default',
                    facing: 'down',
                } satisfies MapSceneData);
            },
        );
        this.cameras.main.fadeOut(180, 15, 23, 42);
    }
}
