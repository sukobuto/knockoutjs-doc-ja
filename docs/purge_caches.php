<?php
require_once '../lib/config.php';
require_once LIB_DIR . '/Util.php';
$dir = "./";

// ディレクトリの内容を読み込みます。
if ($dh = opendir($dir)) {
	while (($file = readdir($dh)) !== false) {
		if (Util::endsWith($file, '.html')) {
			unlink($dir . $file);
		}
	}
	closedir($dh);
}
?>