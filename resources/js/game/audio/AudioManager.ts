export type BgmZoneKey =
    | 'foundations-hub'
    | 'algorithm-woods'
    | 'artisan-city'
    | 'legacy-harbor'
    | 'challenge-mode';

export type SfxKey =
    | 'footstep'
    | 'uiHover'
    | 'uiConfirm'
    | 'uiCancel'
    | 'dialogueBlip'
    | 'terminalOpen'
    | 'testRun'
    | 'testPass'
    | 'testFail'
    | 'terminalUnlock'
    | 'saveGame'
    | 'loadGame'
    | 'fastTravel';

export type FootstepSurface = 'grass' | 'stone' | 'wood';

export interface AudioPrefs {
    muted: boolean;
    bgmVolume: number;
    sfxVolume: number;
}

interface SynthTrack {
    tempoMs: number;
    leadWave: OscillatorType;
    bassWave: OscillatorType;
    leadNotes: number[];
    bassNotes: number[];
}

const STORAGE_KEY = 'elephpant_audio_prefs_v1';

const DEFAULT_PREFS: AudioPrefs = {
    muted: false,
    bgmVolume: 0.35,
    sfxVolume: 0.55,
};

// Gentle, low-fatigue GBA-style looping chiptune melodies (Hz; 0 = rest)
const SYNTH_TRACKS: Record<BgmZoneKey, SynthTrack> = {
    // Warm, welcoming C-major town melody
    'foundations-hub': {
        tempoMs: 260,
        leadWave: 'triangle',
        bassWave: 'sine',
        leadNotes: [
            523.25, 0, 659.25, 587.33, 523.25, 0, 392.0, 440.0, 523.25, 587.33,
            659.25, 0, 587.33, 0, 523.25, 0,
        ],
        bassNotes: [
            130.81, 0, 196.0, 0, 164.81, 0, 196.0, 0, 174.61, 0, 220.0, 0,
            196.0, 0, 130.81, 0,
        ],
    },
    // Curious, rhythmic A-Dorian forest melody
    'algorithm-woods': {
        tempoMs: 230,
        leadWave: 'square',
        bassWave: 'triangle',
        leadNotes: [
            440.0, 523.25, 587.33, 0, 659.25, 587.33, 523.25, 493.88, 440.0, 0,
            392.0, 440.0, 523.25, 0, 440.0, 0,
        ],
        bassNotes: [
            110.0, 0, 164.81, 0, 146.83, 0, 164.81, 0, 130.81, 0, 146.83, 0,
            110.0, 0, 164.81, 0,
        ],
    },
    // Upbeat, structured F-major citadel theme
    'artisan-city': {
        tempoMs: 215,
        leadWave: 'square',
        bassWave: 'triangle',
        leadNotes: [
            349.23, 440.0, 523.25, 0, 587.33, 523.25, 440.0, 523.25, 698.46, 0,
            659.25, 587.33, 523.25, 440.0, 349.23, 0,
        ],
        bassNotes: [
            174.61, 0, 220.0, 0, 196.0, 0, 261.63, 0, 174.61, 0, 233.08, 0,
            261.63, 0, 174.61, 0,
        ],
    },
    // Steady, atmospheric D-minor coastal harbor theme
    'legacy-harbor': {
        tempoMs: 280,
        leadWave: 'triangle',
        bassWave: 'sine',
        leadNotes: [
            293.66, 0, 349.23, 440.0, 392.0, 0, 349.23, 329.63, 293.66, 0,
            261.63, 293.66, 349.23, 0, 293.66, 0,
        ],
        bassNotes: [
            146.83, 0, 146.83, 0, 116.54, 0, 130.81, 0, 146.83, 0, 110.0, 0,
            130.81, 0, 146.83, 0,
        ],
    },
    // Minimal focused coding pulse
    'challenge-mode': {
        tempoMs: 320,
        leadWave: 'sine',
        bassWave: 'triangle',
        leadNotes: [
            261.63, 0, 329.63, 0, 392.0, 0, 329.63, 0, 293.66, 0, 349.23, 0,
            392.0, 0, 0, 0,
        ],
        bassNotes: [
            130.81, 0, 0, 0, 146.83, 0, 0, 0, 130.81, 0, 0, 0, 110.0, 0, 0, 0,
        ],
    },
};

