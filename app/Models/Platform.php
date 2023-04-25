<?php

namespace App\Models;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model implements AttachableInterface
{
    use HasFactory;

    use PaperclipTrait;

    public const PLATFORM_TYPE_SOCIAL_MEDIA = 'Social Media';
    public const PLATFORM_TYPE_VIDEO = 'Video';
    public const PLATFORM_TYPE_MUSIC = 'Music';
    public const PLATFORM_TYPE_PHOTOGRAPHY = 'Photography';

    public const PLATFORM_TYPES = [
        'SOCIAL_MEDIA' => self::PLATFORM_TYPE_SOCIAL_MEDIA,
        'VIDEO' => self::PLATFORM_TYPE_VIDEO,
        'MUSIC' => self::PLATFORM_TYPE_MUSIC,
        'PHOTOGRAPHY' => self::PLATFORM_TYPE_PHOTOGRAPHY,
    ];

    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('icon');

        parent::__construct($attributes);
    }
}
