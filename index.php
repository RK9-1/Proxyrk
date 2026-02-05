<?php
$channels_file = "channels.json";

if (!file_exists($channels_file)) {
    die("channels.json not found");
}

$channels = json_decode(file_get_contents($channels_file), true);
if (!$channels) {
    die("Invalid JSON");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Sony Channels</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { background:#000; color:#fff; font-family:Arial; margin:0; }
h1 { text-align:center; padding:10px; }
.list { display:flex; flex-wrap:wrap; }
a {
  width:50%;
  box-sizing:border-box;
  padding:12px;
  border:1px solid #222;
  text-decoration:none;
  color:#fff;
  background:#111;
}
a:hover { background:#333; }
</style>
</head>
<body>

<h1>📺 Sony Channels</h1>

<div class="list">
<?php foreach($channels as $id => $ch): ?>
  <a href="player.php?id=<?php echo urlencode($id); ?>">
    <?php echo htmlspecialchars($ch['name']); ?>
  </a>
<?php endforeach; ?>
</div>

</body>
</html>
