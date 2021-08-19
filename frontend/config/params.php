<?php
return [
    'adminEmail' => 'admin@example.com',
    'bsVersion' => '4.x', // this will set globally `bsVersion` to Bootstrap 4.x for all Krajee Extensions
    // other settings
    // 'adminEmail' => 'admin@example.com'
    'bsDependencyEnabled' => false, // this will not load Bootstrap CSS and JS for all Krajee extensions 
    // you need to ensure you load the Bootstrap CSS/JS manually in your view layout before Krajee CSS/JS assets
    //
    // other params settings below
    // 'bsVersion' => '4.x', 
    // 'adminEmail' => 'admin@example.com'
];
