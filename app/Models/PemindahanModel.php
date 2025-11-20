<?php

namespace App\Models;

use CodeIgniter\Model;

class PemindahanModel extends Model
{
    protected $table            = 'pemindahan_proposal'; // Nama tabel Anda
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Sesuaikan jika Anda menggunakan soft deletes
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_user_pengusul',
        'id_user_v1',
        'id_user_v2',
        'id_user_v3',
        'status_proposal',
        'notes_v1',
        'notes_v2',
        'notes_v3',
        'id_ba'
    ];

    // Dates
    protected $useTimestamps = true; // Set true jika ada created_at dan updated_at di tabel Anda
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation (optional, but good practice)
    protected $validationRules    = [];
    protected $validationMessages = [];

    /**
     * Mengambil proposal yang dibuat oleh user tertentu.
     */
    public function getProposalsByUser(int $userId)
    {
        return $this->select('pemindahan_proposal.*, users_pengusul.name as pengusul_name,
                             users_v1.name as v1_name, users_v2.name as v2_name, users_v3.name as v3_name')
            ->join('users as users_pengusul', 'users_pengusul.id = pemindahan_proposal.id_user_pengusul')
            ->join('users as users_v1', 'users_v1.id = pemindahan_proposal.id_user_v1', 'left')
            ->join('users as users_v2', 'users_v2.id = pemindahan_proposal.id_user_v2', 'left')
            ->join('users as users_v3', 'users_v3.id = pemindahan_proposal.id_user_v3', 'left')
            ->where('pemindahan_proposal.id_user_pengusul', $userId)
            ->orderBy('pemindahan_proposal.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Mengambil proposal yang perlu diverifikasi oleh user saat ini
     * (untuk daftar verifikasi di sisi admin).
     */
    public function getProposalsForVerification(int $verifierId, string $roleJabatan)
    {
        $builder = $this->select('pemindahan_proposal.*, users_pengusul.name as pengusul_name,
                                 users_v1.name as v1_name, users_v2.name as v2_name, users_v3.name as v3_name')
            ->join('users as users_pengusul', 'users_pengusul.id = pemindahan_proposal.id_user_pengusul')
            ->join('users as users_v1', 'users_v1.id = pemindahan_proposal.id_user_v1', 'left')
            ->join('users as users_v2', 'users_v2.id = pemindahan_proposal.id_user_v2', 'left')
            ->join('users as users_v3', 'users_v3.id = pemindahan_proposal.id_user_v3', 'left');

        if ($roleJabatan === 'arsiparis') { // Verifikator 1
            $builder->where('pemindahan_proposal.id_user_v1', $verifierId)
                ->whereIn('pemindahan_proposal.status_proposal', ['submitted', 'rejected_1']);
        } elseif ($roleJabatan === 'pemangku') { // Verifikator 2
            $builder->where('pemindahan_proposal.id_user_v2', $verifierId)
                ->whereIn('pemindahan_proposal.status_proposal', ['verified_1', 'rejected_2']);
        } elseif ($roleJabatan === 'verifikator') { // Verifikator 3
            $builder->where('pemindahan_proposal.id_user_v3', $verifierId)
                ->whereIn('pemindahan_proposal.status_proposal', ['verified_2', 'rejected_3']);
        } else {
            return []; // Bukan verifikator yang valid
        }

        return $builder->orderBy('pemindahan_proposal.created_at', 'DESC')
            ->findAll();
    }
    public function searchItems(?string $keyword, ?int $userId = null, string $role = 'superadmin')
    {
        $builder = $this->where('status_arsip', 'aktif');

        if ($role === 'user' && $userId !== null) {
            $builder->where('id_user', $userId);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('no_dokumen', $keyword)
                ->orLike('judul_dokumen', $keyword)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
