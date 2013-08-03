<article>
	
	<h1>Hello World デモ</h1>
	<p>
		2つのテキストボックスが ViewModel にある <em>Observable</em> プロパティーにバインドされています。
		"フルネーム" の表示は <em>Computed Observable</em> プロパティにバインドされています。
	</p>
	<p>
		どちらのテキストボックスを編集しても、"フルネーム" が更新されます。
		HTML コードを見ると、"onchange" イベントをキャッチする必要がないことがわかります。
		Knockout は UI の更新を検知します。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<span>ファーストネーム: <input type="text" data-bind="value: firstName" /></span><br>
		<span>ラストネーム: <input type="text" data-bind="value: lastName" /></span><br>
		<h2>Hello, <span data-bind="text: fullName"> </span>!</h2>
	</div>
	<script type="text/javascript">
		// Here's my data model
		var ViewModel = function(first, last) {
		    this.firstName = ko.observable(first);
		    this.lastName = ko.observable(last);
		 
		    this.fullName = ko.computed(function() {
					// Knockout tracks dependencies automatically. It knows that fullName depends on firstName and lastName, because these get called when evaluating fullName.
					return this.firstName() + " " + this.lastName();
			}, this);
		};
		 
		ko.applyBindings(new ViewModel("Planet", "Earth")); // This makes Knockout get to work
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;p&gt;ファーストネーム: &lt;input data-bind=&quot;value: firstName&quot; /&gt;&lt;/p&gt;
&lt;p&gt;ラストネーム: &lt;input data-bind=&quot;value: lastName&quot; /&gt;&lt;/p&gt;
&lt;h2&gt;Hello, &lt;span data-bind=&quot;text: fullName&quot;&gt; &lt;/span&gt;!&lt;/h2&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">// ViewModel を定義します
var ViewModel = function(first, last) {
	this.firstName = ko.observable(first);
	this.lastName = ko.observable(last);
	 
	this.fullName = ko.computed(function() {
		// Knockout は依存を自動的にトラッキングします。
		// fullName の評価中に firstName と lastName を呼び出すため、
		// それぞれに依存していることが検知されます。
		return this.firstName() + &quot; &quot; + this.lastName();
	}, this);
};

// 次のコードで Knockout を起動します。
ko.applyBindings(new ViewModel(&quot;Planet&quot;, &quot;Earth&quot;));</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/LkqTU/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>

