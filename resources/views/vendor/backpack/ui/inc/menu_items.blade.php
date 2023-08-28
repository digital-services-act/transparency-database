{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Platforms" icon="la la-building nav-icon" :link="backpack_url('platform')" />

<x-backpack::menu-item title="Workflows" icon="la la-question" :link="backpack_url('workflow')" />
<x-backpack::menu-item title="Workflow instances" icon="la la-question" :link="backpack_url('workflow-instance')" />
<x-backpack::menu-item title="Workflow steps" icon="la la-question" :link="backpack_url('workflow-step')" />
<x-backpack::menu-item title="Workflow stages" icon="la la-question" :link="backpack_url('workflow-stage')" />