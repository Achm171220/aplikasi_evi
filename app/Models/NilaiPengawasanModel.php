<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiPengawasanModel extends Model
{
    protected $table = 'nilai_pengawasan'; // Sesuaikan dengan nama tabel Anda
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_es2', 'tahun', 'skor', 'kategori', 'id_user']; // Sesuaikan field

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $builder;

    /**
     * Method untuk DataTables Server Side
     */
    public function getDataTablesList($request)
    {
        $builder = $this->db->table($this->table . ' as np')
            ->select('np.id, np.tahun, np.skor, np.kategori, es2.nama_es2, u.name as user_name')
            ->join('unit_kerja_es2 as es2', 'es2.id = np.id_es2', 'left')
            ->join('users as u', 'u.id = np.id_user', 'left');

        $this->builder = $builder;

        $column_search = ['es2.nama_es2', 'np.tahun', 'np.kategori', 'np.skor'];
        $column_order  = [null, 'es2.nama_es2', 'np.tahun', 'np.skor', 'np.kategori', 'u.name', null];
        $order = ['np.tahun' => 'DESC', 'es2.nama_es2' => 'ASC'];

        return $this->getDataTables($request, $column_search, $column_order, $order);
    }

    /**
     * Core DataTables Processing
     */
    protected function getDataTables($request, $column_search, $column_order, $order)
    {
        $i = 0;

        // Pencarian
        $search = $request->getPost('search')['value'] ?? '';
        if (!empty($search)) {
            $this->builder->groupStart();
            foreach ($column_search as $item) {
                if ($i === 0) {
                    $this->builder->like($item, $search);
                } else {
                    $this->builder->orLike($item, $search);
                }
                $i++;
            }
            $this->builder->groupEnd();
        }

        // Ordering
        $orderData = $request->getPost('order');
        if ($orderData) {
            $orderColumnIndex = $orderData[0]['column'] ?? 0;
            $orderDir = $orderData[0]['dir'] ?? 'asc';

            if (isset($column_order[$orderColumnIndex]) && $column_order[$orderColumnIndex] !== null) {
                $this->builder->orderBy($column_order[$orderColumnIndex], $orderDir);
            }
        } else {
            // Default order
            foreach ($order as $key => $value) {
                $this->builder->orderBy($key, $value);
            }
        }

        // Total records tanpa filter
        $recordsTotal = $this->builder->countAllResults(false);

        // Pagination
        $length = $request->getPost('length') ?? 10;
        $start = $request->getPost('start') ?? 0;

        if ($length != -1) {
            $this->builder->limit($length, $start);
        }

        // Ambil data
        $data = $this->builder->get()->getResultArray();

        return [
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal, // Sama dengan recordsTotal karena sudah difilter di query
            'data' => $data
        ];
    }
}
