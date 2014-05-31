<?php //  /tips/common/sidemenu.php
$menues = array(
	'<h2>;1' => '入門編',
	'helloWorld' => 'Hello world;ko.observable と ko.computed',
	'clickCounter' => 'クリックカウンター;宣言型バインディングの使い方と Knockout が依存を自動的に<br>トラッキングする仕組み',
	'simpleList' => 'シンプルなリスト;ko.observableArray を使った開発',
	'betterList' => 'リストを改良する;複数の振る舞いを組み合わせる',
	'controlTypes' => '各種 Form 部品;すべての Form 部品のリファレンス',
	'collections' => 'コレクションを操る;ネストされた配列を操る方法',
	'grid' => 'ページング機能付きの表;再利用できる部品<br>またはプラグインを作成する',
	'animatedTransitions' => 'アニメーション効果;アニメーションを実装する2つのシンプルな方法 (1つはカスタムバインディング)',
	
	'<h2>;2' => '中級編',
	'contactsEditor' => '連絡帳;ネストされたリストを編集する',
	'gridEditor' => 'グリッドエディタ;foreach バインディングを使い, jQuery Validation を組み合わせる',
	'cartEditor' => '買い物カゴ;ドロップダウンをネストさせる方法<br>文字列をフォーマットする方法',
	'twitter' => 'Twitter クライアント;テンプレート, 宣言型バインディング,<br>Ajax を組み合わせる',
	
	'<h2>;3' => 'Tips (翻訳者オリジナル他)',
	'knockout-es5' => 'Knockout ES5 で<br>より自然な書き方へ',
	'withTypeScript' => 'TypeScript + Knockout ES5 で<br>さらにシンプルに',
	/*
	'viewSideAnimation' => 'View 側でアニメーション効果を実装',
	'' => '',
	*/
);

echo '<aside>';
$in_list = false;
foreach ($menues as $key => $data) {
	if (Util::startsWith($key, '<h')) {
		if ($in_list) {
			echo '</ol>';
			$in_list = false;
		}
		$tmp = explode(';', $key);
		switch ($tmp[0]) {
			case '<h2>': echo '<h2>', $data, '</h2>'; break;
			case '<h3>': echo '<h3>', $data, '</h3>'; break;
		}
	} else {
		if (!$in_list) {
			echo '<ol>';
			$in_list = true;
		}
		$tmp = explode(';', $data);
		echo '		<li';
		if ($key == $identifier) echo ' class="active"';
		echo '><a href="';
		if (Util::startsWith($key, '<a>')) echo str_replace('<a>;', '', $key); 
		else echo './', $key;
		echo '">', $tmp[0], '</a><small>', $tmp[1], '</small></li>';
	}
}
echo '</aside>';
?>