<?php
$id = $_GET['id'] ?? '';
$json_data = @file_get_contents("channels.json");
$channels = json_decode($json_data, true);

// Fallback to first channel if ID invalid
if (!$channels || !isset($channels[$id])) {
    $id = array_key_first($channels);
}

$c = $channels[$id];
$stream_url = "proxy.php?c=" . $id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Stream: <?php echo $c['name']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/artplayer/dist/artplayer.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root { --primary: #E50914; --glass: rgba(20, 20, 20, 0.95); }
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; background: #000; font-family: sans-serif; overflow: hidden; }

        .menu-btn {
            position: absolute; top: 20px; left: 20px; z-index: 100;
            background: var(--primary); color: white; border: none;
            padding: 10px 15px; border-radius: 5px; cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5); font-size: 1.2rem;
        }

        .sidebar {
            position: fixed; top: 0; left: -320px; width: 300px; height: 100%;
            background: var(--glass); backdrop-filter: blur(10px);
            z-index: 99; transition: 0.3s ease;
            border-right: 1px solid #333; display: flex; flex-direction: column;
        }
        .sidebar.active { left: 0; }

        .sidebar-header { padding: 20px; background: #000; color: var(--primary); font-weight: bold; font-size: 1.5rem; text-align: center; }
        .channel-list { flex: 1; overflow-y: auto; padding: 10px; }
        
        .channel-item {
            display: block; padding: 12px; margin-bottom: 5px;
            color: #ccc; text-decoration: none; border-radius: 5px;
            transition: 0.2s; font-size: 0.9rem;
        }
        .channel-item:hover, .channel-item.active { background: var(--primary); color: white; }

        #artplayer { width: 100%; height: 100%; }
    </style>
</head>
<body>

    <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">LIVE TV</div>
        <div class="channel-list">
            <?php foreach ($channels as $key => $val): ?>
                <a href="?id=<?php echo $key; ?>" class="channel-item <?php echo ($id == $key) ? 'active' : ''; ?>">
                    <?php echo $val['name']; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="artplayer"></div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        const art = new Artplayer({
            container: '#artplayer',
            url: '<?php echo $stream_url; ?>',
            type: 'm3u8',
            setting: true,
            autoplay: true,
            fullscreen: true,
            fullscreenWeb: true,
            theme: '#E50914',
            customType: {
                m3u8: function (video, url) {
                    if (Hls.isSupported()) {
                        const hls = new Hls();
                        hls.loadSource(url);
                        hls.attachMedia(video);
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = url;
                    }
                },
            },
        });
    </script>
</body>
</html>
