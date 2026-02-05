<?php
/**
 * 📺 Universal HLS/M3U8 & TS Proxy
 * Features: Absolute path rewriting, Binary TS proxying, CORS Beast Mode.
 */

// ==========================================
// ⚙️ CONFIGURATION
// ==========================================
$channels_file = 'channels.json';
$self_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";

// ==========================================
// 🌍 CORS "BEAST MODE"
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Range, X-Requested-With, Content-Type, Accept");
    header("Access-Control-Max-Age: 86400");
    exit(0);
}
header("Access-Control-Allow-Origin: *");

// ==========================================
// 🛠️ HANDLER 1: TS Segment Proxying
// ==========================================
// If the request has a ?ts= parameter, we fetch the video data directly
if (isset($_GET['ts'])) {
    $ts_url = $_GET['ts'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ts_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
    
    $ts_data = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status == 200) {
        header("Content-Type: video/mp2t"); // Standard TS Mime-type
        echo $ts_data;
    } else {
        header("HTTP/1.1 404 Not Found");
    }
    exit;
}

// ==========================================
// 📺 HANDLER 2: Playlist Proxying (M3U8)
// ==========================================
$id = $_GET['c'] ?? '';
if (!file_exists($channels_file)) die("❌ Error: channels.json not found.");

$json = json_decode(file_get_contents($channels_file), true);
if (!isset($json[$id])) die("❌ Error: Invalid Channel ID.");

$target_url = $json[$id]['url'];

// Fetch the M3U8 Playlist
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
]);

$data = curl_exec($ch);
$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

if (!$data) die("❌ Error: Could not fetch source.");

// ==========================================
// 🔧 THE MAGIC: Path Rewriting for TS Proxy
// ==========================================
$base_url = dirname($effective_url) . '/';

/**
 * This regex finds lines that are paths (don't start with #)
 * 1. If it's already a full URL (http...), it wraps it: script.php?ts=http...
 * 2. If it's a relative path, it fixes it to absolute then wraps it.
 */
$data = preg_replace_callback('/^(?!#)(.+)$/m', function($matches) use ($base_url, $self_url) {
    $line = trim($matches[1]);
    if (empty($line)) return $line;

    // Convert relative to absolute
    if (strpos($line, 'http') !== 0) {
        $line = $base_url . $line;
    }

    // Proxy via this script
    return $self_url . "?ts=" . urlencode($line);
}, $data);

header("Content-Type: application/vnd.apple.mpegurl");
echo $data;
?>
