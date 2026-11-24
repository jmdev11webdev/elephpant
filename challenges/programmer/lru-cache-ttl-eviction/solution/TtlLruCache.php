<?php

declare(strict_types=1);

class TtlLruCache
{
    /** @var array<string, array{value: mixed, expiresAt: int}> */
    private array $entries = [];

    public function __construct(
        private int $capacity,
    ) {}

    public function put(string $key, mixed $value, int $now, int $ttlSeconds): void
    {
        $this->purgeExpired($now);

        if ($this->capacity <= 0) {
            return;
        }

        unset($this->entries[$key]);

        if (count($this->entries) >= $this->capacity) {
            $oldestKey = array_key_first($this->entries);
            if ($oldestKey !== null) {
                unset($this->entries[$oldestKey]);
            }
        }

        $this->entries[$key] = [
            'value' => $value,
            'expiresAt' => $now + $ttlSeconds,
        ];
    }

    public function get(string $key, int $now): mixed
    {
        if (! isset($this->entries[$key])) {
            return null;
        }

        $entry = $this->entries[$key];
        if ($entry['expiresAt'] <= $now) {
            unset($this->entries[$key]);

            return null;
        }

        unset($this->entries[$key]);
        $this->entries[$key] = $entry;

        return $entry['value'];
    }

    private function purgeExpired(int $now): void
    {
        foreach ($this->entries as $k => $entry) {
            if ($entry['expiresAt'] <= $now) {
                unset($this->entries[$k]);
            }
        }
    }
}
