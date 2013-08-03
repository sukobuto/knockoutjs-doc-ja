<article class="infinished">
	
	<h1>バインディング・コンテキスト</h1>
	<p>
		バインディング・コンテキストは、バインディングから参照できるデータをもつオブジェクトです。
		バインディングの適用にあたって、Knockout は自動的にバインディング・コンテキストの階層を作成・管理します。
		階層のルートは、<code>ko.applyBindings(viewModel)</code> に渡した <code>viewModel</code> です。
		また、<code>with</code> や <code>foreach</code> などのフロー制御バインディングを使う度に、
		ネストされた ViewModel データを参照する子コンテキストが作成されます。
	</p>
	<p>
		バインディング・コンテキストはどのバインディングでも参照できる、次の特別な変数を提供します。
	</p>
	<ul>
		
		<li>
			<code>$parent</code>
			<p>
				親コンテキストの ViewModel オブジェクトであり、現コンテキストの一つ外側にあたります。
				ルートコンテキストでは <code>undefined</code> です。
			</p>
			<pre class="brush: html;">&lt;h1 data-bind=&quot;text: name&quot;&gt;&lt;/h1&gt;

&lt;div data-bind=&quot;with: manager&quot;&gt;
	&lt;!-- ネストされたバインディング・コンテキスト内部 --&gt;
	&lt;span data-bind=&quot;text: name&quot;&gt;&lt;/span&gt; は
	&lt;span data-bind=&quot;text: $parent.name&quot;&gt;&lt;/span&gt; のマネージャーです。
&lt;/div&gt;</pre>
		</li>
		
		<li>
			<code>$parents</code>
			<p>すべての親 ViewModel の配列で、次のようになっています。</p>
			<p><code>$parents[0]</code> ...親のコンテキストの ViewModel (<code>$parent</code> と同じ)</p>
			<p><code>$parents[1]</code> ...親の親のコンテキストの ViewModel</p>
			<p><code>$parents[2]</code> ...親の親の親のコンテキストの ViewModel</p>
			<p>...ルートコンテキストの ViewModel まで続く</p>
		</li>
		
		<li>
			<code>$root</code>
			<p>
				ルートコンテキストにあたるメインの ViewModel であり、最上位のコンテキストです。<br>
				<code>$parents[$parents.length - 1]</code> と同義です。
			</p>
		</li>
		
		<li>
			<code>$data</code>
			<p>
				現在のコンテキストの ViewModel です。
				ルートコンテキストに限り、<code>$data</code> と <code>$root</code> は同義です。
				<code>$data</code> は、次の例のように ViewModel のプロパティではなく
				ViewModel 自体を参照したいときに便利です。
			</p>
			<pre class="brush: html;">&lt;ul data-bind=&quot;foreach: ['cats', 'dogs', 'fish']&quot;&gt;
	&lt;li&gt;値は &lt;span data-bind=&quot;text: $data&quot;&gt;&lt;/span&gt; です&lt;/li&gt;
&lt;/ul&gt;</pre>
		</li>
		
		<li>
			<code>$index</code> (<code>foreach</code> バインディング内でのみ使用可能)
			<p>
				<code>foreach</code> バインディングによってレンダリングされる、配列の現在の要素のインデックスです。
				他のコンテキスト変数と違って <code>$index</code> は Observable であり、
				配列へのアイテム追加・削除などによるアイテムのインデックスの変動により更新されます。
			</p>
		</li>
		
		<li>
			<code>$parentContext</code>
			<p>
				親の階層のバインディング・コンテキスト・オブジェクトを指します。
				これは、親の階層の ViewModel を指す <code>$parent</code> とは異なります。
				例えば、外側の <code>foreach</code> のアイテムのインデックスに、
				内側のコンテキストからアクセスするときに便利です。
				(使い方: <code>$parentContext.$index</code>)<br>
				ルートコンテキストでは <code>undefined</code> です。
			</p>
		</li>
		
	</ul>
	
	<p>
		次の特別な変数もバインディングに使用できますが、バインディング・コンテキスト・オブジェクトのプロパティではありません。
	</p>
	<ul>
		
		<li>
			<code>$context</code>
			<p>
				現在のバインディング・コンテキスト・オブジェクトを指します。
				ViewModel にコンテキスト変数と同名のプロパティがある場合に、
				コンテキスト変数にアクセスするために使うことができます。
				また、コンテキスト・オブジェクトを引数に関数を呼び出す、という場面があるかもしれません。
			</p>
			<pre class="brush: html;">&lt;ul data-bind=&quot;foreach: items&quot;&gt;
	&lt;li&gt;
		名前: &lt;span data-bind=&quot;text: name&quot;&gt; &lt;/span&gt;
		ViewModelの $index: &lt;span data-bind=&quot;text: $index&quot;&gt; &lt;/span&gt;
		コンテキストの $index: &lt;span data-bind=&quot;text: $context.$index&quot;&gt; &lt;/span&gt;
		コンテキストを解析: &lt;div data-bind=&quot;html: analyzeContext($context)&quot;&gt;&lt;/div&gt;
	&lt;/li&gt;
&lt;/ul&gt;

&lt;script&gt;
	ko.applyBindings({
		items: ko.observableArray([
			{ name: &quot;A&quot;, $index: 10 },
			{ name: &quot;B&quot;, $index: 20 },
			{ name: &quot;B&quot;, $index: 30 }
		])
	});
&lt;/script&gt;</pre>
		</li>
		
		<li>
			<code>$element</code>
			<p>
				現在のバインディングにおける、エレメントの DOM オブジェクト
				(バーチャルエレメントの場合はコメントの DOM オブジェクト) です。
				次の例のように、現在のエレメントの属性にアクセスする必要があるときに便利です。
			</p>
			<pre class="brush: html;">&lt;div id=&quot;item1&quot; data-bind=&quot;text: $element.id&quot;&gt;&lt;/div&gt;</pre>
		</li>
		
	</ul>
	
	<h3 id="controlling_or_modifying_the_binding_context_in_custom_bindings">カスタムバインディングでバインディング・コンテキストを制御・加工する</h3>
	
	<p>
		ビルトインバインディングの <code>with</code> や <code>foreach</code> のように、
		カスタムバインディングで配下のエレメントのバインディングコンテキストを変更、
		またはバインディング・コンテキスト・オブジェクトを拡張して特別な変数を追加することができます。
		これについては、
		<a href="custom-bindings-controlling-descendant-bindings">配下のバインディングを制御するカスタムバインディングを作成する</a>
		にて詳しく説明します。
	</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/binding-context.html">こちら</a></div>
	
</article>

