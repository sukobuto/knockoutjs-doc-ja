<article>
	<h1>Observable Array</h1>
	
	<p>
		もしひとつのオブジェクトの変更を検知し、反応させたいのであれば <a href="observables">Observable</a> を使って下さい。
		もしコレクション (配列) の変更を検知し、反応させたいのであれば <code>observableArray</code> を使って下さい。
		これは複数の値を表示もしくは編集できるようにする場面や、アイテムの追加・削除により UI の繰り返し部分を出現・消滅させるといった場面で役に立ちます。
	</p>
	
	<h3 id="example">例</h3>
	<pre class="brush: js">	var myObservableArray = ko.observableArray(); // 最初は空の配列
	myObservableArray.push('何らかの値'); // 値を追加し、監視側に通知する</pre>
	
	<p>
			<code>observableArray</code> を UI にバインドし、ユーザに変更させる方法は
			<a href="http://knockoutjs.com/examples/simpleList.html" class="tips_link">the simple list example</a> をご覧ください。
	</p>
	
	<h3 id="key_point_an_observablearray_tracks_which_objects_are_in_the_array_not_the_state_of_those_objects">キーポイント</h3>
	<p><strong>observableArray が把握するのは“どのオブジェクトが配列に含まれるのか”であり、“それぞれのオブジェクトの状態”ではありません。</strong></p>
	
	<p>
		単に <code>observableArray</code> にオブジェクトを追加しただけでは、そのオブジェクトのプロパティは監視されません。
		もちろん、望むならばそれらのプロパティを Observable にすることができますが、それは別の課題です。
		<code>observableArray</code> は単純に、どのオブジェクトを保持しているかを把握し、追加・削除された際に監視側に通知します。
	</p>
	
	<h2 id="prepopulating_an_observablearray">observableArray に初期値を設定する</h2>
	
	<p>
		もし空の observableArray ではなく、初期値としてアイテムを設定したいのであれば、
		次の例のようにコンストラクタに配列を指定して下さい。
	</p>
	<pre class="brush: js;">// この Observable Array は最初から3つのオブジェクトを持ちます
var anotherObservableArray = ko.observableArray([
	{ name: "Bungle", type: "Bear" },
	{ name: "George", type: "Hippo" },
	{ name: "Zippy", type: "Unknown" }
]);</pre>
	
	<h2 id="reading_information_from_an_observablearray">observableArray から情報を取得する</h2>
	<p>
		舞台裏では、<code>observableArray</code> は実際のところ、値が配列である <a href="observables">Observable</a> です。
		(加えて、observableArray は後述の特徴を含みます。)<br>
		そのためほかの Observable と同様に、<code>observableArray</code> を引数なしで関数として呼び出すことで、背後にある JavaScript 配列 を取得することができます。
		そうしたら次のように、背後の配列から情報を取得できます。
	</p>
	<pre class="brush: js;">alert('配列の要素数: ' + myObservableArray().length);
