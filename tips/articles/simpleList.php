<article>
	
	<h1>シンプルなリスト</h1>
	
	<p>
		配列をバインドするサンプルです。
	</p>
	<p>
		テキストを入力しないと「追加」ボタンがクリックできないようになっています。<br>
		HTML コードで <code>enable</code> バインディングの使い方を確認して下さい。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<form data-bind="submit: addItem">
			新しいアイテム
			<input type="text" data-bind='value: itemToAdd, valueUpdate: "afterkeydown"' />
			<button type="submit" data-bind="enable: itemToAdd().length > 0">追加</button>
			<h4>アイテム一覧</h4>
			<select multiple="multiple" width="50" data-bind="options: items"> </select>
		</form>
	</div>
	<script type="text/javascript">
		var SimpleListModel = function(items) {
			this.items = ko.observableArray(items);
			this.itemToAdd = ko.observable("");
			this.addItem = function() {
				if (this.itemToAdd() != "") {
					// アイテムを追加します。
					// 追加先の items は observableArray なので、対応する UI が更新されます。
					this.items.push(this.itemToAdd());
					// itemToAdd は Observable であり、テキストボックスにバインドされているため、
					// 次のようにすることでテキストボックスをクリアできます。
					this.itemToAdd("");
				}
			}.bind(this);  // this が常にこの ViewModel を指すようにします
		};

		ko.applyBindings(new SimpleListModel(["Alpha", "Beta", "Gamma"]), document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;form data-bind=&quot;submit: addItem&quot;&gt;
    新しいアイテム
    &lt;input data-bind='value: itemToAdd, valueUpdate: &quot;afterkeydown&quot;' /&gt;
    &lt;button type=&quot;submit&quot; data-bind=&quot;enable: itemToAdd().length &gt; 0&quot;&gt;追加&lt;/button&gt;
    &lt;p&gt;アイテム一覧&lt;/p&gt;
    &lt;select multiple=&quot;multiple&quot; width=&quot;50&quot; data-bind=&quot;options: items&quot;&gt; &lt;/select&gt;
&lt;/form&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var SimpleListModel = function(items) {
	this.items = ko.observableArray(items);
	this.itemToAdd = ko.observable(&quot;&quot;);
	this.addItem = function() {
		if (this.itemToAdd() != &quot;&quot;) {
			// アイテムを追加します。
			// 追加先の items は observableArray なので、対応する UI が更新されます。
			this.items.push(this.itemToAdd());
			// itemToAdd は Observable であり、テキストボックスにバインドされているため、
			// 次のようにすることでテキストボックスをクリアできます。
			this.itemToAdd(&quot;&quot;);
		}
	}.bind(this);  // this が常にこの ViewModel を指すようにします
};

ko.applyBindings(new SimpleListModel([&quot;Alpha&quot;, &quot;Beta&quot;, &quot;Gamma&quot;]));</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/bxfXd/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>

