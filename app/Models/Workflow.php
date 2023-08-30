<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function stages()
    {
        return $this->hasMany(WorkflowStage::class);
    }

}
