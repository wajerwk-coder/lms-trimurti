<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    | Dipakai untuk upload foto profil ke Cloudinary (Railway ephemeral storage fix)
    | Set CLOUDINARY_CLOUD_NAME dan CLOUDINARY_UPLOAD_PRESET di Railway Variables.
    */

    'cloud_name'    => env('CLOUDINARY_CLOUD_NAME', 'aw9h9icb'),
    'api_key'       => env('CLOUDINARY_API_KEY', ''),
    'api_secret'    => env('CLOUDINARY_API_SECRET', ''),
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', 'lms_photos'),
    'secure'        => true,
];
