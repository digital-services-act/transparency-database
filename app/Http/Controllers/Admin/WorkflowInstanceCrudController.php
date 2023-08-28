<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WorkflowInstanceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class WorkflowInstanceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WorkflowInstanceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\WorkflowInstance::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/workflow-instance');
        CRUD::setEntityNameStrings('workflow instance', 'workflow instances');

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
//        CRUD::setFromDb(); // set columns from db columns.

//        Crud::column('workflow_id')->type('select')->label('WF Parent')->entity('workflow')->attribute('name')->model('App\Models\Workflow');
        CRUD::addColumns(['id']); // add multiple columns, at the end of the stack
        CRUD::addColumn([
            // 1-n relationship
            'label'          => 'Workflow', // Table column heading
            'type'           => 'select2',
            'name'           => 'workflow_id', // the column that contains the ID of that connected entity;
            'entity'         => 'workflow', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
            'visibleInTable' => true,
            'visibleInModal' => false,
        ]);

        CRUD::addColumn([
            // 1-n relationship
            'label'          => 'Current Step', // Table column heading
            'type'           => 'select2',
            'name'           => 'current_step', // the column that contains the ID of that connected entity;
            'entity'         => 'currentStep', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
            'visibleInTable' => true,
            'visibleInModal' => false,
        ]);

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(WorkflowInstanceRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        CRUD::addField([  // Select2
            'label'     => 'Workflow',
            'type'      => 'select2',
            'name'      => 'workflow_id', // the db column for the foreign key
            'entity'    => 'workflow', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            // 'wrapperAttributes' => [
            //     'class' => 'form-group col-md-6'
            //   ], // extra HTML attributes for the field wrapper - mostly for resizing fields
//            'tab' => 'Basic Info',
        ]);

        CRUD::addField([
            // 1-n relationship
            'label'          => 'Current Step', // Table column heading
            'type'           => 'select2',
            'name'           => 'current_step', // the column that contains the ID of that connected entity;
            'entity'         => 'currentStep', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
            'visibleInTable' => true,
            'visibleInModal' => false,
        ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
