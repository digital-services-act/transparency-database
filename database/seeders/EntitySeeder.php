<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names =          [
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

        foreach ($names as $name) {
            Entity::factory()->state([
                'name' => $name,
                'kind' => 'organization'
            ])->create();

            User::factory()->state([
                'name' => $name,
                'email'=> "fake-user@".strtolower($name).".com"
            ])->create();
        }
//        Entity::factory()->count(100)->create();
    }
}
