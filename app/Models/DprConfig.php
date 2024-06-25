<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UserId;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Project;
use App\Models\WorkPackage;
use App\Models\DprImport;
use App\Models\DprManage;
use App\Models\DprMap;
use App\Models\DprLog;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DprConfig extends Model
{
    use HasFactory, UserId, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('DPR config')
            ->setDescriptionForEvent(fn(string $eventName) => "DPR config has been {$eventName}");;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id')->where('status',1);
    }
    public function Project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id')->where('status',1);
    }

     public function WorkPackage()
    {
        return $this->belongsTo(WorkPackage::class, 'work_pack_id', 'id')->where('status',1);
    }
    public function DprMap()
    {
        return $this->belongsTo(DprMap::class, 'id', 'dpr_config_id');
    }
    public function dprManage() {
        return $this->belongsTo(DprManage::class, 'id', 'dpr_config_id');
    }
    public function DprLogs()
    {
         return $this->hasMany(DprLog::class, 'dpr_config_id', 'id')->groupBy('random_no');
    }
    public function dprImport()
    {
         return $this->hasmany(DprImport::class, 'dpr_config_id', 'id');
    }
}
