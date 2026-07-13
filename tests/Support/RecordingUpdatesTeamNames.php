<?php

namespace Filament\Jetstream\Tests\Support;

use Filament\Jetstream\Contracts\UpdatesTeamNames;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;

class RecordingUpdatesTeamNames implements UpdatesTeamNames
{
    public bool $called = false;

    public ?FilamentUser $user = null;

    public ?Model $team = null;

    /** @var array<string, mixed> */
    public array $input = [];

    public function update(FilamentUser $user, Model $team, array $input): void
    {
        $this->called = true;
        $this->user = $user;
        $this->team = $team;
        $this->input = $input;
    }
}
