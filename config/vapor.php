<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redirect "www" To The Root Domain
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Vapor will redirect requests to the "www"
    | subdomain to the application's root domain. When this option is not
    | enabled, Vapor redirects your root domain to the "www" subdomain.
    |
    */

    'redirect_to_root' => true,

    /*
    |--------------------------------------------------------------------------
    | Redirect robots.txt
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Vapor will redirect requests for your
    | application's "robots.txt" file to the location of the S3 asset
    | bucket or CloudFront's asset URL instead of serving directly.
    |
    */

    'redirect_robots_txt' => true,

    /*
    |--------------------------------------------------------------------------
    | Servable Assets
    |--------------------------------------------------------------------------
    |
    | Here you can configure list of public assets that should be servable
    | from your application's domain instead of only being servable via
    | the public S3 "asset" bucket or the AWS CloudFront CDN network.
    |
    */

    'serve_assets' => [
        'static/ecl/images/icons/sprites/icons.svg',
        'static/ecl/styles/optional/ecl-ec-default.css',
        'static/ecl/styles/optional/ecl-reset.css',
        'static/ecl/styles/ecl-ec.css',
        'static/ecl/styles/ecl-ec-print.css',
        'static/ecl/scripts/ecl-ec.js'
    ],

];