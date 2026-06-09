<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseVelocity extends Model
{
    /** @use HasFactory<\Database\Factories\DatabaseVelocityFactory> */
    use HasFactory;

    protected $fillable = [
        'max_statement_id',
        'rows_per_second',
    ];

    protected function casts(): array
    {
        return [
            'max_statement_id' => 'integer',
            'rows_per_second' => 'decimal:2',
        ];
    }
}
