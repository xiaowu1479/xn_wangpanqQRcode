<?php exit;
$setting = setting_get('xnx_pandown_setting');
if (empty($setting['clear_qrcode'])) return;

$firstpid = $thread['firstpid'] ?? 0;
if (empty($firstpid)) return;

$first = post_read_cache($firstpid);
if (empty($first)) return;

$message = $first['message'] ?? $first['message_fmt'] ?? '';
if (empty($message)) return;

PandownService::cleanQRByMessage($message);
