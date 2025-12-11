<?php

namespace App\Models\Tenants;

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Events\TicketClosed;
use App\Models\Tenants\User;
use App\Events\TicketCreated;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'status',
        'description',
        'reporter_email',
        'being_notified',
        'closed_at',
        'handled_at'
    ];

    protected $appends = [
        'asset_code',
        'ticketable_route',
    ];

    protected $with = [
        'reporter:id,first_name,last_name',
        'ticketable',
        'interventions',
    ];

    protected $hidden = [
        'reported_by',
        'ticketable_id',
    ];


    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime:Y-m-d h:i',
            'created_at' => 'datetime:Y-m-d h:i',
            'updated_at' => 'datetime:Y-m-d h:i',
            'handled_at' => 'datetime:Y-m-d h:i',
            'being_notified' => 'boolean',
            'status' => TicketStatus::class
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($ticket) {
            event(new TicketCreated($ticket, $ticket->ticketable));
        });

        static::deleting(function ($ticket) {
            $ticket->interventions()->delete();

            // TODO service to delete pictures from the disk
            $ticket->pictures()->delete();
        });

        static::updated(function ($ticket) {
            if ($ticket->getOriginal('status') !== TicketStatus::CLOSED && $ticket->getChanges()['status'] === TicketStatus::CLOSED->value) {
                event(new TicketClosed($ticket));
            }
        });
    }


    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    // Asset, Site, Building, Floor, Room
    public function ticketable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function closeTicket()
    {
        if (Auth::guard('tenant')->user()?->id)
            $this->closer()->associate(Auth::guard('tenant')->user()->id);

        if (!$this->handled_at)
            $this->handled_at = Carbon::now();

        $this->status = TicketStatus::CLOSED->value;
        $this->closed_at = $this->closed_at ?? Carbon::now();

        return $this->save();
    }

    public function ticketableRoute(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ticketable->locationRoute ?? ''
        );
    }

    public function assetCode(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ticketable->code ?? "Deleted"
        );
    }

    public function changeStatusToOngoing(): void
    {
        $this->handled_at = $this->handled_at ?? Carbon::now();
        $this->status = TicketStatus::ONGOING->value;
        $this->save();
    }

    public function scopeForMaintenanceManager(Builder $query, ?User $user = null)
    {
        $user = $user ?? Auth::user();

        if ($user?->hasRole('Maintenance Manager')) {
            return $query->whereHas(
                'ticketable.maintainable',
                fn($q) =>
                $q->where('maintenance_manager_id', $user->id)
            );
        }

        return $query;
    }
}
