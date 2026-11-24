import Phaser from 'phaser';
import { audioManager } from '@/game/audio/AudioManager';
import type { FootstepSurface } from '@/game/audio/AudioManager';
import { resolveDialogue } from '@/game/dialogue';
import { gameEvents } from '@/game/events';
import {
    DEFAULT_WORLD_STATE,
    mapKeyToZone,
    zoneToMapKey,
} from '@/game/worldState';
import type { WorldStateSnapshot } from '@/game/worldState';

export type Direction = 'down' | 'up' | 'left' | 'right';

export interface MapSceneData {
    mapKey?: string;
    spawnId?: string;
    tileX?: number;
    tileY?: number;
    facing?: Direction;
    transitioned?: boolean;
}

interface TiledProperty {
    name: string;
    type?: string;
    value: string | number | boolean;
}

interface WarpTrigger {
    tileX: number;
    tileY: number;
    targetMap: string;
    targetSpawn: string;
    facing?: Direction;
}

interface InteractiveTrigger {
    tileX: number;
    tileY: number;
    kind: 'npc' | 'sign' | 'terminal';
    dialogueKey: string;
    npcId?: string;
    challengeCenter?: boolean;
    filterDiscipline?: string;
    challengeId?: string;
    triggerId?: string;
}

interface BlockerTile {
    tileX: number;
    tileY: number;
    triggerId: string;
    openTileGid: number;
}

const TILE_SIZE = 16;
const STEP_DURATION_MS = 150;
const CAMERA_ZOOM = 3;
const FADE_DURATION_MS = 300;

const IDLE_FRAMES: Record<Direction, number> = {
    down: 0,
    up: 3,
    left: 6,
    right: 9,
};

const DIRECTION_DELTAS: Record<Direction, { dx: number; dy: number }> = {
    down: { dx: 0, dy: 1 },
    up: { dx: 0, dy: -1 },
    left: { dx: -1, dy: 0 },
    right: { dx: 1, dy: 0 },
};

const MAP_TO_SCENE: Record<string, string> = {
    'localhost-town': 'OverworldScene',
    'algorithm-woods': 'OverworldScene',
    'artisan-city': 'OverworldScene',
    'legacy-harbor': 'OverworldScene',
    'challenge-center': 'InteriorScene',
    'starter-house': 'InteriorScene',
    'logic-pavilion': 'InteriorScene',
    'billing-guild': 'InteriorScene',
    'security-warehouse': 'InteriorScene',
};

export abstract class BaseMapScene extends Phaser.Scene {
    protected abstract readonly defaultMapKey: string;
    protected abstract readonly defaultSpawnId: string;

    protected map!: Phaser.Tilemaps.Tilemap;
    protected groundLayer: Phaser.Tilemaps.TilemapLayer | null = null;
    protected detailLayer: Phaser.Tilemaps.TilemapLayer | null = null;
    protected collisionLayer: Phaser.Tilemaps.TilemapLayer | null = null;
    protected player!: Phaser.GameObjects.Sprite;

    protected currentMapKey = '';
    protected playerTileX = 0;
    protected playerTileY = 0;
    protected facing: Direction = 'down';

    protected isMoving = false;
    protected isPaused = false;
    protected isTransitioning = false;
    protected interactUnlockAt = 0;

    private warps = new Map<string, WarpTrigger>();
    private interactables = new Map<string, InteractiveTrigger>();
    private occupiedNpcTiles = new Set<string>();
    private blockers: BlockerTile[] = [];
    private terminalBadges = new Map<string, Phaser.GameObjects.Rectangle>();
    private interactPrompt?: Phaser.GameObjects.Text;

    private cursors?: Phaser.Types.Input.Keyboard.CursorKeys;
    private wasd?: {
        up: Phaser.Input.Keyboard.Key;
        down: Phaser.Input.Keyboard.Key;
        left: Phaser.Input.Keyboard.Key;
        right: Phaser.Input.Keyboard.Key;
    };
    private interactKeys: Phaser.Input.Keyboard.Key[] = [];
    private eventUnsubscribers: Array<() => void> = [];

