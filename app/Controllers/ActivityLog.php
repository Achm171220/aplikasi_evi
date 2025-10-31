<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;

class ActivityLog extends BaseController
{
    protected $activityLogModel;
    protected $session;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
        $this->session = session();
    }

    public function index()
    {
        // Pengecekan hak akses: hanya Superadmin atau admin yang bisa melihat
        if (session()->get('role_access') !== 'superadmin' && session()->get('role_access') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki izin untuk melihat log aktivitas.');
        }

        $data = [
            'title'   => 'Log Aktivitas Sistem',
            'session' => $this->session,
        ];
        return view('activity_log/index', $data);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $result = $this->activityLogModel->getDataTablesList($this->request);

            $data = [];
            foreach ($result['data'] as $log) {
                // Tampilkan username, email, dan role access
                $userDisplay = '<strong>' . esc($log['username']) . '</strong><br>' .
                '<small class="text-muted">' . esc($log['email'] ?? 'N/A') . '</small>';
                $roleDisplay = '<span class="badge bg-secondary">' . esc(ucfirst($log['role_access'] ?? 'N/A')) . '</span>';

                $row = [
                    '', // No. urut (diisi oleh JS)
                    date('d M Y H:i:s', strtotime($log['created_at'])),
                    $userDisplay, // <-- Perubahan di sini
                    $roleDisplay, // <-- Kolom baru
                    esc($log['event']),
                    esc($log['target_table']) . ' (ID: ' . ($log['target_id'] ?? '-') . ')',
                    esc($log['ip_address']),
                    '<button type="button" class="btn btn-sm btn-info btn-view-detail" data-id="' . $log['id'] . '" data-bs-toggle="tooltip" title="Lihat Detail Log"><i class="bi bi-info-circle"></i></button>'
                ];
                $data[] = $row;
            }
            return $this->response->setJSON(['draw' => $result['draw'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'data' => $data]);
        }
    }

    // Mungkin tambahkan method untuk melihat detail JSON data_before/data_after
    public function viewDetail($id)
    { /* ... */
    }
}
