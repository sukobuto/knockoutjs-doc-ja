<article>
	
	<h1>"text" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>text</code> バインディングは関連付けられた DOM エレメントに、指定したパラメータのテキストを表示します。
	</p>
	
	<p>
		代表的に、<code>&lt;span&gt;</code> や <code>&lt;em&gt;</code>
		といったテキストを表示する伝統的なエレメントで用いる際に便利ですが、
		技術的にはどんなエレメントでも使えます。
	</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: html;">本日のメッセージ: &lt;span data-bind=&quot;text: myMessage&quot;&gt;&lt;/span&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		myMessage: ko.observable() // 最初は空
	};
	viewModel.myMessage(&quot;Hello, world!&quot;); // これでテキストが表示される
&lt;/script&gt;</pre>
	
	<h3 id="parameters">パラメタ</h3>
	
	<ul>
		<li>
			主パラメタ
			<p>
				Knockout はこのパラメタの値を、対象のエレメントのテキストノードに設定します。
				以前の内容は全て上書きされます。
			</p>
			<p>
				このパラメタの値が Observable である場合、値が変更される度にエレメントのテキストを更新します。
				Observable でない場合は、エレメントのテキストは一度だけ設定され、以降は更新されません。
			</p>
			<p>
				もし数値や文字列以外の値を指定した場合、その値の <code>toString</code> メソッドにて文字列化したテキストが表示されます。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_1_using_functions_and_expressions_to_detemine_text_values">(注1) 関数および式を使ってテキストを決定する</h3>
	<p>
		テキストをプログラム上で判定する場合の選択肢のひとつに <a href="computedObservables">Computed Observable</a> を作成する方法があります。
		どういったテキストを表示するべきかを記述した評価関数を使用します。
	</p>
	
	<p>例:</p>
	<pre class="brush: js;">今日のアイテムは &lt;span data-bind=&quot;text: priceRating&quot;&gt;&lt;/span&gt; です。
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		price: ko.observable(24.95)
	};
	viewModel.priceRating = ko.computed(function() {
		return this.price() &gt; 50 ? &quot;高額&quot; : &quot;安価&quot;;
	}, viewModel);
&lt;/script&gt;</pre>
	
	<p>これで、<code>price</code> の値が変化した時、条件に応じて "高額" と "安価" が切り替わります。</p>
	<p>
		また、このように単純な判定であれば Computed Observable を用いずとも同じことができます。
		<code>text</code> バインディングに、次のように任意の式を渡すことができます。
	</p>
	
	<pre class="brush: html;">今日のアイテムは &lt;span data-bind=&quot;text: price() &gt; 50 ? '高額' : '安価'&quot;&gt;&lt;/span&gt; です。</pre>
	
	<p>Computed Observable <code>priceRating</code> がなくても全く同じ結果が得られます。</p>
	
	<h3 id="note_2_about_html_encoding">(注2) HTML エンコーディングについて</h3>
	
	<p>
		このバインディングは、テキストノードを使ってテキストを表示します。
		そのため、悪意のあるスクリプトを実行してしまうリスクもなく、安全に文字列を設定できます。
		たとえば次のように記述した場合、
	</p>
	<pre class="brush: js;">viewModel.myMessage("<i>Hello, world!</i>");</pre>
	
	<p>...文字列は斜体で表示されず、HTML タグを含めてプレーンテキストとして表示されます。</p>
	<p>HTML を表示させたい場合、<a href="html-binding">"html" バインディング</a> を使用して下さい。</p>
	
	<h3 id="note_3_using_text_without_a_container_element">(注3) コンテナエレメントなしで "text" を使う</h3>
	<p>
		ときには、余計なエレメントを増やすこと無く <code>text</code> バインディングを使いたいことがあるかもしれません。
		例えば、<code>option</code> エレメントの中に他のエレメントを含めることはできません。
	</p>
	<pre class="brush: html;">&lt;select data-bind=&quot;foreach: items&quot;&gt;
	&lt;option&gt;アイテム「&lt;span data-bind=&quot;text: name&quot;&gt;&lt;/span&gt;」&lt;/option&gt;
&lt;/select&gt;</pre>
	
	<p>対処法は、コメントタグを用いた <em>コンテナレス構文</em> を使うことです。</p>
	
	<pre class="brush: html;">&lt;select data-bind=&quot;foreach: items&quot;&gt;
	&lt;option&gt;アイテム「&lt;!-- ko text: name--&gt;&lt;!--/ko--&gt;」&lt;/option&gt;
&lt;/select&gt;</pre>
	
	<p>
		このコメント <code>&lt;!--ko--&gt;</code> と <code>&lt;!--/ko--&gt;</code> は、
		内部にマークアップを含む“バーチャルエレメント”の 開始 / 終了 のマーカーとしての役割をもっています。
		Knockout はこのバーチャルエレメント構文を理解し、本当のコンテナエレメントがあるかのようにバインドします。
	</p>
	
	<h3 id="note_4_about_an_ie_6_whitespace_quirk">(注4) IE6 の空白に関する“癖”について</h3>
	
	<p>
		IE6 には、「空の span エレメントに続く空白は無視される」という謎な癖があります。
		この現象に対して Knockout で直接できることはありませんが、次のように書きたい場合:
	</p>
	<pre class="brush: html;">Welcome, &lt;span data-bind=&quot;text: userName&quot;&gt;&lt;/span&gt; to our web site.</pre>
	
	<p>
		... IE6 は <code>to our web site</code> のすぐ手前にある空白をレンダリングしませんが、
		<code>&lt;span&gt;</code> の中に何らかのテキストを入れることで回避できます。
	</p>
	<pre class="brush: html;">Welcome, &lt;span data-bind=&quot;text: userName&quot;&gt;&amp;nbsp;&lt;/span&gt; to our web site.</pre>
	
	<p>その他のブラウザおよび、IE6 より新しいバージョンの IE にはこの癖はありません。</p>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/text-binding.html">こちら</a></div>
	
</article>