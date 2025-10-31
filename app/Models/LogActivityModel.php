<?php

namespace App\Models;

use CodeIgniter\Model;

class LogActivityModel extends Model
{
    protected $table = 'log_activities';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'date_login',
        'date_logout',
        'range_active',
        'location',
        'ip_address',
        'user_agent',
        'notes'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
