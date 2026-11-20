<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Actions\Action;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Models\Team;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class PendingTeamInvitations extends BaseLivewireComponent implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->team->teamInvitations()->latest()->getQuery())
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('email'),
                ]),
            ])
            ->paginated(false)
            ->recordActions([
                Action::make('resendTeamInvitation')
                    ->label(__('filament-team-guard::default.action.resend_team_invitation.label'))
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::check('updateTeamMember', $this->team))
                    ->action(fn ($record) => $this->resendTeamInvitation($record)),
                Action::make('cancelTeamInvitation')
                    ->label(__('filament-team-guard::default.action.cancel_team_invitation.label'))
                    ->color('danger')
                    ->visible(fn () => Gate::check('removeTeamMember', $this->team))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->cancelTeamInvitation($this->team, $record)),
            ]);
    }

    public function resendTeamInvitation(Model $invitation)
    {
        Gate::authorize('updateTeamMember', $this->team);
        $this->ensureInvitationBelongsToTeam($invitation, $this->team);

        Mail::to($invitation->email)->send(new TeamInvitation($invitation));

        $this->sendNotification(__('filament-team-guard::default.notification.team_invitation_sent.success.message'));
    }

    public function cancelTeamInvitation(Team $team, Model $invitation)
    {
        Gate::authorize('removeTeamMember', $team);
        $this->ensureInvitationBelongsToTeam($invitation, $team);

        $invitation->delete();

        $team->fresh();

        $this->sendNotification(
            __('filament-team-guard::default.notification.team_invitation_cancelled.success.message')
        );
    }

    protected function ensureInvitationBelongsToTeam(Model $invitation, Team $team): void
    {
        $foreignKey = Jetstream::getForeignKeyColumn($team::class);

        if ((string) $invitation->getAttribute($foreignKey) !== (string) $team->getKey()) {
            throw new AuthorizationException;
        }
    }

    public function render()
    {
        return view('filament-team-guard::livewire.teams.pending-team-invitations');
    }
}
