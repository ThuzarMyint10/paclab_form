<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends email without ever breaking the workflow — if the mail server is down
 * the action still completes and the error is written to storage/logs/laravel.log.
 */
class Notifier
{
    public static function send(string|array $to, Mailable $mail): bool
    {
        $to = array_values(array_filter((array) $to));
        if (empty($to)) {
            return false;
        }

        try {
            Mail::to($to)->send($mail);

            return true;
        } catch (\Throwable $e) {
            Log::error('Email could not be sent: '.$e->getMessage(), ['to' => $to, 'mail' => get_class($mail)]);

            return false;
        }
    }

    /** Email every address listed in Settings > Admin notification emails */
    public static function admins(Mailable $mail): bool
    {
        return self::send(Setting::adminEmails(), $mail);
    }
}
