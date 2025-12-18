<?php

namespace App\Helpers;

use App\Jobs\RTokenJob;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Illuminate\Support\Str;

class RTokenQueue
{
    /**
     * @return array{
     *   job_id: int,
     *   newHash: string,
     *   expectedOldHash: string
     * }|null
     */
    public static function findPendingForUser(int|string $userId): ?array
    {
        // Pull candidate jobs (database queue driver)
        $jobs = DB::table('jobs')
            ->select('id', 'payload')
            ->where('payload', 'like', '%RTokenJob%') // quick prefilter
            ->get();

        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            if (!is_array($payload)) {
                continue;
            }

            // Defensive: ensure it's our job class
            $display = $payload['displayName'] ?? '';
            if ($display !== RTokenJob::class) {
                continue;
            }

            $command = $payload['data']['command'] ?? null;
            if (!$command) {
                continue;
            }

            try {
                $jobObject = unserialize($command); // same pattern you already use
                if (!is_object($jobObject)) {
                    continue;
                }

                $ref = new ReflectionClass($jobObject);
                if ($ref->getName() !== RTokenJob::class) {
                    continue;
                }

                // Pull private props: user_id, expectedOldHash, newHash
                $pUser = $ref->getProperty('user_id');
                $uid = (string) $pUser->getValue($jobObject);

                if ((string) $uid !== (string) $userId) {
                    continue;
                }

                $pNew = $ref->getProperty('newHash');
                $newHash = (string) $pNew->getValue($jobObject);

                $pOld = $ref->getProperty('expectedOldHash');
                $expectedOldHash = (string) $pOld->getValue($jobObject);

                return [
                    'job_id'          => (int) $job->id,
                    'newHash'         => $newHash,
                    'expectedOldHash' => $expectedOldHash,
                ];
            } catch (Exception $e) {
                // Fallback: very brittle string match; skip to avoid bad data
                continue;
            }
        }

        return null;
    }

    /**
     * Rotate user refresh token:
     *  - Generates a new UUID refresh token
     *  - Stores its hash in DB
     *  - Returns hashed token (sent to client)
     */
    public static function rotateUserRT(User $user): string
    {
        // 1) If there is already a pending rotation job, REUSE its newHash.
        if ($pending = self::findPendingForUser($user->id)) {
            // Everyone gets the same token; DB will become this value when job executes
            return $pending['newHash'];
        }

        // 2) Otherwise, create a new token, schedule a single rotation, return it.
        $newRefreshToken = Str::uuid()->toString();
        $hashedNewRefreshToken = hash('sha256', $newRefreshToken);

        $oldHash = (string) $user->refresh_token_hash;

        RTokenJob::dispatch(
            (string) $user->id,
            $oldHash,
            $hashedNewRefreshToken
        )->delay(now()->addMinute()); // matches your 60s overlap strategy

        return $hashedNewRefreshToken;
    }
}
