<?php
$id = $_GET['id'] ?? '';

// Load channels
$json_data = @file_get_contents("channels.json");
$channels = json_decode($json_data, true);

if (!$channels || !isset($channels[$id])) {
    die("<div style='color:white; background:#222; padding:20px; font-family:sans-serif; text-align:center;'>
            <h2>❌ Channel Not Found</h2>
            <p>The ID <b>".htmlspecialchars($id)."</b> does not exist in your list.</p>
         </div>");
}

$c = $channels[$id];
$stream_url = "proxy.php?c=" . $id;
$logo = $c['logo'] ?? 'https://via.placeholder.com/200x100?text=No+Logo';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Watching: <?php echo $c['name']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/artplayer/dist/artplayer.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body, html { 
            margin: 0; padding: 0; width: 100%; height: 100%; 
            background-color: #050505; 
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            color: #fff;
        }

        /* Container for the player */
        .main-container {
            display: flex;
            flex-direction: column;
            width: 100vw;
            height: 100vh;
        }

        /* Top Bar / Header */
        .player-header {
            padding: 15px 25px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, transparent 100%);
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 15px;
            pointer-events: none; /* Let clicks pass to player */
        }

        .channel-logo {
            height: 40px;
            width: auto;
            border-radius: 5px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
        }

        .channel-info h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8);
        }

        /* The Player itself */
        #artplayer {
            width: 100%;
            height: 100%;
            flex: 1;
        }

        /* Error Message Overlay */
        .art-error-custom {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #111;
            color: #ff4d4d;
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="player-header">
            <img src="<?php echo $logo; ?>" alt="Logo" class="channel-logo">
            <div class="channel-info">
                <h1><?php echo $c['name']; ?></h1>
            </div>
        </div>

        <div id="artplayer"></div>
    </div>

    <script>
        const art = new Artplayer({
            container: '#artplayer',
            url: '<?php echo $stream_url; ?>',
            type: 'm3u8',
            setting: true,
            pip: true,
            fullscreen: true,
            fullscreenWeb: true,
            autoSize: false,
            autoMini: true,
            screenshot: true,
            cast: true, // Google Cast Support
            playbackRate: true,
            aspectRatio: true,
            theme: '#E50914', // Netflix Red
            icons: {
                loading: '<img src="https://i.gifer.com/ZZ5H.gif" width="50">',
            },
            customType: {
                m3u8: function (video, url) {
                    if (Hls.isSupported()) {
                        const hls = new Hls();
                        hls.loadSource(url);
                        hls.attachMedia(video);
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = url;
                    } else {
                        art.notice.show = 'Unsupported Browser';
                    }
                },
            },
        });

        // Automatically start playback
        art.on('ready', () => {
            art.play().catch(() => {
                art.notice.show = 'Click to Play';
            });
        });

        // Handle errors gracefully
        art.on('video:error', () => {
            console.error("Video Error Detected");
            art.notice.show = 'Stream Offline or Proxy Error';
        });
    </script>
</body>
</html>
