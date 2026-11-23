export type DialogueKind = 'npc' | 'sign' | 'terminal';

export interface ShowDialoguePayload {
    speaker?: string;
    lines: string[];
    kind?: DialogueKind;
}

export interface OpenChallengeCenterPayload {
    source?: string;
}

export interface OpenChallengePayload {
    challengeId: string;
}

export interface GameEventMap {
    'show-dialogue': ShowDialoguePayload;
    'open-challenge-center': OpenChallengeCenterPayload;
    'open-challenge': OpenChallengePayload;
    'pause-overworld': void;
    'resume-overworld': void;
}

type EventKey = keyof GameEventMap;
type EventHandler<K extends EventKey> = (payload: GameEventMap[K]) => void;

class TypedGameEventBus {
    private readonly listeners = new Map<
        EventKey,
        Set<EventHandler<EventKey>>
    >();

    public on<K extends EventKey>(
        event: K,
        handler: EventHandler<K>,
    ): () => void {
        let set = this.listeners.get(event);

        if (!set) {
            set = new Set();
            this.listeners.set(event, set);
        }

        const wrapped = handler as EventHandler<EventKey>;
        set.add(wrapped);

        return () => {
            this.off(event, handler);
        };
    }

    public off<K extends EventKey>(event: K, handler: EventHandler<K>): void {
        const set = this.listeners.get(event);

        if (!set) {
            return;
        }

        set.delete(handler as EventHandler<EventKey>);

        if (set.size === 0) {
            this.listeners.delete(event);
        }
    }

    public emit<K extends EventKey>(
        event: K,
        ...args: GameEventMap[K] extends void ? [] : [payload: GameEventMap[K]]
    ): void {
        const set = this.listeners.get(event);

        if (!set) {
            return;
        }

        const payload = args[0] as GameEventMap[K];

        for (const handler of Array.from(set)) {
            handler(payload);
        }
    }

    public clear(): void {
        this.listeners.clear();
    }
}

export const gameEvents = new TypedGameEventBus();