alert('最初の要素:  ' + myObservableArray()[0]);</pre>
	
	<p>
		技術的には、JavaScript 標準の配列の機能は、背後にある配列を操作するために全て使うことができます。
		しかし通常は、よりよい選択肢があります。
		Knockout の observableArray は同様の機能を持っており、それらは以下の理由からより便利なのです。
	</p>
	
	<ol>
		<li>
			すべての対象ブラウザに対して互換性があります。
			(例えば、IE8以前では indexOf 関数が使えませんが、Knockout の indexOf は使えます。)
		</li>
		<li>
			<code>push</code> や <code>splice</code> などの配列の要素を操作する関数において、
			Knockout のメソッドは依存トラッキングメカニズムを自動的に引き起こすため、
			すべての登録されたリスナに対して変更が通知され、UI が自動更新されます。
		</li>
		<li>
			構文がより便利です。
			Knockout の <code>push</code> メソッドを呼ぶには、<code>myObservableArray.push(...)</code> と書けばOKです。
			<code>myObservableArray().push(...)</code> と書くよりかは多少よいでしょう。
		</li>
	</ol>
	
	<p>
		このページの残りの部分では、<code>observableArray</code> の配列の情報を読み書きする機能について説明します。
	</p>
	
	<h3 id="indexOf">indexOf</h3>
	
	<p>
		<code>indexOf</code> 関数は、配列の中で指定したパラメタに一致する要素が最初に見つかったインデックスを返却します。
		例えば、<code>myObservableArray.indexOf('あいう')</code> とした場合、<code>あいう</code> に一致する要素があれば
		その要素のゼロベース・インデックスを返します。一致する要素がみつからない場合は <code>-1</code> を返します。
	</p>
	
	<h3 id="slice">slice</h3>
	
	<p>
		<code>slice</code> 関数は、JavaScript 標準配列の <code>slice</code> 関数と同等です。
		(引数に与えられた開始インデックスと終了インデックスをもとに、配列から切り出した要素を返却します。)
		<code>myObservableArray.slice(...)</code> と <code>myObservableArray().slice(...)</code> の結果は等価です。
	</p>
	
	<h2 id="manipulating_an_observablearray">observableArray に対する変更</h2>
	
	<p>
		<code>observableArray</code> は配列の内容を変更し、かつリスナに通知するよく知られた形の関数群を提供しています。
	</p>
	
	<h3 id="pop_push_shift_unshift_reverse_sort_splice">pop, push, shift, unshift, reverse, sort, splice</h3>
	
	<p>これらの関数は全て JavaScript 標準配列の関数と同等の動作をし、かつリスナに対して変更を通知します。</p>
	
	<ul>
		<li><code>myObservableArray.push('新しい値')</code> 　 配列の末尾に要素を追加します。</li>
		<li><code>myObservableArray.pop()</code> 　 配列から末尾の要素を削除・返却します。</li>
		<li><code>myObservableArray.unshift('新しい値')</code> 　 配列の先頭に要素を挿入します。</li>
		<li><code>myObservableArray.shift()</code>　  配列から先頭の要素を削除・返却します。</li>
		<li><code>myObservableArray.reverse()</code> 　 配列の要素順を反転します。</li>
		<li>
			<code>myObservableArray.sort()</code> 　 配列の要素をソートします。
			<ul>
				<li>デフォルトで、アルファベット順もしくは数値順にソートします。</li>
				<li>
					オプションとして、ソートのための評価関数を指定できます。
					評価関数は配列から2つのオブジェクトを引数として受け取り、
					1つめのオブジェクトが小さければ負の数値を、2つめのオブジェクトが小さければ正の数値を、
					等価であれば 0 を返却するようにして下さい。
					例えば、'person' オブジェクトをラストネームでソートする場合、次のようにします。
					<pre class="brush: js;">myObservableArray.sort(function(left, right) {
	return left.lastName == right.lastName
		? 0 : (left.lastName < right.lastName ? -1 : 1);
});</pre>
				</li>
			</ul>
		</li>
		<li>
			<code>myObservableArray.splice(startIndex, length)</code> 　 startIndex に位置する要素から length 個分の要素を配列から削除・返却します。
			例えば、<code>myObservableArray.splice(1, 3)</code> とすると インデックス 1 から続く3つの要素 (つまり2, 3, 4 番目の要素) を削除し、配列として返却します。
		</li>
	</ul>
	
	<p>
		これらの <code>observableArray</code> の関数について詳しく知りたい場合は、同等の
		<a href="https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array#Methods_2">JavaScript 標準の配列関数のドキュメント</a>を参照して下さい。
	</p>
	
	<h3 id="remove_and_removeall">remove と removeAll</h3>
	<p>
		<code>observableArray</code> には JavaScript 標準配列にはないより便利なメソッドがあります。
	</p>
	<ul>
		<li><code>myObservableArray.remove(someItem)</code> <br><code>someItem</code> に一致する要素を全て削除し、それらを配列として返却します。</li>
		<li><code>myObservableArray.remove(function(item) { return item.age < 18 } )</code> <br>プロパティ <code>age</code> が18 より小さい要素を全て削除し、それらを配列として返却します。</li>
		<li><code>myObservableArray.removeAll(['Chad', 132, undefined])</code> <br><code>'Chad', 123, undefined</code> に一致する要素を全て削除し、それらを配列として返却します。</li>
		<li><code>myObservableArray.removeAll()</code> <br>全ての要素を削除し、それらを配列として返却します。</li>
	</ul>
	
	<h3 id="destroy_and_destroyall_note_usually_relevant_to_ruby_on_rails_developers_only">destroy と destroyAll (※通常、Ruby on Rails を使用した開発にのみ関係します)</h3>
	<p>
		<code>destroy</code> 関数と <code>destroyAll</code> 関数は主に Ruby on Rails を使用した開発に対する利便性のためにあります。
	</p>
	<ul>
		<li><code>myObservableArray.remove(someItem)</code> <br><code>someItem</code> に一致するオブジェクトを全て検索し、それらに <code>_destroy</code> という特別なプロパティを値 <code>true</code> とともに付与します。</li>
		<li><code>myObservableArray.remove(function(item) { return item.age < 18 } )</code> <br>プロパティ <code>age</code> が18 より小さいオブジェクトを全て検索し、それらに <code>_destroy</code> という特別なプロパティを値 <code>true</code> とともに付与します。</li>
		<li><code>myObservableArray.removeAll(['Chad', 132, undefined])</code> <br><code>'Chad', 123, undefined</code> に一致するオブジェクトを全て検索し、それらに <code>_destroy</code> という特別なプロパティを値 <code>true</code> とともに付与します。</li>
		<li><code>myObservableArray.removeAll()</code> <br>全てのオブジェクトを検索し、それらに <code>_destroy</code> という特別なプロパティを値 <code>true</code> とともに付与します。</li>
	</ul>
	
	<p>
		<code>_destroy</code> とはなんぞや?という話ですが、 Rails デベロッパーにとってのみ本当におもしろいものです。
		Rails の便利なところは、JSON オブジェクトグラフをアクションに渡すと、
		フレームワークが自動的に ActiveRecord オブジェクトグラフに変換しデータベースに保存してくれることです。
		オブジェクトがデータベースに既に存在するかを把握しており、INSERT文 または UPDATE文を正しく発行してくれます。
		フレームワークにレコードの削除を伝えるには、<code>_destroy</code> を <code>true</code> にして削除フラグを立てるだけです。
	</p>
	
	<p>
		留意点として、Knockout は <code>foreach</code> バインディングをレンダリングする際、
		<code>_destroy</code> プロパティが <code>true</code> になっているオブジェクトを自動的に隠します。
		したがって、“削除”ボタンの類で <code>destroy(someItem)</code> を発動させることで、
		即座に UI から対象のアイテムを消滅させることができます。
		その後、JSON オブジェクトグラフを Rails に送信することで、それらのアイテムは
		データベースからも削除されるという算段です。
		(配列のその他の要素は、通常通り挿入または更新されます)
	</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/observableArrays.html">こちら</a></div>
	
</article>