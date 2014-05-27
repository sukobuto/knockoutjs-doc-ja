<article>

	<h1>Computed Observable</h1>
	<p>
		<strong>ファーストネーム</strong> と <strong>ラストネーム</strong> の Observable プロパティがあるとします。フルネームを画面に表示するにはどうしましょう？
		そこで Computed Observable の出番です。Computed Observable は一つ以上の Observable に依存する function であり、
		依存している Observable の変更により自動的に更新されます。
	</p>

	<p>例えば、次のような ViewModel クラスがあるとします。</p>
	<pre class="brush: js;">function AppViewModel() {
	this.firstName = ko.observable('ボブ');
	this.lastName = ko.observable('スミス');
}</pre>

	<p>次のように、フルネームを返す Computed Observable を追加することができます。</p>
	<pre class="brush: js;">function AppViewModel() {
	// ... firstName と lastName はそのままに
	
	this.fullName = ko.computed(function() {
		return this.firstName() + " " + this.lastName();
	}, this);
}</pre>

	<p>これをUIエレメントにバインドできます。</p>
	<pre class="brush: html;">お名前： &lt;span data-bind="text: fullName"&gt;&lt;/span&gt;</pre>

	<p>
		これで、<code>firstName</code> か <code>lastName</code> の何れかに変更があれば更新されます。
		(ko.computed に渡した評価関数は、依存しているいずれかのプロパティが変更される度に呼び出されます。
		評価関数の返却値もまた、その都度UIエレメントや別の Computed Observable など監視側に通知されます。)
	</p>

	<h3 id="managing_this">'this' の管理</h3>
	<p><em>入門者の方は、例に示すのと同じコーディングパターンに従うのであれば、このセクションをスキップして頂いても問題ありません。次の「簡略化のための常套手段」の例を参考にしてください。</em></p>

	<p>
		上記の例で、<code>ko.computed</code> の２つ目の引数 ( <code>this</code> を渡していること ) について疑問を持たれた方がいらっしゃるかと思います。
		これは Computed Observable 内部で使用される this を指定しています。
		この <code>this</code> を渡さない場合、 <code>this.firstName()</code> や <code>this.lastName()</code> を参照できなくなってしまいます。
		JavaScript に精通している方にとっては明白ですが、そうでない方にとってはやや不可思議に見えるかもしれません。
		(C# や Java ではプログラマが <code>this</code> の値を設定することはありません。
		しかし JavaScript では、function というものが本質的にはどんなオブジェクトにも属さないため、<code>this</code> の値を設定できるようになっているのです。)
	</p>

	<h3>簡略化のための常套手段</h3>
	<p>
		あらゆる場面で <code>this</code> に気を払うのは骨が折れます。これを簡略化するための常套手段は、
		ViewModel のコンストラクタで最初に <code>this</code> の参照を別の変数にコピーしておくことです。
		( 伝統的な例: <code>self</code> )
		これで、ViewModel 内で一貫して <code>self</code> を使うことができます。
	</p>
	<pre class="brush: js">function AppViewModel() {
	var self = this;
	
	self.firstName = ko.observable('ボブ');
	self.lastName = ko.observable('スミス');
	self.fullName = ko.computed(function() {
		return self.firstName() + " " + self.lastName();
	});
}</pre>
	<p>
		<code>self</code> は function クロージャの内部で保持されるため、<code>ko.computed</code> に渡した評価関数のようなネストされたどの function でも使えます。
		この常套手段は <a href="http://knockoutjs.com/examples/">Live Examples</a> で紹介されているように、イベントハンドラ等を追加した際にも非常に便利です。
	</p>

	<h3>依存チェーンは続くよ　(あなたが望む限り)　どこまでも</h3>
	<p>次の例のように、Computed Observable を繋いでいくことができます。</p>
	<ul>
		<li>
			アイテムのセットを表す配列の Observable 「<code>items</code>」
		</li>
		<li>
			選択されたアイテムのインデックスを全て保持する配列の Observable 「<code>selectedIndexes</code>」
		</li>
		<li>
			<code>items</code> の中から <code>selectedIndexes</code> に一致するアイテムを全て抜き出して、配列として返す<br>
			Computed Observable 「<code>selectedItems</code>」
		</li>
		<li>
			<code>selectedItems</code> に１つ以上のアイテムが含まれるか否かを<br>
			<code>true</code> か <code>false</code> で返す	Computed Observable など...<br>
			...ボタンなどのUIエレメントを、このプロパティで 使用可/不可 をスイッチングできます。
		</li>
	</ul>
	<p>
		こうすることで、<code>items</code> や <code>selectedIndexes</code> の変更は波紋となって Computed Observable のチェーンを伝っていき、
		それらの変更は UIに反映されます。とても整然としていてエレガントです。
	</p>

	<h1>書き込み可能な Computed Observable</h1>
	<p><em>入門者の方はこのセクションを読み飛ばしていただいて問題ありません。書き込み可能な Computed Observable はかなり発展的な技法で、必要とされる場面はそう多くありません。</em></p>

	<p>
		ここまで読まれた方は、Computed Observable は 他の Observable の値を元に、計算された値を持っているということがお分かりいただけたと思います。
		つまり、通常の Computed Observable は読み取り専用です。
		意外に思われるかもしれませんが、Computed Observable を書き込み可能にすることができます。
		方法は、「書き込まれた値に応じて処理をするコールバック」を設定するだけです。
	</p>
	<p>
		読み取りと書き込みを処理する独自ロジックを介在させることによって、書き込み可能 Computed Observable はあたかも単なる Observable のように使うことができるようになります。
		これは、幅広い用途で使えるパワフルな特徴です。単なる Observable と同じように、メソッドチェーンで複数の Observable プロパティを変更することだってできます。<br>
		例： <code>myViewModel.fullName('ジョー スミス').age(50)</code>
	</p>

	<h3>例1: 入力を分解する</h3>
	<p>
		"ファーストネーム + ラストネーム = フルネーム" の例で考えてみましょう。フルネームは <strong>ファーストネーム</strong> と <strong>ラストネーム</strong>　に分解できます。
		ユーザが <code>fullName</code> に対して入力したら、その値を背後の <code>firstName</code> と <code>lastName</code>
		にマッピングすれば良いのです。
	</p>
	<pre class="brush: js;">function MyViewModel() {
	this.firstName = ko.observable('Planet');
	this.lastName = ko.observable('Earth');
	
	this.fullName = ko.computed({
		read: function() {
			return this.firstName() + " " + this.lastName();
		},
		write: function(value) {
			var lastSpacePos = value.lastIndexOf(" ");
			if (lastSpacePos &gt; 0) { // スペースを含まない値は無視する
				this.firstName(value.substring(0, lastSpacePos)); // "firstName" を更新
				this.lastName(value.substring(lastSpacePos + 1)); // "lastName" を更新
			}
		},
		owner: this
	});
}

