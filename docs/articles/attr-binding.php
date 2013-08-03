<article>
	
	<h1>"attr" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>attr</code> バインディングは関連付けられた DOM エレメントに属性を設定する汎用的な機能です。
		例えば、エレメントの <code>title</code> 属性や、<code>img</code> タグの <code>src</code>
		属性、リンクの <code>href</code> 属性を ViewModel の値に基づいて設定することで、
		対応するプロパティが変更される度に自動的に更新させることができます。
	</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: js;">&lt;a data-bind=&quot;attr: { href: url, title: details }&quot;&gt;
	レポート
&lt;/a&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		url: ko.observable(&quot;year-end.html&quot;),
		details: ko.observable(&quot;最後の年末統計情報レポート&quot;)
	};
&lt;/script&gt;</pre>
	
	<p>
		上記の例では、エレメントの <code>href</code> 属性に <code>year-end.html</code> を、
		<code>title</code> 属性に <code>最後の年末統計情報レポート</code> が設定されます。
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				属性名と同じ名前のプロパティをもつ JavaScript オブジェクトを渡します。
				それぞれのプロパティには、対応する属性に適用したい値を指定します。
			</p>
			<p>
				パラメタが参照している値が Observable である場合、バインディングは値が変更される度に属性の値を更新します。
				Observable でない場合、属性は一度だけ適用され、以降は更新されません。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_applying_attributes_whose_name_arent_legal_javascript_variable_names">(注) JavaScript 変数名として無効な名前の属性を適用するには</h3>
	
	<p><code>data-something</code> のような属性を適用する場合、次のように書くことはできません。</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;attr: { data-something: someValue }&quot;&gt;...&lt;/div&gt;</pre>
	
	<p>
		なぜなら、<code>data-something</code> はこの場合において正当ではない名前だからです。
		これは、名前を文字列リテラルにするためクォートで囲むだけで解決します。
		この書き方は JavaScript オブジェクトリテラルの正当な構文です。
		(技術的には、JSON の仕様において常にクォートで囲むべきとされていますが、実際のところはどちらでも構いません)
	</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;attr: { 'data-something': someValue }&quot;&gt;...&lt;/div&gt;</pre>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/attr-binding.html">こちら</a></div>
	
</article>