const ZONE_TO_BGM: Record<string, BgmZoneKey> = {
    'foundations-hub': 'foundations-hub',
    'localhost-town': 'foundations-hub',
    'starter-house': 'foundations-hub',
    'challenge-center': 'foundations-hub',
    'algorithm-woods': 'algorithm-woods',
    'logic-pavilion': 'algorithm-woods',
    'artisan-city': 'artisan-city',
    'billing-guild': 'artisan-city',
    'legacy-harbor': 'legacy-harbor',
    'security-warehouse': 'legacy-harbor',
    'challenge-mode': 'challenge-mode',
};

class GameAudioManager {
    private ctx: AudioContext | null = null;
    private masterGain: GainNode | null = null;
    private bgmGain: GainNode | null = null;
    private bgmFilter: BiquadFilterNode | null = null;
    private sfxGain: GainNode | null = null;

    private prefs: AudioPrefs = this.loadPrefs();
    private listeners = new Set<(prefs: AudioPrefs) => void>();

    private currentBgmKey: BgmZoneKey | null = null;
    private previousZoneBgmKey: BgmZoneKey = 'foundations-hub';
    private synthTimer: number | null = null;
    private synthStep = 0;

    private customTrackCache = new Map<BgmZoneKey, string | null>();
    private activeHtmlAudio: HTMLAudioElement | null = null;

    private lastFootstepAt = 0;
    private lastBlipAt = 0;
    private unlockBound = false;

    public constructor() {
        if (typeof window !== 'undefined') {
            this.bindAutoplayUnlock();
        }
    }

    public getPrefs(): AudioPrefs {
        return { ...this.prefs };
    }

    public subscribe(listener: (prefs: AudioPrefs) => void): () => void {
        this.listeners.add(listener);
        listener(this.getPrefs());

        return () => {
            this.listeners.delete(listener);
        };
    }

    public setMuted(muted: boolean): void {
        this.prefs.muted = muted;
        this.persistPrefs();
        this.applyGainLevels();
        this.notifyListeners();
    }

    public toggleMute(): void {
        this.setMuted(!this.prefs.muted);
    }

    public setBgmVolume(volume: number): void {
        this.prefs.bgmVolume = Math.max(0, Math.min(1, volume));
        if (this.prefs.bgmVolume > 0 && this.prefs.muted) {
            this.prefs.muted = false;
        }
        this.persistPrefs();
        this.applyGainLevels();
        this.notifyListeners();
    }

    public setSfxVolume(volume: number): void {
        this.prefs.sfxVolume = Math.max(0, Math.min(1, volume));
        if (this.prefs.sfxVolume > 0 && this.prefs.muted) {
            this.prefs.muted = false;
        }
        this.persistPrefs();
        this.applyGainLevels();
        this.notifyListeners();
    }

    public playZoneBgm(zone: string): void {
        const bgmKey = ZONE_TO_BGM[zone] ?? 'foundations-hub';
        if (bgmKey !== 'challenge-mode') {
            this.previousZoneBgmKey = bgmKey;
        }

        if (this.currentBgmKey === bgmKey) {
            return;
        }

        void this.crossfadeTo(bgmKey);
    }

    public enterChallengeMode(): void {
        if (this.ctx && this.bgmFilter) {
            const now = this.ctx.currentTime;
            this.bgmFilter.frequency.cancelScheduledValues(now);
            this.bgmFilter.frequency.setValueAtTime(
                this.bgmFilter.frequency.value,
                now,
            );
            this.bgmFilter.frequency.linearRampToValueAtTime(650, now + 0.35);
        }
        void this.crossfadeTo('challenge-mode');
    }

    public exitChallengeMode(): void {
        if (this.ctx && this.bgmFilter) {
            const now = this.ctx.currentTime;
            this.bgmFilter.frequency.cancelScheduledValues(now);
            this.bgmFilter.frequency.setValueAtTime(
                this.bgmFilter.frequency.value,
                now,
            );
            this.bgmFilter.frequency.linearRampToValueAtTime(14000, now + 0.35);
        }
        void this.crossfadeTo(this.previousZoneBgmKey);
    }

    public stopBgm(): void {
        if (this.synthTimer !== null) {
            window.clearInterval(this.synthTimer);
            this.synthTimer = null;
        }

        if (this.activeHtmlAudio) {
            this.activeHtmlAudio.pause();
            this.activeHtmlAudio = null;
        }

        this.currentBgmKey = null;
    }

