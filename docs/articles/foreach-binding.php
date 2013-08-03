<article>
	
	<h1>"foreach" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>foreach</code> バインディングは配列の各要素のためのマークアップセクションを繰り返し生成し、
		それぞれのマークアップのコピーに対し、対応する配列要素をバインドします。
		主にリストやテーブルをレンダリングする際に使用します。
	</p>
	
	<p>
		配列が <a href="observableArrays">Observable Array</a> である場合、
		要素を追加・削除・並べ替えすると、このバインディングは効率良く UI を更新します。
		具体的には、他の DOM エレメントに影響を与えることなくマークアップのコピーを挿入・削除、
		または既存の DOM エレメントを並べ替えるようになっています。
		この仕組みは、配列が変更されたときに <code>foreach</code> の出力を全てやり直すよりはるかに高速です。
	</p>
	
	<p>
		もちろん、<code>foreach</code> バインディングをいくつでも入れ子にすることができますし、
		<a href="if-binding"><code>if</code></a> や
		<a href="with-binding"><code>with</code></a>
		のような別のフロー制御バインディングと同時に使うこともできます。
	</p>
	
	<h3 id="example_1_iterating_over_an_array">例1: 配列を反復処理する</h3>
	<p>配列の各要素をリードオンリーなテーブルの各行にするために <code>foreach</code> を使用する例です。</p>
	<pre class="brush: html;">&lt;table&gt;
	&lt;thead&gt;
		&lt;tr&gt;&lt;th&gt;First name&lt;/th&gt;&lt;th&gt;Last name&lt;/th&gt;&lt;/tr&gt;
	&lt;/thead&gt;
	&lt;tbody data-bind=&quot;foreach: people&quot;&gt;
		&lt;tr&gt;
			&lt;td data-bind=&quot;text: firstName&quot;&gt;&lt;/td&gt;
			&lt;td data-bind=&quot;text: lastName&quot;&gt;&lt;/td&gt;
		&lt;/tr&gt;
	&lt;/tbody&gt;
&lt;/table&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	ko.applyBindings({
		people: [
			{ firstName: 'Bert', lastName: 'Bertington' },
			{ firstName: 'Charles', lastName: 'Charlesforth' },
			{ firstName: 'Denise', lastName: 'Dentiste' }
		]
	});
&lt;/script&gt;</pre>
	
	<h3 id="example_2_live_example_with_add_remove">例2: 追加・削除のデモ</h3>
	<p>Observable Array を使うことで変更が同期される例です。</p>
	
	<div class="demo" id="demo_1">
		<h4>名簿</h4>
		<ul data-bind="foreach: people">
			<li>
				index: <span data-bind="text: $index"> </span>
				名前: <span data-bind="text: name"> </span>
				<a href="#" data-bind="click: $parent.removePerson">削除</a>
			</li>
		</ul>
		<button data-bind="click: addPerson">追加</button>
	</div>
	<script type="text/javascript">
		ko.applyBindings(
			(new function() {
				var self = this;
				
				self.people = ko.observableArray([
					{ name: 'Bert' },
					{ name: 'Charles' },
					{ name: 'Denise' }
				]);
				
				self.addPerson = function() {
					self.people.push({ name: new Date() + " に追加" });
				};
				
				self.removePerson = function() {
					self.people.remove(this);
				};
			}()),
			document.getElementById('demo_1')
		);
	</script>
	
	<h4>ソースコード: View</h4>
	<pre class="brush: html;">&lt;h4&gt;名簿&lt;/h4&gt;
&lt;ul data-bind=&quot;foreach: people&quot;&gt;
	&lt;li&gt;
		index: &lt;span data-bind=&quot;text: $index&quot;&gt; &lt;/span&gt;
		名前: &lt;span data-bind=&quot;text: name&quot;&gt; &lt;/span&gt;
		&lt;a href=&quot;#&quot; data-bind=&quot;click: $parent.removePerson&quot;&gt;削除&lt;/a&gt;
	&lt;/li&gt;