    public create(data?: MapSceneData): void {
        this.isMoving = false;
        this.isPaused = false;
        this.isTransitioning = false;
        this.interactUnlockAt = 0;
        this.warps.clear();
        this.interactables.clear();
        this.occupiedNpcTiles.clear();
        this.blockers = [];
        this.terminalBadges.clear();
        this.interactKeys = [];

        const mapKey = data?.mapKey ?? this.defaultMapKey;
        const spawnId = data?.spawnId ?? this.defaultSpawnId;
        this.currentMapKey = mapKey;
        this.facing = data?.facing ?? 'down';

        this.cameras.main.setBackgroundColor('#0f172a');

        this.map = this.make.tilemap({ key: mapKey });
        const tileset = this.map.addTilesetImage(
            'stack-region-tiles',
            'stack-region-tiles',
        );

        if (!tileset) {
            throw new Error(
                `Failed to load tileset "stack-region-tiles" for map "${mapKey}".`,
            );
        }

        this.groundLayer = this.map.createLayer('ground', tileset, 0, 0);
        this.detailLayer = this.map.createLayer('ground_detail', tileset, 0, 0);
        this.collisionLayer = this.map.createLayer('collision', tileset, 0, 0);
        const overheadLayer = this.map.createLayer('overhead', tileset, 0, 0);

        this.groundLayer?.setDepth(0);
        this.detailLayer?.setDepth(1);
        this.collisionLayer?.setDepth(2);
        overheadLayer?.setDepth(20);

        const spawn = this.parseObjectLayer(spawnId);
        this.applyUnlockedBlockers();
        this.renderTerminalIndicators();

        // Backward-compatible Save Coordinate Guard: ensure saved coordinates are in-bounds & walkable
        const hasValidSavedCoords =
            typeof data?.tileX === 'number' &&
            typeof data?.tileY === 'number' &&
            this.canStepTo(data.tileX, data.tileY);

        this.playerTileX = hasValidSavedCoords
            ? (data?.tileX as number)
            : spawn.tileX;
        this.playerTileY = hasValidSavedCoords
            ? (data?.tileY as number)
            : spawn.tileY;

        this.player = this.add.sprite(
            this.tileToWorldCenter(this.playerTileX),
            this.tileToWorldCenter(this.playerTileY),
            'player-elephpant',
            IDLE_FRAMES[this.facing],
        );
        this.player.setDepth(10);

        this.interactPrompt = this.add
            .text(0, 0, 'E / Space', {
                fontFamily: 'monospace',
                fontSize: '7px',
                fontStyle: 'bold',
                color: '#f8fafc',
                backgroundColor: '#0f172ad9',
                padding: { x: 3, y: 1 },
            })
            .setOrigin(0.5, 1)
            .setDepth(30)
            .setVisible(false);

        this.configureCamera();
        this.configureInput();
        this.subscribeToEvents();
        this.updateInteractPrompt();
        this.emitPlayerLocation(Boolean(data?.transitioned));

        this.cameras.main.fadeIn(FADE_DURATION_MS, 15, 23, 42);

        this.events.once(Phaser.Scenes.Events.SHUTDOWN, () => {
            this.cleanupSubscriptions();
        });
    }

    public update(): void {
        if (this.isPaused || this.isTransitioning) {
            return;
        }

        if (
            !this.isMoving &&
            this.time.now >= this.interactUnlockAt &&
            this.isInteractJustPressed()
        ) {
            this.tryInteract();
            return;
        }

        if (this.isMoving) {
            return;
        }

        const nextDirection = this.getHeldDirection();

        if (nextDirection !== null) {
            this.attemptStep(nextDirection);
        }
    }

    private getWorldState(): WorldStateSnapshot {
        const stored = this.registry.get('worldState') as
            | WorldStateSnapshot
            | undefined;

        return stored ?? DEFAULT_WORLD_STATE;
    }

    private applyUnlockedBlockers(): void {
        const unlocked = new Set(this.getWorldState().unlockedTriggers);

        for (const blocker of this.blockers) {
            if (!unlocked.has(blocker.triggerId)) {
                continue;
            }

            this.collisionLayer?.removeTileAt(blocker.tileX, blocker.tileY);

            if (blocker.openTileGid > 0) {
                this.detailLayer?.putTileAt(
                    blocker.openTileGid,
                    blocker.tileX,
                    blocker.tileY,
                );
            }
        }
    }

