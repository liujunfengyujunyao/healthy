<?php
//开启ob缓存
ob_start();
//输出一些内容
echo 'Hello, this is ob!';
//读取ob缓存中的内容
// $str = ob_get_contents();
//读取并删除ob缓存中的内容
$str = ob_get_clean();

echo $str;