&lt;/ul&gt;
&lt;button data-bind=&quot;click: addPerson&quot;&gt;追加&lt;/button&gt;</pre>
	
	<h4>ソースコード: ViewModel</h4>
	<pre class="brush: js;">funtion AppViewModel() {
	var self = this;
	
	self.people = ko.observableArray([
		{ name: 'Bert' },
		{ name: 'Charles' },
		{ name: 'Denise' }
	]);
	
	self.addPerson = function() {
		self.people.push({ name: new Date() + &quot; に追加&quot; });
	};
	
	self.removePerson = function() {
		self.people.remove(this);
	};
}

ko.applyBindings(new AppViewModel());</pre>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				反復処理させたい配列を渡して下さい。
				バインディングは各要素のためのマークアップセクションを出力します。
			</p>
			<p>
				別の方法として、<code>data</code> プロパティに反復処理させたい配列を指定した
				JavaScript オブジェクトリテラルを渡すことができます。
				オブジェクトリテラルには <code>afterAdd</code> や <code>includeDestroyed</code>
				というオプションを含めることができます。詳細は<a href="#note_7_postprocessing_or_animating_the_generated_dom_elements">後述の例</a>を参照して下さい。
			</p>
			<p>
				配列が Observable Array である場合、<code>foreach</code> バインディングは要素が変更される度に
				DOM 内のマークアップセクションを追加・削除することにより更新します。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_1_referring_to_each_array_entry_using_data">(注1) $data で配列の各要素を参照する</h3>
	
	<p>
		上記の例で示したように、<code>foreach</code> ブロック内部のバインディングでは配列要素のプロパティを使うことができます。
		例えば、<a href="#example_1_iterating_over_an_array">例1</a> では配列の各要素にある
		<code>firstName</code> と <code>lastName</code> の2つのプロパティを参照しています。
	</p>
	<p>
		しかし、配列の要素のプロパティではなく、要素自体を参照したい場合はどうでしょう。
		その場合、<a href="binding-context">特別なコンテキスト変数</a> である <code>$data</code> を使います。
		<code>foreach</code> ブロック内部において、<code>$data</code> は「現在のアイテム」を意味します。
	</p>
	<pre class="brush: html;">&lt;ul data-bind=&quot;foreach: months&quot;&gt;
	&lt;li&gt;
		現在のアイテム: &lt;b data-bind=&quot;text: $data&quot;&gt;&lt;/b&gt;
	&lt;/li&gt;
&lt;/ul&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	ko.applyBindings({
		months: [ 'Jan', 'Feb', 'Mar', 'etc' ]
	});
&lt;/script&gt;</pre>
	
	<p>
		望むならば、各要素のプロパティを参照する際に <code>$data</code> を使用することもできます。
		例えば、<a href="#example_1_iterating_over_an_array">例1</a> の一部を次のように書き換えることができます。
	</p>
	<pre class="brush: html;">&lt;td data-bind=&quot;text: $data.firstName&quot;&gt;&lt;/td&gt;</pre>
	
	<p>
		でもそれはマストではありません。なぜなら、デフォルトで常に <code>$data</code> のコンテキスト内で評価されるからです。
	</p>
	
	<h3 id="note_2_using_index_parent_and_other_context_properties">(注2) $index, $parent, およびその他のコンテキスト変数</h3>
	
	<p>
		上記の <a href="#example_2_live_example_with_add_remove">例2</a> に見えるように、
		<code>$index</code> で現在の配列要素のゼロベース・インデックスを参照することができます。
		<code>$index</code> は Observable であり、アイテムのインデックスが変更されると更新されます。
		(つまり、配列に要素が追加・削除されたとき $index がバインドされている部分が更新されます。)
	</p>
	<p>同様に、<code>$parent</code> で <code>foreach</code> の外側のデータにアクセスできます。</p>
	<pre class="brush: html;">&lt;h1 data-bind=&quot;text: blogPostTitle&quot;&gt;&lt;/h1&gt;
