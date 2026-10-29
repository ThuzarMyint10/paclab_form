<?php

return [
    /*
    | Staff mailboxes that receive "new enquiry" and customer response notifications.
    | Can also be changed from Admin > Settings (the setting overrides this value).
    */
    'admin_emails' => array_filter(array_map('trim', explode(',', env('PACLAB_ADMIN_EMAILS', 'paclab@pacificlab.com.sg')))),

    'seed_admin_email' => env('PACLAB_SEED_ADMIN_EMAIL', 'admin@pacificlab.com.sg'),
    'seed_admin_password' => env('PACLAB_SEED_ADMIN_PASSWORD', 'ChangeMe@2026'),

    'currencies' => ['SGD', 'USD'],

    'storage_options' => ['Room Temperature', 'Refrigerate (2-8°C)', 'Freeze (-20°C)'],
];
