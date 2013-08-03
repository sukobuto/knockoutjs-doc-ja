<article>
	
	<h1>"if" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>if</code> バインディングは、与えられた値および式の評価結果が <code>true</code>
		(もしくは <code>true</code> に相当する値: <code>null</code> でないオブジェクトもしくは空でない文字列等)
		のときに、マークアップの一部を表示させます。
	</p>
	
	<p>
		<code>if</code> バインディングは、一見 <a href="visible-binding"><code>visible</code> バインディング</a>
		と同様の役割を演じます。異なるのは、<code>visible</code> バインディングでは対象のマークアップは常に
		DOM 上に存在しており、CSS を制御することで非表示にしている点です。
		常に DOM 上に存在しているため、配下エレメントの <code>data-bind</code> も常に適用された状態です。
		<code>if</code> バインディングでは、対象のマークアップを物理的に追加・削除します。
		したがって、配下エレメントの <code>data-bind</code> は <code>if</code> にバインドされた評価値が
		<code>true</code> のときにのみ適用されます。
	</p>
	
	<h3 id="example_1">例1</h3>
	
	<p>
		Observable の値に従って、マークアップの一部が動的に追加・削除される様子を示します。
	</p>
	
	<div class="demo" id="demo_1">
		<label>
			<input type="checkbox" data-bind="checked: displayMessage"/> メッセージを表示
		</label>
		<div data-bind="if: displayMessage">メッセージですぞ！</div>
	</div>
	<script type="text/javascript">
		ko.applyBindings(
			{ displayMessage: ko.observable(false) },
			document.getElementById('demo_1')
		);
	</script>
	
	<h4>ソースコード: View</h4>
	<pre class="brush: html;">&lt;label&gt;&lt;input type=&quot;checkbox&quot; data-bind=&quot;checked: displayMessage&quot; /&gt; メッセージを表示&lt;/label&gt;
 
&lt;div data-bind=&quot;if: displayMessage&quot;&gt;メッセージですぞ！&lt;/div&gt;</pre>
	
	<h4>ソースコード: ViewModel</h4>
	<pre class="brush: js;">ko.applyBindings({
	displayMessage: ko.observable(false)
});</pre>
	
	<h3 id="example_2">例2</h3>
	<p>
		次の例では、"水星"の <code>&lt;div&gt;</code> の内容は空になりますが、
		"地球"の場合内容が含まれます。理由は、地球には <code>null</code> でない <code>capital</code>
		プロパティがあるからです。反して水星のプロパティは <code>null</code> です。
	</p>
	
	<pre class="brush: html;">&lt;ul data-bind=&quot;foreach: planets&quot;&gt;
	&lt;li&gt;
		惑星: &lt;b data-bind=&quot;text: name&quot;&gt; &lt;/b&gt;
		&lt;div data-bind=&quot;if: capital&quot;&gt;
			都市: &lt;b data-bind=&quot;text: capital.cityName&quot;&gt; &lt;/b&gt;
		&lt;/div&gt;
	&lt;/li&gt;
&lt;/ul&gt;
	
	
&lt;script&gt;
	ko.applyBindings({
		planets: [
			{ name: '水星', capital: null }, 
			{ name: '地球', capital: { cityName: '東京' } }        
		]
	});
&lt;/script&gt;</pre>
	
	<p>
		このコードを正しく動作させるために、<code>if</code> バインディングが不可欠である重要な理由があります。
		<code>if</code> バインディングなしで (代わりに<code>visible</code> バインディングを使った場合など)
		は、"水星"の <code>capital.cityName</code>
		を評価しようとしたとき、<code>capital</code> 自体が
		<code>null</code> であるためエラーになってしまいます。
		JavaScript では、<code>null</code> や <code>undefined</code>
		である値のサブプロパティを参照することは許されません。
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				判定に使用される式です。<code>true</code> (もしくは相当する値) と評価された場合、
				内包するマークアップはドキュメント上に展開され、そのマークアップに含まれる
				<code>data-bind</code> 属性が適切に処理されます。
				<code>false</code> と評価された場合、内包するマークアップはドキュメントから削除されます。
			</p>
			<p>
				式が Observable を伴う場合、その値が変更されるたびに式が再評価されます。
				その際、<code>if</code> ブロックに含まれるマークアップも値が変更される度に追加・削除されます。
				<code>data-bind</code> 属性は、マークアップの新たなコピーが追加される度に処理されます。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_using_if_without_a_container_element">(注) コンテナエレメントなしで "if" を使う</h3>
	<p>
		ときには、<code>if</code> バインディングを適用するためのコンテナエレメントを設置することなく、
		マークアップの一部の あり/なし をコントロールしたいことがあるかもしれません。
		例えば、次の例のように <code>&lt;li&gt;</code> エレメントをコントロールしたい場合...
	</p>
	<pre class="brush: html;">&lt;ul&gt;
	&lt;li&gt;このアイテムは常に表示させたい&lt;/li&gt;
	&lt;li&gt;このアイテムの あり/なし を動的に切り替えたい&lt;/li&gt;
&lt;/ul&gt;</pre>
	
	<p>
		この場合、<code>&lt;ul&gt;</code> タグに <code>if</code> を配置することはできません。
		(最初の<code>&lt;li&gt;</code> タグにまで影響してしまいます。)<br>
		さらに、2つ目の <code>&lt;li&gt;</code> タグを別のタグで囲むことも不可能です。
		(<code>&lt;ul&gt;</code> タグの直下に <code>&lt;li&gt;</code>
		以外のコンテナエレメントを挿入することはできません。)<br>
	</p>
	
	<p>対処法は、コメントタグを用いた <em>コンテナレス構文</em> を使うことです。</p>
	
	<pre class="brush: html;">&lt;ul&gt;
	&lt;li&gt;このアイテムは常に表示させたい&lt;/li&gt;
	&lt;!-- ko if: someExpressionGoesHere --&gt;
		&lt;li&gt;このアイテムの あり/なし を動的に切り替えたい&lt;/li&gt;
	&lt;!-- /ko --&gt;
&lt;/ul&gt;</pre>
	
	<p>
		このコメント <code>&lt;!--ko--&gt;</code> と <code>&lt;!--/ko--&gt;</code> は、
		内部にマークアップを含む“バーチャルエレメント”の 開始 / 終了 のマーカーとしての役割をもっています。
		Knockout はこのバーチャルエレメント構文を理解し、本当のコンテナエレメントがあるかのようにバインドします。
	</p>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/if-binding.html">こちら</a></div>
	
</article>

