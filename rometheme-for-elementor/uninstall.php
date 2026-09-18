<?php

/**
 * Dijalankan saat RTMKit dihapus lewat menu Plugins.
 *
 * WordPress tidak memuat plugin-nya di sini, jadi file ini berdiri sendiri:
 * tidak ada autoloader, tidak ada kelas RTMKit. Tugasnya dua — memberi tahu
 * RTMHub bahwa situs ini berhenti memakai RTMKit kalau pemiliknya memang pernah
 * mengizinkan, lalu membereskan option milik telemetry sendiri. Pengaturan lain
 * (widget, modul, template) sengaja tidak disentuh: menghapus plugin untuk
 * memasang ulang tidak boleh menghilangkan konfigurasi orang.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$rtmkit_payload = get_option('rtm_connector_last_payload');

// Menghapus plugin bukan izin baru. Kalau pemiliknya tidak pernah mencentang
// izin data pemakaian — atau sudah mencabutnya — kepergiannya pun tidak
// dilaporkan; option-nya langsung dibersihkan di bawah.
$rtmkit_consent = get_option('rtmkit_telemetry_consent', '');

// Laporkan sebelum option koneksinya ikut hilang. Hanya situs yang connect yang
// mengirim event; situs lain tidak punya apa pun untuk dikirim.
if ('granted' === $rtmkit_consent && is_array($rtmkit_payload) && ! empty($rtmkit_payload['access_token']) && is_string($rtmkit_payload['access_token'])) {
    $rtmkit_parts = explode('|', $rtmkit_payload['access_token'], 2);
    $rtmkit_token = isset($rtmkit_parts[1]) ? $rtmkit_parts[1] : $rtmkit_parts[0];

    $rtmkit_api = defined('RTMHUB_API_URL') ? RTMHUB_API_URL : 'https://api.rometheme.net/';

    $rtmkit_version = get_option('rtmkit_version');
    $rtmkit_version = is_string($rtmkit_version) ? $rtmkit_version : '';

    // Event yang masih mengantre ikut dikirim: ini kesempatan terakhirnya.
    // Jeda dari hub (rtmkit_telemetry_paused_until) sengaja diabaikan — tidak
    // ada "nanti" setelah uninstall, dan hub tidak memasukkan kiriman berisi
    // uninstall ke batas totalnya.
    $rtmkit_queue = get_option('rtmkit_telemetry_queue', []);
    $rtmkit_queue = is_array($rtmkit_queue) ? array_values(array_filter($rtmkit_queue, 'is_array')) : [];

    $rtmkit_queue[] = [
        'event'          => 'plugin_uninstalled',
        'plugin'         => 'rometheme-for-elementor',
        'plugin_version' => $rtmkit_version,
        'occurred_at'    => gmdate('c'),
    ];

    wp_remote_post(
        trailingslashit(untrailingslashit($rtmkit_api)) . 'connector/v1/telemetry/events',
        [
            // Pengguna sedang menunggu halaman Plugins; jangan lama-lama.
            'timeout'     => 5,
            'redirection' => 3,
            'blocking'    => true,
            'sslverify'   => true,
            'headers'     => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $rtmkit_token,
            ],
            'body'        => wp_json_encode([
                'domain'            => strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST)),
                'wp_version'        => get_bloginfo('version'),
                'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null,
                'events'            => array_slice($rtmkit_queue, -50),
            ]),
        ]
    );
}

foreach (
    [
        'rtmkit_telemetry_queue',
        'rtmkit_telemetry_consent',
        'rtmkit_telemetry_install',
        'rtmkit_telemetry_runtime',
        'rtmkit_telemetry_scan',
        'rtmkit_telemetry_widget_map',
        'rtmkit_telemetry_addons',
        // Versi lama menyimpan penutupan kartu di halaman Widgets; tidak
        // ditulis lagi, tapi tetap dibersihkan untuk situs yang sudah punya.
        'rtmkit_telemetry_notice_dismissed',
        'rtmkit_telemetry_admin_notice_dismissed',
    ] as $rtmkit_option
) {
    delete_option($rtmkit_option);
}

delete_transient('rtmkit_telemetry_paused_until');

// Hitungan widget per post yang disimpan untuk laporan pemakaian.
delete_post_meta_by_key('_rtmkit_widget_counts');

foreach (
    [
        'rtmkit_telemetry_flush',
        'rtmkit_telemetry_widget_report',
        'rtmkit_telemetry_widget_changed',
        'rtmkit_telemetry_widget_scan',
        'rtmkit_telemetry_widget_send',
        'rtmkit_telemetry_bootstrap',
    ] as $rtmkit_hook
) {
    wp_clear_scheduled_hook($rtmkit_hook);
}
