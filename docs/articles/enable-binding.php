<article class="infinished">
	
	<blockquote><i class="icon-exclamation-sign"></i> 申し訳ございません。この項目は翻訳が完了しておりません。いましばらくお待ち下さい。</blockquote>
	
	<h1>"enable" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>enable</code> バインディングは、値が <code>true</code>
		のときだけ関連付けられた DOM エレメントを使用可能にします。
		<code>input</code> や <code>select</code>, <code>textarea</code> などの form 部品でよく使います。
	</p>
	
	<h3 id="example">例</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;
	&lt;input type='checkbox' data-bind=&quot;checked: hasCellphone&quot; /&gt;
	携帯電話を持っている
&lt;/p&gt;
&lt;p&gt;
	電話番号:
	&lt;input type='text' data-bind=&quot;value: cellphoneNumber, enable: hasCellphone&quot; /&gt;
&lt;/p&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	hasCellphone : ko.observable(false),
	cellphoneNumber: &quot;&quot;
};</pre>
	
	<p>
		最初 "電話番号" のテキストボックスは disabled の状態で入力できません。
		"携帯電話を持っている" のチェックボックスにチェックを入れたときのみ入力できるようになります。
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				関連付けられた DOM エレメントを使用可能にするか否かを制御するための値です。
			</p>
			<p>
				boolean でない値は妥当に解釈されます。
				例えば <code>0</code> と <code>null</code> は <code>false</code> として扱われ、
				<code>21</code> と <code>null</code> でないオブジェクトは <code>true</code> として扱われます。
			</p>
			<p>
				このパラメタの値が Observable である場合、このバインディングは値が変更される度にエレメントの 使用可 / 使用不可 を更新します。
				Observable でない場合は、エレメントの 使用可 / 使用不可 は一度だけ設定され、以降は更新されません。
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
