<?php

namespace Filament\Jetstream\Tests\Support;

use Filament\Jetstream\Actions\UpdateTeamName as BaseUpdateTeamName;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CustomPrefixUpdatesTeamNames extends BaseUpdateTeamName
{
    public function update(FilamentUser $user, Model $team, array $input): void
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateTeamName');

        $team = get_class($team)::findOrFail($team->id);

        $team->update([
            'name' => 'CUSTOM: ' . $input['name'],
        ]);
    }
}
