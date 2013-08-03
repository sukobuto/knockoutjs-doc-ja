<script type="text/javascript" src="http://knockoutjs.com/examples/resources/knockout.simpleGrid.1.3.js"></script>
<style>
    .ko-grid { margin-bottom: 1em; width: 25em; border: 1px solid silver; background-color:#464646; }
    .ko-grid th { text-align:left; background-color: Black; color:White; }
    .ko-grid td, th { padding: 0.4em; }
    .ko-grid tr:nth-child(odd) { background-color: #353535; }
    .ko-grid-pageLinks { margin-bottom: 1em; }
    .ko-grid-pageLinks a { padding: 0.5em; }
    .ko-grid-pageLinks a.selected { background-color: Black; color: White; }
    .demo { height:20em; overflow:auto } /* Mobile Safari reflows pages slowly, so fix the height to avoid the need for reflows */
</style>

<article>
	
	<h1>ページング機能付きの表</h1>
	<p>
		バインディングは <code>text</code> や <code>visible</code>, <code>click</code> などが全てではありません。
		実は簡単に独自のバインディングを追加することができます。
		単にイベントハンドラを追加したり、DOM エレメントのプロパティを更新したりするようなバインディングであれば、
		数行で実装することができます。
	</p>
	<p>
		しかしそれだけではありません。このページで紹介する <code>simpleGrid</code> のような、
		再利用できる部品やプラグインをカスタムバインディングで作成することができます。
	</p>
	<p>
		プラグインが独自の標準 ViewModel クラスを提供している場合
		(このサンプルでは <code>ko.simpleGrid.viewModel</code>)、
		ViewModel クラスは主に2つの役割を持ちます。
		1つめは、プラグインのインスタンスがどのように動作すべきかを設定する役割
		(このサンプルではページサイズと列定義) です。
		2つめは、ViewModel のプロパティが Observable である場合は、
		外部からそのプロパティを変更することで UI を更新させる役割
		(このサンプルでは現在のページ番号) です。例えば、「最初のページへ移動」ボタンの処理を見て下さい。
	</p>
	<p>
		HTML コードを見て下さい。<code>simpleGrid</code> の使い方はとても簡単です。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<div data-bind='simpleGrid: gridViewModel'> </div>

		<button data-bind='click: addItem'>
			追加
		</button>

		<button data-bind='click: sortByName'>
			名前でソート
		</button>

		<button data-bind='click: jumpToFirstPage, enable: gridViewModel.currentPageIndex'>
			最初のページへ移動
		</button> 
	</div>
	<script type="text/javascript">
		var initialData = [
			{ name: "子猫の旅路", sales: 352, price: 75.95 },
			{ name: "すばやいコヨーテ", sales: 89, price: 190.00 },
			{ name: "トカゲ激昂", sales: 152, price: 25.00 },
			{ name: "無関心モンキー", sales: 1, price: 99.95 },
			{ name: "ドラゴンの憂鬱", sales: 0, price: 6350 },
			{ name: "ヤバいオタマジャクシ", sales: 39450, price: 0.35 },
			{ name: "楽観的なカタツムリ", sales: 420, price: 1.50 }
		];

		var PagedGridModel = function(items) {
			this.items = ko.observableArray(items);

			this.addItem = function() {
				this.items.push({ name: "新書", sales: 0, price: 100 });
			};

			this.sortByName = function() {
				this.items.sort(function(a, b) {
					return a.name < b.name ? -1 : 1;
				});
			};

			this.jumpToFirstPage = function() {
				this.gridViewModel.currentPageIndex(0);
			};

			this.gridViewModel = new ko.simpleGrid.viewModel({
				data: this.items,
				columns: [
					{ headerText: "タイトル", rowText: "name" },
					{ headerText: "販売実績(冊)", rowText: "sales" },
					{ headerText: "価格", rowText: function (item) { return "$" + item.price.toFixed(2) } }
				],
				pageSize: 4
			});
		};

		ko.applyBindings(new PagedGridModel(initialData));
	</script>		
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;div data-bind='simpleGrid: gridViewModel'&gt; &lt;/div&gt;

&lt;button data-bind='click: addItem'&gt;
	追加
&lt;/button&gt;

&lt;button data-bind='click: sortByName'&gt;
	名前でソート
&lt;/button&gt;

&lt;button data-bind='click: jumpToFirstPage, enable: gridViewModel.currentPageIndex'&gt;
	最初のページへ移動
&lt;/button&gt; </pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var initialData = [
    { name: &quot;子猫の旅路&quot;, sales: 352, price: 75.95 },
    { name: &quot;すばやいコヨーテ&quot;, sales: 89, price: 190.00 },
    { name: &quot;トカゲ激昂&quot;, sales: 152, price: 25.00 },
    { name: &quot;無関心モンキー&quot;, sales: 1, price: 99.95 },
    { name: &quot;ドラゴンの憂鬱&quot;, sales: 0, price: 6350 },
    { name: &quot;ヤバいオタマジャクシ&quot;, sales: 39450, price: 0.35 },
    { name: &quot;楽観的なカタツムリ&quot;, sales: 420, price: 1.50 }
];
 
var PagedGridModel = function(items) {
    this.items = ko.observableArray(items);
 
    this.addItem = function() {
        this.items.push({ name: &quot;新書&quot;, sales: 0, price: 100 });
    };
 
    this.sortByName = function() {
        this.items.sort(function(a, b) {
            return a.name &lt; b.name ? -1 : 1;
        });
    };
 
    this.jumpToFirstPage = function() {
        this.gridViewModel.currentPageIndex(0);
    };
 
    this.gridViewModel = new ko.simpleGrid.viewModel({
        data: this.items,
        columns: [
            { headerText: &quot;タイトル&quot;, rowText: &quot;name&quot; },
            { headerText: &quot;販売実績(冊)&quot;, rowText: &quot;sales&quot; },
            { headerText: &quot;価格&quot;, rowText: function (item) { return &quot;$&quot; + item.price.toFixed(2) } }
        ],
        pageSize: 4
    });
};
 
ko.applyBindings(new PagedGridModel(initialData));</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/QSRBR/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>
