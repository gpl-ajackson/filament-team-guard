<?php

namespace Filament\Jetstream\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Jetstream\Actions\UpdateTeamName as UpdateTeamNameAction;
use Filament\Jetstream\Contracts\UpdatesTeamNames;
use Filament\Jetstream\JetstreamPlugin;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Policies\TeamPolicy;
use Filament\Jetstream\Tests\Concerns\CreatesTeamSchema;
use Filament\Jetstream\Tests\Stubs\User;
use Filament\Jetstream\Tests\Support\CustomPrefixUpdatesTeamNames;
use Filament\Jetstream\Tests\Support\RecordingUpdatesTeamNames;
use Filament\Jetstream\Tests\TestCase;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class UpdateTeamNameTest extends TestCase
{
    use CreatesTeamSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::policy(Team::class, TeamPolicy::class);

        app()->forgetInstance(Panel::class);

        $panel = Panel::make()
            ->default()
            ->id('test')
            ->path('test')
            ->login()
            ->plugins([
                JetstreamPlugin::make()->teams(),
            ]);

        Filament::registerPanel($panel);
        Filament::setCurrentPanel($panel);
        Filament::setServingStatus();
    }

    public function test_service_provider_binds_default_update_team_name_action(): void
    {
        $action = $this->app->make(UpdatesTeamNames::class);

        $this->assertInstanceOf(UpdateTeamNameAction::class, $action);
    }

    public function test_default_update_team_name_action_updates_team_in_database(): void
    {
        [$user, $team] = $this->createOwnedTeam('Original Name');

        $this->assertTrue($user->ownsTeam($team));

        /** @var UpdatesTeamNames $action */
        $action = $this->app->make(UpdatesTeamNames::class);

        $action->update($user, $team, ['name' => 'Renamed Team']);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Renamed Team',
        ]);
    }

    public function test_custom_update_team_name_binding_is_used_when_resolved_from_container(): void
    {
        [$user, $team] = $this->createOwnedTeam('Original Name');

        $this->app->singleton(UpdatesTeamNames::class, CustomPrefixUpdatesTeamNames::class);

        $this->app->make(UpdatesTeamNames::class)->update($user, $team, ['name' => 'Renamed Team']);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'CUSTOM: Renamed Team',
        ]);
    }

    public function test_update_team_name_component_delegates_to_updates_team_names_contract(): void
    {
        [$user, $team] = $this->createOwnedTeam('Original Name');

        $recording = new RecordingUpdatesTeamNames;
        $this->app->instance(UpdatesTeamNames::class, $recording);

        $freshTeam = Team::query()->findOrFail($team->id);

        $this->app->make(UpdatesTeamNames::class)->update($user, $freshTeam, ['name' => 'Delegated Name']);

        $this->assertTrue($recording->called);
        $this->assertSame($user->id, $recording->user?->id);
        $this->assertSame($team->id, $recording->team?->id);
        $this->assertSame('Delegated Name', $recording->input['name']);
    }

    public function test_update_team_name_livewire_component_uses_contract_instead_of_direct_model_update(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/Livewire/Teams/UpdateTeamName.php');

        $this->assertStringContainsString('use Filament\Jetstream\Contracts\UpdatesTeamNames;', $source);
        $this->assertStringContainsString('app(UpdatesTeamNames::class)', $source);
        $this->assertStringContainsString('$action->update($this->authUser(), $team, $data);', $source);
        $this->assertStringNotContainsString('$team->update([', $source);
    }

    /**
     * @return array{0: User, 1: Team}
     */
    protected function createOwnedTeam(string $name): array
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'owner-' . uniqid() . '@example.com',
            'password' => 'password',
        ]);

        $team = Team::query()->forceCreate([
            'user_id' => $user->id,
            'name' => $name,
            'personal_team' => false,
        ]);

        $user->forceFill(['current_team_id' => $team->id])->save();

        return [$user->fresh(), $team->fresh()];
    }
}
