<style type="text/css">        	
    .demo th { text-align: left }
    .demo .price { text-align: right; padding-right: 2em; }
    .demo .grandTotal { border-top: 1px solid silver; padding-top: 0.5em; font-size: 1.2em; }
    .demo .grandTotal SPAN { font-weight: bold; }
    
    .demo table, .demo td, .demo th { padding: 0.2em; border-width: 0; margin: 0; vertical-align: top; }
    .demo td input, .demo td select { width: 8em; }
    .demo td.quantity input { width: 4em; }
    .demo td select { height: 1.8em; white-space: nowrap; }
</style>
<script src="resources/sampleProductCategories.js" type="text/javascript"></script>
<article>
	
	<h1>買い物カゴ</h1>
	
	<p>
		Computed Observable がどのように連鎖するかを示したサンプルです。
		買い物カゴの各行にはそれぞれの小計を示す <code>ko.computed</code> プロパティがあり、
		これらはさらに合計を示す <code>ko.computed</code> プロパティに繋がります。
		内容を変更すると、その変更が波紋となって Computed Observable の連鎖を伝い、
		すべての関連する UI が更新されます。
	</p>
	
	<p>
		またこのサンプルでは、ドロップダウンリストをカスケードさせるシンプルな方法も示しています。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<table width='100%'>
			<thead>
				<tr>
					<th width='25%'>カテゴリ</th>
					<th width='25%'>製品</th>
					<th class='price' width='15%'>価格</th>
					<th class='quantity' width='10%'>数量</th>
					<th class='price' width='15%'>小計</th>
					<th width='10%'> </th>
				</tr>
			</thead>
			<tbody data-bind='foreach: lines'>
				<tr>
					<td>
						<select data-bind='options: sampleProductCategories, optionsText: "name", optionsCaption: "選択...", value: category'> </select>
					</td>
					<td data-bind="with: category">
						<select data-bind='options: products, optionsText: "name", optionsCaption: "選択...", value: $parent.product'> </select>
					</td>
					<td class='price' data-bind='with: product'>
						<span data-bind='text: formatCurrency(price)'> </span>
					</td>
					<td class='quantity'>
						<input data-bind='visible: product, value: quantity, valueUpdate: "afterkeydown"' />
					</td>
					<td class='price'>
						<span data-bind='visible: product, text: formatCurrency(subtotal())' > </span>
					</td>
					<td>
						<a href='#' data-bind='click: $parent.removeLine'>削除</a>
					</td>
				</tr>
			</tbody>
		</table>
		<p class='grandTotal'>
			合計金額: <span data-bind='text: formatCurrency(grandTotal())'> </span>
		</p>
		<button data-bind='click: addLine'>製品を追加</button>
		<button data-bind='click: save'>注文</button>
	</div>
	
	<script type="text/javascript">
		function formatCurrency(value) {
			return "$" + value.toFixed(2);
		}

		var CartLine = function() {
			var self = this;
			self.category = ko.observable();
			self.product = ko.observable();
			self.quantity = ko.observable(1);
			self.subtotal = ko.computed(function() {
				return self.product() ? self.product().price * parseInt("0" + self.quantity(), 10) : 0;
			});

			// カテゴリが変更された時に、製品の選択状態をリセットする
			self.category.subscribe(function() {
				self.product(undefined);
			});
		};

		var Cart = function() {
			// 買い物カゴ各行の情報を保持し、それらから合計金額を算出する
			var self = this;
			self.lines = ko.observableArray([new CartLine()]); // デフォルトで1行格納する
			self.grandTotal = ko.computed(function() {
				var total = 0;
				$.each(self.lines(), function() { total += this.subtotal() })
				return total;
			});

			// アクション
			self.addLine = function() { self.lines.push(new CartLine()) };
			self.removeLine = function(line) { self.lines.remove(line) };
			self.save = function() {
				var dataToSave = $.map(self.lines(), function(line) {
					return line.product() ? {
						productName: line.product().name,
						quantity: line.quantity()
					} : undefined
				});
				alert("次のようにサーバに送信できます: " + JSON.stringify(dataToSave));
			};
		};

		ko.applyBindings(new Cart(), document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;table width='100%'&gt;
	&lt;thead&gt;
		&lt;tr&gt;
			&lt;th width='25%'&gt;カテゴリ&lt;/th&gt;
			&lt;th width='25%'&gt;製品&lt;/th&gt;
			&lt;th class='price' width='15%'&gt;価格&lt;/th&gt;
			&lt;th class='quantity' width='10%'&gt;数量&lt;/th&gt;
			&lt;th class='price' width='15%'&gt;小計&lt;/th&gt;
			&lt;th width='10%'&gt; &lt;/th&gt;
		&lt;/tr&gt;
	&lt;/thead&gt;
	&lt;tbody data-bind='foreach: lines'&gt;
		&lt;tr&gt;
			&lt;td&gt;
				&lt;select data-bind='options: sampleProductCategories, optionsText: &quot;name&quot;, optionsCaption: &quot;選択...&quot;, value: category'&gt; &lt;/select&gt;
			&lt;/td&gt;
			&lt;td data-bind=&quot;with: category&quot;&gt;
				&lt;select data-bind='options: products, optionsText: &quot;name&quot;, optionsCaption: &quot;選択...&quot;, value: $parent.product'&gt; &lt;/select&gt;
			&lt;/td&gt;
			&lt;td class='price' data-bind='with: product'&gt;
				&lt;span data-bind='text: formatCurrency(price)'&gt; &lt;/span&gt;
			&lt;/td&gt;
			&lt;td class='quantity'&gt;
				&lt;input data-bind='visible: product, value: quantity, valueUpdate: &quot;afterkeydown&quot;' /&gt;
			&lt;/td&gt;
			&lt;td class='price'&gt;
				&lt;span data-bind='visible: product, text: formatCurrency(subtotal())' &gt; &lt;/span&gt;
			&lt;/td&gt;
			&lt;td&gt;
				&lt;a href='#' data-bind='click: $parent.removeLine'&gt;削除&lt;/a&gt;
			&lt;/td&gt;
		&lt;/tr&gt;
	&lt;/tbody&gt;
&lt;/table&gt;
&lt;p class='grandTotal'&gt;
	合計金額: &lt;span data-bind='text: formatCurrency(grandTotal())'&gt; &lt;/span&gt;
&lt;/p&gt;
&lt;button data-bind='click: addLine'&gt;製品を追加&lt;/button&gt;
&lt;button data-bind='click: save'&gt;注文&lt;/button&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">function formatCurrency(value) {
	return &quot;$&quot; + value.toFixed(2);
}

