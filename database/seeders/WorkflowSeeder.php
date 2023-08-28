<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('workflows')->insert([
            'id' => 1,
            'name' => 'onboarding-platform',
            'description' => 'Onboarding Workflow to create a platform'
        ]);

        DB::table('workflow_stages')->insert([
            'id' => 1,
            'name' => 'platform-creation',
            'description' => 'Creation of the platform',
            'workflow_id' => 1
        ]);

        DB::table('workflow_stages')->insert([
            'id' => 2,
            'name' => 'platform-approval',
            'description' => 'Approval of the platform',
            'workflow_id' => 1
        ]);

        DB::table('workflow_stages')->insert([
            'id' => 3,
            'name' => 'platform-approved',
            'description' => 'Platform has been approved',
            'workflow_id' => 1
        ]);

        DB::table('workflow_steps')->insert([
            'id' => 1,
            'name' => 'form-create',
            'description' => 'Form to create a new platform',
            'workflow_stage_id' => 1,
            'sequence' => 10,
            'type' => 'human_task',
        ]);

        DB::table('workflow_steps')->insert([
            'id' => 2,
            'name' => 'notification-platform-for-approval',
            'description' => 'Notification when a platform has been submitted for approval',
            'workflow_stage_id' => 1,
            'sequence' => 20,
            'type' => 'automated_task',
        ]);

        DB::table('workflow_steps')->insert([
            'id' => 3,
            'name' => 'form-approve-platform',
            'description' => 'Form to approve a platform',
            'workflow_stage_id' => 2,
            'sequence' => 10,
            'type' => 'human_task',
        ]);

        DB::table('workflow_steps')->insert([
            'id' => 4,
            'name' => 'update-platform-approved',
            'description' => 'Update platform status',
            'workflow_stage_id' => 3,
            'sequence' => 10,
            'type' => 'automated_task',
        ]);

        DB::table('workflow_steps')->insert([
            'id' => 5,
            'name' => 'update-platform-owner',
            'description' => 'Update platform owner',
            'workflow_stage_id' => 3,
            'sequence' => 20,
            'type' => 'automated_task',
        ]);

        DB::table('workflow_steps')->insert([
            'id' => 6,
            'name' => 'notification-platform-approved',
            'description' => 'Send a notification to approved platform creator',
            'workflow_stage_id' => 3,
            'sequence' => 30,
            'type' => 'automated_task',
        ]);

        DB::table('workflow_instances')->insert([
            'id' => 1,
            'workflow_id' => 1,
            'current_step' => 1,
            'assigned_to' => 1,
        ]);
    }
}
