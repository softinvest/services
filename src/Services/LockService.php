<?php

namespace SoftInvest\Services;

use Illuminate\Support\Facades\Redis;

class LockService
{
    private const LOCK_TIMEOUT = 10;

    /**
     * @param  $uuid
     * @param  string|null $who
     * @param  int         $timeout
     * @return bool
     */
    public function locked($uuid, ?string $who = null, int $timeout = self::LOCK_TIMEOUT): bool
    {
        $key = 'lock:' . $uuid;
        if (Redis::setnx($key, ($who ?: '') . ' at ' . now()->format('d.m.Y H:i:s'))) {
            Redis::expire($key, $timeout); // 1h
            return true;
        }
        return false;
    }

    /**
     * @param  $uuid
     * @return mixed
     */
    public function release($uuid): mixed
    {
        return Redis::del('lock:' . $uuid);
    }

    /**
     * @param  $uuid
     * @return bool
     */
    public function wasLocked($uuid): bool
    {
        return (bool)Redis::exists('lock:' . $uuid);
    }
}