var CartLine = function() {
	var self = this;
	self.category = ko.observable();
	self.product = ko.observable();
	self.quantity = ko.observable(1);
	self.subtotal = ko.computed(function() {
		return self.product() ? self.product().price * parseInt(&quot;0&quot; + self.quantity(), 10) : 0;
	});

	// カテゴリが変更された時に、製品の選択状態をリセットする
	self.category.subscribe(function() {
		self.product(undefined);
	});
};

var Cart = function() {
	// 買い物カゴ各行の情報を保持し、それらから合計金額を算出する
	var self = this;
	self.lines = ko.observableArray([new CartLine()]); // デフォルトで1行格納する
	self.grandTotal = ko.computed(function() {
		var total = 0;
		$.each(self.lines(), function() { total += this.subtotal() })
		return total;
	});

	// アクション
	self.addLine = function() { self.lines.push(new CartLine()) };
	self.removeLine = function(line) { self.lines.remove(line) };
	self.save = function() {
		var dataToSave = $.map(self.lines(), function(line) {
			return line.product() ? {
				productName: line.product().name,
				quantity: line.quantity()
			} : undefined
		});
		alert(&quot;次のようにサーバに送信できます: &quot; + JSON.stringify(dataToSave));
	};
};

ko.applyBindings(new Cart());</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/adNuR/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>
