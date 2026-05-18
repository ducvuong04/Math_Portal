<?php
$text = "* Phủ định của mệnh đề \$P\$ kí hiệu là \$\\bar{P}\$.";
$res = preg_replace('/^\s*[\*\-]\s+(.*?)(?:\r?\n|$)/m', '<li>$1</li>', $text);
var_dump($res);
?>
