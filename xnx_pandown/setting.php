<?php
!defined('DEBUG') AND exit('Access Denied.');

$action = param(3);
if ($method == 'GET') {
    $setting = setting_get('xnx_pandown_setting');
    include _include(APP_PATH . 'plugin/xnx_pandown/view/htm/setting.htm');
} elseif ($method == 'POST') {
    $op = param('op');
    if ($op == 'save') {
        $setting = setting_get('xnx_pandown_setting');
        $setting['clear_qrcode'] = param('clear_qrcode', 0);
        setting_set('xnx_pandown_setting', $setting);
        message(0, lang('xnx_pandown_save_success'));
    } elseif ($op == 'clear') {
        $count = PandownService::cleanAllQR();
        message(0, lang('xnx_pandown_clear_success', array('count' => $count)));
    }
    message(-1, lang('operation_failed'));
}