ko.applyBindings(new MyViewModel());</pre>

	<p>
		この例では、<code>write</code> コールバックがテキストの入力を受け付け、 "ファーストネーム" と "ラストネーム" に分割し、
		それぞれ背後にある Observable に書き戻しています。
		この ViewModel を、次のように DOM にバインドできます。
	</p>
	<pre class="brush: html">&lt;p&gt;ファーストネーム: &lt;span data-bind="text: firstName"&gt;&lt;/span&gt;
&lt;p&gt;ラストネーム: &lt;span data-bind="text: lastName"&gt;&lt;/span&gt;
&lt;h2&gt;こんにちわ、&lt;input data-bind="value: fullName"/&gt;!&lt;/h2&gt;</pre>

	<p>
		これは <a href="/tips/helloWorld">Hello World</a> の例の逆で、
		ファーストネームとラストネームは編集できませんが、結合されたフルネームは編集できます。
	</p>
	<p>
		前述の ViewModel コードでは、単一の引数によって Computed Observable を初期化する方法を紹介しました。
		使用可能なすべてのオプションを知りたい方は <a href="#computed_observable_reference">Computed Observable リファレンス</a> をご覧ください。
	</p>

	<h3>例２: コンバーター</h3>
	<p>
		ときには、保存されているものと異なる書式でデータを表示したいことがあると思います。
		例えば、ある価格情報が float 値で保存されているけれど、ユーザには固定少数点数で通貨記号を含めた編集を許可したいといった場合です。
		書き込み可能 Computed Observable を使えば、フォーマットされた価格を表現し、背後にある float 値に対して受け付けた入力をマッピングすることができます。
	</p>
	<pre class="brush: js;">function MyViewModel() {
	this.price = ko.observable(25.99);
	
	this.formattedPrice = ko.computed({
		read: function() {
			return '$' + this.price().toFixed(2);
		},
		write: function(value) {
			// 不要な文字を取り除き float 値にパース
			value = parseFloat(value.replace(/[^\.\d]/g, ""));
			// それを背後の Observable "price" に書き込む
			this.price(isNaN(value) ? 0 : value);
		},
		owner: this
	});
}

