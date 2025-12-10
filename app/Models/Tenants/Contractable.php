<?php

namespace App\Models\Tenants;

use App\Observers\ContractableObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Contractable extends MorphPivot
{
    protected $table = 'contractables';
    public $incrementing = true;

    protected static function booted()
    {
        parent::booted();

        static::created(function ($contractable) {
            // dump('created in contractable');
        });

        static::deleted(function ($contractable) {
            // dump('*** DELETED in contractable : ' . $contractable->id);
        });
    }




    public function contractable()
    {
        return $this->morphTo();
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
