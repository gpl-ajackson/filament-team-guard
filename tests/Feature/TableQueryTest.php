<?php

namespace Filament\Jetstream\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Jetstream\JetstreamPlugin;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Tests\Concerns\CreatesTeamSchema;
use Filament\Jetstream\Tests\Stubs\User;
use Filament\Jetstream\Tests\TestCase;
use Filament\Panel;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Filament 5 type-hints Table::applyQueryScopes(Builder $query) (seen in 5.4.1 and
 * 5.11), so a table ->query() closure returning a Relation throws a TypeError when
 * the table renders. The package tables must return an Eloquent Builder.
 *
 * Filament 5.4.1's Table::getQuery() calls $query->clone(), which a Relation forwards
 * to its Builder, so getQuery() alone does not expose a Relation there. The test also
 * checks the raw ->query() closure result so it catches the regression on any version.
 */
class TableQueryTest extends TestCase
{
    use CreatesTeamSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_pending_team_invitations_query_is_a_builder_scoped_to_the_team(): void
    {
        $owner = User::query()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $team = Team::query()->forceCreate(['user_id' => $owner->id, 'name' => 'Team A', 'personal_team' => false]);
        $other = Team::query()->forceCreate(['user_id' => $owner->id, 'name' => 'Team B', 'personal_team' => false]);

        $team->teamInvitations()->create(['email' => 'mine@example.com', 'role' => 'editor']);
        $other->teamInvitations()->create(['email' => 'theirs@example.com', 'role' => 'editor']);

        $component = new PendingTeamInvitations;
        $component->team = $team;

        $table = $component->table(Table::make($component));

        $this->assertInstanceOf(Builder::class, $this->evaluateTableQueryClosure($table));

        $query = $table->getQuery();

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertSame(['mine@example.com'], $query->pluck('email')->all());
    }

    /**
     * Returns what the table's ->query() closure returns, before Filament applies its scopes.
     */
    protected function evaluateTableQueryClosure(Table $table): mixed
    {
        $query = (fn () => $this->query)->call($table);

        return $query instanceof \Closure ? $query() : $query;
    }
}
