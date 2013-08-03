<article>
	
	<h1>"visible" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>visible</code> バインディングは渡された値に応じて、関連付けられた DOM エレメントの 表示 / 非表示 を切り替えます。
	</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: html;">&lt;div data-bind=&quot;visible: shouldShowMessage&quot;&gt;
	このメッセージは &quot;shouldShowMessage&quot; が true を保持している場合にのみ表示されます。
&lt;/div&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		shouldShowMessage: ko.observable(true) // 最初はメッセージを表示
	};
	viewModel.shouldShowMessage(false); // ... メッセージを非表示に
	viewModel.shouldShowMessage(true); // ... メッセージを表示
&lt;/script&gt;</pre>
	
	<h3 id="parameters">パラメタ</h3>
	
	<ul>
		<li>
			主パラメタ
			<ul>
				<li>
					<p>
						パラメタが <strong>false ライク</strong> な値として解決される場合 (boolean 値の <code>false</code>, 数値の0, null, undefined の何れか)、
						このバインディングは <code>yourElement.style.display</code> に <code>none</code> を設定することにより、エレメントを非表示にします。
						これは CSS による display スタイル定義より優先されます。
					</p>
				</li>
				<li>
					<p>
						パラメタが <strong>true ライク</strong> な値として解決される場合 (boolean 値の <code>true</code>, もしくは null でないオブジェクトや配列)、
						このバインディングは <code>yourElement.style.display</code> の値を削除することにより、エレメントを表示します。
					</p>
					<p>
						※このとき、あなたが CSS で設定したどんな display スタイル定義も適用されます。
						(したがって、<code>display:table-row</code> のような CSS ルールも、このバインディングと連携してうまく動きます。)
					</p>
				</li>
			</ul>
			<p>
				このパラメタの値が Observable である場合、このバインディングは値が変更される度にエレメントの 可視 / 不可視 を更新します。
				Observable でない場合は、エレメントの 可視 / 不可視 は一度だけ設定され、以降は更新されません。
			</p>
		</li>
		<li>
			追加パラメタ
			<ul>
				<li>なし</li>
			</ul>
		</li>
	</ul>
	
	<h3 id="note_using_functions_and_expressions_to_control_element_visibility">(注) 関数および式を使ってエレメントの 可視 / 不可視 をコントロールする</h3>
	
	<p>
		パラメタの値として、関数および任意の式を使うこともできます。
		関数および式を用いた場合、Knockout はその 関数を実行 / 式を評価 し、その結果をエレメントを隠すかどうかの判定に使用します。
	</p>
	
	<p>例:</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;visible: myValues().length &gt; 0&quot;&gt;
	このメッセージは 'myValues' に
	一つ以上の要素が含まれているとき表示されます。
&lt;/div&gt;
 
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		myValues: ko.observableArray([]) // 最初は空:メッセージは非表示
	};
	viewModel.myValues.push(&quot;some value&quot;); // これでメッセージが表示される
&lt;/script&gt;</pre>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/visible-binding.html">こちら</a></div>
	
</article>