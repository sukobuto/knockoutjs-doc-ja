<style type='text/css'>
    .planet { background-color: #226142; padding: 5px; border: 1px solid #303030; margin-bottom: 0.5em; font-size: 0.75em; }
    .planet.rock { background-color: #583F2B; }
    .demo input { margin: 0 0.3em 0 1em; }
</style>
<article>
	
	<h1>アニメーション効果</h1>
	<p>
		このサンプルでは、アニメーションを実装する2つの方法を示します。
	</p>
	<blockquote>
		<h3>※訳者注</h3>
		<p>
			このサンプルは、コードのシンプルさにおいて問題があると訳者は考えております。<br/>
			詳しくは <a href="http://qiita.com/sukobuto/items/9fc4bdc3463f13bdc00c" target="_blank">
				サンプルに惑わされるな！KnockoutでUIエフェクトを使う際のベター・プラクティス
			</a> をご覧ください。
		</p>
	</blockquote>
	<ul>
		<li>
			<p>
				<code>template</code>/<code>foreach</code> バインディングを使う場合、
				<code>afterAdd</code> や <code>beforeRemove</code> にコールバックを指定することができます。
				それによりエレメントの追加・削除に対してコードを介入させることができるため。
				jQuery の <code>slideUp</code>/<code>slideDown()</code> やそれに類似するアニメーション手法を簡単に導入することができます。
				惑星タイプを切り替えたり、惑星を追加することでこの動作を見ることができます。
			</p>
		</li>
		<li>
			<p>
				Observable の値に従って任意の方法でエレメントの状態を操るカスタムバインディングを記述することは、難しくありません。
				この例では、<code>fadeVisible</code> というカスタムバインディングを作成しています。
				このカスタムバインディングは、 Observable の値が変化したときに
				関連付けられた DOM エレメントをアニメーションさせるために jQuery の
				<code>fadeIn</code>/<code>fadeOut</code> 関数を使います。
				「オプションを表示」にチェックを入れたり、外したりしてをこの動作を確認して下さい。
			</p>
		</li>
	</ul>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<h2>惑星</h2>
		<p> 
			<label>
				<input type='checkbox' data-bind='checked: displayAdvancedOptions' />
				オプションを表示
			</label>
		</p>
			
		<p data-bind='fadeVisible: displayAdvancedOptions'>
			表示:
			<label><input type='radio' name="type" value='all' data-bind='checked: typeToShow' />すべて</label>
			<label><input type='radio' name="type" value='rock' data-bind='checked: typeToShow' />岩石惑星</label>
			<label><input type='radio' name="type" value='gasgiant' data-bind='checked: typeToShow' />巨大ガス惑星</label>
		</p>
			
		<div data-bind='template: { foreach: planetsToShow,
								    beforeRemove: hidePlanetElement,
								    afterAdd: showPlanetElement }'>
			<div data-bind='attr: { "class": "planet " + type }, text: name'> </div>
		</div>
			
		<p data-bind='fadeVisible: displayAdvancedOptions'>
			<button data-bind='click: addPlanet.bind($data, "rock")'>岩石惑星を追加</button>
			<button data-bind='click: addPlanet.bind($data, "gasgiant")'>巨大ガス惑星を追加</button>
		</p>
	</div>
	<script type="text/javascript">
		var PlanetsModel = function() {
			this.planets = ko.observableArray([
				{ name: "水星", type: "rock"},
				{ name: "金星", type: "rock"},
				{ name: "地球", type: "rock"},
				{ name: "火星", type: "rock"},
				{ name: "木製", type: "gasgiant"},
				{ name: "土星", type: "gasgiant"},
				{ name: "天王星", type: "gasgiant"},
				{ name: "海王星", type: "gasgiant"},
				{ name: "冥王星", type: "rock"}
			]);
			
			this.typeToShow = ko.observable("all");
			this.displayAdvancedOptions = ko.observable(false);
			
			this.addPlanet = function(type) {
				this.planets.push({
					name: "新惑星",
					type: type
				});
			};
			
			this.planetsToShow = ko.computed(function() {
				// 惑星のリストを 条件 "typeToShow" でフィルタリングします。
				var desiredType = this.typeToShow();
				if (desiredType == "all") return this.planets();
				return ko.utils.arrayFilter(this.planets(), function(planet) {
					return planet.type == desiredType;
				});
			}, this);
			
			// 惑星リスト用のアニメーション callback
			this.showPlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
			this.hidePlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }
		};
		
		// jQuery の fadeIn() / fadeout() メソッドを使ってエレメントの 可視/不可視 を切り替えるカスタムばインディング
		// 別のJSファイルに分割して読み込むこともできます。
		ko.bindingHandlers.fadeVisible = {
			init: function(element, valueAccessor) {
				// 最初に、値に応じて即座にエレメントの 可視/不可視 を設定します。
				var value = valueAccessor();
				// Observable かどうかがわからない値は、"unwrapObservable" を使って処理することができます。
				$(element).toggle(ko.utils.unwrapObservable(value));
			},
			update: function(element, valueAccessor) {
				// 値の変化に応じて、ゆっくりと 可視/不可視 の切り替えを行います。
				var value = valueAccessor();
				ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
			}
		};
		
		ko.applyBindings(new PlanetsModel(), document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;h2&gt;惑星&lt;/h2&gt;
&lt;p&gt; 
	&lt;label&gt;
		&lt;input type='checkbox' data-bind='checked: displayAdvancedOptions' /&gt;
		オプションを表示
	&lt;/label&gt;
&lt;/p&gt;

&lt;p data-bind='fadeVisible: displayAdvancedOptions'&gt;
	表示:
	&lt;label&gt;&lt;input type='radio' name=&quot;type&quot; value='all' data-bind='checked: typeToShow' /&gt;すべて&lt;/label&gt;
	&lt;label&gt;&lt;input type='radio' name=&quot;type&quot; value='rock' data-bind='checked: typeToShow' /&gt;岩石惑星&lt;/label&gt;
	&lt;label&gt;&lt;input type='radio' name=&quot;type&quot; value='gasgiant' data-bind='checked: typeToShow' /&gt;巨大ガス惑星&lt;/label&gt;
&lt;/p&gt;

&lt;div data-bind='template: { foreach: planetsToShow,
							beforeRemove: hidePlanetElement,
							afterAdd: showPlanetElement }'&gt;
	&lt;div data-bind='attr: { &quot;class&quot;: &quot;planet &quot; + type }, text: name'&gt; &lt;/div&gt;
