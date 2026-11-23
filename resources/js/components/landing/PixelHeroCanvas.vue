<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';

const canvasRef = ref<HTMLCanvasElement | null>(null);
let rafId: number | null = null;

const LOGICAL_WIDTH = 320;
const LOGICAL_HEIGHT = 180;
const TILE = 16;

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

function drawTile(
    ctx: CanvasRenderingContext2D,
    sheet: HTMLImageElement,
    gid: number,
    dx: number,
    dy: number,
): void {
    const id = gid - 1;
    const sx = (id % 8) * TILE;
    const sy = Math.floor(id / 8) * TILE;
    ctx.drawImage(sheet, sx, sy, TILE, TILE, dx, dy, TILE, TILE);
}

onMounted(async () => {
    const canvas = canvasRef.value;

    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');

    if (!ctx) {
        return;
    }

    ctx.imageSmoothingEnabled = false;

    let tileset: HTMLImageElement;
    let playerSheet: HTMLImageElement;
    let npcSheet: HTMLImageElement;

    try {
        [tileset, playerSheet, npcSheet] = await Promise.all([
            loadImage('/assets/tilesets/stack-region-tiles.png'),
            loadImage('/assets/tilesets/player-elephpant.png'),
            loadImage('/assets/tilesets/npcs.png'),
        ]);
    } catch {
        return;
    }

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    const clouds = [
        { x: 24, y: 14, w: 34, speed: 6 },
        { x: 138, y: 10, w: 46, speed: 4.5 },
        { x: 244, y: 20, w: 30, speed: 7 },
    ];

    const sparkles = [
        { x: 56, y: 118, phase: 0 },
        { x: 152, y: 92, phase: 1.4 },
        { x: 246, y: 124, phase: 2.8 },
        { x: 288, y: 84, phase: 4.1 },
    ];

    // Patrol path for the elePHPant adventurer across Localhost Town
    const minX = 48;
    const maxX = 256;
    let playerX = 112;
    let playerDir: 'right' | 'left' = 'right';
    let lastTime = performance.now();

    const render = (now: number): void => {
        const dt = Math.min((now - lastTime) / 1000, 0.05);
        lastTime = now;

        if (!prefersReducedMotion) {
            for (const cloud of clouds) {
                cloud.x += cloud.speed * dt;

                if (cloud.x > LOGICAL_WIDTH + 16) {
                    cloud.x = -cloud.w - 8;
                }
            }

            const speed = 34;

            if (playerDir === 'right') {
                playerX += speed * dt;

                if (playerX >= maxX) {
                    playerX = maxX;
                    playerDir = 'left';
                }
            } else {
                playerX -= speed * dt;

                if (playerX <= minX) {
                    playerX = minX;
                    playerDir = 'right';
                }
            }
        }

        // 1. Twilight-to-dawn retro GBA sky band (top 36px)
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, LOGICAL_WIDTH, 16);
        ctx.fillStyle = '#1e1b4b';
        ctx.fillRect(0, 16, LOGICAL_WIDTH, 16);
        ctx.fillStyle = '#312e81';
        ctx.fillRect(0, 32, LOGICAL_WIDTH, 16);

        // Pixel clouds
        for (const cloud of clouds) {
            const cx = Math.round(cloud.x);
            ctx.fillStyle = '#6366f1';
            ctx.fillRect(cx + 4, cloud.y, cloud.w - 8, 6);
            ctx.fillStyle = '#c7d2fe';
            ctx.fillRect(cx, cloud.y + 3, cloud.w, 6);
            ctx.fillRect(cx + 6, cloud.y - 2, cloud.w - 12, 4);
        }

        // 2. Ground tiles (rows y=3..11 -> 48..180)
        for (let row = 3; row < 12; row++) {
            for (let col = 0; col < 20; col++) {
                const gid = (col * 5 + row * 7) % 5 === 0 ? 2 : 1;
                drawTile(ctx, tileset, gid, col * TILE, row * TILE);
            }
        }

        // Main dirt path across row 8 & 9 (y=128, 144)
        for (let col = 0; col < 20; col++) {
            drawTile(ctx, tileset, 3, col * TILE, 8 * TILE);
            drawTile(ctx, tileset, 3, col * TILE, 9 * TILE);
        }

        // Central cobblestone plaza around Challenge Center entrance
        for (let col = 11; col <= 16; col++) {
            drawTile(ctx, tileset, 4, col * TILE, 7 * TILE);
            drawTile(ctx, tileset, 4, col * TILE, 8 * TILE);
        }

        // Wildflowers & welcome mats
        drawTile(ctx, tileset, 9, 2 * TILE, 10 * TILE);
        drawTile(ctx, tileset, 9, 7 * TILE, 10 * TILE);
        drawTile(ctx, tileset, 9, 17 * TILE, 10 * TILE);
        drawTile(ctx, tileset, 11, 5 * TILE, 7 * TILE);
        drawTile(ctx, tileset, 11, 14 * TILE, 7 * TILE);

        // 3. Trees along edges
        for (const [tx, ty] of [
            [0, 5],
            [1, 5],
            [9, 5],
            [18, 5],
            [19, 5],
            [0, 10],
            [19, 10],
        ]) {
            drawTile(ctx, tileset, 17, tx * TILE, ty * TILE);
            drawTile(ctx, tileset, 33, tx * TILE, (ty - 1) * TILE);
        }

        // 4. Starter House (cols 3..7, rows 3..6)
        drawTile(ctx, tileset, 35, 3 * TILE, 3 * TILE);
        drawTile(ctx, tileset, 36, 4 * TILE, 3 * TILE);
        drawTile(ctx, tileset, 36, 5 * TILE, 3 * TILE);
        drawTile(ctx, tileset, 36, 6 * TILE, 3 * TILE);
        drawTile(ctx, tileset, 37, 7 * TILE, 3 * TILE);
        drawTile(ctx, tileset, 35, 3 * TILE, 4 * TILE);
        drawTile(ctx, tileset, 36, 4 * TILE, 4 * TILE);
        drawTile(ctx, tileset, 36, 5 * TILE, 4 * TILE);
        drawTile(ctx, tileset, 36, 6 * TILE, 4 * TILE);
        drawTile(ctx, tileset, 37, 7 * TILE, 4 * TILE);
        for (let c = 3; c <= 7; c++) {
            drawTile(ctx, tileset, 25, c * TILE, 5 * TILE);
        }
        drawTile(ctx, tileset, 25, 3 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 26, 4 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 27, 5 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 26, 6 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 25, 7 * TILE, 6 * TILE);

        // 5. Challenge Center (cols 11..17, rows 3..6)
        drawTile(ctx, tileset, 38, 11 * TILE, 3 * TILE);
        for (let c = 12; c <= 16; c++) {
            drawTile(ctx, tileset, 39, c * TILE, 3 * TILE);
            drawTile(ctx, tileset, 39, c * TILE, 4 * TILE);
        }
        drawTile(ctx, tileset, 40, 17 * TILE, 3 * TILE);
        drawTile(ctx, tileset, 38, 11 * TILE, 4 * TILE);
        drawTile(ctx, tileset, 40, 17 * TILE, 4 * TILE);

        for (let c = 11; c <= 17; c++) {
            drawTile(ctx, tileset, c === 14 ? 31 : 28, c * TILE, 5 * TILE);
        }
        drawTile(ctx, tileset, 28, 11 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 29, 12 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 28, 13 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 30, 14 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 49, 14 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 28, 15 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 29, 16 * TILE, 6 * TILE);
        drawTile(ctx, tileset, 28, 17 * TILE, 6 * TILE);

        // Lamp posts & Town Signpost
        drawTile(ctx, tileset, 24, 12 * TILE, 7 * TILE);
        drawTile(ctx, tileset, 24, 16 * TILE, 7 * TILE);
        drawTile(ctx, tileset, 20, 8 * TILE, 7 * TILE);

        // 6. Mentor Ada NPC (subtle idle bob)
        const npcBob =
            !prefersReducedMotion && Math.floor(now / 600) % 2 === 0 ? -1 : 0;
        ctx.drawImage(
            npcSheet,
            0,
            0,
            TILE,
            TILE,
            10 * TILE,
            7 * TILE + npcBob,
            TILE,
            TILE,
        );

        // 7. Animated elePHPant player walking along the main path
        const walkFrame = prefersReducedMotion ? 0 : Math.floor(now / 150) % 3;
        const dirRow = playerDir === 'right' ? 3 : 2;
        ctx.drawImage(
            playerSheet,
            walkFrame * TILE,
            dirRow * TILE,
            TILE,
            TILE,
            Math.round(playerX),
            8 * TILE + 2,
            TILE,
            TILE,
        );

        // 8. Twinkling 16-bit fireflies / sparkles
        for (const s of sparkles) {
            const pulse = Math.sin(now / 420 + s.phase);

            if (pulse > 0.25) {
                ctx.fillStyle = pulse > 0.7 ? '#fef08a' : '#818cf8';
                ctx.fillRect(s.x, s.y, 2, 2);
            }
        }

        if (!prefersReducedMotion) {
            rafId = window.requestAnimationFrame(render);
        }
    };

    render(performance.now());
});

onUnmounted(() => {
    if (rafId !== null) {
        window.cancelAnimationFrame(rafId);
        rafId = null;
    }
});
</script>

<template>
    <div class="overflow-hidden rounded-xl retro-bezel p-2">
        <div
            class="mb-2 flex items-center justify-between border-b border-game-border px-2 pb-1.5 font-mono text-[11px] text-slate-400"
        >
            <span class="flex items-center gap-2 text-indigo-300">
                <span
                    class="inline-block size-2 rounded-full bg-game-emerald"
                />
                LOCALHOST TOWN • LIVE 16-BIT SCENE
            </span>
            <span class="hidden text-slate-500 sm:inline"
                >320×180 • 16-BIT</span
            >
        </div>

        <div
            class="relative aspect-video w-full overflow-hidden rounded-lg bg-game-panel"
        >
            <canvas
                ref="canvasRef"
                :width="LOGICAL_WIDTH"
                :height="LOGICAL_HEIGHT"
                aria-label="Animated 16-bit pixel-art scene of Localhost Town in elePHPant"
                class="block h-full w-full [image-rendering:pixelated]"
            />
        </div>
    </div>
</template>
