<?php

namespace App\Models\Tenants;

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected function casts(): array
    {
        return [
            'closed_at' => 'date:d-m-Y',
            'created_at' => 'date:d-m-Y',
            'updated_at' => 'date:d-m-Y',
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

    public function closeTicket()
    {
        $this->closer()->associate(Auth::guard('tenant')->user()->id);

        $this->status = TicketStatus::CLOSED->value;
        $this->closed_at = now();

        return $this->save();
    }
}
