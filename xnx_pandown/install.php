<?php
!defined('DEBUG') AND exit('Forbidden');

$cache_dir = APP_PATH . 'plugin/xnx_pandown/cache/';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0777, TRUE);
}

setting_set('xnx_pandown_setting', array(
    'clear_qrcode' => 0,
));
