<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = ['name', 'description', 'workflow_stage_id', 'order', 'type'];

    public function stage()
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
