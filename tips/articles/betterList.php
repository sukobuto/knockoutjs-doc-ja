<article>
	
	<h1>リストを改良する</h1>
	<p>
		<a href="simpleList">シンプルなリスト</a> の例に基づき、
		さらにアイテムを削除する機能とリストをソートする機能を追加します。
		「削除」と「ソート」のボタンは、状況に応じてクリックできないようになります。
		(アイテムが1件もない場合など)
	</p>
	<p>
		HTML コードを見ると、実装のために必要なコードがどれだけ少ないかが分かります。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<form data-bind="submit: addItem">
			新しいアイテム
			<input type="text" data-bind='value: itemToAdd, valueUpdate: "afterkeydown"' />
			<button type="submit" data-bind="enable: itemToAdd().length > 0">追加</button>
		</form>
		<h4>アイテム一覧</h4>
		<select multiple="multiple" width="50"
				data-bind="options: allItems, selectedOptions: selectedItems"> </select>
		<div>
			<button data-bind="click: removeSelected, enable: selectedItems().length > 0">削除</button>
			<button data-bind="click: sortItems, enable: allItems().length > 1">ソート</button>
		</div>
	</div>
	<script type="text/javascript">
		var BetterListModel = function() {
			this.itemToAdd = ko.observable("");
			this.allItems = ko.observableArray(["リコッタチーズ", "エシャロット", "ロマネスコ", "オリーブオイル", "イタリアンパセリ", "パルミジャーノチーズ"]);
			this.selectedItems = ko.observableArray(["オリーブオイル"]);
			
			this.addItem = function() {
				if ((this.itemToAdd() != "") && (this.allItems.indexOf(this.itemToAdd()) < 0)) 
					this.allItems.push(this.itemToAdd());
				this.itemToAdd("");
			};
			
			this.removeSelected = function() {
				this.allItems.removeAll(this.selectedItems());
				this.selectedItems([]);
			};
			
			this.sortItems = function() {
				this.allItems.sort();
			};
		};

		ko.applyBindings(new BetterListModel(), document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;form data-bind=&quot;submit: addItem&quot;&gt;
	新しいアイテム
	&lt;input type=&quot;text&quot; data-bind='value: itemToAdd, valueUpdate: &quot;afterkeydown&quot;' /&gt;
	&lt;button type=&quot;submit&quot; data-bind=&quot;enable: itemToAdd().length &gt; 0&quot;&gt;追加&lt;/button&gt;
&lt;/form&gt;
&lt;h4&gt;アイテム一覧&lt;/h4&gt;
&lt;select multiple=&quot;multiple&quot; width=&quot;50&quot;
		data-bind=&quot;options: allItems, selectedOptions: selectedItems&quot;&gt; &lt;/select&gt;
&lt;div&gt;
	&lt;button data-bind=&quot;click: removeSelected, enable: selectedItems().length &gt; 0&quot;&gt;削除&lt;/button&gt;
	&lt;button data-bind=&quot;click: sortItems, enable: allItems().length &gt; 1&quot;&gt;ソート&lt;/button&gt;
&lt;/div&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var BetterListModel = function() {
	this.itemToAdd = ko.observable(&quot;&quot;);
	this.allItems = ko.observableArray([&quot;リコッタチーズ&quot;, &quot;エシャロット&quot;, &quot;ロマネスコ&quot;, &quot;オリーブオイル&quot;, &quot;イタリアンパセリ&quot;, &quot;パルミジャーノチーズ&quot;, &quot;&quot;]);
	this.selectedItems = ko.observableArray([&quot;オリーブオイル&quot;]);

	this.addItem = function() {
		if ((this.itemToAdd() != &quot;&quot;) &amp;&amp; (this.allItems.indexOf(this.itemToAdd()) &lt; 0))
			this.items.push(this.itemToAdd());
		this.itemToAdd(&quot;&quot;);
	};

	this.removeSelected = function() {
		this.allItems.removeAll(this.selectedItems());
		this.selectedItems([]); // 選択状態をクリア
	};

	this.sortItems = function() {
		this.allItems.sort();
	};
};

ko.applyBindings(new BetterListModel());</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/aDahT/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>