<article>
	
	<h1>"options" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>options</code> バインディングは、ドロップダウンリスト (すなわち <code>&lt;select&gt;</code> エレメント)
		または複数選択リスト (たとえば <code>&lt;select size='6'&gt;</code>) に表示する選択肢を制御します。
		このバインディングは <code>&lt;select&gt;</code> 以外のエレメントでは使用できません。
	</p>
	<p>
		配列もしくは ObservableArray をバインドして使用します。
		<code>&lt;select&gt;</code> エレメントには、配列の各アイテムがそれぞれひとつの選択肢として表示されます。
	</p>
	<p>
		注: 複数選択リストの場合、選択された選択肢を設定・取得するのに、
		<a href="selectedOptions-binding"><code>selectedOptions</code> バインディング</a> を使用します。
		択一リストの場合は <a href="value-binding"><code>value</code> バインディング</a> で選択された値を設定・取得することができます。
	</p>
	
	<h3 id="example_1_dropdown_list">例1: ドロップダウンリスト</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;行き先の国: &lt;select data-bind=&quot;options: availableCountries&quot;&gt;&lt;/select&gt;&lt;/p&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		// 選択肢の初期値を設定
		availableCountries : ko.observableArray(['フランス', 'ドイツ', 'スペイン'])
	};
	
	// ... その後 ...
	viewModel.availableCountries.push('中国'); // 選択肢を追加
&lt;/script&gt;</pre>
	
	<h3 id="example_2_multiselect_list">例2: 複数選択リスト</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;
	訪れたい国を選択して下さい:
	&lt;select data-bind=&quot;options: availableCountries&quot; size=&quot;5&quot; multiple=&quot;true&quot;&gt;&lt;/select&gt;
&lt;/p&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
&lt;script type=&quot;text/javascript&quot;&gt;
	var viewModel = {
		availableCountries : ko.observableArray(['フランス', 'ドイツ', 'スペイン'])
	};
&lt;/script&gt;</pre>
	
	<h3 id="example_3_dropdown_list_representing_arbitrary_javascript_objects_not_just_strings">例3: 単なる文字列ではなく任意の JavaScript オブジェクトを表示する</h3>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;
	あなたの国: 
	&lt;select data-bind=&quot;options: availableCountries,
							    optionsText: 'countryName',
							    value: selectedCountry,
							    optionsCaption: 'Choose...'&quot;&gt;&lt;/select&gt;
&lt;/p&gt;

&lt;div data-bind=&quot;visible: selectedCountry&quot;&gt; &lt;!-- どれかを選択したときに表示される --&gt;
	選択した国の人工:
	&lt;span data-bind=&quot;text: selectedCountry() ? selectedCountry().countryPopulation : 'unknown'&quot;&gt;&lt;/span&gt;.
&lt;/div&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
&lt;script type=&quot;text/javascript&quot;&gt;
	
	var Country = function(name, population) {
		this.countryName = name;
		this.countryPopulation = population;    
	};        
	
	var viewModel = {
		availableCountries : ko.observableArray([
			new Country(&quot;イギリス&quot;, 65000000),
			new Country(&quot;アメリカ&quot;, 320000000),
			new Country(&quot;スウェーデン&quot;, 29000000)
		]),
		selectedCountry : ko.observable()
	};
&lt;/script&gt;</pre>
	
	<p>
		
	</p>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>
	
</article>

