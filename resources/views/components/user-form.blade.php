@props(['user' => null, 'options' => null, 'roles' => [], 'action' => 'create'])

@if($action === 'create')
<x-ecl.textfield label="Email" type="email" name="email" id="email" required=true value="{{ $user->email }}" />
@endif

<h3 class="ecl-u-type-heading-3 ecl-u-mb-l">Platform</h3>

<x-ecl.select label="Platform"
              name="platform_id"
              id="platform_id" default="{{ $user->platform_id }}"
              :options="$options['platforms']"
              :required="false" />

<h3 class="ecl-u-type-heading-3 ecl-u-mb-l">Roles</h3>

<ul class="ecl-unordered-list ecl-unordered-list--no-bullet ecl-u-mb-l">
    <x-ecl.error-feedback name="roles" />
    @foreach($options['roles'] as $role)
        <li class="ecl-unordered-list__item">
            <x-ecl.checkbox id="permission-{{ $role->id }}"
                            name="roles[]"
                            value="{{ $role->id }}"
                            checked="{{ ($action=='edit' && $user->roles()->pluck('id')->contains($role->id)) || ($action=='create' && $role->name == 'Contributor') }}"
                            label="{{ $role->name }}"
            />
        </li>
    @endforeach
</ul>