ko.applyBindings(new MyViewModel());</pre>

	<p>あとはいつものようにバインドするだけです。</p>
	<pre class="brush: html">&lt;p&gt;価格を入力: &lt;input data-bind="value: formattedPrice"/&gt;&lt;p&gt;</pre>

	<p>
		これで、ユーザが新しい価格を入力する度に、どのような書式で入力したかに関わらず、
		通貨記号と小数点以下2桁の形式に更新されます。
		これはユーザにとって優れたエクスペリエンスを提供します。なぜなら、ユーザは、ソフトウェアがそれぞれ
		どのような書式で入力すればよいかを知らなくても使うことができるからです。
		小数点以下2桁以上が入力できないことは、それを入力してみれば (自動的に削除されることから) わかります。
		同じく <code>write</code> コールバックにてマイナス記号その他を取り除いているため、マイナスの値が入力できないこともわかります。
	</p>

	<h3>例３: フィルタリングと検証</h3>
	<p>
		例１で、空白を含まない値は無視しています。
		つまり基準に合致しない場合に、受け付けた入力を背後にある Observable に書き込まないという、いわばフィルタのようなことができます。
	</p>
	<p>
		この手順をさらに進めてみましょう。
		<code>isValid</code> フラグを設けることで、最後に入力された値が条件を満たさないときだけ、エラーメッセージを表示するようにできます。
		バリデーションを実現するより簡単な方法がありますが (後述)、まずは次の ViewModel でメカニズムを説明致します。
	</p>
	<pre class="brush: js;">function MyViewModel() {
	this.acceptedNumericValue = ko.observable(123);
	this.lastInputWasValid = ko.observable(true);
	
	this.attemptedValue = ko.computed({
		read: this.acceptedNumericValue,
		write: function(value) {
			if (isNaN(value))
				this.lastInputWasValid(false);
			else {
				this.lastInputWasValid(true);
				this.acceptedNumericValue(value); // 背後のプロパティに書き込む
			}
		},
		owner: this
	});
}

ko.applyBindings(new MyViewModel());</pre>

	<p>View: </p>
	<pre class="brush: html;">&lt;p&gt;数値を入力: &lt;input data-bind="value: attemptedValue"/&gt;&lt;/p&gt;