&lt;ul data-bind=&quot;foreach: likes&quot;&gt;
	&lt;li&gt;
		&lt;b data-bind=&quot;text: name&quot;&gt;&lt;/b&gt; は
		&lt;b data-bind=&quot;text: $parent.blogPostTitle&quot;&gt; が好きです。 &lt;/b&gt;
	&lt;/li&gt;
&lt;/ul&gt;</pre>
	
	<p>
		<code>$index</code> や <code>$parent</code> およびその他のコンテキストプロパティについての詳細は
		<a href="binding-context">バインディングコンテキストプロパティ</a> を参照してください。
	</p>
	
	<h3 id="note_3_using_as_to_give_an_alias_to_foreach_items">(注3) “as”を使って foreach アイテムに別名をつける</h3>
	<p>
		注1で述べたように、配列の各要素にアクセスするのには <code>$data</code> <a href="binding-context">コンテキスト変数</a> を使います。
		この変数に対して、次のように <code>as</code> オプションを使って別名をつけたほうがやりやすい場面があります。
	</p>
	<pre class="brush: html;">&lt;ul data-bind=&quot;foreach: { data: people, as: 'person' }&quot;&gt;&lt;/ul&gt;</pre>
	
	<p>
		この <code>foreach</code> ループ内部のどこでも、<code>person</code> を使って配列の現在の要素にアクセスできます。
		特に便利な場面は、入れ子になった <code>foreach</code> ブロックで、下層のブロックから上層のブロックのアイテムにアクセスしたい場合です。
	</p>
	<pre class="brush: html;">&lt;ul data-bind=&quot;foreach: { data: categories, as: 'category' }&quot;&gt;
	&lt;li&gt;
		&lt;ul data-bind=&quot;foreach: { data: items, as: 'item' }&quot;&gt;
			&lt;li&gt;
				&lt;span data-bind=&quot;text: category.name&quot;&gt;&lt;/span&gt;:
				&lt;span data-bind=&quot;text: item&quot;&gt;&lt;/span&gt;
			&lt;/li&gt;
		&lt;/ul&gt;
	&lt;/li&gt;
&lt;/ul&gt;
	
&lt;script&gt;
	var viewModel = {
		categories: ko.observableArray([
			{ name: 'Fruit', items: [ 'Apple', 'Orange', 'Banana' ] },
			{ name: 'Vegetables', items: [ 'Celery', 'Corn', 'Spinach' ] }
		])
	};
	ko.applyBindings(viewModel);
&lt;/script&gt;</pre>
	
	<p>
		メモ：<code>as</code> に渡す値は文字列リテラルですので、シングルクォートで囲むことを忘れないで下さい。
		( × : <code>as: category</code>   ○ : <code>as : 'category'</code> )
		なぜなら、この値は新しい変数の名前であって、既に存在する変数の値を読み込むのではないからです。
	</p>
	
	<h3 id="note_4_using_foreach_without_a_container_element">(注4) コンテナエレメントなしで "foreach" を使う</h3>
	<p>
		「繰り返し部分に <code>foreach</code> を使いたいけど、<code>foreach</code> を使うためにエレメントを増やしたくない。」
		ということがあります。次の例を見て下さい。
	</p>
	<pre class="brush: html;">&lt;ul&gt;
	&lt;li class=&quot;header&quot;&gt;ヘッダアイテム&lt;/li&gt;
	&lt;!-- 以降を配列から動的に生成 --&gt;
	&lt;li&gt;アイテム A&lt;/li&gt;
	&lt;li&gt;アイテム B&lt;/li&gt;
	&lt;li&gt;アイテム C&lt;/li&gt;
&lt;/ul&gt;</pre>
	
	<p>
		この例では、通常の <code>foreach</code> バインディングを設置する場所がありません。
		<code>&lt;ul&gt;</code> タグに設置するにしても、ヘッダアイテムが配置できません。
		また、<code>&lt;lt&gt;</code> タグは <code>&lt;ul&gt;</code> タグ直下にしか配置できないため、
		新たなエレメントを <code>&lt;ul&gt;</code> タグ配下に配置するわけにもいきません。
	</p>
	<p>
		これはコメントタグによるコンテナレス構文を使うことで解決できます。
	</p>
	<pre class="brush: html;">&lt;ul&gt;
	&lt;li class=&quot;header&quot;&gt;ヘッダアイテム&lt;/li&gt;
	&lt;!-- ko foreach: myItems --&gt;
	&lt;li&gt;アイテム &lt;span data-bind=&quot;text: $data&quot;&gt;&lt;/span&gt;&lt;/li&gt;
	&lt;!-- /ko --&gt;
