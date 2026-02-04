<?php
// ==========================================
// 🚀 CONFIGURATION & SECURITY
// ==========================================
$allowed_domains = ['allinonereborn.xyz', 'localhost']; // Add domains here
$channels_file = 'channels.json';

// 1. 🛡️ FIREWALL: Referer Protection
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$domain_valid = false;
foreach ($allowed_domains as $domain) {
    if (strpos($referer, $domain) !== false) {
        $domain_valid = true;
        break;
    }
}

if (!$domain_valid) {
    header("HTTP/1.1 403 Forbidden");
    exit("🚫 Access Denied: Unauthorized Domain.");
}

// ==========================================
// 🌍 CORS "BEAST MODE" HANDLING
// ==========================================
// Handle the "Preflight" OPTIONS request that browsers send before the real request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Range, X-Requested-With, Content-Type, Accept");
    header("Access-Control-Max-Age: 86400"); // Cache this check for 1 day
    exit(0);
}

// Standard CORS headers for GET requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/vnd.apple.mpegurl");

// ==========================================
// 📺 CHANNEL LOADING
// ==========================================
$id = $_GET['c'] ?? '';
if (!file_exists($channels_file)) die("❌ Error: channels.json not found.");

$json = json_decode(file_get_contents($channels_file), true);
if (!isset($json[$id])) die("❌ Error: Invalid Channel ID.");

$target_url = $json[$id]['url'];

// ==========================================
// ⚡ THE ENGINE: cURL with "Spoofing"
// ==========================================
$ch = curl_init();

// Basic Options
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects!
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// 🎭 MASQUERADE: Look like a real browser
$headers = [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Accept: */*",
    "Connection: keep-alive"
];

// Optional: Spoof the source's referer if needed (some streams require it)
// $headers[] = "Referer: https://source-website.com/"; 

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL errors (risky but needed for some IPTV)

$data = curl_exec($ch);
$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // Get final URL after redirects
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200 || empty($data)) {
    header("HTTP/1.1 502 Bad Gateway");
    exit("❌ Error: Stream Source Unreachable (Code: $http_code)");
}

// ==========================================
// 🔧 INTELLIGENT PATH REWRITING (The Magic)
// ==========================================
// We need the "Base URL" of the final destination to fix relative .ts paths
$base_url = dirname($effective_url) . '/';

// Regex to find any line that does NOT start with # (comments) or http (already absolute)
// and prepend the base URL to it.
$data = preg_replace('/^(?!#|http)(.*)$/m', $base_url . '$1', $data);

// ==========================================
// 📤 OUTPUT
// ==========================================
echo $data;
?>
