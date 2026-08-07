<?php
!defined('DEBUG') AND exit('Access Denied');

class PandownService {

    const CACHE_DIR = 'plugin/xnx_pandown/cache/';

    public static function extractUrl($text) {
        $url_pattern = '#https?://[^\s<>"\'\x{4e00}-\x{9fa5}]+#u';
        if (preg_match($url_pattern, $text, $m)) {
            return $m[0];
        }
        $patterns = array(
            '#pan\.baidu\.com/s/[a-zA-Z0-9\-]+#i',
            '#yun\.baidu\.com/s/[a-zA-Z0-9\-]+#i',
            '#pan\.quark\.cn/s/[a-zA-Z0-9]+#i',
            '#kuake\.com/s/[a-zA-Z0-9]+#i',
            '#drive\.uc\.cn/s/[a-zA-Z0-9]+#i',
            '#uc\.cn/s/[a-zA-Z0-9]+#i',
            '#pan\.xunlei\.cn/s/[a-zA-Z0-9]+#i',
            '#xunlei\.com/s/[a-zA-Z0-9]+#i',
            '#aliyundrive\.com/s/[a-zA-Z0-9]+#i',
            '#aliyundrive\.net/s/[a-zA-Z0-9]+#i',
            '#guangyapan\.com/s/[a-zA-Z0-9_]+#i',
        );
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $url = $m[0];
                if (!preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . $url;
                }
                return $url;
            }
        }
        return $text;
    }

    public static function detectType($url) {
        $url_lower = strtolower($url);
        $types = array(
            '百度网盘' => array('pan.baidu.com', 'yun.baidu.com'),
            '夸克网盘' => array('pan.quark.cn', 'kuake.com'),
            'UC网盘'   => array('drive.uc.cn', 'uc.cn', 'www.uc.cn'),
            '迅雷网盘' => array('pan.xunlei.cn', 'xunlei.com'),
            '阿里云盘' => array('aliyundrive.com', 'aliyundrive.net'),
            '光鸭云盘' => array('guangyapan.com'),
        );
        foreach ($types as $name => $domains) {
            foreach ($domains as $domain) {
                if (strpos($url_lower, $domain) !== false) {
                    return $name;
                }
            }
        }
        return '网盘链接';
    }

    public static function generateQR($url) {
        $hash = md5($url);
        $cache_dir = APP_PATH . self::CACHE_DIR;
        $cache_file = $cache_dir . $hash . '.png';
        $qr_url = 'plugin/xnx_pandown/cache/' . $hash . '.png';

        if (!is_file($cache_file)) {
            if (!is_dir($cache_dir)) {
                mkdir($cache_dir, 0777, TRUE);
            }
            $qrlib = APP_PATH . 'plugin/xnx_pandown/includes/phpqrcode/qrlib.php';
            if (is_file($qrlib)) {
                include_once $qrlib;
                QRcode::png($url, $cache_file, QR_ECLEVEL_L, 4);
            }
        }
        return $qr_url;
    }

    public static function processMessage(&$first) {
        if (empty($first['message_fmt'])) return;

        $first['message_fmt'] = htmlspecialchars_decode($first['message_fmt']);

        $preg_pd = preg_match_all('/\[pd\s+url="(.*?)"(?:\s+code="(.*?)")?\]/i', $first['message_fmt'], $matches);
        if (empty($preg_pd) || empty($matches[0])) return;

        $GLOBALS['xnx_has_pandown'] = true;

        for ($i = 0; $i < count($matches[0]); $i++) {
            $tag = $matches[0][$i];
            $raw = $matches[1][$i];
            $code = isset($matches[2][$i]) ? trim($matches[2][$i]) : '';
            $url = self::extractUrl($raw);
            if (empty($url)) continue;

            $type_name = self::detectType($url);
            $qr_url = self::generateQR($url);
            $safe_url = esc_attr($url);
            $code_attr = $code !== '' ? ' data-code="' . esc_attr($code) . '"' : '';

            $html = '<div class="xnx-pandown-download">'
                . '<a href="javascript:void(0)" class="xnx-pandown-btn btn" data-url="' . $safe_url . '" data-qr="' . $qr_url . '"' . $code_attr . '>'
                . '<i class="ti ti-cloud-down me-1"></i>'
                . '<span class="xnx-pandown-text">' . esc_html($type_name) . '</span>'
                . '</a>'
                . '</div>';

            $first['message_fmt'] = str_replace($tag, $html, $first['message_fmt']);
        }
    }

    public static function cleanQRByMessage($message) {
        preg_match_all('/\[pd\s+url="(.*?)"(?:\s+code=".*?")?\]/i', $message, $matches);
        if (empty($matches[1])) return 0;

        $cache_dir = APP_PATH . self::CACHE_DIR;
        $count = 0;
        foreach ($matches[1] as $url) {
            $hash = md5(trim($url));
            $cache_file = $cache_dir . $hash . '.png';
            if (is_file($cache_file)) {
                unlink($cache_file);
                $count++;
            }
        }
        return $count;
    }

    public static function cleanAllQR() {
        $cache_dir = APP_PATH . self::CACHE_DIR;
        $files = glob($cache_dir . '*.png');
        $count = 0;
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $count++;
                }
            }
        }
        return $count;
    }
}
