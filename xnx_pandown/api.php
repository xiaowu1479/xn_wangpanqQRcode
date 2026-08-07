<?php
!defined('DEBUG') AND exit('Access Denied');

$op = param(1, '');

if ($op === 'qr') {
    $url = param('url', '');
    if (empty($url)) {
        message(-1, 'URL is empty');
    }
    $hash = md5($url);
    $cache_dir = APP_PATH . 'plugin/xnx_pandown/cache/';
    $cache_file = $cache_dir . $hash . '.png';
    if (!is_file($cache_file)) {
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0777, TRUE);
        }
        $qrlib = APP_PATH . 'plugin/xnx_pandown/includes/phpqrcode/qrlib.php';
        if (is_file($qrlib)) {
            include_once $qrlib;
            QRcode::png($url, $cache_file, QR_ECLEVEL_L, 4);
        }
        if (!is_file($cache_file)) {
            message(-1, 'QR generation failed');
        }
    }
    $qr_url = 'plugin/xnx_pandown/cache/' . $hash . '.png';
    message(0, 'OK', array('url' => $qr_url));
}

message(-1, lang('xnx_pandown_unknown_op'));
