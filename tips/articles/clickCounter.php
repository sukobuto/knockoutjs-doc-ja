<article>
	
	<h1>クリックカウンター</h1>
	<p>
		このサンプルでは、ViewModel を作成し HTML マークアップへ様々なバインディングを適用することで、
		ViewModel の状態が反映され、編集される様子を確認できます。
	</p>
	
	<p>
		Knockout は依存をトラッキングします。内部的に、<code>hasClickedTooManyTimes</code> は
		<code>numberOfClicks</code> の変更通知を購読しており、<code>numberOfClicks</code>
		が変更される度に <code>hasClickedTooManyTimes</code> は再評価されます。
		同様に、UI の複数の箇所でも <code>hasClickedTooManyTimes</code> を参照し、変更通知を購読した状態になっています。
		したがって <code>hasClickedTooManyTimes</code> が変化する度に、該当する UI が更新されます。
	</p>
	
	<p>
		変更通知の購読をプログラムする必要はありません。
		フレームワークにより必要に応じて生成・破棄されます。
		HTML コードを見ると、いかにシンプルであるかがわかります。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<div>クリック回数 <span data-bind='text: numberOfClicks'>&nbsp;</span> 回</div>

		<button data-bind='click: registerClick, disable: hasClickedTooManyTimes'>クリックしてね</button>

		<div data-bind='visible: hasClickedTooManyTimes'>
			クリックしすぎです！指がすり減らないうちにやめて下さい ヽ(^o^;)丿
			<button data-bind='click: resetClicks'>リセット</button>
		</div>
	</div>
	<script type="text/javascript">
		var ClickCounterViewModel = function() {
			this.numberOfClicks = ko.observable(0);

			this.registerClick = function() {
				this.numberOfClicks(this.numberOfClicks() + 1);
			};

			this.resetClicks = function() {
				this.numberOfClicks(0);
			};

			this.hasClickedTooManyTimes = ko.computed(function() {
				return this.numberOfClicks() >= 5;
			}, this);
		};

		ko.applyBindings(new ClickCounterViewModel(), document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;div&gt;クリック回数 &lt;span data-bind='text: numberOfClicks'&gt;&amp;nbsp;&lt;/span&gt; 回&lt;/div&gt;
 
&lt;button data-bind='click: registerClick, disable: hasClickedTooManyTimes'&gt;クリックしてね&lt;/button&gt;
 
&lt;div data-bind='visible: hasClickedTooManyTimes'&gt;
	クリックしすぎです！指がすり減らないうちにやめて下さい ヽ(^o^;)丿
	&lt;button data-bind='click: resetClicks'&gt;リセット&lt;/button&gt;
&lt;/div&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var ClickCounterViewModel = function() {
	this.numberOfClicks = ko.observable(0);

	this.registerClick = function() {
		this.numberOfClicks(this.numberOfClicks() + 1);
	};

	this.resetClicks = function() {
		this.numberOfClicks(0);
	};

	this.hasClickedTooManyTimes = ko.computed(function() {
		return this.numberOfClicks() &gt;= 5;
	}, this);
};

ko.applyBindings(new ClickCounterViewModel());</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/3Lqsx/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>

