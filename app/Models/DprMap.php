<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UserId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\ItemDescriptionMaster;

class DprMap extends Model
{
    use HasFactory, UserId, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('DPR Mapping')
            ->setDescriptionForEvent(fn(string $eventName) => "DPR Mapping has been {$eventName}");;
    }
    public function itemDesc() {
        return $this->belongsTo(ItemDescriptionMaster::class, 'item_desc', 'title');
    }
}
