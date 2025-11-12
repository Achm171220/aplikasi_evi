<?php

/**
 * =================================================================================
 * HELPER OTENTIKASI DAN OTORISASI (auth_helper)
 * =================================================================================
 *
 * Berisi fungsi-fungsi terpusat untuk menangani semua logika yang berkaitan dengan
 * hak akses pengguna (otorisasi) dalam aplikasi.
 *
 * Terdiri dari dua fungsi utama:
 * 1. has_permission(): Mengecek apakah pengguna memiliki izin untuk melakukan aksi tertentu.
 * 2. apply_auth_filter(): Menerapkan filter pada query database agar pengguna
 * hanya dapat melihat data yang sesuai dengan lingkup unit kerjanya.
 *
 */


/**
 * Fungsi utama untuk semua pengecekan hak akses berbasis peran (role).
 * Fungsi ini adalah jantung dari sistem RBAC (Role-Based Access Control).
 *
 * @param string $permission Nama izin (permission) yang akan dicek. Contoh: 'delete_user', 'cud_arsip'.
 * @param array|null $contextData Data tambahan untuk pengecekan yang bersifat kontekstual.
 * Contoh: saat menghapus user, kita perlu tahu role dari user yang akan dihapus.
 * Data ini akan berisi ['role_access' => 'user', 'id_es3' => 5].
 * @return bool True jika pengguna diizinkan, false jika ditolak.
 */
