<?php
/**
 * ドキュメントページ生成PHP
 * @author Kenta Suzuki
 * @copyright sukobuto.com
 * 
 * 要求された identifier に該当する記事を生成し保存すると共に出力する。
 * 記事データは ./articles に格納されている。( ./articles/<identifier>.html )
 * ナビゲーションなどのパーツデータは ./common　に格納されている。 
 * サイドメニュー等を
 */

require_once '../lib/config.php';
require_once LIB_DIR . '/Util.php';
require_once LIB_DIR . '/Michelf/MarkdownExtra.inc.php';

$identifier = Util::g('identifier');
$php_src = "articles/{$identifier}.php";
$md_src = "articles/{$identifier}.md";
if (!(file_exists($md_src) or file_exists($php_src))) Util::transfar('/');

ob_start();
//////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<?php include 'common/head.php'?>
</head>
<body>

<div id="brand_line">&nbsp;</div>
<div id="page_wrap">
	<?php include 'common/header.php'?>
	<div id="content_wrap" class="docs">
		<?php
		if (file_exists($md_src)) {
			echo '<article>';
			echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents($md_src));
			echo '<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/'.$identifier.'.html">こちら</a></div>';
			echo '</article>';
		} else if (file_exists($php_src)) {
			include $php_src;
		}
		?>
		<?php include 'common/sidemenu.php'?>
	</div><!-- #content_wrap -->
	<?php include '../common/footer.php'?>
</div><!-- #page_wrap -->
<?php include '../common/switch_board.php'?>
<?php include '../common/analytics.php'?>
</body>
</html>
<?php 
//////////////////////////////////////
$page = ob_get_contents();
ob_end_flush();

if (file_put_contents($identifier.'.html', $page) > 0) {
	Util::log(LOG_INFO, __FILE__, $identifier.'.html has created.');
}