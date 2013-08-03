<article class="infinished">
	
	<blockquote><i class="icon-exclamation-sign"></i> 申し訳ございません。この項目は翻訳が完了しておりません。いましばらくお待ち下さい。</blockquote>
	
	<h1>"submit" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>submit</code> バインディングは、関連付けられた DOM エレメントが submit されたときに
		指定した JavaScript 関数を実行するイベントハンドラを追加します。
	</p>
	<p>
		form で <code>submit</code> バインディングを使用した場合、
		Knockout はその form の、ブラウザの通常の動作を妨げます (<code>preventDefault</code>)。
		つまり、ブラウザは指定された関数を実行しますが、form の内容をサーバに送信することはありません。
		この仕様の理由は、通常 <code>submit</code> バインディングを使う場合、form は ViewModel のインターフェイスとして使い、
		標準の HTML form としては使わないからです。
		もしも標準の HTML form としてフォームを送信させたいのであれば、<code>submit</code> にバインドした関数が
		<code>true</code> を返却するようにして下さい。
	</p>
	
	<h3 id="example">例</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;form data-bind=&quot;submit: doSomething&quot;&gt;
	...フォーム部品をここに配置...
	&lt;button type=&quot;submit&quot;&gt;登録&lt;/button&gt;
&lt;/form&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	doSomething : function(formElement) {
		// ... なにかする
	}
};</pre>
	
	<p>
		上記の例を見ていただければわかるように、submit にバインドした関数にはパラメタとして form エレメントが渡されます。
		このパラメタは無視できますが、<code>ko.utils.postJson</code> のようなユーティリティで利用します。
	</p>
	
	<blockquote>
		<h3>訳者注 - ko.utils.postJson</h3>
		<p>
			<code>ko.utils.postJson</code> は form エレメントから、そのフォームのデータを
			JSON にしてサーバに POST 送信するユーティリティ機能です。
			現在、本家ドキュメントにおいてこの項目はリンク切れとなっており、
			修正され次第翻訳する予定です。
		</p>
	</blockquote>
	
	<h3 id="why_not_just_put_a__handler_on_the_submit_button">Submit ボタンに <code>cilck</code> バインディングすればいいのでは？</h3>
	
	<p>
		確かに、form で <code>submit</code> を使う代わりに、Submit ボタンで <code>click</code> を使うことができます。
		<code>submit</code> ならではの利点は、Submit をするのにボタンクリック以外の手段があるということです。
		たとえばテキストボックスに入力中、Enter キーで Submit できます。
	</p>
	
	<div class="demo" id="demo_1">
		<h3>訳者注 - 検索ボックスでデモ</h3>
		↓click バインディングだと、ボタンでしか反応しない。<br>
		<div>
			<input type="text" data-bind="value: Keyword" placeholder="キーワードを入力"/>
			<button data-bind="click: Search">検索</button>
		</div>
		↓submit バインディングだと、テキストボックスで Enter キーをタイプしても実行される。<br>
		<form data-bind="submit: Search">
			<input type="text" data-bind="value: Keyword, valueUpdate: 'afterkeydown'" placeholder="キーワードを入力"/>
			<button type="submit">検索</button>
		</form>
		<div data-bind="text: Result" style="background-color: #2a371d"> </div>
		
		<pre class="brush: html;">&lt;!-- View --&gt;
&lt;h3&gt;訳者注 - 検索ボックスでデモ&lt;/h3&gt;
↓click バインディングだと、ボタンでしか反応しない。&lt;br&gt;
&lt;div&gt;
	&lt;input data-bind=&quot;value: Keyword&quot; placeholder=&quot;キーワードを入力&quot;/&gt;
	&lt;button data-bind=&quot;click: Search&quot;&gt;検索&lt;/button&gt;
&lt;/div&gt;
↓submit バインディングだと、テキストボックスで Enter キーをタイプしても実行される。&lt;br&gt;
&lt;form data-bind=&quot;submit: Search&quot;&gt;
	&lt;input data-bind=&quot;value: Keyword, valueUpdate: 'afterkeydown'&quot; placeholder=&quot;キーワードを入力&quot;/&gt;
	&lt;button type=&quot;submit&quot;&gt;検索&lt;/button&gt;
&lt;/form&gt;
&lt;div data-bind=&quot;text: Result&quot;&gt; &lt;/div&gt;</pre>
		<pre class="brush: js;">// ViewModel
function SearchBoxViewModel() {
	var self = this;
	self.Keyword = ko.observable(&quot;&quot;);
	self.Result = ko.observable();
	self.Search = function() {
		if (self.Keyword() === &quot;&quot;) {
			alert(&quot;キーワードを入力して下さい&quot;);
			return;
		}
		self.Result(self.Keyword() + &quot; の検索結果...&quot;);
		self.Keyword(&quot;&quot;);
	};
}
ko.applyBindings(new SearchBoxViewModel());</pre>
	</div>
	<script type="text/javascript">
		function SearchBoxViewModel() {
			var self = this;
			self.Keyword = ko.observable("");
			self.Result = ko.observable();
			self.Search = function() {
				if (self.Keyword() === "") {
					alert("キーワードを入力して下さい");
					return;
				}
				self.Result(self.Keyword() + " の検索結果...");
				self.Keyword("");
			};
		}
		ko.applyBindings(new SearchBoxViewModel(), document.getElementById('demo_1'));
	</script>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				エレメントの <code>submit</code> イベントにバインドしたい関数です。
			</p>
			<p>
				どんな JavaScript 関数も使用できます。ViewModel の関数である必要は、必ずしもありません。<br>
				<code>submit: someObject.someFunction</code>
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

