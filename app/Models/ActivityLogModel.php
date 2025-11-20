<?php

namespace App\Models;

class ActivityLogModel extends BaseModel
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_user',
        'username',
        'event',
        'target_table',
        'target_id',
        'data_before',
        'data_after',
        'ip_address',
        'user_agent'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = ''; // Tidak ada updated_at
}
