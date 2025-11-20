<?php

namespace App\Models;

use CodeIgniter\Model;

class ArsipTemaLinkModel extends Model
{
    protected $table = 'arsip_tema_link';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_tema', 'id_item_aktif'];
}