&lt;/ul&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	ko.applyBindings({
		myItems: [ 'A', 'B', 'C' ]
	});
&lt;/script&gt;</pre>
	
	<p>
		このコメント <code>&lt;!--ko--&gt;</code> と <code>&lt;!--/ko--&gt;</code> は、
		内部にマークアップを含む“バーチャルエレメント”の 開始 / 終了 のマーカーとしての役割をもっています。
		Knockout はこのバーチャルエレメント構文を理解し、本当のコンテナエレメントがあるかのようにバインドします。
	</p>

	<h3 id="note_5_how_array_changes_are_detected_and_handled">(注5) 配列の変更が検出・処理される仕組み</h3>
	<p>
		配列の要素が追加・削除・移動されたとき、<code>foreach</code> バインディングは何がどう変わったのかを把握するために
		効率的な差分アルゴリズムを使用し、一致させるために DOM を更新します。
		これにより、同時に複数の変更があっても処理することができます。
	</p>
	
	<ul>
		<li>
			配列に<strong>要素を追加</strong>すると、<code>foreach</code> はテンプレートの新たなコピーを生成し DOM に挿入します。
		</li>
		<li>
			配列の<strong>要素を削除</strong>すると、<code>foreach</code> は該当する DOM エレメントを単純に削除します。
		</li>
		<li>
			配列の<strong>要素を (同じインスタンスを保ったまま) 並べ替える</strong>と、<code>foreach</code> は該当する DOM エレメントを単に新たな位置へ移動します。
		</li>
	</ul>
	
	<p>
		DOM エレメントを非破壊的に並べ替えることが保証されないことに注意してください。
		アルゴリズムを迅速に完了するために、少ない配列要素の単純な変動を検出するために最適化されています。
		アルゴリズムは無関係な挿入と削除を含む、同時多発的な並べ替えを検出した場合、
		スピードを保つために、「移動」の代わりに「削除」と「追加」による並べ替えが選択します。
		その場合、対応する一連の DOM エレメントが削除され、再生成されます。
		ほとんどの開発者は、この特殊なケースに遭遇することはまずありませんし、
		もし遭遇したとしてもユーザエクスペリエンスに影響はありません。
	</p>
	
	<h3 id="note_6_destroyed_entries_are_hidden_by_default">(注6) “destroy”された要素はデフォルトで非表示になります</h3>
	
	<p>
		時折、レコードの存在を失うこと無く、エントリーを削除済みとしてマークしたいという要件があります。
		論理削除と呼ばれる方法ですが、この方法についての詳細は
		<a href="observableArray#destroy_and_destroyall_note_usually_relevant_to_ruby_on_rails_developers_only">observableArray の destroy と destroyAll</a>
		を参照して下さい。
	</p>
	
	<p>
		デフォルトでは、<code>foreach</code> バインディングは削除済みとしてマークされた要素をスキップ (=隠蔽) します。
		削除済みの要素を表示させたい場合は、次の例のように <code>includeDestroyed</code> オプションを使用して下さい。
	</p>
	
	<pre class="brush: html;">&lt;div data-bind='foreach: { data: myArray, includeDestroyed: true }'&gt;
	...
