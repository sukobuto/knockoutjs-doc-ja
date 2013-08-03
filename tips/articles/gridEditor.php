<style type="text/css">
    .demo table, .demo td, .demo th { padding: 0.2em; border-width: 0; }
    .demo td input { width: 13em; }
    tr { vertical-align: top; }
    .demo input.error { border: 1px solid red; background-color: #FDC; }
    .demo label.error { display: block; color: Red; font-size: 0.8em; }    
</style>
<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>

<article>
	
	<h1>グリッドエディタ</h1>
	
	<p>
		<code>foreach</code> バインディングを使って、配列の各アイテムにしたがって内容を表示する例です。
		アイテムを追加・削除しても、Knockout ではすべてを再レンダリングせずに済みます。
		新たなアイテムに応じたエレメントのみがレンダリングされます。
		これは、他のリッチ UI コントロール (Validatorなど) の状態が保たれることを意味します。
	</p>
	
	<p>
		このサンプルのビルドおよび ASP.NET MVC との統合に関する詳細なステップバイステップのチュートリアルについては、
		<a href="http://blog.stevensanderson.com/2010/07/12/editing-a-variable-length-list-knockout-style/">
			このブログの記事
		</a>
		を参照してください。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<form action='/someServerSideHandler'>
			<p>欲しいものリスト: <span data-bind='text: gifts().length'>&nbsp;</span> 点</p>
			<table data-bind='visible: gifts().length > 0'>
				<thead>
					<tr>
						<th>名前</th>
						<th>価格</th>
						<th />
					</tr>
				</thead>
				<tbody data-bind='foreach: gifts'>
					<tr>
						<td><input type="text" class='required' data-bind='value: name, uniqueName: true' /></td>
						<td><input type="text" class='required number' data-bind='value: price, uniqueName: true' /></td>
						<td><a href='#' data-bind='click: $root.removeGift'>削除</a></td>
					</tr>
				</tbody>
			</table>

			<button data-bind='click: addGift'>追加</button>
			<button data-bind='enable: gifts().length > 0' type='submit'>登録</button>
		</form>
	</div>
	
	<script type="text/javascript">
		var GiftModel = function(gifts) {
			var self = this;
			self.gifts = ko.observableArray(gifts);

			self.addGift = function() {
				self.gifts.push({
					name: "",
					price: ""
				});
			};

			self.removeGift = function(gift) {
				self.gifts.remove(gift);
			};

			self.save = function(form) {
				alert("次のようにサーバに送信できます: " + ko.utils.stringifyJson(self.gifts));
				// ここで通常のフォーム送信同様に送信する場合、次のように書いてください:
				// ko.utils.postJson($("form")[0], self.gifts);
			};
		};

		var viewModel = new GiftModel([
			{ name: "高帽子", price: "39.95"},
			{ name: "長いクローク", price: "120.00"}
		]);
		ko.applyBindings(viewModel, document.getElementById('demo_1'));

		// jQuery Validation を起動
		$("form").validate({ submitHandler: viewModel.save });
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;form action='/someServerSideHandler'&gt;
	&lt;p&gt;欲しいものリスト: &lt;span data-bind='text: gifts().length'&gt;&amp;nbsp;&lt;/span&gt; 点&lt;/p&gt;
	&lt;table data-bind='visible: gifts().length &gt; 0'&gt;
		&lt;thead&gt;
			&lt;tr&gt;
				&lt;th&gt;名前&lt;/th&gt;
				&lt;th&gt;価格&lt;/th&gt;
				&lt;th /&gt;
			&lt;/tr&gt;
		&lt;/thead&gt;
		&lt;tbody data-bind='foreach: gifts'&gt;
			&lt;tr&gt;
				&lt;td&gt;&lt;input type=&quot;text&quot; class='required' data-bind='value: name, uniqueName: true' /&gt;&lt;/td&gt;
				&lt;td&gt;&lt;input type=&quot;text&quot; class='required number' data-bind='value: price, uniqueName: true' /&gt;&lt;/td&gt;
				&lt;td&gt;&lt;a href='#' data-bind='click: $root.removeGift'&gt;削除&lt;/a&gt;&lt;/td&gt;
			&lt;/tr&gt;
		&lt;/tbody&gt;
	&lt;/table&gt;

	&lt;button data-bind='click: addGift'&gt;追加&lt;/button&gt;
	&lt;button data-bind='enable: gifts().length &gt; 0' type='submit'&gt;登録&lt;/button&gt;
&lt;/form&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var GiftModel = function(gifts) {
	var self = this;
	self.gifts = ko.observableArray(gifts);

	self.addGift = function() {
		self.gifts.push({
			name: &quot;&quot;,
			price: &quot;&quot;
		});
	};

	self.removeGift = function(gift) {
		self.gifts.remove(gift);
	};

	self.save = function(form) {
		alert(&quot;次のようにサーバに送信できます: &quot; + ko.utils.stringifyJson(self.gifts));
		// ここで通常のフォーム送信同様に送信する場合、次のように書いてください:
		// ko.utils.postJson($(&quot;form&quot;)[0], self.gifts);
	};
};

var viewModel = new GiftModel([
	{ name: &quot;高帽子&quot;, price: &quot;39.95&quot;},
	{ name: &quot;長いクローク&quot;, price: &quot;120.00&quot;}
]);
ko.applyBindings(viewModel, document.getElementById('demo_1'));

// jQuery Validation を起動
$(&quot;form&quot;).validate({ submitHandler: viewModel.save });</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/7RDc3/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>

