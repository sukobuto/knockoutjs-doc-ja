<article>
	
	<h1>"css" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>css</code> バインディングは一つまたは複数の CSS クラスを関連付けられた DOM エレメントに追加・削除します。
		簡単な例では、「マイナスの数値であれば赤でハイライトする」といった場合に便利です。
	</p>
	
	<p>
		注: CSS クラスを用いずに、直接スタイル属性をアサインしたい場合は、<a href="style-binding">"style" バインディング</a> を使います。
	</p>
	
	<h3 id="example_with_static_classes">例 : 静的なクラス</h3>
	<pre class="brush: js;">&lt;div data-bind=&quot;css: { profitWarning: currentProfit() &lt; 0 }&quot;&gt;
	損益情報
&lt;/div&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		currentProfit: ko.observable(150000) // 正の値なので、&quot;profitWarning&quot; クラスは適用されない。
	};
	viewModel.currentProfit(-50); // これで &quot;profitWarning&quot; クラスが適用される
&lt;/script&gt;</pre>
	
	<p>
		上記の例では、<code>profitWarning</code> という CSS クラスは <code>currentProfit</code>
		の値がゼロを下回ったときに適用され、ゼロ以上になれば除去されます。
	</p>
	
	<h3 id="example_with_dynamic_classes">例 : 動的なクラス</h3>
	<pre class="brush: js;">&lt;div data-bind=&quot;css: profitStatus&quot;&gt;
	損益情報
&lt;/div&gt;

&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		currentProfit: ko.observable(150000)
	};

	// 正の値として評価されるため、まず &quot;profitPositive&quot; クラスが適用される
	viewModel.profitStatus = ko.computed(function() {
		return this.currentProfit() &lt; 0 ? &quot;profitWarning&quot; : &quot;profitPositive&quot;;
	}, viewModel);

	// &quot;profitPositive&quot; クラスは除去され、&quot;profitWarning&quot; クラスが適用される
	viewModel.currentProfit(-50);
&lt;/script&gt;</pre>
	
	<p>
		上記の例では、<code>currentProfit</code> が正の値であれば <code>profitPositive</code> クラスが適用され、
		負の値であれば <code>profitWarning</code> クラスが適用されます。
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	
	<ul>
		<li>
			主パラメタ
			<p>
				静的な CSS クラス名を使う場合は、CSS クラス名と同じ名前のプロパティをもつ JavaScript オブジェクトを渡します。
				それぞれのプロパティは <code>true</code> または <code>false</code> として評価され、
				結果に応じてそれぞれのクラスが適用・除去されます。
			</p>
			<p>
				一度に複数の CSS クラスを設定できます。
			</p>
			<pre class="brush: html;">&lt;div data-bind=&quot;css: { profitWarning: currentProfit() &lt; 0,
					   majorHighlight: isSevere }&quot;&gt;</pre>
			<p>
				次のように、クラス名 (プロパティ) をクォートでくくることで、一つの条件に合致する場合に複数のクラスを設定することができます。
			</p>
			<pre class="brush: html;">&lt;div data-bind=&quot;css: { profitWarning: currentProfit() &lt; 0,
					   &apos;major highlight&apos;: isSevere }&quot;&gt;</pre>
			<p>
				boolean ではない値はルーズに boolean として解釈されます。
				例えば、<code>0</code> と <code>null</code> は <code>false</code> として扱われ、
				<code>21</code> や <code>null</code> でないオブジェクトは <code>true</code> として扱われます。
			</p>
			<p>
				パラメタが参照している値が Observable である場合、バインディングは値が変更される度に CSS クラスを追加・除去します。
				Observable でない場合、CSS クラスは一度だけ追加・除去され、以降はそのままとなります。
			</p>
			<p>
				動的な CSS クラス名を使う場合は、CSS クラス名に一致する文字列を渡すことで、その CSS クラスを対象のエレメントに追加できます。
				パラメタが参照している値が Observable である場合、値が変更されると、前回追加したクラスを全て除去した後
				Observable の新しい値に一致するクラスを追加します。
			</p>
			<p>
				また、関数や任意の式をパラメタの値として使用することもできます。
				Knockout はそれらの評価結果の値を、特定の CSS クラスを追加するのか削除するのかの判定に使用します。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_applying_css_classes_whose_names_arent_legal_javascript_variable_names">(注) JavaScript 変数名として無効な名前の CSS クラスを適用するには</h3>
	
	<p><code>my-class</code> のような CSS クラスを適用するとき、次のように書くことはできません。</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;css: { my-class: someValue }&quot;&gt;...&lt;/div&gt;</pre>
	
	<p>
		なぜなら、<code>my-class</code> はこの場合において正当ではない名前だからです。
		これは、名前を文字列リテラルにするためクォートで囲むだけで解決します。
		この書き方は JavaScript オブジェクトリテラルの正当な構文です。
		(技術的には、JSON の仕様において常にクォートで囲むべきとされていますが、実際のところはどちらでも構いません)
	</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;css: { 'my-class': someValue }&quot;&gt;...&lt;/div&gt;</pre>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/css-binding.html">こちら</a></div>
	
</article>