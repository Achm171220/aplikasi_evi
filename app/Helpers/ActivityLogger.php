<?php

use App\Models\ActivityLogModel;
use CodeIgniter\HTTP\CLIRequest; // Untuk membedakan request CLI

/**
 * Fungsi global untuk mencatat aktivitas pengguna.
 *
 * @param string $event       Deskripsi singkat event (contoh: 'Login', 'Update User', 'Create Item').
 * @param string $targetTable Nama tabel yang terpengaruh (contoh: 'users', 'item_aktif').
 * @param int|null $targetId  ID dari record yang terpengaruh.
 * @param array $dataBefore   Data sebelum perubahan (untuk Update/Delete).
 * @param array $dataAfter    Data setelah perubahan (untuk Create/Update).
 */
function log_activity(string $event, string $targetTable, ?int $targetId = null, array $dataBefore = [], array $dataAfter = [])
{
    $session = session();
    $request = service('request');

    // Hindari logging dari CLI (misal, saat menjalankan spark commands)
    if ($request instanceof CLIRequest) {
        return;
    }

    $id_user = $session->get('id');
    $username = $session->get('name');

    // Siapkan data log
    $logData = [
        'id_user'       => $id_user,
        'username'      => $username ?? 'Guest/System', // Default jika tidak login
        'event'         => $event,
        'target_table'  => $targetTable,
        'target_id'     => $targetId,
        'data_before'   => json_encode($dataBefore),
        'data_after'    => json_encode($dataAfter),
        'ip_address'    => $request->getIPAddress(),
        'user_agent'    => $request->getUserAgent()->getAgentString(),
    ];

    (new ActivityLogModel())->insert($logData);
}