&lt;div data-bind="visible: !lastInputWasValid()"&gt;数値ではありません!&lt;/div&gt;</pre>

	<p>
		<code>acceptedNumericValue</code> は数値のみを格納しており、数値以外の値の入力は
		<code>acceptedNumericValue</code> は更新せずにエラーメッセージを表示させるきっかけとなります。
	</p>

	<p>
		※ 上記の例のように、入力が数値であるかを検証するような微々たる用途では、このテクニックは大げさすぎます。
		jQuery Validation の number class を使うほうがはるかに単純になります。
		<a href="http://knockoutjs.com/examples/gridEditor.html">grid editor デモ</a> で紹介しているように、Knockout と jQuery Validation はとても相性が良いです。
		それは別として、前述の ViewModel の例では「どんなフィードバックを出現させるかをコントロールする独自ロジック」を使ったフィルタリングとバリデーションの
		汎用的なメカニズムを説明させていただきました。
		jQuery Validation が解決するものよりもより複雑なシナリオに対応することができます。
	</p>

	<h1>依存トラッキングの仕組み</h1>
	<p><em>
			入門者の方にとってこのセクションは必須ではありません。
			ここでは、Knockout が依存をトラッキング (把握) する仕組みおよび、UI上の正しい部品が更新される仕組みを解説します。
		</em></p>

	<p>非常にシンプルで素敵です。トラッキングアルゴリズムは以下のようになっています。</p>
	<ol>
		<li>
			<code>ko.computed(evaluator)</code> のようにしてComputed Observable が宣言されると、
			Knockout はその初期値を取得するため、直ちに引数として受け取った評価関数を呼び出します。
		</li>
		<li>
			評価関数を実行している間、Knockout は 評価関数内部で読み取られたすべての Observable (または Computed Observable) をログに記録します。
		</li>
		<li>
			評価関数の実行が完了したら、Knockout はそれぞれの Observable (またはComputed Observable) に対してサブスクリプションを設定します。
			このサブスクリプションのコールバックでは評価関数を再び呼び出し、ステップ１からの処理を繰り返します。
			(古いサブスクリプションは破棄し、適用されないようにします。)
		</li>
		<li>
			Knockout は Computed Observable のすべての購読者に対し、新しい値を通知します。
		</li>
	</ol>

	<p>
		即ち、Knockout は評価関数を最初に実行した時の依存だけを検出しているのではありません。毎回検出しているのです。
		これは、動的な依存を実現できるということを意味します。例えば A,B,C の３つの Observable があり、 
		A は B または C のどちらに依存するかを決定するといった仕様の Computed Observable では、
		A が変更された場合と、B もしくは C のうち現在選択されている方が変更された場合にのみ再評価されます。
		あなたが依存を定義する必要はありません。依存はコードの実行時に自動推論されるのです。
	</p>

	<p>依存を動的に選択する例: 現在選択されていないプロパティに変更があっても評価関数は実行されない。</p>
	<pre class="brush: js;">function MyViewModel() {
	this.A = ko.observable(true); // 依存対象を決定するプロパティ
	this.B = ko.observable(100);
	this.C = ko.observable(200);
	
	// 依存対象が動的に選択される Computed Observable
	this.DynamicDepend = ko.computed(function() {
		if (this.A()) return "B が選択されました。" + this.B();
		else return "C が選択されました。" + this.C();
	}, this);
}</pre>

	<h3 id="controlling_dependencies_using_peek">Peek で依存をコントロールする</h3>
	<p>
		Knockout では通常、あなたが望んでいるかどうかに関わらず、全ての依存がトラッキングされます。
		しかし時には、どの Observable が Computed Observable を更新させるのかをコントロールする必要があります。
		とりわけ、Ajax リクエストを発行するといったアクションを含む Computed Observable などでは、依存対象をコントロールする必要が出てきます。
		<code>peek</code> 関数は、依存を生成することなくプロパティにアクセスすることを可能にします。
	</p>

	<p>
		次の例をもとに説明します。
		２つの Observable プロパティから Ajax リクエストを発行し、<code>currentPageData</code> という
		Observable プロパティを更新する Computed Observable があるとします。
		この Computed Observable は <code>pageIndex</code> が変更された時に更新されますが、
		<code>selectedItem</code> の変更については無視します。
		<code>peek</code> を使ってアクセスしているからです。
		このケースでは、<code>selectedItem</code> の現在の値はあくまでも「新しいデータセットが読み込まれたとき」
		にトラッキングされる値として使用しています。
	</p>

	<pre class="brush: js;">ko.computed(function() {
	var params = {
		page: this.pageIndex(),
		selected: this.selectedItem.peek()
	};
	// jQuery の getJSON メソッド で Ajax リクエスト → 取得したデータを currentPageData に格納
	$.getJSON('/Some/Json/Service', params, this.currentPageData);
}, this);</pre>

	<p>
		※ もし Computed Observable が高頻度で更新されるのを抑止したいのであれば、
		<a href="throttle-extender">スロットル拡張</a> を使用してください。
	</p>

	<h3>※ なぜ循環依存は意味を成さないのか</h3>
	<p>
		Computed Observable はいくつかの Observable インプットを単一の Observable アウトプットに対応付けるための機能です。
		そのため、依存に循環を含むことは無意味です。
		循環は再帰とは異なります。エクセルの2つのセルが関数により互いに参照しあっているようなものです。
		無限な評価ループを引き起こしてしまいます。
	</p>

	<p>
		Knockout は依存グラフに循環があったとき、どうするでしょうか。
		次のルールにより、強制的に無限ループを回避します。
	</p>

	<p><strong>既に実行中の評価関数は呼び出さない</strong></p>

	<p>
		このルールにより、循環を含むコードでは思いがけない影響を受けます。
		少なくとも、２つのシチュエーションに関係します。
		1つは、2つの Computed Observable が互いに依存している場合です。
		(片方もしくは両方の Computed Observable に <code>deferEvaluation</code> が使われているのであれば可能です。)
		2つ目は、Computed Observable が依存している別の Observable を自ら変更する場合です。(直接依存していようが、依存チェーンを介して依存していようが同じです。)
		これらのパターンのうち一つでも当てはまり、循環依存を完全に回避したいのであれば、前述の <code>peek</code> 関数を使用して下さい。
	</p>

	<h1>プロパティが Computed Observable かどうかを判定する</h1>

	<p>
		Computed Observable であるかどうかをプログラム上で判定できると便利なことがあります。
		Knockout は <code>ko.isComputed</code> というユーリティティ関数を提供しています。
		例えば Computed Observable の値を除外してサーバに送信したい場合などでは、次のようにします。
	</p>

	<pre class="brush: js;">for (var prop in myObject) {
	if (myObject.hasOwnProperty(prop) &amp;&amp; !ko.isComputed(myObject[prop])) {
		result[prop] = myObject[prop];
	}
}</pre>

	<p>
		ほかにも、Knockout は Observable および Computed Observable のために次ような関数を用意しています。
	</p>

	<ul>
		<li>
			<code>ko.isObservable</code> &nbsp;-&nbsp; Observable, ObservableArray およびすべての Computed Observable であれば true を返します。
		</li>
		<li>
			<code>ko.isWriteableObservable</code> &nbsp;-&nbsp; Observable, ObservableArray, および書き込み可能 Computed Observable であれば true を返します。
		</li>
	</ul>

	<h1 id="computed_observable_reference">Computed Observable リファレンス</h1>

	<p>Computed Observable は次の形式により生成できます。</p>

	<ol>
		<li>

			<code>ko.computed( evaluator [, targetObject, options] )</code> &nbsp;—&nbsp; この形式はほとんどの利用方法に対応しています。
			<ul>
				<li>
					<code>evaluator</code> &nbsp;—&nbsp; Computed Observable の現在の値を評価するための関数です。
				</li>
				<li>
					<code>targetObject</code> &nbsp;—&nbsp; この値が与えられた場合、評価関数を実行する際の <code>this</code> の値として定義します。
					詳細は <a href="#managing_this">'this' の管理</a> のセクションを読んで下さい。
				</li>
				<li>
					<code>options</code> &nbsp;—&nbsp; 付加的なプロパティを含むオブジェクトです。内容は。次の完全なリストを参照して下さい。
				</li>
			</ul>
		</li>
		<li>

			<code>ko.computed( options )</code> &nbsp;—&nbsp; 次のプロパティを含む JavaScript オブジェクトにより生成するシングルパラメタ形式です。
			<ul>
				<li>
					<code>read</code> &nbsp;—&nbsp; (必須) Computed Observable の現在の値を評価するための関数です。
				</li>
				<li>
					<code>write</code> &nbsp;—&nbsp; (任意) 与えられた場合、Computed Observable は書き込み可能となります。
					この引数は、Computed Observable に値を書き込む関数です。
					典型的には、受け取った値をもとに背後にあるいくつかの Observable に値を書き込むためのカスタムロジックとなります。
				</li>
				<li>
					<code>owner</code> &nbsp;—&nbsp; (任意) 与えられた場合、<code>read</code> および <code>write</code> コールバック実行の際
					<code>this</code> の値として定義します。
				</li>
				<li>
					<code>deferEvaluation</code> &nbsp;—&nbsp; (任意) このオプションが true であれば、
					この Computed Observable はアクセスされるまで現在の値の評価を行わなくなります。
					デフォルトでは、作成された時点から即時評価されます。
				</li>
				<li>
					<code>disposeWhen</code> &nbsp;—&nbsp; (任意) このComputed Observable を破棄するかどうかを判定する評価関数です。
					与えられた場合、この関数は再評価の度に Computed Observable が破棄されるべきかを判定するために実行されます。
					この関数が <code>true</code> またはそれに相当する値を返却した場合、Computed Observable は破棄されます。
				</li>
				<li>
					<code>disposeWhenNodeIsRemoved</code> &nbsp;—&nbsp; (任意) DOMノードを指定します。
					与えられた場合、指定された DOM ノードが Knockout により削除された時に、この Computed Observable は破棄されます。
					このオプションは、template およびフロー制御バインディングによってノードが削除された時に
					バインディングで使用されていた Computed Observable を破棄する目的で使います。
				</li>
			</ul>

		</li>
	</ol>
	
	<p>Computed Observable は次の関数を提供しています。</p>
	
	<ul>
		<li>
			<code>dispose()</code> &nbsp;—&nbsp; 手動により Computed Observable を破棄し、依存のためのすべてのサブスクリプションをクリアします。
			これは Computed Observable が更新されるのを止めたい場合や、破棄されない Observable への依存をもつ
			Computed Observable に使用されているメモリを解放したい場合に便利な機能です。
		</li>
		<li>
			<code>extend( extenders )</code> &nbsp;—&nbsp; Computed Observable に、与えられた<a href="extenders">拡張</a>を適用します。
		</li>
		<li>
			<code>getDependenciesCount()</code> &nbsp;—&nbsp; Computed Observable が現在依存している対象の数を返却します。
		</li>
		<li>
			<code>getSubscriptionsCount()</code> &nbsp;—&nbsp; Computed Observable に対するサブスクリプションの現在の数を返却します。
		</li>
		<li>
			<code>isActive()</code> &nbsp;—&nbsp; Computed Observable が更新される可能性がある場合、<code>true</code> を返却します。
			ほかの Observable に対する依存を持たない Computed Observable であれば <code>false</code> を返却します。
		</li>
		<li>
			<code>peek()</code> &nbsp;—&nbsp; 依存を生成せずに、Computed Observable の現在の値を取得します。
			(<a href="#controlling_dependencies_using_peek">peek</a> のセクションを参照して下さい。)
		</li>
		<li>
			<code>subscribe( callback [,callbackTarget, event] )</code> &nbsp;—&nbsp; Computed Observable からの変更通知を受け取るために
			<a href="observables#explicitly_subscribing_to_observables">サブスクリプションを手動で登録</a>します。
		</li>
	</ul>
	
	<h1>Dependent Observable はどうした?</h1>
	<p>
		Knockout 2.0 より前のバージョンにおいて、Computed Observable は <em>Dependent Observable</em> として知られていましたが、
		バージョン2.0 にて、<code>ko.dependentObservable</code> を <code>ko.computed</code> に改名しました。
		理由は「説明しやすい」「言いやすい」「入力しやすい」からです。
		ただし既存のコードはそのまま動作しますので心配しないでください。
		ランタイム上で、<code>ko.dependentObservable</code> は <code>ko.computed</code> の別名として定義されており、
		この2つは同義です。
	</p>

	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/computedObservables.html">こちら</a></div>
</article>
