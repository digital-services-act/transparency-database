<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

class Notice extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date_sent' => 'timestamp',
        'date_enacted' => 'timestamp',
        'date_abolished' => 'timestamp',
    ];

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'notices_index';
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body
        ];
    }

    /**
     * @return \Closure
     */
    public function formatTimeStamp(): \Closure
    {
        return fn($value) => $value ? Carbon::parse($value)->format('d-m-Y H:m') : null;
    }

    protected function dateSent(): Attribute
    {
        return Attribute::make(
            get: $this->formatTimeStamp(),
        );
    }

    protected function dateEnacted(): Attribute
    {
        return Attribute::make(
            get: $this->formatTimeStamp(),
        );
    }

    protected function dateAbolished(): Attribute
    {
        return Attribute::make(
            get: $this->formatTimeStamp(),
        );
    }

    public function entities()
    {
        return $this->belongsToMany(Entity::class)->withPivot('role');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