&lt;/div&gt;</pre>
	
	<h3 id="note_7_postprocessing_or_animating_the_generated_dom_elements">(注7) 生成された DOM エレメントを後処理またはアニメーションさせる方法</h3>
	
	<p>
		生成された DOM エレメントに対して何らかのカスタムロジックを実行させる必要がある場合、
		コールバック <code>afterRender</code>/<code>afterAdd</code>/<code>beforeRemove</code>/<code>beforeMove</code>/<code>afterMove</code> を使用します。
	</p>
	
	<blockquote>
		<p>
			<strong>ご注意:</strong> これらのコールバックは、リスト内の変更に関連したアニメーションを引き起こすための機能として意図されています。
			もしも新たな DOM エレメントが追加されたときの振る舞いを設けることが目的なのであれば
			(イベントハンドラやサードパーティの UI 制御を起動するなど)、
			代わりにその新たな振る舞いを <a href="custom-bindings">カスタムバインディング</a>
			として実装するほうが遥かに簡単です。
			なぜなら、カスタムバインディングにすることで、どこでも <code>foreach</code>
			バインディングに依存せずに使いまわすことができるからです。
		</p>
	</blockquote>
	
	<p>
		次に示すのは、<code>afterAdd</code> を使って、よくある“Yellow Fade”
		(訳注: ページの一部が更新されたときに背景色が黄色くなって、だんだん薄くなっていくアレ。
		from <a href="http://blogs.yahoo.co.jp/irons765/9309885.html">jQuery で "Yellow Fade" / ironsJPのブログ</a>) を実装する例です。
		背景色をアニメーションさせるために、<a href="https://github.com/jquery/jquery-color">jQuery Color plugin</a> が必要です。
	</p>
	
	<pre class="brush: js;">&lt;ul data-bind=&quot;foreach: { data: myItems, afterAdd: yellowFadeIn }&quot;&gt;
	&lt;li data-bind=&quot;text: $data&quot;&gt;&lt;/li&gt;
&lt;/ul&gt;
	
&lt;button data-bind=&quot;click: addItem&quot;&gt;Add&lt;/button&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	ko.applyBindings({
		myItems: ko.observableArray([ 'A', 'B', 'C' ]),
		yellowFadeIn: function(element, index, data) {
			$(element).filter(&quot;li&quot;)
			.animate({ backgroundColor: 'yellow' }, 200)
			.animate({ backgroundColor: 'white' }, 800);
		},
		addItem: function() { this.myItems.push('新しいアイテム'); }
	});
