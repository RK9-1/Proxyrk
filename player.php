<?php
$id = $_GET['id'] ?? '';
$channels = include "channels.json";

if (!isset($channels[$id])) {
    die("Invalid Channel");
}
$c = $channels[$id];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $c['name']; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://cdn.jwplayer.com/libraries/IDzF9Zmk.js"></script>

<style>
html,body{margin:0;background:#000;height:100%}
#player{width:100%;height:100%}
</style>
</head>
<body>

<div id="player"></div>

<script>
jwplayer("player").setup({
    file: "proxy.php?c=<?php echo $id; ?>",
    image: "<?php echo $c['logo']; ?>",
    autostart: true,
    width: "100%",
    height: "100%",
    stretching: "exactfit"
});
</script>

</body>
</html>
