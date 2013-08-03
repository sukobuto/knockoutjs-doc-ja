<article>
	
	<h1>"checked" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>checked</code> バインディングは ViewModel のプロパティと
		チェックボックス (<code>&lt;input type="checkbox"&gt;</code>) や
		ラジオボタン (<code>&lt;input type="radio"&gt;</code>)
		などのチェックできるフォーム部品をリンクします。
	</p>
	
	<p>
		ユーザが関連付けられたフォーム部品にチェックを入れると、ViewModel の値が更新されます。
		反対に、ViewModel の値を変更することでフォーム部品のチェック状態を変更することができます。
	</p>
	
	<p>
		(注) テキストボックスやドロップダウンリストなど、チェックできないフォーム部品は、
		<a href="value-binding"><code>value</code> バインディング</a>を使ってバインドしてください。
	</p>
	
	<h3 id="example_with_checkbox">チェックボックスの例</h3>
	
	<pre class="brush: html;">&lt;p&gt;ニュースメールを購読する: &lt;input type=&quot;checkbox&quot; data-bind=&quot;checked: wantsNews&quot; /&gt;&lt;/p&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		wantsNews: ko.observable(true) // 最初はチェックされた状態
	};
	
	// ... その後 ...
	viewModel.wantsNews(false); // これでチェックが外される
&lt;/script&gt;</pre>
	
	<h3 id="example_adding_checkboxes_bound_to_an_array">チェックボックスを配列にバインドする例</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;ニュースメールを購読する: &lt;input type=&quot;checkbox&quot; data-bind=&quot;checked: wantsNews&quot; /&gt;&lt;/p&gt;
&lt;div data-bind=&quot;visible: wantsNews&quot;&gt;
	ニュースカテゴリ:
	&lt;div&gt;&lt;input type=&quot;checkbox&quot; value=&quot;IT&quot; data-bind=&quot;checked: newsCategories&quot; /&gt; IT&lt;/div&gt;
	&lt;div&gt;&lt;input type=&quot;checkbox&quot; value=&quot;Sports&quot; data-bind=&quot;checked: newsCategories&quot; /&gt; スポーツ&lt;/div&gt;
	&lt;div&gt;&lt;input type=&quot;checkbox&quot; value=&quot;Economy&quot; data-bind=&quot;checked: newsCategories&quot; /&gt; 経済&lt;/div&gt;
&lt;/div&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	wantsNews: ko.observable(true),
	newsCategories: ko.observableArray([&quot;IT&quot;,&quot;Sports&quot;])
	// 最初は IT とスポーツにチェックが入った状態
};

// ... その後 ...
viewModel.newsCategories.push(&quot;Economy&quot;); // これで経済にチェックが入る</pre>
	
	<h3 id="example_adding_radio_buttons">ラジオボタンの例</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;ニュースメールを購読する: &lt;input type=&quot;checkbox&quot; data-bind=&quot;checked: wantsNews&quot; /&gt;&lt;/p&gt;
&lt;div data-bind=&quot;visible: wantsNews&quot;&gt;
	ニュースカテゴリを選択して下さい:
	&lt;div&gt;&lt;input type=&quot;radio&quot; name=&quot;newsCategoryGroup&quot; value=&quot;IT&quot; data-bind=&quot;checked: newsCategory&quot; /&gt; IT&lt;/div&gt;
	&lt;div&gt;&lt;input type=&quot;radio&quot; name=&quot;newsCategoryGroup&quot; value=&quot;Sports&quot; data-bind=&quot;checked: newsCategory&quot; /&gt; スポーツ&lt;/div&gt;
	&lt;div&gt;&lt;input type=&quot;radio&quot; name=&quot;newsCategoryGroup&quot; value=&quot;Economy&quot; data-bind=&quot;checked: newsCategory&quot; /&gt; 経済&lt;/div&gt;
&lt;/div&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	wantsNews: ko.observable(true),
	newsCategory: ko.observable(&quot;IT&quot;) // 最初は IT が選択された状態
};

// ... その後 ...
viewModel.newsCategory(&quot;Sports&quot;); // これでスポーツが選択される</pre>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			Knockout はエレメントのチェック状態を、このパラメタの値に対応させます。
			以前のチェック状態は上書きされます。
			パラメタの値のバインド方法は、対象のエレメントのタイプにより決定されます。
			
			<ul>
				<li>
					<p>
						<strong>チェックボックス</strong>の場合、値が <code>true</code> であればチェックボックスを　<em>チェック済み</em>　に、
						<code>false</code> であれば　<em>未チェック</em>　にします。
						<code>boolean</code> でない値を指定した場合は、相当する値として解釈されます。
						つまり、<code>0</code> 以外の数値, <code>null</code> 以外のオブジェクト, 空ではない文字列は <code>true</code> として解釈され、
						<code>0</code>, <code>null</code>, <code>undefined</code>, および空文字列は <code>false</code> として解釈されます。
					</p>
					
					<p>
						ユーザがチェックボックスにチェックを入れるか、外したとき、Knockout は ViewModel のプロパティに
						<code>true</code> または <code>false</code> を設定します。
					</p>
					
					<p>
						考慮すべき点は、パラメタが配列として解決される場合です。
						この場合、Knockout はチェックボックスの value にマッチするアイテムが配列に含まれる場合に、
						そのチェックボックスをチェック済みにし、逆にマッチするアイテムが含まれないチェックボックスのチェックを外します。
					</p>
					
					<p>
						ユーザがチェックボックスにチェックを入れるか、外したとき、Knockout は配列に対し、
						アイテムを追加・削除します。
					</p>
				</li>
				<li>
					<p>
						<strong>ラジオボタン</strong>の場合、Knockout はラジオボタンの value が値と同じである場合にのみ、そのラジオボタンにチェックを入れます。
						したがって、値は文字列である必要があります。
						前述の例では、<code>value="IT"</code> のラジオボタンは ViewModel のプロパティ「<code>newsCategory</code>」が
						<code>"IT"</code> のときのみチェック済みになります。
					</p>
					
					<p>
						ユーザがラジオボタンの選択状態を変更したとき、Knockout は ViewModel のプロパティに
						選択されたラジオボタンの <code>value</code> 属性の値を設定します。
						前述の例では、<code>value="Sports"</code> のラジオボタンを選択すると
						<code>viewModel.newsCategory</code> に <code>"Sports"</code> が設定されます。
					</p>
					
					<p>
						もちろん、これは複数のラジオボタンを一つのプロパティにバインドする場合に最も便利です。
						確実に択一選択されるようにするため、<code>name</code> 属性に同じ値を設定するべきです。
						(前述の例では <code>newsCategoryGroup</code>)<br>
						これによりラジオボタンがグループ化され、一度に一つのみ選択できるようになります。
					</p>
				</li>
			</ul>
			
			<p>
				パラメタが Observable である場合、バインディングは値が変更される度にエレメントのチェック状態を更新します。
				Observable でない場合、エレメントのチェック状態は一度だけ設定され、以降は更新されません。
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
