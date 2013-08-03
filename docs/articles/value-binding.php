<article class="infinished">
	
	<blockquote><i class="icon-exclamation-sign"></i> 申し訳ございません。この項目は翻訳が完了しておりません。いましばらくお待ち下さい。</blockquote>
	
	<h1>"value" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>value</code> バインディングは関連付けられた DOM エレメントの値と ViewModel のプロパティーをリンクさせます。
		<code>&lt;input&gt;</code> や <code>&lt;select&gt;</code>, <code>&lt;textarea&gt;</code>
		などのフォーム部品で使用します。
	</p>
	<p>
		ユーザがフォームの値を編集すると、ViewModel の値も更新されます。
		同様に、ViewModel の値を変更すると、フォームの値に反映されます。
	</p>
	<p>
		(注) チェックボックスまたはラジオボタンを使う場合は <code>value</code>
		バインディングではなく、<a href="checked-binding">"checked" バインディング</a>
		を使ってチェック状態を管理します。
	</p>
	
	<h3 id="example">例</h3>
	
	<pre class="brush: html;"><!-- View -->
&lt;p&gt;ユーザ名: &lt;input data-bind=&quot;value: userName&quot; /&gt;&lt;/p&gt;
&lt;p&gt;パスワード: &lt;input type=&quot;password&quot; data-bind=&quot;value: userPassword&quot; /&gt;&lt;/p&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	userName: ko.observable(&quot;&quot;),
	userPassword: ko.observable(&quot;abc&quot;)
};</pre>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				Knockout はエレメントの <code>value</code> プロパティにこのパラメタの値をセットします。
				以前の値は上書きされます。
			</p>
			<p>
				このパラメタが Observable である場合、このバインディングは値が変更される度にエレメントの値をを更新します。
				Observable でない場合は、エレメントの値は一度だけ設定され、以降は更新されません。
			</p>
			<p>
				数値や文字列以外の値 (オブジェクトもしくは配列) を指定した場合、
				表示されるテキストは <code>指定したパラメタ.toString()</code> を実行した結果となります。
				(この機能に大した利用価値はないので、文字列か数値を指定するのがベストです。)
			</p>
			<p>
				ユーザが対象のフォーム部品を編集したとき、Knockout は ViewModel 上のプロパティを更新します。
				通常 Knockout は、(1) フォーム部品の値が変更され、(2) かつ他の DOM ノードにフォーカスが移った時点で
				ViewModel を更新します (つまり、<code>change</code> イベント発生時)。<br>
				しかし <code>valueUpdate</code> という追加パラメタを使うことで、更新を発生させるためのイベントを設定することができます。
			</p>
		</li>
		<li>
			追加パラメタ
			<ul>
				<li>
					<code>valueUpdate</code>
					<p>
						バインディングに <code>valueUpdate</code> というパラメタが含まれる場合、
						<code>change</code> イベントのほかにフォーム部品の値の変更を検知する追加のイベントが定義されます。
						一般的によく使われる値は次の文字列です。
					</p>
					<ul>
						<li><code>'keyup'</code> &nbsp;-&nbsp; ユーザがキーを離したタイミングで更新</li>
						<li><code>'keypress'</code> &nbsp;-&nbsp; ユーザがキーを押さえたタイミングで更新</li>
						<li>
							<code>'afterkeydown'</code> &nbsp;-&nbsp; ユーザがキーボードで入力を開始したらすぐに更新<br>
							ブラウザの <code>keydown</code> イベントをキャッチし、非同期で処理します。
						</li>
					</ul>
					<p>
						ViewModel の値をリアルタイムで同期するのが目的であれば、<code>'afterkeydown'</code> が最適です。
					</p>
					<h4>例:</h4>
					<pre class="brush: html;">&lt;p&gt;値: &lt;input data-bind=&quot;value: someValue, valueUpdate: 'afterkeydown'&quot; /&gt;&lt;/p&gt;
&lt;p&gt;入力された値: &lt;span data-bind=&quot;text: someValue&quot;&gt;&lt;/span&gt;&lt;/p&gt; &lt;!-- リアルタイムで更新される --&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		someValue: ko.observable(&quot;この値を編集&quot;)
	};
&lt;/script&gt;</pre>
				</li>
			</ul>
		</li>
	</ul>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>
	
</article>