function has_permission(string $permission, $contextData = null): bool
{
    // Mengambil data sesi pengguna yang sedang login untuk dasar pengecekan.
    $session = session();
    $role = $session->get('role_access'); // e.g., 'superadmin', 'admin', 'user'
    $jabatan = $session->get('role_jabatan'); // e.g., 'sekretaris', 'arsiparis'
    $authData = $session->get('auth_data'); // Berisi ID unit kerja seperti 'id_es1', 'id_es2', 'id_es3'

    // =================================================================
    // 1. Logika untuk Role 'superadmin'
    // =================================================================
    // Superadmin memiliki akses hampir ke semua fitur.
    if ($role === 'superadmin') {
        // Pengecualian khusus: Superadmin tidak bisa menghapus sesama Superadmin.
        // Ini adalah aturan keamanan untuk mencegah penguncian sistem (system lockout).
        if ($permission === 'delete_user' && isset($contextData['role_access']) && $contextData['role_access'] === 'superadmin') {
            return false;
        }
        // Untuk semua izin lainnya, Superadmin diizinkan.
        return true;
    }
    if ($role === 'manager') {
        // Pengecualian khusus: Superadmin tidak bisa menghapus sesama Superadmin.
        // Ini adalah aturan keamanan untuk mencegah penguncian sistem (system lockout).
        if ($permission === 'delete_user' && isset($contextData['role_access']) && $contextData['role_access'] === 'manager') {
            return false;
        }
        // Untuk semua izin lainnya, Superadmin diizinkan.
        return true;
    }
    // =================================================================
    // 2. Logika untuk Role 'admin'
    // =================================================================
    // Admin memiliki wewenang lebih luas dari 'user', tapi terbatas pada lingkup unit kerjanya (misal: Es.2).
    if ($role === 'admin') {
        switch ($permission) {
                // Izin yang diberikan secara langsung untuk semua Admin.
            case 'access_master_data':
            case 'cud_master_data': // Create, Update, Delete
            case 'access_users_management':
            case 'cud_users_management':
            case 'access_unit_kerja_admin':
            case 'access_arsip_aktif':
            case 'access_arsip_inaktif':
                return true;

                // Izin 'delete_user' memerlukan pengecekan kontekstual.
            case 'delete_user':
                // Admin hanya bisa menghapus user dengan role 'user'
                // DAN user tersebut harus berada di bawah lingkup unit kerja Es.2 si Admin.
                $userToDeleteRole = $contextData['role_access'] ?? null;
                $userToDeleteEs3Id = $contextData['id_es3'] ?? null;
                $adminEs2Id = $authData['id_es2'] ?? null;

                // Aturan #1: Admin tidak bisa menghapus 'admin' atau 'superadmin'.
                if ($userToDeleteRole === 'admin' || $userToDeleteRole === 'superadmin') {
                    return false;
                }

                // Aturan #2: Admin bisa menghapus 'user' jika user tersebut berada di bawah Es.2 miliknya.
                if ($userToDeleteRole === 'user' && !empty($adminEs2Id) && !empty($userToDeleteEs3Id)) {
                    // Lakukan pengecekan ke database untuk memvalidasi hirarki unit kerja.
                    // Apakah Es.3 dari user yang akan dihapus merupakan bagian dari Es.2 si Admin?
                    $unitKerjaEs3Model = new \App\Models\UnitKerjaEs3Model();
                    $es3ToDel = $unitKerjaEs3Model->find($userToDeleteEs3Id);
                    if ($es3ToDel && $es3ToDel['id_es2'] === $adminEs2Id) {
                        return true; // Izin diberikan jika valid.
                    }
                }
                return false; // Default: tolak jika kondisi tidak terpenuhi.

                // Izin yang secara eksplisit ditolak untuk Admin.
            case 'cud_arsip': // Admin TIDAK BISA CUD (Create, Update, Delete) Arsip Aktif.
                return false;

                // Izin yang bergantung pada jabatan (sub-role).
            case 'cud_arsip_inaktif': // Hanya Admin dengan jabatan tertentu yang bisa CUD Arsip Inaktif.
                return in_array($jabatan, ['arsiparis', 'pengampu', 'verifikator']);

            case 'access_pemindahan':
            case 'pemindahan_tugas': // Admin (jabatan tertentu) bertugas memverifikasi pemindahan.
                return in_array($jabatan, ['arsiparis', 'pengampu', 'verifikator']);

                // Izin level Superadmin yang tidak dimiliki Admin.
            case 'access_hak_fitur':
            case 'cud_unit_kerja_superadmin':
                return false;

                // Prinsip "Deny by Default": Jika izin tidak terdaftar di atas, maka ditolak.
            default:
                return false;
        }
    }

    // =================================================================
    // 3. Logika untuk Role 'user'
    // =================================================================
    // User memiliki wewenang paling terbatas, umumnya terkait pekerjaan sehari-hari.
    if ($role === 'user') {
        switch ($permission) {
                // Izin akses dasar untuk melihat data arsip.
            case 'access_arsip_aktif':
            case 'access_arsip_inaktif':
                return true;

                // Izin CUD bergantung pada jabatan spesifik.
            case 'cud_arsip':
                // User boleh CUD Arsip Aktif HANYA JIKA jabatannya 'Sekretaris' atau 'Pengelola Arsip'.
                return in_array($jabatan, ['sekretaris', 'pengelola_arsip', 'arsiparis']);

            case 'cud_arsip_inaktif':
                // User dengan jabatan 'Arsiparis' atau 'Pengampu' bisa CUD Arsip Inaktif.
                return in_array($jabatan, ['arsiparis', 'pengampu']);

                // Izin terkait proses bisnis pemindahan arsip.
            case 'access_pemindahan':
            case 'pemindahan_usulan': // User (jabatan tertentu) yang membuat usulan pemindahan.
                return in_array($jabatan, ['sekretaris', 'pengelola_arsip']);

                // Daftar lengkap izin yang TIDAK dimiliki oleh role 'user'.
                // Ini untuk memastikan tidak ada celah keamanan.
            case 'access_master_data':
            case 'access_users_management':
            case 'access_hak_fitur':
            case 'access_unit_kerja_admin':
            case 'pemindahan_tugas': // Tugas verifikasi bukan oleh user.
            case 'cud_master_data':
            case 'cud_users_management':
            case 'cud_unit_kerja_superadmin':
                return false;

                // Prinsip "Deny by Default".
            default:
                return false;
        }
    }

    // Jika pengguna tidak login atau memiliki peran yang tidak terdefinisi, tolak semua akses.
    return false;
}


/**
 * Menerapkan filter hak akses ke Query Builder untuk data arsip dan unit kerja.
 * Fungsi ini memastikan pengguna hanya melihat data dari unit kerjanya sendiri atau di bawahnya,
 * sesuai dengan hirarki Eselon (Es.1 -> Es.2 -> Es.3).
 *
 * @param \CodeIgniter\Database\BaseBuilder $builder Instance Query Builder yang akan dimodifikasi.
 * @param string $targetTable Nama tabel utama atau alias tabel dalam query yang akan difilter.
 * Contoh: 'item_aktif', 'unit_kerja_es3'.
 * @return \CodeIgniter\Database\BaseBuilder Builder yang sudah dimodifikasi dengan klausa WHERE jika perlu.
 */
