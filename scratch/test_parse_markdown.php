<?php
require_once __DIR__ . '/../includes/functions.php';

$text = "### 2. Mệnh đề phủ định\n* Phủ định của mệnh đề \$P\$ kí hiệu là \$\\bar{P}\$. Nếu \$P\$ đúng thì \$\\bar{P}\$ sai, nếu \$P\$ sai thì \$\\bar{P}\$ đúng.";
echo "INPUT:\n" . $text . "\n\n";
echo "OUTPUT:\n" . parse_markdown($text) . "\n";
?>
