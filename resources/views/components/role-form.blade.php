@props(['role' => null, 'options' => null, 'permissions' => null])
<x-ecl.textfield label="Name" name="name" id="name" required=true value="{{ $role->name }}"/>

<ul class="ecl-unordered-list ecl-unordered-list--no-bullet ecl-u-mb-l">
    @foreach($permissions as $permission)
        <li class="ecl-unordered-list__item">
            <x-ecl.checkbox id="permission-{{ $permission->id }}"
                             name="permissions[]"
                             value="{{ $permission->id }}"
                             checked="{{ $role->permissions()->pluck('id')->contains($permission->id) }}"
                             label="{{ $permission->name }}"
            />
        </li>
    @endforeach
</ul>