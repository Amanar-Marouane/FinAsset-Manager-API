<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RTokenJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $user_id,
        private string $expectedOldHash,
        private string $newHash
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->user_id);
        if (!$user) {
            return;
        }

        // Only rotate if no one else changed it meanwhile
        if ($user->refresh_token_hash === $this->expectedOldHash) {
            $user->update(['refresh_token_hash' => $this->newHash]);
        }
    }
}
