<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;
use Config\Services;

$routes = Services::routes();

$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);


$routes->get('login', 'Auth::login');
$routes->get('/', 'Auth::login', ['as' => 'login']);
$routes->get('login', 'Auth::login');
$routes->post('auth/login', 'Auth::attemptLogin');
$routes->get('auth/logout', 'Auth::logout');
// Contoh Route untuk Arsip
$routes->get('arsip', 'ArsipController::index', ['filter' => 'auth']);
$routes->post('login/process', 'Auth::loginProcess');
$routes->get('logout', 'Auth::logout');

$routes->group('trial', function ($routes) {
    $routes->get('', 'Trial::create');
    $routes->post('list-data', 'Trial::listDataApi'); // Endpoint AJAX untuk DataTables
    // --- TAMBAHKAN RUTE INI ---
    // Endpoint AJAX untuk Select2/API
    $routes->post('store', 'Trial::store', ['as' => 'Trial.store']);
    $routes->get('search-sima', 'Trial::searchSimaApi', ['as' => 'Trial.searchSimaApi']);
    $routes->get('search-sadewa', 'Trial::searchSadewaApi', ['as' => 'Trial.searchSadewaApi']);
});

$routes->get('files/qrcodes/(:segment)', 'FileController::serveQrCode/$1');

$routes->group('api', [
    'namespace' => 'App\\Controllers\\Api',
    'filter' => 'auth',
], function ($routes) {
    $routes->get('unit-kerja/eselon2/(:num)', 'UnitKerja::getEselon2ByEselon1/$1');
    $routes->get('unit-kerja/eselon3/(:num)', 'UnitKerja::getEselon3ByEselon2/$1');
});

$routes->group('api/v1', ['namespace' => 'App\\Controllers\\Api'], function ($routes) {
    $routes->group('auth', function ($routes) {
        $routes->post('login', 'Auth::login');
    });
});

