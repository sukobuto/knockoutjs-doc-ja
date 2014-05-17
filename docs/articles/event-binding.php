<article>
	
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

	<h3 id="note_1_passing_a_current_item_as_a_parameter_to_your_handler_function">
		(注1) ハンドラ関数に引数として“現在のアイテム”が渡される
	</h3>

	<p>
		ハンドラを呼び出す際、Knockout は第一引数として現在のモデルを渡します。
		これは、特に配列の各要素を表示し、さらにどのアイテムの UI がクリックされたのかを知る必要があるときに便利です。
	</p>

	<pre class="brush: js;">&lt;ul data-bind=&quot;foreach: places&quot;&gt;
	&lt;li data-bind=&quot;text: $data, event: { mouseover: $parent.logMouseOver }&quot;&gt; &lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;&lt;span data-bind=&quot;text: lastInterest&quot;&gt; &lt;/span&gt; に興味がお有りですか？&lt;/p&gt;

&lt;script type="text/javascript"&gt;
	function MyViewModel() {
		var self = this;
		self.lastInterest = ko.observable();
		self.places = ko.observableArray(['London', 'Paris', 'Tokyo']);

		// 第一引数として現在のアイテムが渡されるため、どの地名がマウスオーバーされたのかがわかる
		self.logMouseOver = function(place) {
			self.lastInterest(place)
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
		場合によっては、イベントの DOM イベントオブジェクトにアクセスする必要がありますよね。
		Knockout は、次の例のようにイベントオブジェクトを第二引数として関数に渡します。
	</p>

	<pre class="brush: js;">&lt;div data-bind=&quot;event: { mouseover: myFunction }&quot;&gt;
	ここにマウスをのせる
&lt;/div&gt;

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

	<pre class="brush: html;">&lt;div data-bind=&quot;event: { mouseover: function(data, event) { myFunction('引数1', '引数2', data, event) } }&quot;&gt;
	ここにマウスをのせる
&lt;/div&gt;</pre>

	<p>
		Knockout は関数リテラルにデータとイベントオブジェクトを渡すため、ハンドラに渡すことができるということになります。
	</p>
	<p>
		別の方法として、関数リテラルを View に記述するのは避けたいのならば
		<a href="https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Function/bind">bind</a> 関数を使いましょう。
		次のように、関数を呼び出す際の引数を指定することができます。
	</p>

	<pre class="brush: html;">&lt;div data-bind=&quot;event: { mouseover: myFunction.bind($data, '引数1', '引数2') }&quot;&gt;
	ここにマウスをのせる
&lt;/div&gt;</pre>

	<h3 id="note_3_allowing_the_default_click_action">
		(注3) デフォルトのクリックの挙動を許可する
	</h3>

	<p>
		通常、Knockout はクリックイベントによるデフォルトの挙動を抑止します。
		これは、もし <code>input</code> タグの <code>keypress</code> イベントを捕捉するために
		<code>event</code> バインディングを使用した場合、
		例えば、ブラウザはハンドラ関数を呼び出すのみで <code>input</code> 要素の値を追加しません。
		より一般的な例として、<code>event</code> バインディングを内部で利用している
		<a href="/docs/click-binding"><code>click</code> バインディング</a> も同じくハンドラ関数を呼び出すのみで
		リンク先への画面遷移を行いません。
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
		通常、Knockout はイベントが上位の要素のイベントハンドラにバブリングすることを許可します。
		例えば、ある要素とその親要素がどちらも <code>mouseover</code> イベントをハンドリングするとします。
		このときこの要素がクリックされると、両方の要素にてハンドラが呼び出されます。
		必要であれば、 <code>(イベント名)Bubble</code> という追加バインディングに <code>false</code>
		を指定することでバブリングを抑止することができます。
	</p>

	<pre class="brush: html;">&lt;div data-bind=&quot;mouseover: myDivHandler&quot;&gt;
	&lt;button data-bind=&quot;mouseover: myButtonHandler, mouseoverBubble: false&quot;&gt;
		ここにカーソルをのせる
	&lt;/button&gt;
&lt;/div&gt;</pre>

	<p>
		この場合通常であれば <code>myButtonHandler</code> が最初に呼び出され、
		イベントは <code>myDivHandler</code> に遡及します。
		しかし、付与された <code>mouseoverBubble</code> バインディングの <code>false</code> 指定によってイベントの遡及は
		<code>myButtonHandler</code> を通過後に止まります。
	</p>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>
	
</article>

