<style type='text/css'>
.readout { width: 17em; float: right; }
.readout td { width: 50%; }
.demo tr { vertical-align: top }
.demo td { padding: 12px; background-color: #1D1713;  }
.demo td.label { text-align: right; padding-right: 0.5em; background-color: transparent; font-size: 12px; }
.demo input[type=radio] { margin: 0 0.25em 0 0.25em }
</style>
<article>
	
	<h1>各種 Form 部品</h1>
	<p>
		リファレンスとして、各種 Form 部品へのバインディングのサンプルを掲載します。
		ドロップダウンリストやラジオボタンなどバインドする方法を明確にするため、
		ViewModel は単純化しています。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<div class="readout">
			<h3>ViewModel の値</h3>
			<table>
				<tr>
					<td class="label">テキスト値:</td>
					<td data-bind="text: stringValue"></td>
				</tr>
				<tr>
					<td class="label">パスワード:</td>
					<td data-bind="text: passwordValue"></td>
				</tr>
				<tr>
					<td class="label">真偽値:</td>
					<td data-bind='text: booleanValue() ? "True" : "False"'></td>
				</tr>
				<tr>
					<td class="label">選択された値<br>(ドロップダウン):</td>
					<td data-bind="text: selectedOptionValue"></td>
				</tr>
				<tr>
					<td class="label">選択された値<br>(複数選択):</td>
					<td data-bind="text: multipleSelectedOptionValues"></td>
				</tr>
				<tr>
					<td class="label">選択された値<br>(ラジオボタン):</td>
					<td data-bind="text: radioSelectedOptionValue"></td>
				</tr>
			</table>
		</div>

		<h3>Form 部品</h3>
		<table>
			<tr>
				<td class="label">テキスト値<br>(変更時に更新):</td>
				<td><input type="text" data-bind="value: stringValue" /></td>
			</tr>
			<tr>
				<td class="label">テキスト値<br>(タイプ時に更新):</td>
				<td><input type="text" data-bind='value: stringValue, valueUpdate: "afterkeydown"' /></td>
			</tr>
			<tr>
				<td class="label">テキスト値<br>(複数行):</td>
				<td><textarea data-bind="value: stringValue"> </textarea></td>
			</tr>
			<tr>
				<td class="label">パスワード:</td>
				<td><input type="password" data-bind="value: passwordValue" /></td>
			</tr>
			<tr>
				<td class="label">チェックボックス:</td>
				<td><input type="checkbox" data-bind="checked: booleanValue" /></td>
			</tr>
			<tr>
				<td class="label">ドロップダウンリスト:</td>
				<td><select data-bind="options: optionValues, value: selectedOptionValue"></select></td>
			</tr>
			<tr>
				<td class="label">複数選択リスト:</td>
				<td><select multiple="multiple" data-bind="options: optionValues, selectedOptions: multipleSelectedOptionValues"></select></td>
			</tr>
			<tr>
				<td class="label">ラジオボタン:</td>
				<td>
					<label><input type="radio" value="Alpha" data-bind="checked: radioSelectedOptionValue" />Alpha</label>
					<label><input type="radio" value="Beta" data-bind="checked: radioSelectedOptionValue" />Beta</label>
					<label><input type="radio" value="Gamma" data-bind="checked: radioSelectedOptionValue" />Gamma</label>
				</td>
			</tr>
		</table>
	</div>
	<script>
		var viewModel = {
			stringValue : ko.observable("こんにちわ"),
			passwordValue : ko.observable("hogehoge"),
			booleanValue : ko.observable(true),
			optionValues : ["Alpha", "Beta", "Gamma"],
			selectedOptionValue : ko.observable("Gamma"),
			multipleSelectedOptionValues : ko.observable(["Alpha"]),
			radioSelectedOptionValue : ko.observable("Beta")
		};
		ko.applyBindings(viewModel, document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;div class=&quot;readout&quot;&gt;
	&lt;h3&gt;ViewModel の値&lt;/h3&gt;
	&lt;table&gt;
		&lt;tr&gt;
			&lt;td class=&quot;label&quot;&gt;テキスト値:&lt;/td&gt;
			&lt;td data-bind=&quot;text: stringValue&quot;&gt;&lt;/td&gt;
		&lt;/tr&gt;
		&lt;tr&gt;
			&lt;td class=&quot;label&quot;&gt;パスワード:&lt;/td&gt;
			&lt;td data-bind=&quot;text: passwordValue&quot;&gt;&lt;/td&gt;
		&lt;/tr&gt;
		&lt;tr&gt;
			&lt;td class=&quot;label&quot;&gt;真偽値:&lt;/td&gt;
			&lt;td data-bind='text: booleanValue() ? &quot;True&quot; : &quot;False&quot;'&gt;&lt;/td&gt;
		&lt;/tr&gt;
		&lt;tr&gt;
			&lt;td class=&quot;label&quot;&gt;選択された値:&lt;/td&gt;
			&lt;td data-bind=&quot;text: selectedOptionValue&quot;&gt;&lt;/td&gt;
		&lt;/tr&gt;
		&lt;tr&gt;
			&lt;td class=&quot;label&quot;&gt;選択された値 (複数選択):&lt;/td&gt;
			&lt;td data-bind=&quot;text: multipleSelectedOptionValues&quot;&gt;&lt;/td&gt;
		&lt;/tr&gt;
		&lt;tr&gt;
			&lt;td class=&quot;label&quot;&gt;ラジオボタンの選択された値:&lt;/td&gt;
			&lt;td data-bind=&quot;text: radioSelectedOptionValue&quot;&gt;&lt;/td&gt;
		&lt;/tr&gt;
	&lt;/table&gt;
&lt;/div&gt;
 
&lt;h3&gt;Form 部品&lt;/h3&gt;
&lt;table&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;テキスト値 (変更時=フォーカスアウト時に更新):&lt;/td&gt;
		&lt;td&gt;&lt;input data-bind=&quot;value: stringValue&quot; /&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;テキスト値 (タイプ時に更新):&lt;/td&gt;
		&lt;td&gt;&lt;input data-bind='value: stringValue, valueUpdate: &quot;afterkeydown&quot;' /&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;テキスト値 (複数行):&lt;/td&gt;
		&lt;td&gt;&lt;textarea data-bind=&quot;value: stringValue&quot;&gt; &lt;/textarea&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;パスワード:&lt;/td&gt;
		&lt;td&gt;&lt;input type=&quot;password&quot; data-bind=&quot;value: passwordValue&quot; /&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;チェックボックス:&lt;/td&gt;
		&lt;td&gt;&lt;input type=&quot;checkbox&quot; data-bind=&quot;checked: booleanValue&quot; /&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;ドロップダウンリスト:&lt;/td&gt;
		&lt;td&gt;&lt;select data-bind=&quot;options: optionValues, value: selectedOptionValue&quot;&gt;&lt;/select&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;複数選択リスト:&lt;/td&gt;
		&lt;td&gt;&lt;select multiple=&quot;multiple&quot; data-bind=&quot;options: optionValues, selectedOptions: multipleSelectedOptionValues&quot;&gt;&lt;/select&gt;&lt;/td&gt;
	&lt;/tr&gt;
	&lt;tr&gt;
		&lt;td class=&quot;label&quot;&gt;ラジオボタン:&lt;/td&gt;
		&lt;td&gt;
			&lt;label&gt;&lt;input type=&quot;radio&quot; value=&quot;Alpha&quot; data-bind=&quot;checked: radioSelectedOptionValue&quot; /&gt;Alpha&lt;/label&gt;
			&lt;label&gt;&lt;input type=&quot;radio&quot; value=&quot;Beta&quot; data-bind=&quot;checked: radioSelectedOptionValue&quot; /&gt;Beta&lt;/label&gt;
			&lt;label&gt;&lt;input type=&quot;radio&quot; value=&quot;Gamma&quot; data-bind=&quot;checked: radioSelectedOptionValue&quot; /&gt;Gamma&lt;/label&gt;
		&lt;/td&gt;
	&lt;/tr&gt;
&lt;/table&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var viewModel = {
	stringValue : ko.observable(&quot;こんにちわ&quot;),
	passwordValue : ko.observable(&quot;hogehoge&quot;),
	booleanValue : ko.observable(true),
	optionValues : [&quot;Alpha&quot;, &quot;Beta&quot;, &quot;Gamma&quot;],
	selectedOptionValue : ko.observable(&quot;Gamma&quot;),
	multipleSelectedOptionValues : ko.observable([&quot;Alpha&quot;]),
	radioSelectedOptionValue : ko.observable(&quot;Beta&quot;)
};
ko.applyBindings(viewModel);</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/ZbrB7/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>
