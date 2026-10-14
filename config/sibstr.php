<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pengingat pengisian SIBSTR Triwulanan
    |--------------------------------------------------------------------------
    |
    | BPS manages the schedule, targets and message from /bps/pengingat; those
    | live in survey_reminder_settings. Only server-level concerns are here.
    |
    | `enabled` decides whether THIS server may send at all, and must be true
    | only on prod: datakitadev runs the same image against a copy of prod, so
    | enabling it there emails real respondents a second time. With it off the
    | page still previews recipients but cannot queue or send.
    |
    */

    'reminder' => [
        'enabled' => (bool) env('SIBSTR_REMINDER_ENABLED', false),

        // A mailer name from config/mail.php; null uses the default (Brevo).
        // Set to "reminder" to send reminders through REMINDER_MAIL_* instead.
        'mailer' => env('SIBSTR_REMINDER_MAILER'),

        // Must be an address on a domain verified with the mail provider.
        'from_address' => env('SIBSTR_REMINDER_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),

        // The scheduler sends at most this many per minute, pausing between
        // emails, to stay under the provider's rate limit.
        'batch_size'    => (int) env('SIBSTR_REMINDER_BATCH_SIZE', 60),
        'send_delay_ms' => (int) env('SIBSTR_REMINDER_SEND_DELAY_MS', 500),

        // Collection for a quarter opens the month after it ends.
        'collection_months' => [1, 4, 7, 10],

        // Initial values for the settings page, before BPS first saves it.
        'defaults' => [
            'days'             => [2, 7, 13, 17, 22],
            'time'             => '08:00',
            'from_name'        => 'BPS Kota Batam',
            'reply_to'         => env('SIBSTR_REMINDER_REPLY_TO'),
            // Blok II R201 answers that put a company out of scope.
            'excluded_kondisi' => ['tutup', 'tidak_ditemukan', 'double_ganda_duplikat'],
        ],
    ],

];
