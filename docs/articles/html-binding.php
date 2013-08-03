<article>
	
	<h1>"html" バインディング</h1>
	<h3 id="purpose">用途</h3>
	<p>
		<code>html</code> バインディングは関連付けられた DOM エレメントに、指定したパラメータの HTML を表示します。
	</p>
	<p>
		ViewModel の値が、レンダリングしたい HTML マークアップ文字列である場合に使います。
	</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: html">&lt;div data-bind=&quot;html: details&quot;&gt;&lt;/div&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		details: ko.observable() // 最初は空
	};
	viewModel.details(&quot;&lt;em&gt;詳細は &lt;a href='report.html'&gt;こちら&lt;/a&gt; をご覧ください。&lt;/em&gt;&quot;); // HTML コンテンツが表示される
&lt;/script&gt;</pre>
	
	<h3 id="parameters">パラメタ</h3>
	
	<ul>
		<li>
			主パラメタ
			<p>
				Knockout はこのパラメタの値を、対象のエレメントの <code>innerHTML</code> に設定します。
				以前の内容は全て上書きされます。
			</p>
			<p>
				このパラメタの値が Observable である場合、値が変更される度にエレメントの内容を更新します。
				Observable でない場合は、エレメントの内容は一度だけ設定され、以降は更新されません。
			</p>
			<p>
				もし数値や文字列以外の値を指定した場合、その値の <code>toString</code> メソッドにて文字列化した内容が
				<code>innerHTML</code> に設定されます。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_about_html_encoding">(注) HTML エンコーディングについて</h3>
	<p>
		このバインディングは、<code>innerHTML</code> を使ってエレメントにコンテンツを設定します。
		したがって、信頼できないデータソースの値を使用しないよう注意する必要があります。
		なぜなら、第三者により悪意のあるスクリプトが混入される可能性があるからです。
		表示する値が安全である確証がない場合、<code>innerText</code> または <code>textContent</code>
		を使用して値を表示する <a href="text-binding">"text" バインディング</a> を使用することができます。
	</p>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/html-binding.html">こちら</a></div>
	
</article>