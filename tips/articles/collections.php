<style type='text/css'>
    .renderTime { color: #777; font-style: italic; font-size: 0.8em; }
</style>

<article>
	
	<h1>コレクションを操る</h1>
	
	<p>
		<code>foreach</code> バインディングを使ってコレクションをレンダリングする方法を示すサンプルです。
	</p>
	<p>
		エレメントの内容は <code>foreach</code> バインディングによって、
		コレクションに含まれるアイテムそれぞれのために繰り返し生成されます。
		また、<code>foreach</code> は容易にネストできます。
		Knockout は DOM の変更を必要最小限に留めます。
		「レンダリング時刻を表示」を有効にして、アイテムが追加された時に他の DOM エレメントに影響がないことを確認して下さい。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<h2>子だくさん名簿</h2>
		<ul data-bind="foreach: people">
			<li>
				<div>
					<span data-bind="text: name"> </span> さんの <span data-bind='text: children().length'>&nbsp;</span> 人のお子様:
					<a href='#' data-bind='click: addChild '>また産まれた！</a>
					<span class='renderTime' data-bind='visible: $root.showRenderTimes'>
						(<span data-bind='text: new Date().getSeconds()' > </span>秒 にレンダリング)
					</span>
				</div>
				<ul data-bind="foreach: children">
					<li>
						<span data-bind="text: $data"> </span>
						<span class='renderTime' data-bind='visible: $root.showRenderTimes'>
							(<span data-bind='text: new Date().getSeconds()' > </span>秒 にレンダリング)
						</span>
					</li>
				</ul>
			</li>
		</ul>
		<label><input data-bind='checked: showRenderTimes' type='checkbox' /> レンダリング時刻を表示</label> 
	</div>
	<script type="text/javascript">
		// 自分の名前と子を保持し、新たな子を追加するメソッドをもつ Person クラス
		var Person = function(name, children) {
			this.name = name;
			this.children = ko.observableArray(children);

			this.addChild = function() {
				this.children.push("新しいお子様");
			}.bind(this);
		}

		// 汎化した UI の状態を保持するが、UI の実装に依存しない ViewModel
		var viewModel = {
			people: [
				new Person("Annabelle", ["Arnie", "Anders", "Apple"]),
				new Person("Bertie", ["Boutros-Boutros", "Brianna", "Barbie", "Bee-bop"]),
				new Person("Charles", ["Cayenne", "Cleopatra"])
			],
			showRenderTimes: ko.observable(false)
		};

		ko.applyBindings(viewModel, document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;h2&gt;子だくさん名簿&lt;/h2&gt;
&lt;ul data-bind=&quot;foreach: people&quot;&gt;
	&lt;li&gt;
		&lt;div&gt;
			&lt;span data-bind=&quot;text: name&quot;&gt; &lt;/span&gt; さんの &lt;span data-bind='text: children().length'&gt;&amp;nbsp;&lt;/span&gt; 人のお子様:
			&lt;a href='#' data-bind='click: addChild '&gt;また産まれた！&lt;/a&gt;
			&lt;span class='renderTime' data-bind='visible: $root.showRenderTimes'&gt;
				(&lt;span data-bind='text: new Date().getSeconds()' &gt; &lt;/span&gt;秒 にレンダリング)
			&lt;/span&gt;
		&lt;/div&gt;
		&lt;ul data-bind=&quot;foreach: children&quot;&gt;
			&lt;li&gt;
				&lt;span data-bind=&quot;text: $data&quot;&gt; &lt;/span&gt;
				&lt;span class='renderTime' data-bind='visible: $root.showRenderTimes'&gt;
					(&lt;span data-bind='text: new Date().getSeconds()' &gt; &lt;/span&gt;秒 にレンダリング)
				&lt;/span&gt;
			&lt;/li&gt;
		&lt;/ul&gt;
	&lt;/li&gt;
&lt;/ul&gt;
&lt;label&gt;&lt;input data-bind='checked: showRenderTimes' type='checkbox' /&gt; レンダリング時刻を表示&lt;/label&gt; </pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">// 自分の名前と子を保持し、新たな子を追加するメソッドをもつ Person クラス
var Person = function(name, children) {
	this.name = name;
	this.children = ko.observableArray(children);

	this.addChild = function() {
		this.children.push(&quot;新しいお子様&quot;);
	}.bind(this);
}

// 汎化した UI の状態を保持するが、UI の実装に依存しない ViewModel
var viewModel = {
	people: [
		new Person(&quot;Annabelle&quot;, [&quot;Arnie&quot;, &quot;Anders&quot;, &quot;Apple&quot;]),
		new Person(&quot;Bertie&quot;, [&quot;Boutros-Boutros&quot;, &quot;Brianna&quot;, &quot;Barbie&quot;, &quot;Bee-bop&quot;]),
		new Person(&quot;Charles&quot;, [&quot;Cayenne&quot;, &quot;Cleopatra&quot;])
	],
	showRenderTimes: ko.observable(false)
};

ko.applyBindings(viewModel);</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/GSvnh/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>

