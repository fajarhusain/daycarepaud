<?php

/**
 * Override hub session: child show routes per index hub.
 * Show routes matching {prefix}.show are auto-discovered when {prefix}.index exists.
 * Modal types are auto-discovered from modals.php via TourRegistry::modalTypesForHub().
 */
return [
    'admin.anak.index' => [
        'show' => ['admin.anak.show'],
    ],

    'adminkelas.anak.index' => [
        'show' => ['adminkelas.anak.show'],
    ],

    'admin.kritik-saran.index' => [
        'show' => ['admin.kritik-saran.show'],
    ],

    'admin.orangtua-chat.index' => [
        'show' => ['admin.orangtua-chat.show'],
    ],

    'admin.master-kegiatan-rutin.index' => [
        'show' => ['admin.master-kegiatan-rutin.show'],
    ],

    'pengajar.master-kegiatan-rutin.index' => [
        'show' => ['pengajar.master-kegiatan-rutin.show'],
    ],

    'orangtua.kritik-saran.index' => [
        'show' => ['orangtua.kritik-saran.show'],
    ],

    'admin.pembayaran-bulanan.index' => [
        'show' => ['admin.pembayaran-bulanan.show'],
    ],

    'orangtua.pembayaran.index' => [
        'show' => ['orangtua.pembayaran.show'],
    ],
];
