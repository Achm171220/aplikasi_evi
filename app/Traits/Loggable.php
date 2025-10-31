<?php

namespace App\Traits;

use App\Models\ActivityLogModel;

trait Loggable
{
    /**
     * Secara otomatis mencatat aktivitas setelah data dibuat.
     */
    protected function logAfterInsert(array $data)
    {
        if ($data['result'] === false) {
            return $data;
        }

        $logModel = new ActivityLogModel();
        $logModel->save([
            'id_user'       => session()->get('user_id'),
            'username'      => session()->get('user_name') ?? 'System',
            'event'         => 'membuat data baru di ' . $this->table,
            'target_table'  => $this->table,
            'target_id'     => $data['id'],
            'data_after'    => json_encode($data['data']),
            'ip_address'    => request()->getIPAddress(),
            'user_agent'    => request()->getUserAgent(),
        ]);

        return $data;
    }

    /**
     * Secara otomatis mencatat aktivitas setelah data diperbarui.
     */
    protected function logAfterUpdate(array $data)
    {
        if ($data['result'] === false) {
            return $data;
        }

        $logModel = new ActivityLogModel();

        foreach ($data['id'] as $id) {
            $logModel->save([
                'id_user'       => session()->get('user_id'),
                'username'      => session()->get('user_name') ?? 'System',
                'event'         => 'memperbarui data di ' . $this->table,
                'target_table'  => $this->table,
                'target_id'     => $id,
                'data_before'   => json_encode($this->find($id)), // Ambil data lama sebelum diubah
                'data_after'    => json_encode($data['data']),
                'ip_address'    => request()->getIPAddress(),
                'user_agent'    => request()->getUserAgent(),
            ]);
        }

        return $data;
    }

    /**
     * Secara otomatis mencatat aktivitas setelah data dihapus.
     */
    protected function logAfterDelete(array $data)
    {
        if ($data['result'] === false) {
            return $data;
        }

        $logModel = new ActivityLogModel();

        foreach ($data['id'] as $id) {
            $logModel->save([
                'id_user'       => session()->get('user_id'),
                'username'      => session()->get('user_name') ?? 'System',
                'event'         => 'menghapus data di ' . $this->table,
                'target_table'  => $this->table,
                'target_id'     => $id,
                'data_before'   => json_encode($data['data']), // Data yang dihapus
                'ip_address'    => request()->getIPAddress(),
                'user_agent'    => request()->getUserAgent(),
            ]);
        }

        return $data;
    }
}
