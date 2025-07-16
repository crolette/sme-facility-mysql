<?php

namespace App\Models\Tenants;

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Models\Tenants\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    ];

    protected $appends = [
        'asset_code',
    ];

    protected $with = [
        'reporter',
        'closer',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'date:d-m-Y h:m',
            'created_at' => 'date:d-m-Y H:m',
            'updated_at' => 'date:d-m-Y H:m',
            'being_notified' => 'boolean'
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function ticketable(): MorphTo
    {
        return $this->morphTo();
    }

    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'imageable');
    }

    public function closeTicket()
    {
        $this->closer()->associate(Auth::guard('tenant')->user()->id);

        $this->status = TicketStatus::CLOSED->value;
        $this->closed_at = now();

        return $this->save();
    }

    public function assetCode(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ticketable->code
        );
    }
}