    private configureCamera(): void {
        const camera = this.cameras.main;
        camera.setZoom(CAMERA_ZOOM);
        camera.setRoundPixels(true);

        const viewWidth = this.scale.width / CAMERA_ZOOM;
        const viewHeight = this.scale.height / CAMERA_ZOOM;
        const mapWidth = this.map.widthInPixels;
        const mapHeight = this.map.heightInPixels;

        const boundsX = mapWidth < viewWidth ? -(viewWidth - mapWidth) / 2 : 0;
        const boundsY =
            mapHeight < viewHeight ? -(viewHeight - mapHeight) / 2 : 0;
        const boundsWidth = Math.max(mapWidth, viewWidth);
        const boundsHeight = Math.max(mapHeight, viewHeight);

        camera.setBounds(boundsX, boundsY, boundsWidth, boundsHeight);
        camera.startFollow(this.player, true, 0.12, 0.12);
    }

    private configureInput(): void {
        const keyboard = this.input.keyboard;

        if (!keyboard) {
            return;
        }

        keyboard.addCapture([
            Phaser.Input.Keyboard.KeyCodes.UP,
            Phaser.Input.Keyboard.KeyCodes.DOWN,
            Phaser.Input.Keyboard.KeyCodes.LEFT,
            Phaser.Input.Keyboard.KeyCodes.RIGHT,
            Phaser.Input.Keyboard.KeyCodes.SPACE,
        ]);

        this.cursors = keyboard.createCursorKeys();
        this.wasd = {
            up: keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.W),
            down: keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.S),
            left: keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.A),
            right: keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.D),
        };
        this.interactKeys = [
            keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.E),
            keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.SPACE),
            keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.ENTER),
        ];
    }

    private subscribeToEvents(): void {
        this.cleanupSubscriptions();

        this.eventUnsubscribers.push(
            gameEvents.on('pause-overworld', () => {
                this.isPaused = true;
                this.stopWalkAnimation();
            }),
            gameEvents.on('resume-overworld', (payload) => {
                this.isPaused = false;
                this.interactUnlockAt = this.time.now + 150;

                if (payload?.unlockedTriggerId) {
                    const state = this.getWorldState();

                    if (
                        !state.unlockedTriggers.includes(
                            payload.unlockedTriggerId,
                        )
                    ) {
                        this.registry.set('worldState', {
                            ...state,
                            unlockedTriggers: [
                                ...state.unlockedTriggers,
                                payload.unlockedTriggerId,
                            ],
                        });
                    }

                    this.applyUnlockedBlockers();
                    this.refreshTerminalIndicators(payload.unlockedTriggerId);
                }
            }),
            gameEvents.on('load-world-state', (payload) => {
                if (this.isTransitioning) {
                    return;
                }

                if (payload.unlockedTerminals) {
                    const state = this.getWorldState();
                    this.registry.set('worldState', {
                        ...state,
                        unlockedTriggers: [...payload.unlockedTerminals],
                    });
                }

                const targetMap = zoneToMapKey(payload.zone);
                const targetScene = MAP_TO_SCENE[targetMap] ?? 'OverworldScene';

                this.isPaused = false;
                this.isTransitioning = true;
                this.stopWalkAnimation();

                this.cameras.main.once(
                    Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE,
                    () => {
                        this.scene.start(targetScene, {
                            mapKey: targetMap,
                            tileX: payload.x,
                            tileY: payload.y,
                            facing: payload.facing,
                            transitioned: false,
                        } satisfies MapSceneData);
                    },
                );

                this.cameras.main.fadeOut(FADE_DURATION_MS, 15, 23, 42);
            }),
        );
    }

    private cleanupSubscriptions(): void {
        for (const unsubscribe of this.eventUnsubscribers) {
            unsubscribe();
        }

        this.eventUnsubscribers = [];
    }

    private parseObjectLayer(requestedSpawnId: string): {
        tileX: number;
        tileY: number;
    } {
        const objectLayer = this.map.getObjectLayer('objects');
        const spawns = new Map<string, { tileX: number; tileY: number }>();

        if (objectLayer) {
            for (const obj of objectLayer.objects) {
                const tileX = Math.floor((obj.x ?? 0) / TILE_SIZE);
                const tileY = Math.floor((obj.y ?? 0) / TILE_SIZE);
                const props = this.extractProperties(
                    obj.properties as TiledProperty[] | undefined,
                );

                if (obj.type === 'spawn') {
                    const spawnId = String(props.spawnId ?? 'default');
                    spawns.set(spawnId, { tileX, tileY });
                } else if (obj.type === 'warp') {
                    const targetMap = String(props.targetMap ?? '');
                    const targetSpawn = String(props.targetSpawn ?? 'default');
                    const rawFacing = props.facing
                        ? String(props.facing)
                        : undefined;
                    const facing =
                        rawFacing === 'up' ||
                        rawFacing === 'down' ||
                        rawFacing === 'left' ||
                        rawFacing === 'right'
                            ? rawFacing
                            : undefined;

                    if (targetMap) {
                        this.warps.set(this.tileKey(tileX, tileY), {
                            tileX,
                            tileY,
                            targetMap,
                            targetSpawn,
                            facing,
                        });
                    }
                } else if (obj.type === 'blocker') {
                    const triggerId = String(props.triggerId ?? '');
                    const openTileGid = Number(props.openTileGid ?? 0);

                    if (triggerId) {
                        this.blockers.push({
                            tileX,
                            tileY,
                            triggerId,
                            openTileGid,
                        });
                    }
                } else if (
                    obj.type === 'npc' ||
                    obj.type === 'sign' ||
                    obj.type === 'terminal'
                ) {
                    const dialogueKey = String(props.dialogueKey ?? '');
                    const npcId = props.npcId ? String(props.npcId) : undefined;
                    const isHidden = Boolean(props.hidden);
                    const filterDiscipline = props.filterDiscipline
                        ? String(props.filterDiscipline)
                        : undefined;
                    const challengeId = props.challengeId
                        ? String(props.challengeId)
                        : undefined;
                    const triggerId = props.triggerId
                        ? String(props.triggerId)
                        : undefined;

                    this.interactables.set(this.tileKey(tileX, tileY), {
                        tileX,
                        tileY,
                        kind: obj.type,
                        dialogueKey,
                        npcId,
                        challengeCenter: Boolean(props.challengeCenter),
                        filterDiscipline,
                        challengeId,
                        triggerId,
                    });

                    if (obj.type === 'npc' && !isHidden) {
                        this.occupiedNpcTiles.add(this.tileKey(tileX, tileY));
                        const frame =
                            npcId === 'center-guide' ||
                            npcId?.startsWith('guild-')
                                ? 3
                                : 0;
                        const npcSprite = this.add.sprite(
                            this.tileToWorldCenter(tileX),
                            this.tileToWorldCenter(tileY),
                            'npcs',
                            frame,
                        );
                        npcSprite.setDepth(10);
                    }
                }
            }
        }

        return (
            spawns.get(requestedSpawnId) ??
            spawns.get(this.defaultSpawnId) ?? { tileX: 2, tileY: 2 }
        );
    }

    private extractProperties(
        rawProps?: TiledProperty[],
    ): Record<string, string | number | boolean> {
        const result: Record<string, string | number | boolean> = {};

        if (!Array.isArray(rawProps)) {
            return result;
        }

        for (const entry of rawProps) {
            result[entry.name] = entry.value;
        }

        return result;
    }

    private getHeldDirection(): Direction | null {
        if (this.cursors?.up.isDown || this.wasd?.up.isDown) {
            return 'up';
        }

        if (this.cursors?.down.isDown || this.wasd?.down.isDown) {
            return 'down';
        }

        if (this.cursors?.left.isDown || this.wasd?.left.isDown) {
            return 'left';
        }

        if (this.cursors?.right.isDown || this.wasd?.right.isDown) {
            return 'right';
        }

        return null;
    }

    private isInteractJustPressed(): boolean {
        return this.interactKeys.some((key) =>
            Phaser.Input.Keyboard.JustDown(key),
        );
    }

    private attemptStep(direction: Direction): void {
        this.facing = direction;
        const { dx, dy } = DIRECTION_DELTAS[direction];
        const targetX = this.playerTileX + dx;
        const targetY = this.playerTileY + dy;

        if (!this.canStepTo(targetX, targetY)) {
            this.stopWalkAnimation();
            this.updateInteractPrompt();
            this.emitPlayerLocation(false);
            return;
        }

        this.isMoving = true;
        this.interactPrompt?.setVisible(false);
        this.playerTileX = targetX;
        this.playerTileY = targetY;
        audioManager.playSfx('footstep', this.detectSurface(targetX, targetY));

        this.player.anims.play(`player-walk-${direction}`, true);

        this.tweens.add({
            targets: this.player,
            x: this.tileToWorldCenter(targetX),
            y: this.tileToWorldCenter(targetY),
            duration: STEP_DURATION_MS,
            ease: 'Linear',
            onComplete: () => {
                this.isMoving = false;

                const warp = this.warps.get(
                    this.tileKey(this.playerTileX, this.playerTileY),
                );

                if (warp) {
                    this.triggerWarp(warp);
                    return;
                }

                this.updateInteractPrompt();
                this.emitPlayerLocation(false);

                if (this.getHeldDirection() === null) {
                    this.stopWalkAnimation();
                }
            },
        });
    }

    private canStepTo(tileX: number, tileY: number): boolean {
        if (
            tileX < 0 ||
            tileY < 0 ||
            tileX >= this.map.width ||
            tileY >= this.map.height
        ) {
            return false;
        }

        if (this.occupiedNpcTiles.has(this.tileKey(tileX, tileY))) {
            return false;
        }

        const collisionTile = this.collisionLayer?.getTileAt(tileX, tileY);

        if (collisionTile) {
            const tileProps = collisionTile.properties as
                | { collides?: boolean }
                | undefined;

            if (tileProps?.collides === true) {
                return false;
            }
        }

        return true;
    }

    private triggerWarp(warp: WarpTrigger): void {
        const targetScene = MAP_TO_SCENE[warp.targetMap];

        if (!targetScene) {
            return;
        }

        this.isTransitioning = true;
        this.stopWalkAnimation();

        const nextFacing: Direction =
            warp.facing ?? (targetScene === 'InteriorScene' ? 'up' : 'down');

        this.cameras.main.once(
            Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE,
            () => {
                this.scene.start(targetScene, {
                    mapKey: warp.targetMap,
                    spawnId: warp.targetSpawn,
                    facing: nextFacing,
                    transitioned: true,
                } satisfies MapSceneData);
            },
        );

        this.cameras.main.fadeOut(FADE_DURATION_MS, 15, 23, 42);
    }

    private tryInteract(): void {
        const { dx, dy } = DIRECTION_DELTAS[this.facing];
        const targetX = this.playerTileX + dx;
        const targetY = this.playerTileY + dy;
        const trigger = this.interactables.get(this.tileKey(targetX, targetY));

        if (!trigger) {
            return;
        }

        if (trigger.kind === 'terminal') {
            this.isPaused = true;
            this.stopWalkAnimation();
            gameEvents.emit('pause-overworld');

            if (trigger.challengeId) {
                gameEvents.emit('open-challenge', {
                    challengeId: trigger.challengeId,
                });
                return;
            }

            if (trigger.challengeCenter) {
                gameEvents.emit('open-challenge-center', {
                    source: this.currentMapKey || this.defaultMapKey,
                    discipline: trigger.filterDiscipline,
                });
                return;
            }
        }

        const payload = resolveDialogue(
            trigger.dialogueKey,
            this.getWorldState(),
        );

        this.isPaused = true;
        this.stopWalkAnimation();
        gameEvents.emit('pause-overworld');
        gameEvents.emit('show-dialogue', {
            ...payload,
            kind: payload.kind ?? trigger.kind,
        });
    }

    private stopWalkAnimation(): void {
        if (this.player.anims.isPlaying) {
            this.player.anims.stop();
        }

        this.player.setFrame(IDLE_FRAMES[this.facing]);
    }

    private renderTerminalIndicators(): void {
        const unlocked = new Set(this.getWorldState().unlockedTriggers);
        const blockerTriggers = new Set(
            this.blockers.map((blocker) => blocker.triggerId),
        );

        for (const [key, trigger] of this.interactables.entries()) {
            if (trigger.kind !== 'terminal') {
                continue;
            }

            const isSolved =
                trigger.triggerId !== undefined &&
                unlocked.has(trigger.triggerId);
            const isGateLock =
                trigger.triggerId !== undefined &&
                blockerTriggers.has(trigger.triggerId);

            const color = isSolved
                ? 0x34d399
                : isGateLock
                  ? 0xf59e0b
                  : 0x22d3ee;

            const indicator = this.add.rectangle(
                this.tileToWorldCenter(trigger.tileX),
                trigger.tileY * TILE_SIZE + 2,
                6,
                3,
                color,
                0.95,
            );
            indicator.setDepth(15);

            if (!isSolved) {
                this.tweens.add({
                    targets: indicator,
                    alpha: 0.35,
                    duration: 700,
                    yoyo: true,
                    repeat: -1,
                });
            }

            this.terminalBadges.set(key, indicator);
        }
    }

    private refreshTerminalIndicators(newlyUnlockedTriggerId?: string): void {
        const unlocked = new Set(this.getWorldState().unlockedTriggers);

        for (const [key, trigger] of this.interactables.entries()) {
            if (trigger.kind !== 'terminal' || !trigger.triggerId) {
                continue;
            }

            const indicator = this.terminalBadges.get(key);
            if (!indicator || !unlocked.has(trigger.triggerId)) {
                continue;
            }

            this.tweens.killTweensOf(indicator);
            indicator.setFillStyle(0x34d399, 1);
            indicator.setAlpha(1);

            if (trigger.triggerId === newlyUnlockedTriggerId) {
                const cue = this.add
                    .text(
                        this.tileToWorldCenter(trigger.tileX),
                        trigger.tileY * TILE_SIZE - 4,
                        'UNLOCKED!',
                        {
                            fontFamily: 'monospace',
                            fontSize: '7px',
                            fontStyle: 'bold',
                            color: '#34d399',
                            backgroundColor: '#022c22cc',
                            padding: { x: 2, y: 1 },
                        },
                    )
                    .setOrigin(0.5, 1)
                    .setDepth(35);

                this.tweens.add({
                    targets: cue,
                    y: cue.y - 12,
                    alpha: 0,
                    duration: 1100,
                    ease: 'Cubic.easeOut',
                    onComplete: () => cue.destroy(),
                });
            }
        }
    }

    private updateInteractPrompt(): void {
        if (!this.interactPrompt || !this.player) {
            return;
        }

        const { dx, dy } = DIRECTION_DELTAS[this.facing];
        const target = this.interactables.get(
            this.tileKey(this.playerTileX + dx, this.playerTileY + dy),
        );

        if (!target) {
            this.interactPrompt.setVisible(false);
            return;
        }

        this.interactPrompt.setPosition(this.player.x, this.player.y - 11);
        this.interactPrompt.setVisible(true);
    }

    private detectSurface(tileX: number, tileY: number): FootstepSurface {
        const gid = this.groundLayer?.getTileAt(tileX, tileY)?.index ?? 1;
        if (gid === 6 || gid === 7) {
            return 'wood';
        }
        if (gid === 3 || gid === 4 || gid === 8) {
            return 'stone';
        }
        return 'grass';
    }

    private emitPlayerLocation(transitioned: boolean): void {
        gameEvents.emit('player-location-changed', {
            zone: mapKeyToZone(this.currentMapKey || this.defaultMapKey),
            x: this.playerTileX,
            y: this.playerTileY,
            mapWidth: this.map.width,
            mapHeight: this.map.height,
            facing: this.facing,
            transitioned,
        });
    }

    private tileToWorldCenter(tileCoord: number): number {
        return tileCoord * TILE_SIZE + TILE_SIZE / 2;
    }

    private tileKey(x: number, y: number): string {
        return `${x},${y}`;
    }
}
