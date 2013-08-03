<article>
	
	<h1>"style" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>style</code> バインディングは関連付けられた DOM エレメントにスタイルを追加・削除します。
		例えば、値がマイナスとなった項目をハイライトする場合や、
		変化する数値に一致するように、グラフ上のバーの幅を設定するといった場合に便利です。
	</p>
	
	<p>注: 直接的にスタイルを適用するのではなく、CSS クラスを設定したい場合は <a href="css-binding">"css" バインディング</a>を使います。</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: html;">&lt;div data-bind=&quot;style: { color: currentProfit() &lt; 0 ? 'red' : 'black' }&quot;&gt;
	損益情報
&lt;/div&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		currentProfit: ko.observable(150000) // 正の値であるため、まずは黒で表示される
	};
	viewModel.currentProfit(-50); // これでDIVの内容が赤く表示される
&lt;/script&gt;</pre>
	
	<p>
		上記の例では、<code>currentProfit</code> の値がゼロを下回った時に、エレメントの <code>style.color</code> プロパティに
		<code>red</code> が設定され、ゼロ以上になれば <code>black</code> が設定されます。
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				スタイル名と同じ名前のプロパティをもつ JavaScript オブジェクトを渡します。
				それぞれのプロパティには、対応するスタイルに適用したい値を指定します。
			</p>
			<p>
				一度に複数のスタイルを設定できます。
			</p>
			<pre class="brush: html;">&lt;div data-bind=&quot;style: { color: currentProfit() &lt; 0 ? 'red' : 'black',
						 fontWeight: isSevere() ? 'bold' : '' }&quot;&gt;...&lt;/div&gt;</pre>
			<p>
				パラメタが参照している値が Observable である場合、バインディングは値が変更される度にスタイルを更新します。
				Observable でない場合、スタイルは一度だけ設定され、以降は更新されません。
			</p>
			<p>
				また、関数や任意の式をパラメタの値として使用することもできます。
				Knockout はそれらの評価結果の値を、適用するスタイルの値の決定に使用します。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_applying_style_whose_names_arent_legal_javascript_variable_names">(注) JavaScript 変数名として無効な名前のスタイルを適用するには</h3>
	
	<p>
		<code>font-weight</code> や <code>text-decoration</code> などの JavaScript の識別子として (ハイフンを含むため)
		無効な名前のスタイルを適用する場合、次のようにキャメルケースに変換して下さい。
	</p>
	<ul>
		<li><del><code>{ font-weight: someValue }</code></del> → <code>{ fontWeight: someValue }</code></li>
		<li><del><code>{ text-decoration: someValue }</code></del> → <code>{ textDecoration: someValue }</code></li>
	</ul>
	
	<p>参照: <a href="http://www.comptechdoc.org/independent/web/cgi/javamanual/javastyle.html">a longer list of style names and their JavaScript equivalents</a></p>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/style-binding.html">こちら</a></div>
	
</article>