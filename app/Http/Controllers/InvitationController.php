<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvitationStoreRequest;
use App\Http\Requests\InvitationUpdateRequest;
use App\Models\Invitation;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class InvitationController extends Controller
{
    public function index(Request $request): View
    {
        $invitations = Invitation::paginate(50);

        return view('invitation.index', ['invitations' => $invitations]);
    }

    public function create(Request $request): View
    {
        $invitation = new Invitation();
        $options = $this->prepareOptions();

        return view('invitation.create', [
            'invitation' => $invitation,
            'options' => $options,
        ]);

    }

    public function store(InvitationStoreRequest $request): RedirectResponse
    {
        $invitation = Invitation::create($request->validated());

        $request->session()->flash('invitation.id', $invitation->id);

        return redirect()->route('invitation.index');
    }

    public function show(Request $request, Invitation $invitation): View
    {
        return view('invitation.show', ['invitation' => $invitation]);
    }

    public function edit(Request $request, Invitation $invitation): View
    {
        $options = $this->prepareOptions();
        return view('invitation.edit', [
            'invitation' => $invitation,
            'options' => $options
        ]);

    }

    public function update(InvitationUpdateRequest $request, Invitation $invitation): RedirectResponse
    {
        $invitation->update($request->validated());

        $request->session()->flash('invitation.id', $invitation->id);

        return redirect()->route('invitation.index');
    }

    public function destroy(Request $request, Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return redirect()->route('invitation.index');
    }

    private function prepareOptions()
    {
        $platforms = Platform::query()->orderBy('name', 'ASC')->get()->map(static fn($platform) => [
            'value' => $platform->id,
            'label' => $platform->name
        ])->toArray();
        array_unshift($platforms, ['value' => '', 'label' => 'Choose a platform']);


        return ['platforms' => $platforms];
    }
}
