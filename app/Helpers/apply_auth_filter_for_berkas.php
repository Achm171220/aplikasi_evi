<?php
function apply_auth_filter_for_berkas($builder)
{
    if (session()->get('role_access') === 'superadmin') {
        return $builder;
    }

    // Buat subquery untuk mendapatkan ID berkas yang berisi item yang diizinkan
    $db = \Config\Database::connect();
    $subQueryItems = $db->table('item_aktif')->select('id_berkas')->where('id_berkas IS NOT NULL');
    $subQueryItems = apply_auth_filter($subQueryItems, 'item_aktif'); // Gunakan helper yang sudah ada!

    return $builder->whereIn('id', $subQueryItems);
}
