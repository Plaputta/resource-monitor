<?php
header("Content-Type: image/png");
$im = imagecreate(1, 1);
imagecolorallocate($im, 0, 0, 0);
// Just pretend to be a slow loading marketing pixel or something...
sleep(rand(1,3));
imagepng($im);
imagedestroy($im);
?>
