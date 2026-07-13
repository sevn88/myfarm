<?php
// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------

namespace think;
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_web_errors.log');

// 加载 .env 环境变量（如果存在）
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env', true);
    foreach ($env as $key => $value) {
        if ($value !== false && $value !== true) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// IP 获取函数
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_REAL_FORWARDED_FOR']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_REAL_FORWARDED_FOR'];
    }
    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    return $ip;
}

// IP 地理限制开关（0=关闭 1=开启）
$block = intval(getenv('BLOCK_IP') ?: 0);

if ($block == 1) {
    function getUserIP() {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) return $client;
        if (filter_var($forward, FILTER_VALIDATE_IP)) return $forward;
        return $remote;
    }

    function get_ip_details($ip_) {
        $key = getenv('IPREGISTRY_KEY') ?: '';
        if (!$key) return null;
        $url = 'https://api.ipregistry.co/' . $ip_ . '?key=' . $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resp = curl_exec($ch);
        curl_close($ch);
        return json_decode($resp, true);
    }

    $ip_ = getUserIP();
    $details = get_ip_details($ip_);

    if ($details) {
        $countrycode = $details['location']['country']['code'] ?? '';
        $isp = $details['company']['type'] ?? '';
        $language = $details['location']['country']['languages'][0]['name'] ?? '';
        $time_zone = $details['time_zone']['name'] ?? '';
        $is_proxy = $details['security']['is_proxy'] ?? false;
        $is_vpn = $details['security']['is_vpn'] ?? false;
        $is_attacker = $details['security']['is_attacker'] ?? false;

        // 只允许香港和孟加拉
        if ($countrycode != 'HK' && $countrycode != 'BD') {
            header("HTTP/1.0 403 Not Found");
            exit();
        }

        // 孟加拉额外限制
        if ($countrycode == 'BD') {
            if ($isp != 'isp') {
                header("HTTP/1.0 403 Not Found");
                exit();
            }
            if ($language != 'Bangla') {
                header("HTTP/1.0 403 Not Found");
                exit();
            }
            if ($time_zone != 'Bangladesh Standard Time') {
                header("HTTP/1.0 403 Not Found");
                exit();
            }
        }

        // 拦截代理、VPN、攻击者
        if ($is_proxy || $is_vpn || $is_attacker) {
            header("HTTP/1.0 403 Not Found");
            exit();
        }
    }
}

/*******************************************************/

$http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
define('SITE_URL', $http . '://' . $_SERVER['HTTP_HOST']);
define('APP_PATH', __DIR__ . '/../application/');
define('PHPEXCEL_ROOT', __DIR__ . '/../extend/PHPExcel/');

require __DIR__ . '/../thinkphp/base.php';
require __DIR__ . '/../extend/org/Mobile.php';

Container::get('app')->run()->send();
