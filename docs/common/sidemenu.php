<?php //  /docs/common/sidemenu.php
$menues = array(
	'<h2>;1' => 'スタートガイド',
	'introduction' => 'Knockoutの機能とメリット',
	'installation' => '導入方法	',
	
	'<h2>;2' => 'Observable (コア技術)',
	'observables' => 'Observable (ViewModelをつくる)',
	'computedObservables' => 'Computed Observable',
	'observableArrays' => 'Observable Array',
	'<h2>;3' => 'バインディング',
	'<h3>;3-1' => 'テキスト及び表現',
	'visible-binding' => 'visible',
	'text-binding' => 'text',
	'html-binding' => 'html',
	'css-binding' => 'css',
	'style-binding' => 'style',
	'attr-binding' => 'attr',
		
	'<h3>;3-2' => 'フロー制御',
	'foreach-binding' => 'foreach',
	'if-binding' => 'if',
	'ifnot-binding' => 'ifnot',
	'with-binding' => 'with',
	'<h3>;3-3' => 'フォーム部品にバインド',
	'click-binding' => 'click',
	'event-binding' => 'event',
	'submit-binding' => 'submit',
	'enable-binding' => 'enable',
	'disable-binding' => 'disable',
	'value-binding' => 'value',
	'hasfocus-binding' => 'hasfocus',
	'checked-binding' => 'checked',
	'd;options-binding' => 'options',
	'd;selectedOptions-binding' => 'selectedOptions',
	'd;uniqueName-binding' => 'uniqueName',
	'<h3>;3-4' => 'レンダリング・テンプレート',
	'd;template-binding' => 'template',
	'<h3>;3-5' => 'バインディングの構文',
	'd;binding-syntax' => 'data-bind の書き方について',
	'binding-context' => 'バインディング・コンテキスト',
	'<h2>;4' => 'カスタムバインディングの作成',
	'd;custom-bindings' => 'カスタムバインディングを作成',
	'd;custom-bindings-controlling-descendant-bindings' => '配下のバインディングを制御する',
	'd;custom-bindings-for-virtual-elements' => '仮想エレメントにバインド<br>できるようにする',
	'd;custom-bindings-disposal' => '破棄処理をカスタマイズ',
	
	'<h2>;5' => 'さらなるテクニック',
	'd;json-data' => 'JSON データの保存と読み込み',
	'd;extenders' => 'Observable を拡張する',
	'd;rateLimit-observable' => '変更通知を遅延させる',
	'd;unobtrusive-event-handling' => '"めだたない"イベントハンドラ',
	'd;fn' => '"fn"で Observable に<br>機能を追加',
	'd;binding-preprocessing' => 'プリプロセスで Knockout の<br>バインディング記法を拡張',
		
	'<h2>;6' => 'プラグイン',
	'd;plugins-mapping' => 'データ連携プラグイン<br>「Mapping」',
	
	'<h2>;7' => '詳細情報',
	'd;browser-suppport' => 'ブラウザサポート',
	'<a>;http://groups.google.com/group/knockoutjs' => 'メーリングリスト',
	'links' => 'リンクおよびチュートリアル',
	'd;amd-loading' => 'RequireJsを用いたAMD<br>(非同期モジュール定義)',
);

echo '<aside>';
$in_list = false;
foreach ($menues as $key => $data) {
	if (Util::startsWith($key, '<h')) {			// when item is header
		if ($in_list) {
			echo '</ol>';
			$in_list = false;
		}
		$tmp = explode(';', $key);
		switch ($tmp[0]) {
			case '<h2>': echo '<h2>', $data, '</h2>'; break;
			case '<h3>': echo '<h3>', $data, '</h3>'; break;
		}
	} else if (Util::startsWith($key, 'd;')) {	// when item is disabled
		if (!$in_list) {
			echo '<ol>';
			$in_list = true;
		}
		$tmp = explode(';', $key);
		echo '		<li>', $data, ' <i class="icon-ban-circle"></i></li>';
	} else {									// when item is valid link
		if (!$in_list) {
			echo '<ol>';
			$in_list = true;
		}
		$additional = "";
		if (Util::startsWith($key, '!;')) {
			$key = str_replace("!;", "", $key);
			$additional = ' <i class="icon-exclamation-sign"></i>';
		}
		echo '		<li';
		if ($key == $identifier) echo ' class="active"';
		echo '><a href="';
		if (Util::startsWith($key, '<a>')) echo str_replace('<a>;', '', $key); 
		else echo './', $key;
		echo '">', $data, $additional, '</a></li>';
	}
}
echo '<p>マーク <i class="icon-ban-circle"></i>, <i class="icon-exclamation-sign"></i> が付いている項目は<br>現在翻訳中です。</p>';
echo '</aside>';
?>