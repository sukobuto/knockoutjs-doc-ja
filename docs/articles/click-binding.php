<article>

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

	<h3 id="note_1_passing_a_current_item_as_a_parameter_to_your_handler_function">
		(注1) ハンドラ関数に引数として“現在のアイテム”が渡される
	</h3>

	<p>
		ハンドラを呼び出す際、Knockout は第一引数として現在のモデルを渡します。
		これは、特に配列の各要素を表示し、さらにどのアイテムの UI がクリックされたのかを知る必要があるときに便利です。
	</p>

	<pre class="brush: js;">&lt;ul data-bind=&quot;foreach: places&quot;&gt;
	&lt;li&gt;
		&lt;span data-bind=&quot;text: $data&quot;&gt;&lt;/span&gt;
		&lt;button data-bind=&quot;click: $parent.removePlace&quot;&gt;Remove&lt;/button&gt;
	&lt;/li&gt;
&lt;/ul&gt;

&lt;script type="text/javascript"&gt;
	function MyViewModel() {
		var self = this;
		self.places = ko.observableArray(['London', 'Paris', 'Tokyo']);

		// 第一引数として現在のアイテムが渡されるため、どれを削除すべきかがわかる
		self.removePlace = function(place) {
			self.places.remove(place)
		}
	}
	ko.applyBindings(new MyViewModel());
&lt;/script&gt;</pre>

	<p>上記サンプルのポイントは2つあります:</p>

	<ul>
		<li>
			<code>foreach</code> や <code>with</code> の内部のように、
			ネストされた <a href="/docs/binding-context">バインディング・コンテキスト</a>
			の中でルート ViewModel やその他親のコンテキストのハンドラ関数を呼び出す場合、
			ハンドラ関数を示すために <code>$parent</code> <code>$root</code>
			といったプレフィックスを使う必要があります。
		</li>
		<li>
			ViewModel にて、 <code>this</code> の別名として <code>self</code> 変数を定義すると便利です。
			<code>this</code> を再定義することで、イベントハンドラや Ajax リクエストのコールバックで発生する
			<span title="JavaScript は this の参照先がコロコロ変わる言語ですから、という意図です。">問題を事前に回避</span> することができます。
		</li>
	</ul>

	<h3 id="note_2_accessing_the_event_object_or_passing_more_parameters">
		(注2) イベントオブジェクトにアクセスする、またはさらなる引数を渡す
	</h3>

	<p>
		場合によっては、クリックイベントの DOM イベントオブジェクトにアクセスする必要がありますよね。
		Knockout は、次の例のようにイベントオブジェクトを第二引数として関数に渡します。
	</p>

	<pre class="brush: js;">&lt;button data-bind=&quot;click: myFunction&quot;&gt;
	ここをクリック
&lt;/button&gt;

&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		myFunction: function (data, event) {
			if (event.shiftKey) {
				// Shift キーが押されていた時のアクション
			} else {
				// 通常のアクション
			}
		}
	};
	ko.applyBindings(viewModel);
&lt;/script&gt;</pre>

	<p>さらに引数を渡す方法として、まずハンドラを、引数を受け渡す関数リテラルでラップする方法があります。</p>

	<pre class="brush: html;">&lt;button data-bind=&quot;click: function(data, event) { myFunction('引数1', '引数2', data, event) }&quot;&gt;
	ここをクリック
&lt;/button&gt;</pre>

	<p>
		Knockout は関数リテラルにデータとイベントオブジェクトを渡すため、ハンドラに渡すことができるということになります。
	</p>
	<p>
		別の方法として、関数リテラルを View に記述するのは避けたいのならば
		<a href="https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Function/bind">bind</a> 関数を使いましょう。
		次のように、関数を呼び出す際の引数を指定することができます。
	</p>

	<pre class="brush: html;">&lt;button data-bind=&quot;click: myFunction.bind($data, '引数1', '引数2')&quot;&gt;
	ここをクリック
&lt;/button&gt;</pre>

	<h3 id="note_3_allowing_the_default_click_action">
		(注3) デフォルトのクリックの挙動を許可する
	</h3>

	<p>
		通常、Knockout はクリックイベントによるデフォルトの挙動を抑止します。
		これは、もし <code>a</code> (リンク)タグに <code>click</code> バインディングを使用した場合、
		例えば、ブラウザはハンドラ関数を呼び出すのみでリンク先への遷移は行いません。
		通常は <code>click</code> バインディングにてリンクは、ハイパーリンクではなく
		ViewModel を操作するための UI 部品として使われるためこのような仕様となっています。
	</p>
	<p>
		もしもクリックイベントのデフォルトの挙動を行わせたい場合は、
		<code>click</code> のハンドラ関数にて <code>true</code> を返却してください。
	</p>

	<h3 id="note_4_preventing_the_event_from_bubbling">
		(注4) イベントバブリングを抑止する
	</h3>

	<p>
		通常、Knockout はクリックイベントが上位の要素のイベントハンドラにバブリングすることを許可します。
		例えば、ある要素とその親要素がどちらも <code>click</code> イベントをハンドリングするとします。
		このときこの要素がクリックされると、両方の要素にてハンドラが呼び出されます。
		必要であれば、 <code>clickBubble</code> という追加バインディングに <code>false</code>
		を指定することでバブリングを抑止することができます。
	</p>

	<pre class="brush: html;">&lt;div data-bind=&quot;click: myDivHandler&quot;&gt;
	&lt;button data-bind=&quot;click: myButtonHandler, clickBubble: false&quot;&gt;
		ここをクリック
	&lt;/button&gt;
&lt;/div&gt;</pre>

	<p>
		この場合通常であれば <code>myButtonHandler</code> が最初に呼び出され、
		クリックイベントは <code>myDivHandler</code> に遡及します。
		しかし、付与された <code>clickBubble</code> バインディングの <code>false</code> 指定によってイベントの遡及は
		<code>myButtonHandler</code> を通過後に止まります。
	</p>

	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>

	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>

</article>

