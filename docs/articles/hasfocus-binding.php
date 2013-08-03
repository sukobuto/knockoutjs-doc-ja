<article>
	
	<h1>"hasfocus" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>hasfocus</code> バインディングは DOM エレメントのフォーカス状態を ViewModel プロパティーにリンクします。
		これは双方向(2way)バインディングです。したがって、
	</p>
	
	<ul>
		<li>
			ViewModel のプロパティに <code>true</code> または <code>false</code> をセットした場合、
			関連付けられたエレメントがフォーカスされるか、またはフォーカスが外れます。
		</li>
		<li>
			ユーザが関連付けられたエレメントをフォーカスするか、またはフォーカスを外した場合、
			ViewModel のプロパティに <code>true</code> または <code>false</code> がセットされます。
		</li>
	</ul>
	
	<p>
		このバインディングは編集可能なエレメントが動的に表示されるような凝ったフォームを作る際に便利です。
		またユーザが入力を開始すべき場所をコントロールする場合や、キャレットの位置に応じて処理をすることにも使えます。
	</p>
	
	<h3 id="example_1_the_basics">例1: 基本</h3>
	
	<p>
		テキストボックスが現在フォーカスされているかをメッセージとして表示するシンプルな例です。
		さらにボタンを使って強制的にフォーカスすることができます。
	</p>
	
	<div class="demo" id="demo_1">
		<input type="text" data-bind="hasfocus: isSelected" />
		<button data-bind="click: setIsSelected">フォーカスする</button>
		<span data-bind="visible: isSelected">テキストボックスはフォーカスされています</span>
	</div>
	<script type="text/javascript">
		ko.applyBindings({
			isSelected: ko.observable(false),
			setIsSelected: function() { this.isSelected(true) }
		}, document.getElementById('demo_1'));
	</script>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;input data-bind=&quot;hasfocus: isSelected&quot; /&gt;
&lt;button data-bind=&quot;click: setIsSelected&quot;&gt;フォーカスする&lt;/button&gt;
&lt;span data-bind=&quot;visible: isSelected&quot;&gt;テキストボックスはフォーカスされています&lt;/span&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
var viewModel = {
	isSelected: ko.observable(false),
	setIsSelected: function() { this.isSelected(true) }
};
ko.applyBindings(viewModel);</pre>
	
	<h3 id="example_2_click_to_edit">例2: クリックして編集</h3>
	
	<p>
		<code>hasfocus</code> バインディングは双方向に作用するため
		(ViewModel プロパティの値 &lt;==&gt; エレメントのフォーカス状態)
		“編集モード”の切り替え方法に便利です。
		この例では、ViewModel の <code>editing</code> プロパティの値に応じて
		<code>&lt;span&gt;</code> または <code>&lt;input&gt;</code> エレメントの何れかで名前を表示します。
		<code>&lt;input&gt;</code> エレメントからフォーカスが外れると、<code>editing</code>
		に <code>false</code> がセットされ、“編集モード”が終了します。
	</p>
	
	<div class="demo" id="demo_2">
		<p>
			名前: 
			<b data-bind="visible: !editing(), text: name, click: edit">&nbsp;</b>
			<input type="text" data-bind="visible: editing, value: name, hasfocus: editing" /><br>
			
			<em>名前をクリックして編集, 他の部分をクリックして変更を適用.</em>
		</p>
	</div>
	<script type="text/javascript">
		function PersonViewModel(name) {
			// プロパティ
			this.name = ko.observable(name);
			this.editing = ko.observable(false);
			
			// アクション
			this.edit = function() { this.editing(true) }
		}
		ko.applyBindings(new PersonViewModel('Bert Bertington'), document.getElementById('demo_2'));
	</script>
	
	<pre class="brush: html;">&lt;!-- View --&gt;
&lt;p&gt;
	名前: 
	&lt;b data-bind=&quot;visible: !editing(), text: name, click: edit&quot;&gt;&amp;nbsp;&lt;/b&gt;
	&lt;input data-bind=&quot;visible: editing, value: name, hasfocus: editing&quot; /&gt;
&lt;/p&gt;
&lt;p&gt;&lt;em&gt;名前をクリックして編集, 他の部分をクリックして変更を適用.&lt;/em&gt;&lt;/p&gt;</pre>
	
	<pre class="brush: js;">// ViewModel
function PersonViewModel(name) {
	// プロパティ
	this.name = ko.observable(name);
	this.editing = ko.observable(false);

	// アクション
	this.edit = function() { this.editing(true) }
}
ko.applyBindings(new PersonViewModel('Bert Bertington'));</pre>
	
	<h3 id="parameters">パラメタ</h3>
	<ul>
		<li>
			主パラメタ
			<p>
				<code>true</code> (または true として評価される値) を渡すことで対象のエレメントにフォーカスします。
				それ以外の値を渡した場合、対象のエレメントからフォーカスを外します。
			</p>
			<p>
				ユーザによるエレメントのフォーカス状態変更に応じて、このパラメタに
				<code>true</code> もしくは <code>false</code> がセットされます。
			</p>
			<p>
				このパラメタが Observable である場合、このバインディングは値が変更される度にエレメントのフォーカス状態を更新します。
			</p>
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

