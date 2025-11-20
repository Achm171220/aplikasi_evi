<?php

namespace App\Controllers;

use App\Models\ItemAktifModel;
use App\Models\BerkasAktifModel;

class Search extends BaseController
{
    public function index()
    {
        $keyword = $this->request->getGet('q'); // 'q' adalah parameter standar untuk query
        $results = [];

        if (!empty($keyword)) {
            $itemAktifModel = new ItemAktifModel();
            $berkasAktifModel = new BerkasAktifModel();

            // --- Lakukan Pencarian & Terapkan Hak Akses ---

            // Cari di Berkas Aktif
            $berkasBuilder = $berkasAktifModel->builder();
            $berkasBuilder->like('nama_berkas', $keyword)->orLike('no_berkas', $keyword);
            // Anda perlu menyesuaikan apply_auth_filter untuk bekerja pada berkas
            // apply_auth_filter_for_berkas($berkasBuilder);
            $results['berkas_aktif'] = $berkasBuilder->limit(10)->get()->getResultArray();

            // Cari di Item Aktif
            $itemBuilder = $itemAktifModel->builder();
            $itemBuilder->like('judul_dokumen', $keyword)->orLike('no_dokumen', $keyword);
            apply_auth_filter($itemBuilder, 'item_aktif'); // Gunakan helper yang sudah ada
            $results['item_aktif'] = $itemBuilder->limit(20)->get()->getResultArray();

            // TODO: Cari di Item Inaktif
            // $results['item_inaktif'] = ...;
        }

        $data = [
            'title'   => 'Hasil Pencarian',
            'keyword' => $keyword,
            'results' => $results,
        ];

        return view('search/index', $data);
    }
}
