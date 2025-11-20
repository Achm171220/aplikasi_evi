<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table            = 'login_attempts';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['ip_address', 'email_attempt', 'time_attempt', 'success', 'user_id'];
    protected $useTimestamps    = false; // Kita atur time_attempt secara manual atau di DB
}
