<?php

namespace App\Http\Traits;

trait PlatformsTrait
{


    public function getPlatforms(): array
    {
        return [
            "Youtube",
            "Google",
            "Amazon",
            "Apple",
            "Microsoft",

            "Tencent",
            "Shopify",
            "Facebook",
            "Twitter",
            "Reddit",

            "TikTok",
            "Pinterest",
            "Instagram",
            "LinkedIn",
            "Snapchat",

            "Dailymotion",
            "Vimeo",
            "Flickr",
            "WeChat",
            "Tumblr"

        ];
    }
}
