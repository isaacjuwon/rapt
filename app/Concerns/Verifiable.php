<?php

namespace App\Concerns;

trait Verifiable
{
    /**
     * Mark the user as verified.
     */
    public function markAsVerified(): void
    {
        $this->forceFill([
            'is_verified' => true,
        ])->save();
    }

    /**
     * Determine if the user is verified.
     */
    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }
}
