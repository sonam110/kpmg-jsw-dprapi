<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DprManage;
use App\Models\DprConfig;
use App\Models\DprLog;
use App\Traits\UserId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DprImport extends Model
{
    use UserId, LogsActivity;
    

    protected $fillable = [
        'user_id',
        'dpr_config_id',
        'dpr_manage_id',
        'data_date',
        'sheet_json_data',
        'item_desc',
        'random_no',
       
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('DPR Import')
            ->setDescriptionForEvent(fn(string $eventName) => "DPR Import has been {$eventName}");;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id')->where('status',1);
    }
    public function dprManage() {
        return $this->belongsTo(DprManage::class, 'dpr_manage_id', 'id');
    }
    public function dprLog() {
        return $this->hasOne(DprLog::class, 'dpr_import_id', 'id');
    }
    public function dprConfig() {
        return $this->belongsTo(DprConfig::class, 'dpr_config_id', 'id');
    }
   /* public function getSheetJsonDataAttribute($value) {
        return $this->attributes['sheet_json_data'] ? json_decode($this->attributes['sheet_json_data'],true) : [];

    }*/
}