    public playSfx(key: SfxKey, surface: FootstepSurface = 'grass'): void {
        if (this.prefs.muted || this.prefs.sfxVolume <= 0) {
            return;
        }

        const ctx = this.ensureContext();
        if (!ctx || ctx.state !== 'running' || !this.sfxGain) {
            return;
        }

        const nowMs = performance.now();

        if (key === 'footstep') {
            if (nowMs - this.lastFootstepAt < 145) {
                return;
            }
            this.lastFootstepAt = nowMs;
            this.playFootstep(ctx, surface);
            return;
        }

        if (key === 'dialogueBlip') {
            if (nowMs - this.lastBlipAt < 45) {
                return;
            }
            this.lastBlipAt = nowMs;
            this.tone(ctx, 640 + Math.random() * 90, 0.032, 'square', 0.14);
            return;
        }

        switch (key) {
            case 'uiHover':
                this.tone(ctx, 520, 0.035, 'triangle', 0.16);
                break;
            case 'uiConfirm':
                this.tone(ctx, 587.33, 0.055, 'square', 0.18, 0);
                this.tone(ctx, 880.0, 0.08, 'square', 0.18, 0.055);
                break;
            case 'uiCancel':
                this.tone(ctx, 440.0, 0.055, 'triangle', 0.2, 0);
                this.tone(ctx, 329.63, 0.08, 'triangle', 0.18, 0.055);
                break;
            case 'terminalOpen':
                this.tone(ctx, 440, 0.06, 'square', 0.18, 0);
                this.tone(ctx, 659.25, 0.07, 'square', 0.18, 0.06);
                this.tone(ctx, 987.77, 0.11, 'triangle', 0.2, 0.13);
                break;
            case 'testRun':
                this.tone(ctx, 740, 0.04, 'square', 0.16, 0);
                this.tone(ctx, 880, 0.05, 'triangle', 0.16, 0.045);
                break;
            case 'testPass':
                this.tone(ctx, 523.25, 0.08, 'square', 0.2, 0);
                this.tone(ctx, 659.25, 0.08, 'square', 0.2, 0.085);
                this.tone(ctx, 783.99, 0.08, 'square', 0.2, 0.17);
                this.tone(ctx, 1046.5, 0.22, 'triangle', 0.25, 0.255);
                break;
            case 'testFail':
                this.tone(ctx, 220, 0.12, 'sawtooth', 0.2, 0);
                this.tone(ctx, 164.81, 0.2, 'sawtooth', 0.2, 0.13);
                break;
            case 'terminalUnlock':
                this.tone(ctx, 523.25, 0.08, 'square', 0.2, 0);
                this.tone(ctx, 659.25, 0.08, 'square', 0.2, 0.08);
                this.tone(ctx, 783.99, 0.08, 'square', 0.2, 0.16);
                this.tone(ctx, 1046.5, 0.1, 'square', 0.22, 0.24);
                this.tone(ctx, 1318.5, 0.28, 'triangle', 0.26, 0.35);
                break;
            case 'saveGame':
                this.tone(ctx, 587.33, 0.07, 'triangle', 0.22, 0);
                this.tone(ctx, 783.99, 0.07, 'triangle', 0.22, 0.075);
                this.tone(ctx, 1174.66, 0.18, 'sine', 0.24, 0.15);
                break;
            case 'loadGame':
                this.tone(ctx, 392.0, 0.07, 'triangle', 0.2, 0);
                this.tone(ctx, 523.25, 0.07, 'triangle', 0.22, 0.075);
                this.tone(ctx, 783.99, 0.18, 'triangle', 0.24, 0.15);
                break;
            case 'fastTravel':
                this.tone(ctx, 330, 0.06, 'sine', 0.18, 0);
                this.tone(ctx, 493.88, 0.06, 'sine', 0.18, 0.055);
                this.tone(ctx, 659.25, 0.07, 'triangle', 0.2, 0.11);
                this.tone(ctx, 987.77, 0.16, 'triangle', 0.22, 0.18);
                break;
        }
    }

    private bindAutoplayUnlock(): void {
        if (this.unlockBound) {
            return;
        }

        this.unlockBound = true;
        const unlock = (): void => {
            const ctx = this.ensureContext();
            if (ctx && ctx.state === 'suspended') {
                void ctx.resume().then(() => {
                    if (
                        this.currentBgmKey &&
                        this.synthTimer === null &&
                        !this.activeHtmlAudio
                    ) {
                        void this.startTrack(this.currentBgmKey);
                    }
                });
            }
        };

        window.addEventListener('pointerdown', unlock, { passive: true });
        window.addEventListener('keydown', unlock, { passive: true });
    }

