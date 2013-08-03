<style type="text/css">
    .demo TR { vertical-align: top; }
	.demo TABLE { border-spacing: 0 5px; }
    .demo TABLE,
	.demo TD,
	.demo TH { padding: 0.2em; border-width: 0; margin: 0; }
    .demo TD A { font-size: 0.8em; text-decoration: none; }
    .demo table.contactsEditor > tbody > TR { background-color: #1D0D0D; }
    .demo td input { width: 8em; }
</style>
<article>
	
	<h1>連絡帳</h1>
	
	<p>
		ネストしたデータ構造を編集するかなり一般的な例です。
		ViewModel はシンプル、View へのバインドは素直で簡単です。
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<h2>連絡先</h2>
		<div id='contactsList'>
			<table class='contactsEditor'>
				<thead>
					<tr>
						<th>ファーストネーム</th>
						<th>ラストネーム</th>
						<th>電話番号</th>
					</tr>
				</thead>
				<tbody data-bind="foreach: contacts">
					<tr>
						<td>
							<input type="text" data-bind='value: firstName' />
							<div><a href='#' data-bind='click: $root.removeContact'>削除</a></div>
						</td>
						<td><input type="text" data-bind='value: lastName' /></td>
						<td>
							<table>
								<tbody data-bind="foreach: phones">
									<tr>
										<td><input type="text" data-bind='value: type' /></td>
										<td><input type="text" data-bind='value: number' /></td>
										<td><a href='#' data-bind='click: $root.removePhone'>削除</a></td>
									</tr>
								</tbody>
							</table>
							<a href='#' data-bind='click: $root.addPhone'>電話番号を追加</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<p>
			<button data-bind='click: addContact'>連絡先を追加</button>
			<button data-bind='click: save, enable: contacts().length > 0'>JSON形式で保存</button>
		</p>

		<textarea data-bind='value: lastSavedJson' rows='5' cols='60' disabled='disabled'> </textarea>
	</div>
	<script type="text/javascript">
		var initialData = [
			{ firstName: "ダニー", lastName: "ラルッソ", phones: [
					{ type: "携帯", number: "(555) 121-2121" },
					{ type: "電話", number: "(555) 123-4567"}]
			},
			{ firstName: "先生", lastName: "宮城", phones: [
					{ type: "携帯", number: "(555) 444-2222" },
					{ type: "電話", number: "(555) 999-1212"}]
			}
		];

		var ContactsModel = function(contacts) {
			var self = this;
			self.contacts = ko.observableArray(ko.utils.arrayMap(contacts, function(contact) {
				return { firstName: contact.firstName, lastName: contact.lastName, phones: ko.observableArray(contact.phones) };
			}));

			self.addContact = function() {
				self.contacts.push({
					firstName: "",
					lastName: "",
					phones: ko.observableArray()
				});
			};

			self.removeContact = function(contact) {
				self.contacts.remove(contact);
			};

			self.addPhone = function(contact) {
				contact.phones.push({
					type: "",
					number: ""
				});
			};

			self.removePhone = function(phone) {
				$.each(self.contacts(), function() { this.phones.remove(phone) })
			};

			self.save = function() {
				self.lastSavedJson(JSON.stringify(ko.toJS(self.contacts), null, 2));
			};

			self.lastSavedJson = ko.observable("")
		};

ko.applyBindings(new ContactsModel(initialData), document.getElementById('demo_1'));
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;h2&gt;連絡先&lt;/h2&gt;
&lt;div id='contactsList'&gt;
	&lt;table class='contactsEditor'&gt;
		&lt;thead&gt;
			&lt;tr&gt;
				&lt;th&gt;ファーストネーム&lt;/th&gt;
				&lt;th&gt;ラストネーム&lt;/th&gt;
				&lt;th&gt;電話番号&lt;/th&gt;
			&lt;/tr&gt;
		&lt;/thead&gt;
		&lt;tbody data-bind=&quot;foreach: contacts&quot;&gt;
			&lt;tr&gt;
				&lt;td&gt;
					&lt;input data-bind='value: firstName' /&gt;
					&lt;div&gt;&lt;a href='#' data-bind='click: $root.removeContact'&gt;削除&lt;/a&gt;&lt;/div&gt;
				&lt;/td&gt;
				&lt;td&gt;&lt;input data-bind='value: lastName' /&gt;&lt;/td&gt;
				&lt;td&gt;
					&lt;table&gt;
						&lt;tbody data-bind=&quot;foreach: phones&quot;&gt;
							&lt;tr&gt;
								&lt;td&gt;&lt;input data-bind='value: type' /&gt;&lt;/td&gt;
								&lt;td&gt;&lt;input data-bind='value: number' /&gt;&lt;/td&gt;
								&lt;td&gt;&lt;a href='#' data-bind='click: $root.removePhone'&gt;削除&lt;/a&gt;&lt;/td&gt;
							&lt;/tr&gt;
						&lt;/tbody&gt;
					&lt;/table&gt;
					&lt;a href='#' data-bind='click: $root.addPhone'&gt;電話番号を追加&lt;/a&gt;
				&lt;/td&gt;
			&lt;/tr&gt;
		&lt;/tbody&gt;
	&lt;/table&gt;
&lt;/div&gt;

&lt;p&gt;
	&lt;button data-bind='click: addContact'&gt;連絡先を追加&lt;/button&gt;
	&lt;button data-bind='click: save, enable: contacts().length &gt; 0'&gt;JSON形式で保存&lt;/button&gt;
&lt;/p&gt;

&lt;textarea data-bind='value: lastSavedJson' rows='5' cols='60' disabled='disabled'&gt; &lt;/textarea&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">var initialData = [
	{ firstName: &quot;ダニー&quot;, lastName: &quot;ラルッソ&quot;, phones: [
			{ type: &quot;携帯&quot;, number: &quot;(555) 121-2121&quot; },
			{ type: &quot;電話&quot;, number: &quot;(555) 123-4567&quot;}]
	},
	{ firstName: &quot;先生&quot;, lastName: &quot;宮城&quot;, phones: [
			{ type: &quot;携帯&quot;, number: &quot;(555) 444-2222&quot; },
			{ type: &quot;電話&quot;, number: &quot;(555) 999-1212&quot;}]
	}
];

var ContactsModel = function(contacts) {
	var self = this;
	self.contacts = ko.observableArray(ko.utils.arrayMap(contacts, function(contact) {
		return { firstName: contact.firstName, lastName: contact.lastName, phones: ko.observableArray(contact.phones) };
	}));

	self.addContact = function() {
		self.contacts.push({
			firstName: &quot;&quot;,
			lastName: &quot;&quot;,
			phones: ko.observableArray()
		});
	};

	self.removeContact = function(contact) {
		self.contacts.remove(contact);
	};

	self.addPhone = function(contact) {
		contact.phones.push({
			type: &quot;&quot;,
			number: &quot;&quot;
		});
	};

	self.removePhone = function(phone) {
		$.each(self.contacts(), function() { this.phones.remove(phone) })
	};

	self.save = function() {
		self.lastSavedJson(JSON.stringify(ko.toJS(self.contacts), null, 2));
	};

	self.lastSavedJson = ko.observable(&quot;&quot;)
};

ko.applyBindings(new ContactsModel(initialData));</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/gZC5k/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>
