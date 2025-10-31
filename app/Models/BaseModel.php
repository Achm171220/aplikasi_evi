<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class BaseModel extends Model
{
    /**
     * Method untuk mengambil data server-side DataTables.
     * 
     * @param \CodeIgniter\HTTP\IncomingRequest $request
     * @param array $column_search Kolom yang bisa dicari.
     * @param array $column_order Kolom yang bisa diurutkan.
     * @param array $order Default order.
     * @return array
     */
    public function getDataTables($request, $column_search = [], $column_order = [], $order = [])
    {
        // Gunakan builder yang sudah dikonfigurasi (misal: dengan join)
        $builder = $this->builder();

        // --- PERUBAHAN DI SINI: TERAPKAN FILTER HAK AKSES SECARA OTOMATIS ---
        $builder = $this->applyAuthFilterToBuilder($builder);
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'];

        // --- Search ---
        if ($searchValue) {
            $builder->groupStart();
            foreach ($column_search as $i => $item) {
                if ($i === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        // --- Order ---
        $orderData = $request->getPost('order');
        if ($orderData && isset($column_order[$orderData[0]['column']])) {
            $builder->orderBy($column_order[$orderData[0]['column']], $orderData[0]['dir']);
        } elseif (!empty($order)) {
            $builder->orderBy(key($order), $order[key($order)]);
        }

        // --- Get Data ---
        try {
            // Hitung total data yang difilter
            $builder_count = clone $builder;
            $recordsFiltered = $builder_count->countAllResults();

            // Ambil data untuk halaman saat ini
            if ($length != -1) {
                $builder->limit($length, $start);
            }
            $data = $builder->get()->getResultArray();

            // Hitung total semua data
            $recordsTotal = $this->countAllResults();

            return [
                'draw'            => $request->getPost('draw'),
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $data,
            ];
        } catch (DatabaseException $e) {
            // Jika ada error database (misal: kolom tidak ada)
            return [
                'error' => 'Database Error: ' . $e->getMessage()
            ];
        }
    }
    protected function applyAuthFilterToBuilder($builder)
    {
        $session = session();

        // Superadmin tidak perlu filter
        if ($session->get('role_access') === 'superadmin') {
            return $builder;
        }

        $authData = $session->get('auth_data');
        if (empty($authData)) {
            return $builder->where('1=0'); // Selalu false jika tidak punya hak
        }

        $tableAlias = $this->table;
        $db = \Config\Database::connect();

        // --- LOGIKA FILTER BERDASARKAN NAMA TABEL ---

        // Filter untuk tabel item_aktif
        if ($tableAlias === 'item_aktif' && $this->db->fieldExists('id_es3', 'item_aktif')) {
            if (!empty($authData['id_es3'])) {
                $builder->where($tableAlias . '.id_es3', $authData['id_es3']);
            } elseif (!empty($authData['id_es2'])) {
                $subQuery = $db->table('unit_kerja_es3')->select('id')->where('id_es2', $authData['id_es2']);
                $builder->whereIn($tableAlias . '.id_es3', $subQuery);
            } elseif (!empty($authData['id_es1'])) {
                $subQueryEs2 = $db->table('unit_kerja_es2')->select('id')->where('id_es1', $authData['id_es1']);
                $subQueryEs3 = $db->table('unit_kerja_es3')->select('id')->whereIn('id_es2', $subQueryEs2);
                $builder->whereIn($tableAlias . '.id_es3', $subQueryEs3);
            }
        }

        // Filter untuk tabel unit_kerja_es3
        elseif ($tableAlias === 'unit_kerja_es3' && $this->db->fieldExists('id_es2', 'unit_kerja_es3')) {
            if (!empty($authData['id_es2'])) {
                $builder->where($tableAlias . '.id_es2', $authData['id_es2']);
            } elseif (!empty($authData['id_es1'])) {
                $subQuery = $db->table('unit_kerja_es2')->select('id')->where('id_es1', $authData['id_es1']);
                $builder->whereIn($tableAlias . '.id_es2', $subQuery);
            }
        }

        // Filter untuk tabel unit_kerja_es2
        elseif ($tableAlias === 'unit_kerja_es2' && $this->db->fieldExists('id_es1', 'unit_kerja_es2')) {
            if (!empty($authData['id_es1'])) {
                $builder->where($tableAlias . '.id_es1', $authData['id_es1']);
            }
        }

        // Jika tidak ada kondisi yang cocok, jangan filter apa-apa (misal: untuk tabel users, klasifikasi, dll)
        return $builder;
    }
}
