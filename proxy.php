<?php
/**
 * 📺 Universal HLS Proxy - Beast Mode
 */
ini_set('memory_limit', '512M');
set_time_limit(0);

// Configuration
$channels_file = 'channels.json';
// Auto-detect URL scheme & host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$self_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// CORS Headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Range, Content-Type");
    exit(0);
}
header("Access-Control-Allow-Origin: *");

// 1. TS SEGMENT HANDLING (Binary Proxy)
if (isset($_GET['ts'])) {
    $ts_url = $_GET['ts'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ts_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Stream directly to output
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Masquerade as a real player
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
    
    header("Content-Type: video/mp2t");
    curl_exec($ch);
    curl_close($ch);
    exit;
}

// 2. PLAYLIST HANDLING (M3U8)
$id = $_GET['c'] ?? '';
if (!file_exists($channels_file)) die("Error: channels.json missing");

$json = json_decode(file_get_contents($channels_file), true);
if (!isset($json[$id])) die("Error: Channel ID not found");

$target_url = $json[$id]['url'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");

$data = curl_exec($ch);
$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200 || empty($data)) {
    die("Error: Stream Unreachable ($http_code)");
}

// 3. REWRITE PATHS
$base_url = dirname($effective_url) . '/';

// Regex to find paths and wrap them in our proxy
$data = preg_replace_callback('/^(?!#)(.+)$/m', function($matches) use ($base_url, $self_url) {
    $line = trim($matches[1]);
    if (empty($line)) return $line;

    if (strpos($line, 'http') !== 0) {
        $line = $base_url . $line;
    }
    
    return $self_url . "?ts=" . urlencode($line);
}, $data);

header("Content-Type: application/vnd.apple.mpegurl");
header("Cache-Control: no-cache");
echo $data;
?>
