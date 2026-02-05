<?php
$channels = json_decode(@file_get_contents('channels.json'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live TV Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0a0a0a; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        h1 { text-align: center; color: #E50914; text-transform: uppercase; letter-spacing: 2px; }
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
            gap: 20px; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .card { 
            background: #1a1a1a; border-radius: 12px; overflow: hidden; 
            transition: transform 0.3s, box-shadow 0.3s; cursor: pointer;
            text-decoration: none; color: white; display: block;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(229, 9, 20, 0.4); }
        .card-img { 
            width: 100%; height: 100px; background: #222; 
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #333;
        }
        .card-img img { width: 100%; height: 100%; object-fit: cover; }
        .card-title { padding: 15px; text-align: center; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>📺 Live Channels</h1>
    <div class="grid">
        <?php if($channels): foreach($channels as $id => $c): ?>
        <a href="player.php?id=<?php echo $id; ?>" class="card">
            <div class="card-img">
                <?php if(!empty($c['logo'])): ?>
                    <img src="<?php echo $c['logo']; ?>" onerror="this.style.display='none'">
                <?php else: ?>
                    <i class="fas fa-play"></i>
                <?php endif; ?>
            </div>
            <div class="card-title"><?php echo $c['name']; ?></div>
        </a>
        <?php endforeach; endif; ?>
    </div>
</body>
</html>
