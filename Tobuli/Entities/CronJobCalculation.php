<?php
namespace Tobuli\Entities;
use Illuminate\Database\Eloquent\Model;

class CronJobCalculation extends Model
{
    protected $table = 'cron_job_calculations';

    protected $fillable = [
        'device_id', 'device_name', 'protocol', 'distance',
        'total_moving_time', 'total_idle_time', 'total_stop_time',
        'min_fuel_level', 'max_fuel_level', 'total_fuel_filled',
        'total_fuel_theft', 'fuel_consumption', 'fuel_average','job_time_from','job_time_to','created_at'
    ];


    public $timestamps = false;
    
    public function getDisplayName(): string
    { 
        return $this->name;
    }
}