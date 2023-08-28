<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStage extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = ['name', 'description', 'workflow_id'];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class);
    }

}
