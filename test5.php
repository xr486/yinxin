<?php
$s = 'abc\\'xyz';
echo strlen($s) . " hex: ";
for ($i=0; $i<strlen($s); $i++) printf("%02x ", ord($s[$i]));
echo "\n";
// 文件里实际
$s2 = "abc\\'xyz"; // 双引号
echo strlen($s2) . " hex: ";
for ($i=0; $i<strlen($s2); $i++) printf("%02x ", ord($s2[$i]));
echo "\n";
