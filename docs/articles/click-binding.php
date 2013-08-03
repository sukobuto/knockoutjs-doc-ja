<article class="infinished">
	
	<blockquote><i class="icon-exclamation-sign"></i> 申し訳ございません。この項目は翻訳が完了しておりません。いましばらくお待ち下さい。</blockquote>
	
	<h1>"click" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>click</code> バインディングは関連付けられた DOM エレメントがクリックされたときに、
		指定した JavaScript 関数を実行するイベントハンドラを追加します。
		<code>button</code> や <code>input</code>、<code>a</code> などで最もよく使用しますが、
		可視エレメントであればなんでも使うことができます。
	</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;div&gt;
	&lt;span data-bind=&quot;text: numberOfClicks&quot;&gt;&lt;/span&gt; 回クリックしました。
	&lt;button data-bind=&quot;click: incrementClickCounter&quot;&gt;クリックして下さい&lt;/button&gt;
&lt;/div&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	numberOfClicks : ko.observable(0),
	incrementClickCounter : function() {
		var previousCount = this.numberOfClicks();
		this.numberOfClicks(previousCount + 1);
	}
};</pre>
	
	<p>
		ボタンをクリックするたび、ViewModel の <code>incrementClickCounter()</code> が実行されます。
		<code>incrementClickCounter()</code> は ViewModel の状態を変化させるので、結果的に UI が更新されます。
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	
	<ul>
		<li>
			主パラメタ
			<p>エレメントの <code>click</code> イベントにバインドしたい関数</p>
			<p>
				どんな JavaScript 関数も使用できます。ViewModel の関数である必要は、必ずしもありません。
				<code>click: someObject.someFunction</code> のように書くことで、どんなオブジェクトの関数も参照できます。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>
	
</article>