&lt;/div&gt;

&lt;p data-bind='fadeVisible: displayAdvancedOptions'&gt;
	&lt;button data-bind='click: addPlanet.bind($data, &quot;rock&quot;)'&gt;岩石惑星を追加&lt;/button&gt;
	&lt;button data-bind='click: addPlanet.bind($data, &quot;gasgiant&quot;)'&gt;巨大ガス惑星を追加&lt;/button&gt;
&lt;/p&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var PlanetsModel = function() {
	this.planets = ko.observableArray([
		{ name: &quot;水星&quot;, type: &quot;rock&quot;},
		{ name: &quot;金星&quot;, type: &quot;rock&quot;},
		{ name: &quot;地球&quot;, type: &quot;rock&quot;},
		{ name: &quot;火星&quot;, type: &quot;rock&quot;},
		{ name: &quot;木製&quot;, type: &quot;gasgiant&quot;},
		{ name: &quot;土星&quot;, type: &quot;gasgiant&quot;},
		{ name: &quot;天王星&quot;, type: &quot;gasgiant&quot;},
		{ name: &quot;海王星&quot;, type: &quot;gasgiant&quot;},
		{ name: &quot;冥王星&quot;, type: &quot;rock&quot;}
	]);

	this.typeToShow = ko.observable(&quot;all&quot;);
	this.displayAdvancedOptions = ko.observable(false);

	this.addPlanet = function(type) {
		this.planets.push({
			name: &quot;新惑星&quot;,
			type: type
		});
	};

	this.planetsToShow = ko.computed(function() {
		// 惑星のリストを 条件 &quot;typeToShow&quot; でフィルタリングします。
		var desiredType = this.typeToShow();
		if (desiredType == &quot;all&quot;) return this.planets();
		return ko.utils.arrayFilter(this.planets(), function(planet) {
			return planet.type == desiredType;
		});
	}, this);

	// 惑星リスト用のアニメーション callback
	this.showPlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
	this.hidePlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }
};

// jQuery の fadeIn() / fadeout() メソッドを使ってエレメントの 可視/不可視 を切り替えるカスタムばインディング
// 別のJSファイルに分割して読み込むこともできます。
ko.bindingHandlers.fadeVisible = {
	init: function(element, valueAccessor) {
		// 最初に、値に応じて即座にエレメントの 可視/不可視 を設定します。
		var value = valueAccessor();
		// Observable かどうかがわからない値は、&quot;unwrapObservable&quot; を使って処理することができます。
		$(element).toggle(ko.utils.unwrapObservable(value));
	},
	update: function(element, valueAccessor) {
		// 値の変化に応じて、ゆっくりと 可視/不可視 の切り替えを行います。
		var value = valueAccessor();
		ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
	}
};

ko.applyBindings(new PlanetsModel());</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/8k8V5/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>