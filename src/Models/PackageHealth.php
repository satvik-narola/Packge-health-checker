<?php

namespace Itzdevsatvik\PackageHealthChecker\Models;

use Illuminate\Database\Eloquent\Model;

class PackageHealth extends Model
{
    protected $table = 'package_health_checks';
    
    protected $fillable = ['results', 'scan_time'];
    
    protected $casts = [
        'results' => 'array',
        'scan_time' => 'datetime',
    ];
}