@props(['user' => null, 'options' => null, 'roles' => []])

<x-ecl.textfield label="Name" name="name" id="name" required=true value="{{ $user->name }}" />

<h3 class="ecl-u-type-heading-3 ecl-u-mb-l">"{{ $user->name }}" Roles</h3>

<ul class="ecl-unordered-list ecl-unordered-list--no-bullet ecl-u-mb-l">
    @foreach($roles as $role)
        <li class="ecl-unordered-list__item">
            <x-ecl.checkbox id="permission-{{ $role->id }}"
                            name="roles[]"
                            value="{{ $role->id }}"
                            checked="{{ $user->roles()->pluck('id')->contains($role->id) }}"
                            label="{{ $role->name }}"
            />
        </li>
    @endforeach
</ul>