<article class="infinished">
	
	<blockquote><i class="icon-exclamation-sign"></i> 申し訳ございません。この項目は翻訳が完了しておりません。いましばらくお待ち下さい。</blockquote>
	
	<h1>"event" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>event</code> バインディングは、関連付けられた DOM エレメントで任意のイベントが発生したときに、
		指定した JavaScript 関数を実行するイベントハンドラを追加することを可能にします。
		<code>keypress</code> や <code>mouseover</code>, <code>mouseout</code> などどのようなイベントでもバインドできます。
	</p>
	
	<h3 id="example">例</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;div&gt;
	&lt;div data-bind=&quot;event: { mouseover: enableDetails, mouseout: disableDetails }&quot;&gt;
		カーソルをここに合わせて下さい。
	&lt;/div&gt;
	&lt;div data-bind=&quot;visible: detailsEnabled&quot;&gt;
		なんらかの詳細メッセージ
	&lt;/div&gt;
&lt;/div&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	detailsEnabled: ko.observable(false),
	enableDetails: function() {
		this.detailsEnabled(true);
	},
	disableDetails: function() {
		this.detailsEnabled(false);
	}
};
ko.applyBindings(viewModel);</pre>
	
	<p>
		最初のエレメントにマウスカーソルが 乗る or 離れる と同時に、ViewModel の <code>detailsEnabled</code>
		を切り替えるメソッドが実行されます。
		2つ目のエレメントは <code>detailsEnabled</code> の値の変化に応じて、表示/非表示 が切り替わります。
	</p>
	
	<ul>
		<li>
			主パラメタ
			<p>
				プロパティを対象のイベント名、値を対象のイベントにバインドしたい関数にした
				JavaScript オブジェクトです。
			</p>
			<p>
				どんな JavaScript 関数も使用できます。ViewModel の関数である必要は、必ずしもありません。<br>
				<code>event: { mouseover: someObject.someFunction }</code>
				のように書くことで、どんなオブジェクトの関数も参照できます。
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