    private ensureContext(): AudioContext | null {
        if (typeof window === 'undefined') {
            return null;
        }

        if (!this.ctx) {
            const AudioCtx =
                window.AudioContext ||
                (
                    window as unknown as {
                        webkitAudioContext?: typeof AudioContext;
                    }
                ).webkitAudioContext;

            if (!AudioCtx) {
                return null;
            }

            this.ctx = new AudioCtx();
            this.masterGain = this.ctx.createGain();
            this.bgmGain = this.ctx.createGain();
            this.bgmFilter = this.ctx.createBiquadFilter();
            this.bgmFilter.type = 'lowpass';
            this.bgmFilter.frequency.value = 14000;
            this.sfxGain = this.ctx.createGain();

            this.bgmGain.connect(this.bgmFilter);
            this.bgmFilter.connect(this.masterGain);
            this.sfxGain.connect(this.masterGain);
            this.masterGain.connect(this.ctx.destination);

            this.applyGainLevels();
        }

        return this.ctx;
    }

    private applyGainLevels(): void {
        if (!this.ctx || !this.masterGain || !this.bgmGain || !this.sfxGain) {
            return;
        }

        const now = this.ctx.currentTime;
        this.masterGain.gain.setTargetAtTime(
            this.prefs.muted ? 0 : 1,
            now,
            0.02,
        );
        this.bgmGain.gain.setTargetAtTime(
            this.prefs.bgmVolume * 0.26,
            now,
            0.04,
        );
        this.sfxGain.gain.setTargetAtTime(this.prefs.sfxVolume, now, 0.02);

        if (this.activeHtmlAudio) {
            this.activeHtmlAudio.volume = this.prefs.muted
                ? 0
                : this.prefs.bgmVolume;
        }
    }

    private async crossfadeTo(nextKey: BgmZoneKey): Promise<void> {
        const ctx = this.ensureContext();
        this.currentBgmKey = nextKey;

        if (!ctx || !this.bgmGain) {
            return;
        }

        const now = ctx.currentTime;
        const targetGain = this.prefs.bgmVolume * 0.26;

        // Smooth 400ms fade-out
        this.bgmGain.gain.cancelScheduledValues(now);
        this.bgmGain.gain.setValueAtTime(this.bgmGain.gain.value, now);
        this.bgmGain.gain.linearRampToValueAtTime(0.0001, now + 0.35);

        await new Promise<void>((resolve) => {
            window.setTimeout(resolve, 360);
        });

        if (this.currentBgmKey !== nextKey) {
            return;
        }

        await this.startTrack(nextKey);

        if (this.ctx && this.bgmGain) {
            const fadeStart = this.ctx.currentTime;
            this.bgmGain.gain.cancelScheduledValues(fadeStart);
            this.bgmGain.gain.setValueAtTime(0.0001, fadeStart);
            this.bgmGain.gain.linearRampToValueAtTime(
                targetGain,
                fadeStart + 0.4,
            );
        }
    }

    private async startTrack(key: BgmZoneKey): Promise<void> {
        if (this.synthTimer !== null) {
            window.clearInterval(this.synthTimer);
            this.synthTimer = null;
        }

        if (this.activeHtmlAudio) {
            this.activeHtmlAudio.pause();
            this.activeHtmlAudio = null;
        }

        const customUrl = await this.resolveCustomTrackUrl(key);
        if (customUrl) {
            const audio = new Audio(customUrl);
            audio.loop = true;
            audio.volume = this.prefs.muted ? 0 : this.prefs.bgmVolume;
            this.activeHtmlAudio = audio;
            void audio.play().catch(() => {
                // Will resume on next user interaction
            });
            return;
        }

        const track = SYNTH_TRACKS[key];
        this.synthStep = 0;

        this.synthTimer = window.setInterval(() => {
            if (
                this.prefs.muted ||
                this.prefs.bgmVolume <= 0 ||
                !this.ctx ||
                this.ctx.state !== 'running' ||
                !this.bgmGain
            ) {
                return;
            }

            const idx = this.synthStep % track.leadNotes.length;
            const leadFreq = track.leadNotes[idx] ?? 0;
            const bassFreq = track.bassNotes[idx] ?? 0;
            const noteDur = (track.tempoMs / 1000) * 0.82;

            if (leadFreq > 0) {
                this.bgmNote(this.ctx, leadFreq, noteDur, track.leadWave, 0.32);
            }
            if (bassFreq > 0) {
                this.bgmNote(
                    this.ctx,
                    bassFreq,
                    noteDur * 1.1,
                    track.bassWave,
                    0.4,
                );
            }

            this.synthStep += 1;
        }, track.tempoMs);
    }