&lt;/script&gt;</pre>
	
	<p>詳細:</p>
	
	<ul>
		<li>
			<p>
				<code>afterRender</code> — 
				最初の初期化の時、および後に新たなアイテムが追加された時の両方において、
				<code>foreach</code> の繰り返し項目がドキュメントが挿入される度に呼び出されます。<br>
				指定されたコールバックに、次のパラメータを提供します。
			</p>
			<ol>
				<li>挿入された DOM ノードの配列</li>
				<li>項目に対してバインドされたアイテム</li>
			</ol>
		</li>
		<li>
			<p>
				<code>afterAdd</code> — 配列にアイテムが追加されたときのみ呼び出される点を除いて、
				<code>afterRender</code> と同様です。
				(配列の初期値に対しての <code>foreach</code> の最初の反復処理では呼び出されません。)<br>
				<code>afterAdd</code> の一般的な用途は、アイテムが追加されるたびにアニメーションさせるように、
				jQuery の <code>$(domNode).fadeIn()</code> のようなメソッドを呼び出すことです。<br>
				指定されたコールバックに、次のパラメータを提供します。
			</p>
			<ol>
				<li>追加された DOM ノード</li>
				<li>追加されたアイテムの、配列上のインデックス</li>
				<li>追加されたアイテム</li>
			</ol>
		</li>
		<li>
			<p>
				<code>beforeRemove</code> — 配列からアイテムが削除されたとき、該当する
				DOM ノードが削除される前に呼び出されます。
				<code>beforeRemove</code> コールバックを指定することで、
				DOM ノードの削除を開発者が行うことができるようになります。
				わかりやすい用途は、該当する DOM ノードを jQuery の <code>$(domNode).fadeOut()</code>
				のようなアニメーション効果を用いて削除する方法です。
				－ これを実装する場合、Knockout は“いつ対象の DOM ノードを削除して良いのか”を知ることができません。
				(アニメーションにかかる時間は場合によりけりです)
				したがって、<code>beforeRemove</code> コールバックを指定した場合は必ず、
				開発者自身が DOM ノードを削除するようにコーディングする必要があります。<br>
				指定されたコールバックに、次のパラメータを提供します。
			</p>
			<ol>
				<li>削除されるべき DOM ノード</li>
				<li>削除されたアイテムの、配列上のインデックス</li>
				<li>削除されたアイテム</li>
			</ol>
		</li>
		<li>
			<p>
				<code>beforeMove</code> — 配列内のアイテムの位置が変更されたとき、該当する
				DOM ノードが移動される前に呼び出されます。
				<code>beforeMove</code> は配列内の、インデックスが変化したすべてのアイテムに対して適用されることに注意してください。
				たとえば配列の先頭にアイテムを挿入した場合、他のすべてのアイテムのインデックスは、それぞれ1ずつインクリメントされます。
				そのため、挿入したアイテム以外のすべてのアイテムのためにコールバックが呼び出されます。
				エレメントの移動をアニメーションで表現させたい場合に、
				<code>beforeMove</code> で移動前の座標を取得しておいて、
				<code>afterMove</code> で移動されたエレメントのアニメーションを開始するという使い方ができます。<br>
				指定されたコールバックに、次のパラメータを提供します。
			</p>
			<ol>
				<li>移動される可能性のある DOM ノード</li>
				<li>移動されたアイテムの、配列上のインデックス</li>
				<li>移動されたアイテム</li>
			</ol>
		</li>
		<li>
			<p>
				<code>afterMove</code> — 配列内のアイテムの位置が変更されたとき、該当する
				DOM ノードが移動された後に呼び出されます。
				<code>afterMove</code> は配列内の、インデックスが変化したすべてのアイテムに対して適用されることに注意してください。
				たとえば配列の先頭にアイテムを挿入した場合、他のすべてのアイテムのインデックスは、それぞれ1ずつインクリメントされます。
				そのため、挿入したアイテム以外のすべてのアイテムのためにコールバックが呼び出されます。<br>
				指定されたコールバックに、次のパラメータを提供します。
			</p>
			<ol>
				<li>移動された可能性のある DOM ノード</li>
				<li>移動されたアイテムの、配列上のインデックス</li>
				<li>移動されたアイテム</li>
			</ol>
		</li>
	</ul>
	<p>
		<code>afterAdd</code> および <code>boforeRemove</code> の例は、
		<a href="http://knockoutjs.com/examples/animatedTransitions.html" class="tips_link">animated transitions</a>
		をご覧ください。
	</p>
	<blockquote>
		<h3>※訳者注 - 誤解にご注意</h3>
		<p>
			実際には、<code>afterAdd</code>, <code>beforeRemove</code>,
			<code>beforeMove</code>, <code>afterMove</code>
			は新たに挿入された DOM ノードごとに、個別に呼び出されることに注意して下さい。
			即ち (注7) で示した "Yellow Fade" の例では、<code>&lt;li&gt;</code>
			タグの前後にある空白のテキストノードに対しても、<code>yellowFadeIn</code>
			が呼び出されます。つまりこの例では一度の追加で <code>yellowFadeIn</code>
			が3回実行されるのです。jQuery の <code>filter</code> メソッドでアニメーションさせる対象を
			<code>&lt;li&gt;</code> タグにフィルタリングしているのはこのためです。<br>
			( v2.1.0, v2.2.0 にて確認 )
			<br><br>
			この挙動についてさらに詳しく知りたい場合は、
			<a href="http://jsfiddle.net/sukobuto/LmAkJ/4/" target="_blank">こちらのデモ</a>
			で、 <strong>Google Chrome の Developer Tools Console</strong> もしくは
			<strong>FireFox プラグインの FireBug Console</strong>
			にて、それぞれのコールバックが呼ばれる回数、および渡される引数を確認することをお勧めします。
		</p>
	</blockquote>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/foreach-binding.html">こちら</a></div>
	
</article>
