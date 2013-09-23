<?php
/**
 * システム設定を定義
 * @author Kenta Suzuki (sukobuto.com)
 */

define('DSEP', DIRECTORY_SEPARATOR); // エイリアス設定

/***** 要確認　ドキュメントルート直下ではない場所で運用する場合はこの値を修正 *****/
$chroot = '';

if (isset($chroot) && $chroot != '') {
	$siteroot_dir = DSEP . $chroot;
	$siteroot_url = '/' . $chroot;
} else {
	$siteroot_dir = '';
	$siteroot_url = '';
}
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT'] . $siteroot_dir);
define('LIB_DIR', SITE_ROOT . '/lib');
define('URL_SITE_ROOT', $siteroot_url . '/');
define('URL_FILES', $siteroot_url . '/files/');

define('DIR_FILES', SITE_ROOT . DSEP . 'files');
define('DIR_DEBUG', SITE_ROOT . DSEP . 'files' . DSEP . 'debug');

?>