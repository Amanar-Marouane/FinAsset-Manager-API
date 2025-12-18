<?php

namespace App\Helpers;

use Carbon\Carbon;

class HumanFormatter
{
    /**
     * Format a Carbon date into a human-friendly string.
     *
     * @param Carbon|null $date
     * @return string|null
     */
    public static function formatDate(?Carbon $date): ?string
    {
        if (!$date) {
            return null;
        }

        if ($date->isToday()) {
            return 'Aujourd’hui à ' . $date->format('H\hi');
        } elseif ($date->isYesterday()) {
            return 'Hier à ' . $date->format('H\hi');
        } else {
            return 'Le ' . $date->format('d M Y à H\hi');
        }
    }

    /**
     * Convert seconds into smart human-readable string.
     */
    public static function formatDuration(?int $seconds): ?string
    {
        if (!$seconds) {
            return null;
        }

        if ($seconds >= 86400) {
            return round($seconds / 86400, 2) . ' days';
        } elseif ($seconds >= 3600) {
            return round($seconds / 3600, 2) . ' hours';
        } elseif ($seconds >= 60) {
            return round($seconds / 60, 2) . ' minutes';
        }

        return $seconds . ' seconds';
    }
}