    private async resolveCustomTrackUrl(
        key: BgmZoneKey,
    ): Promise<string | null> {
        if (this.customTrackCache.has(key)) {
            return this.customTrackCache.get(key) ?? null;
        }

        for (const ext of ['ogg', 'mp3']) {
            const url = `/audio/${key}.${ext}`;
            try {
                const res = await fetch(url, { method: 'HEAD' });
                if (
                    res.ok &&
                    (res.headers.get('content-type') ?? '').startsWith('audio/')
                ) {
                    this.customTrackCache.set(key, url);
                    return url;
                }
            } catch {
                // No custom file present; fall back to built-in 16-bit synth
            }
        }

        this.customTrackCache.set(key, null);
        return null;
    }

    private bgmNote(
        ctx: AudioContext,
        freq: number,
        duration: number,
        type: OscillatorType,
        peak: number,
    ): void {
        if (!this.bgmGain) {
            return;
        }

        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const env = ctx.createGain();

        osc.type = type;
        osc.frequency.setValueAtTime(freq, now);

        env.gain.setValueAtTime(0.0001, now);
        env.gain.linearRampToValueAtTime(peak, now + 0.018);
        env.gain.exponentialRampToValueAtTime(0.0001, now + duration);

        osc.connect(env);
        env.connect(this.bgmGain);

        osc.start(now);
        osc.stop(now + duration + 0.02);
    }

    private tone(
        ctx: AudioContext,
        freq: number,
        duration: number,
        type: OscillatorType,
        peak: number,
        delay = 0,
    ): void {
        if (!this.sfxGain) {
            return;
        }

        const start = ctx.currentTime + delay;
        const osc = ctx.createOscillator();
        const env = ctx.createGain();

        osc.type = type;
        osc.frequency.setValueAtTime(freq, start);

        env.gain.setValueAtTime(0.0001, start);
        env.gain.linearRampToValueAtTime(peak, start + 0.008);
        env.gain.exponentialRampToValueAtTime(0.0001, start + duration);

        osc.connect(env);
        env.connect(this.sfxGain);

        osc.start(start);
        osc.stop(start + duration + 0.02);
    }

    private playFootstep(ctx: AudioContext, surface: FootstepSurface): void {
        const baseFreq =
            surface === 'stone' ? 190 : surface === 'wood' ? 150 : 120;
        const jitter = (Math.random() - 0.5) * 24;
        this.tone(ctx, baseFreq + jitter, 0.035, 'triangle', 0.12);
    }

    private loadPrefs(): AudioPrefs {
        if (typeof window === 'undefined') {
            return { ...DEFAULT_PREFS };
        }

        try {
            const raw = window.localStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return { ...DEFAULT_PREFS };
            }
            const parsed = JSON.parse(raw) as Partial<AudioPrefs>;

            return {
                muted: Boolean(parsed.muted ?? DEFAULT_PREFS.muted),
                bgmVolume:
                    typeof parsed.bgmVolume === 'number'
                        ? Math.max(0, Math.min(1, parsed.bgmVolume))
                        : DEFAULT_PREFS.bgmVolume,
                sfxVolume:
                    typeof parsed.sfxVolume === 'number'
                        ? Math.max(0, Math.min(1, parsed.sfxVolume))
                        : DEFAULT_PREFS.sfxVolume,
            };
        } catch {
            return { ...DEFAULT_PREFS };
        }
    }

    private persistPrefs(): void {
        if (typeof window === 'undefined') {
            return;
        }

        try {
            window.localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify(this.prefs),
            );
        } catch {
            // Ignore storage quota errors
        }
    }

    private notifyListeners(): void {
        const snapshot = this.getPrefs();
        for (const listener of this.listeners) {
            listener(snapshot);
        }
    }
}

export const audioManager = new GameAudioManager();
