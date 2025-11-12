<?php

namespace App\Models\Tenants;

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
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

        'interventions',
    ];

    protected $hidden = [
        'reported_by',
        'ticketable_id',
    ];


    protected function casts(): array
    {
        return [
            'closed_at' => 'date:Y-m-d',
            'created_at' => 'date:Y-m-d',
            'updated_at' => 'date:Y-m-d',
            'handled_at' => 'date:Y-m-d',
            'being_notified' => 'boolean',
            'status' => TicketStatus::class
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($ticket) {
            $ticket->interventions()->delete();

            // TODO service to delete pictures from the disk
            $ticket->pictures()->delete();
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
        $this->closer()->associate(Auth::guard('tenant')->user()->id);

        if (!$this->handled_at)
            $this->handled_at = Carbon::now()->toDateString();

        $this->status = TicketStatus::CLOSED->value;
        $this->closed_at = now();

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
        $this->handled_at = Carbon::now()->toDateString();
        $this->status = TicketStatus::ONGOING->value;
        $this->save();
    }
}