function apply_auth_filter($builder, string $targetTable)
{
    // Superadmin tidak dikenai filter, bisa melihat semua data. Langsung kembalikan builder.
    if (session()->get('role_access') === 'superadmin') {
        return $builder;
    }

    // Ambil data unit kerja pengguna dari sesi.
    $authData = session()->get('auth_data');

    // Jika pengguna (non-superadmin) tidak memiliki data otorisasi unit kerja,
    // cegah dia melihat data apa pun dengan menambahkan kondisi yang selalu salah.
    if (empty($authData)) {
        return $builder->where('1=0'); // Trik standar untuk menghasilkan set data kosong.
    }

    $db = \Config\Database::connect();
    $tableAlias = $targetTable; // Menggunakan nama tabel target sebagai alias untuk predikat WHERE.

    // Terapkan logika filter berdasarkan tabel mana yang sedang di-query.
    if ($tableAlias === 'item_aktif' || $tableAlias === 'berkas_aktif' || $tableAlias === 'item_inaktif' || $tableAlias === 'berkas_inaktif') {
        // Logika filter berjenjang dari yang paling spesifik (Es.3) ke yang paling umum (Es.1).
        if (!empty($authData['id_es3'])) {
            // Pengguna level Es.3 hanya bisa melihat data di Es.3 miliknya.
            $builder->where($tableAlias . '.id_es3', $authData['id_es3']);
        } elseif (!empty($authData['id_es2'])) {
            // Pengguna level Es.2 hanya bisa melihat data di Es.2 miliknya.
            $builder->where($tableAlias . '.id_es2', $authData['id_es2']);
        } elseif (!empty($authData['id_es1'])) {
            // Pengguna level Es.1 bisa melihat semua data dari semua Es.2 yang ada di bawah Es.1 miliknya.
            // Diperlukan subquery untuk mendapatkan semua ID Es.2 yang relevan.
            $subQueryEs2 = $db->table('unit_kerja_es2')->select('id')->where('id_es1', $authData['id_es1']);
            $builder->whereIn($tableAlias . '.id_es2', $subQueryEs2);
        } else {
            // Jika tidak punya ID unit kerja sama sekali, blokir akses data.
            return $builder->where('1=0');
        }
    }
    // Filter khusus saat mengambil data dari tabel 'unit_kerja_es3'.
    elseif ($tableAlias === 'unit_kerja_es3') {
        if (!empty($authData['id_es2'])) {
            // Admin Es.2 bisa melihat semua Es.3 di bawahnya.
            $builder->where($tableAlias . '.id_es2', $authData['id_es2']);
        } elseif (!empty($authData['id_es1'])) {
            // Pengguna Es.1 bisa melihat semua Es.3 dari semua Es.2 di bawahnya.
            $subQuery = $db->table('unit_kerja_es2')->select('id')->where('id_es1', $authData['id_es1']);
            $builder->whereIn($tableAlias . '.id_es2', $subQuery);
        } else {
            // Jika bukan superadmin dan tidak punya lingkup Es.1 atau Es.2, tidak bisa lihat data Es.3.
            return $builder->where('1=0');
        }
    }
    // Filter khusus saat mengambil data dari tabel 'unit_kerja_es2'.
    elseif ($tableAlias === 'unit_kerja_es2') {
        if (!empty($authData['id_es1'])) {
            // Pengguna Es.1 hanya bisa melihat Es.2 di bawahnya.
            $builder->where($tableAlias . '.id_es1', $authData['id_es1']);
        } else {
            // Jika bukan superadmin dan tidak punya lingkup Es.1, tidak bisa lihat data Es.2.
            return $builder->where('1=0');
        }
    }

    // Untuk tabel lain yang tidak masuk dalam kondisi di atas (misal: users, klasifikasi, jenis_naskah),
    // diasumsikan tidak memerlukan filter data berdasarkan unit kerja.
    return $builder;
}