$routes->group('pemindahan', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Pemindahan::dashboard');
    $routes->get('usul', 'Pemindahan::usulan');
    $routes->post('propose', 'Pemindahan::propose');
    $routes->get('monitoring', 'Pemindahan::monitoring');
    $routes->get('verifikasi', 'Pemindahan::verifikasi');
    $routes->get('verifikasi/(:num)', 'Pemindahan::verifikasi/$1');
    $routes->post('verifikasi/process/(:num)', 'Pemindahan::processVerification/$1');
    $routes->get('buat_ba', 'Pemindahan::buatBa');
    $routes->post('process_buat_ba', 'Pemindahan::processBuatBa');
    $routes->get('eksekusi', 'Pemindahan::eksekusi');
    $routes->post('process_eksekusi', 'Pemindahan::processEksekusi');

    // Fitur Melihat Data Arsip Aktif
    $routes->get('data_aktif', 'Pemindahan::dataAktif');
    $routes->get('data_aktif/detail/(:num)', 'Pemindahan::detailAktif/$1');
    $routes->post('ajax_data_aktif', 'Pemindahan::ajaxDataAktif'); // <<< RUTE AJAX BARU

    // Fitur Melihat Data Arsip Inaktif
    $routes->get('data_inaktif', 'Pemindahan::dataInaktif');
    $routes->post('ajax_data_inaktif', 'Pemindahan::ajaxDataInaktif'); // <<< RUTE AJAX BARU

    // Fitur Mengembalikan Item (Restore) dari Inaktif ke Aktif
    $routes->post('restore', 'Pemindahan::restoreItem');
});
$routes->group('/', ['filter' => 'auth'], function ($routes) {
    $routes->get('', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/unconfigured', 'Auth::unconfiguredDashboard');
    $routes->get('profil', 'Profil::index');
    $routes->post('profil/update-password', 'Profil::updatePassword');
    $routes->get('arsip-tematik', 'ArsipTematik::index');

    $routes->group('item-aktif', function ($routes) {
        $routes->get('/', 'ItemAktif::index');
        $routes->get('detail/(:num)', 'ItemAktif::detail/$1');
        $routes->post('list', 'ItemAktif::listData');
        $routes->get('new', 'ItemAktif::new');
        $routes->post('/', 'ItemAktif::create');
        $routes->get('edit/(:num)', 'ItemAktif::edit/$1');
        $routes->match(['put', 'patch'], 'update/(:num)', 'ItemAktif::update/$1');
        $routes->delete('delete/(:num)', 'ItemAktif::delete/$1');
        $routes->post('lepas-berkas', 'ItemAktif::lepasBerkas');
        $routes->get('import', 'ItemAktif::import');
        $routes->post('proses-import', 'ItemAktif::prosesImport');
        $routes->get('download-template', 'ItemAktif::downloadTemplate');

        $routes->get('filter/es2', 'ItemAktif::getEs2ForFilter');
        $routes->get('filter/es3/(:num)', 'ItemAktif::getEs3ForFilter/$1');

        // api sima dan sadewa
        $routes->get('search-sima', 'ItemAktif::searchSimaApi', ['as' => 'ItemAktif.searchSimaApi']);
        $routes->get('search-sadewa', 'ItemAktif::searchSadewaApi', ['as' => 'ItemAktif.searchSadewaApi']);

        // ENDPOINT DATATABLES API (BARU)
        $routes->post('load-sima-data', 'ItemAktif::loadSimaData');
        $routes->post('load-sadewa-data', 'ItemAktif::loadSadewaData');
    });

    $routes->group('berkas-aktif', function ($routes) {
        $routes->get('/', 'BerkasAktif::index');
        $routes->post('list', 'BerkasAktif::listData');
        $routes->get('detail/(:num)', 'BerkasAktif::detail/$1');
        $routes->post('ajaxListItemsInBerkas/(:num)', 'BerkasAktif::ajaxListItemsInBerkas/$1');
        $routes->get('new', 'BerkasAktif::new');
        $routes->post('/', 'BerkasAktif::create');
        $routes->get('edit/(:num)', 'BerkasAktif::edit/$1');
        $routes->match(['put', 'patch'], 'update/(:num)', 'BerkasAktif::update/$1');
        $routes->delete('delete/(:num)', 'BerkasAktif::delete/$1');
        $routes->get('pemberkasan/(:num)', 'BerkasAktif::pemberkasanPage/$1');
        $routes->post('add-items/(:num)', 'BerkasAktif::addItems/$1');
        $routes->post('ajaxListUnfiledItems', 'BerkasAktif::ajaxListUnfiledItems');
        $routes->post('proses-import', 'BerkasAktif::prosesImport');
        $routes->get('download-template', 'BerkasAktif::downloadTemplate');
        $routes->post('tutup/(:num)', 'BerkasAktif::tutupBerkas/$1');
        $routes->post('buka/(:num)', 'BerkasAktif::bukaBerkas/$1');
    });

    $routes->group('', ['filter' => 'admin'], function ($routes) {
        $routes->group('item-inaktif', function ($routes) {
            $routes->get('/', 'ItemInaktif::index');
            $routes->get('detail/(:num)', 'ItemInaktif::detail/$1');
            $routes->post('list', 'ItemInaktif::listData');
            $routes->get('new', 'ItemInaktif::new');
            $routes->post('/', 'ItemInaktif::create');
            $routes->get('edit/(:num)', 'ItemInaktif::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'ItemInaktif::update/$1');
            $routes->delete('delete/(:num)', 'ItemInaktif::delete/$1');
            $routes->post('lepas-berkas', 'ItemInaktif::lepasBerkas');
            $routes->get('import', 'ItemInaktif::import');
            $routes->post('proses-import', 'ItemInaktif::prosesImport');
            $routes->get('download-template', 'ItemInaktif::downloadTemplate');
        });

        $routes->group('berkas-inaktif', function ($routes) {
            $routes->get('/', 'BerkasInaktif::index');
            $routes->post('list', 'BerkasInaktif::listData');
            $routes->get('detail/(:num)', 'BerkasInaktif::detail/$1');
            $routes->post('ajaxListItemsInBerkas/(:num)', 'BerkasInaktif::ajaxListItemsInBerkas/$1');
            $routes->get('new', 'BerkasInaktif::new');
            $routes->post('/', 'BerkasInaktif::create');
            $routes->get('edit/(:num)', 'BerkasInaktif::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'BerkasInaktif::update/$1');
            $routes->delete('delete/(:num)', 'BerkasInaktif::delete/$1');
            $routes->get('pemberkasan/(:num)', 'BerkasInaktif::pemberkasanPage/$1');
            $routes->post('add-items/(:num)', 'BerkasInaktif::addItems/$1');
            $routes->post('ajaxListUnfiledItems', 'BerkasInaktif::ajaxListUnfiledItems');
        });

        $routes->group('laporan-inaktif', function ($routes) {
            $routes->get('/', 'LaporanInaktif::index');
            $routes->get('excel', 'LaporanInaktif::exportExcel');
            $routes->get('pdf', 'LaporanInaktif::exportPdf');
        });
    });

    $routes->group('laporan-aktif', function ($routes) {
        $routes->get('/', 'Laporan::index');
        $routes->get('excel', 'Laporan::exportExcel');
        $routes->get('pdf', 'Laporan::exportPdf');
    });

    $routes->group('data', function ($routes) {
        $routes->get('es2-by-es1/(:num)', 'UnitKerjaEs3::getEs2ByEs1/$1');
        $routes->get('es3-by-es2/(:num)', 'UnitKerjaEs3::getEs3ByEs2/$1');
        $routes->post('check-kode-es3', 'UnitKerjaEs3::checkKode');
        // Rute untuk Select2 AJAX ke API Stara
    });

    $routes->group('peminjaman', function ($routes) {
        $routes->get('/', 'Peminjaman::index');
        $routes->post('list-items', 'Peminjaman::listItems');
        $routes->post('list-berkas', 'Peminjaman::listBerkas');
        $routes->post('pinjam', 'Peminjaman::prosesPinjam');
        $routes->get('monitoring', 'Peminjaman::monitoringIndex');
        $routes->post('monitoring/list', 'Peminjaman::monitoringListData');
        $routes->get('monitoring/detail/(:num)', 'Peminjaman::monitoringDetail/$1');
        $routes->post('monitoring/kembalikan', 'Peminjaman::prosesPengembalian');
        $routes->delete('monitoring/delete/(:num)', 'Peminjaman::deletePeminjaman/$1');
    });
    $routes->group('tema', function ($routes) {
        $routes->get('/', 'Tema::index');
        $routes->post('list', 'Tema::listData');
        $routes->get('new', 'Tema::new'); // AJAX untuk form tambah
        $routes->post('/', 'Tema::create'); // Proses submit tambah
        $routes->get('edit/(:num)', 'Tema::edit/$1'); // AJAX untuk form edit
        $routes->put('(:num)', 'Tema::update/$1'); // Proses submit update
        $routes->delete('(:num)', 'Tema::delete/$1'); // Proses hapus
    });
    $routes->group('', ['filter' => 'admin'], function ($routes) {
        $routes->group('klasifikasi', function ($routes) {
            $routes->get('/', 'Klasifikasi::index');
            $routes->post('list', 'Klasifikasi::listData');
            $routes->get('new', 'Klasifikasi::new');
            $routes->post('/', 'Klasifikasi::create');
            $routes->get('edit/(:num)', 'Klasifikasi::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'Klasifikasi::update/$1');
            $routes->delete('delete/(:num)', 'Klasifikasi::delete/$1');
        });

        $routes->group('jenis-naskah', function ($routes) {
            $routes->get('/', 'JenisNaskah::index');
            $routes->post('list', 'JenisNaskah::listData');
            $routes->get('new', 'JenisNaskah::new');
            $routes->post('/', 'JenisNaskah::create');
            $routes->get('edit/(:num)', 'JenisNaskah::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'JenisNaskah::update/$1');
            $routes->delete('delete/(:num)', 'JenisNaskah::delete/$1');
        });

        $routes->group('unit-kerja-es3', function ($routes) {
            $routes->get('/', 'UnitKerjaEs3::index');
            $routes->post('list', 'UnitKerjaEs3::listData');
            $routes->get('new', 'UnitKerjaEs3::new');
            $routes->post('/', 'UnitKerjaEs3::create');
            $routes->get('edit/(:num)', 'UnitKerjaEs3::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'UnitKerjaEs3::update/$1');
            $routes->delete('delete/(:num)', 'UnitKerjaEs3::delete/$1');
        });

        $routes->group('users', function ($routes) {
            $routes->get('/', 'Users::index');
            $routes->post('list', 'Users::listData');
            $routes->get('new', 'Users::new');
            $routes->post('/', 'Users::create');
            $routes->get('edit/(:num)', 'Users::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'Users::update/$1');
            $routes->delete('(:num)', 'Users::delete/$1');
            $routes->post('import', 'Users::prosesImport');
            $routes->get('template', 'Users::downloadTemplate');
            $routes->get('export-excel', 'Users::exportExcel');
            $routes->get('pegawai/aktif', 'Users::pegawaiAktif');

            $routes->get('getPegawaiFromApiForSelect2', 'Users::getPegawaiFromApiForSelect2');
        });

        $routes->group('riwayat-import', function ($routes) {
            $routes->get('/', 'Import::index');
            $routes->post('list', 'Import::listData');
            $routes->get('preview/(:num)', 'Import::preview/$1');
        });
        $routes->get('unit-kerja/treeview', 'UnitKerja::index');
        $routes->get('unit-kerja/treeview/data', 'UnitKerja::getTreeviewData');
    });
    $routes->group('', ['filter' => 'manager'], function ($routes) {
        $routes->group('hak-fitur', function ($routes) {
            $routes->get('/', 'HakFitur::index');
            $routes->post('list', 'HakFitur::listData');
            $routes->get('new', 'HakFitur::new');
            $routes->post('/', 'HakFitur::create');
            $routes->get('edit/(:num)', 'HakFitur::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'HakFitur::update/$1');
            $routes->delete('delete/(:num)', 'HakFitur::delete/$1');
        });

        $routes->group('unit-kerja-es1', function ($routes) {
            $routes->get('/', 'UnitKerjaEs1::index');
            $routes->post('list', 'UnitKerjaEs1::listData');
            $routes->get('new', 'UnitKerjaEs1::new');
            $routes->post('/', 'UnitKerjaEs1::create');
            $routes->get('edit/(:num)', 'UnitKerjaEs1::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'UnitKerjaEs1::update/$1');
            $routes->delete('delete/(:num)', 'UnitKerjaEs1::delete/$1');
        });

        $routes->group('unit-kerja-es2', function ($routes) {
            $routes->get('/', 'UnitKerjaEs2::index');
            $routes->post('list', 'UnitKerjaEs2::listData');
            $routes->get('new', 'UnitKerjaEs2::new');
            $routes->post('/', 'UnitKerjaEs2::create');
            $routes->get('edit/(:num)', 'UnitKerjaEs2::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'UnitKerjaEs2::update/$1');
            $routes->delete('delete/(:num)', 'UnitKerjaEs2::delete/$1');
        });
        $routes->group('activity-logs', function ($routes) {
            $routes->get('/', 'ActivityLog::index');
            $routes->post('list', 'ActivityLog::listData');
            // $routes->get('detail/(:num)', 'ActivityLogController::viewDetail/$1'); // Jika ada halaman detail
        });
    });
    $routes->group('', ['filter' => 'superadmin'], function ($routes) {
        $routes->group('hak-fitur', function ($routes) {
            $routes->get('/', 'HakFitur::index');
            $routes->post('list', 'HakFitur::listData');
            $routes->get('new', 'HakFitur::new');
            $routes->post('/', 'HakFitur::create');
            $routes->get('edit/(:num)', 'HakFitur::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'HakFitur::update/$1');
            $routes->delete('delete/(:num)', 'HakFitur::delete/$1');
        });

        $routes->group('unit-kerja-es1', function ($routes) {
            $routes->get('/', 'UnitKerjaEs1::index');
            $routes->post('list', 'UnitKerjaEs1::listData');
            $routes->get('new', 'UnitKerjaEs1::new');
            $routes->post('/', 'UnitKerjaEs1::create');
            $routes->get('edit/(:num)', 'UnitKerjaEs1::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'UnitKerjaEs1::update/$1');
            $routes->delete('delete/(:num)', 'UnitKerjaEs1::delete/$1');
        });

        $routes->group('unit-kerja-es2', function ($routes) {
            $routes->get('/', 'UnitKerjaEs2::index');
            $routes->post('list', 'UnitKerjaEs2::listData');
            $routes->get('new', 'UnitKerjaEs2::new');
            $routes->post('/', 'UnitKerjaEs2::create');
            $routes->get('edit/(:num)', 'UnitKerjaEs2::edit/$1');
            $routes->match(['put', 'patch'], 'update/(:num)', 'UnitKerjaEs2::update/$1');
            $routes->delete('delete/(:num)', 'UnitKerjaEs2::delete/$1');
        });
        $routes->group('activity-logs', function ($routes) {
            $routes->get('/', 'ActivityLog::index');
            $routes->post('list', 'ActivityLog::listData');
            // $routes->get('detail/(:num)', 'ActivityLogController::viewDetail/$1'); // Jika ada halaman detail
        });
    });
    // Tambahkan rute ini di dalam grup filter yang sesuai
    $routes->get('arsip-tematik', 'ArsipTematik::index');
    $routes->post('arsip-tematik/list', 'ArsipTematik::listData');
    $routes->post('arsip-tematik/add-theme', 'ArsipTematik::addThemeToItem');
    $routes->post('arsip-tematik/remove-theme', 'ArsipTematik::removeThemeFromItem');
    $routes->get('arsip-tematik/export', 'ArsipTematik::exportExcel'); // <-- RUTE BARU
    // Tambahkan rute ini di dalam grup filter yang sesuai
    $routes->get('search', 'Search::index');
});
