<link href='resources/twitterExample.css' rel='Stylesheet' />
<script src='resources/twitterApi.js' type='text/javascript'> </script>
<style type='text/css'>
	.demo { padding: 0px !important; }
	.demo select { height: 1.7em; }
	.demo button { height: 2em; }
	.demo .currentUsers li {
		list-style-type: none !important;
		margin-left: 0px !important;
	}
	.demo .configuration p,
	.demo .configuration li {
		color: #555 !important;
		text-shadow: none !important;
	}
</style>

<article>
	
	<h1>Twitter クライアント</h1>
	
	<p>
		Knockout の様々な機能を組み合わせて、リッチなユーザインタフェースを作成する洗練された方法を示すサンプルです。
	</p>
	
	<ul>
		<li>
			ユーザのデータは JavaScript オブジェクトとして保管され、
			選択されたリストにもとづいてレンダリングされます。
			これにより、不必要な DOM を隠したりせずとも、
			「Twitterユーザがどのユーザリストに含まれるか」
			についての情報を保つことができます。
		</li>
		<li>
			それぞれのボタンは、条件に基づいて適宜 使用可/使用不可 が切り替わります。
			たとえば、<code>hasUnsavedChanges</code> という <code>computed</code> は、
			「保存」ボタンの有効状態を制御します。
		</li>
		<li>
			JSON を通信形式とする外部の Web サービスからデータを取得し、
			ViewModel に統合することで、すぐに画面に表示されるようになります。
		</li>
	</ul>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<div class='configuration'>
			<div class='listChooser'>
				<button data-bind='click: deleteList, enable: editingList.name'>削除</button>
				<button data-bind='click: saveChanges, enable: hasUnsavedChanges'>保存</button>
				<select data-bind='options: savedLists, optionsValue: "name", value: editingList.name'> </select>
			</div>

			<p>表示中のユーザアカウント: <span data-bind='text: editingList.userNames().length'> </span> 件</p>
			<div class='currentUsers' data-bind='with: editingList'>
				<ul data-bind='foreach: userNames'>
					<li>
						<button data-bind='click: $root.removeUser'>削除</button>
						<div data-bind="text: $data"> </div>
					</li>
				</ul>
			</div>

			<form data-bind='submit: addUser'>
				<input data-bind='value: userNameToAdd, valueUpdate: "keyup", css: { invalid: !userNameToAddIsValid() }' placeholder="ユーザ名"/>
				<button data-bind='enable: canAddUserName' type='submit'>追加</button>
			</form>
		</div>
		<div class='tweets'>
			<div class='loadingIndicator'>読込中...</div>
			<table width='100%' data-bind="foreach: currentTweets">
				<tr>
					<td><img data-bind='attr: { src: profile_image_url }' /></td>
					<td>
						<a class='twitterUser' data-bind='attr: { href: "http://twitter.com/" + from_user }, text: from_user' href='http://twitter.com/${ from_user }' > </a>
						<span data-bind="text: text"> </span>
						<div class='tweetInfo' data-bind='text: created_at'> </div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<script type="text/javascript">
		// ViewModel は作業中のすべての状態を保持します。また、それらを編集できるようにする方法も持っており、
		// さらに必要な状態を背後のデータから計算するために Computed Observable を使用しています。
		// --
		// View (= HTML UI) はこれらの状態を data-bind 属性を使ってバインドします。
		// そのため常に ViewModel の最新の状態に保たれます。
		// また、ViewModel は View にどのようにバインドされるかを意識せずに記述することができます。
		// 
		var savedLists = [
			{ name: "Celebrities", userNames: ['JohnCleese', 'MCHammer', 'StephenFry', 'algore', 'StevenSanderson']},
			{ name: "Microsoft people", userNames: ['BillGates', 'shanselman', 'ScottGu']},
			{ name: "Tech pundits", userNames: ['Scobleizer', 'LeoLaporte', 'techcrunch', 'BoingBoing', 'timoreilly', 'codinghorror']}
		];

		var TwitterListModel = function(lists, selectedList) {
			this.savedLists = ko.observableArray(lists);
			this.editingList = {
				name: ko.observable(selectedList),
				userNames: ko.observableArray()
			};
			this.userNameToAdd = ko.observable("");
			this.currentTweets = ko.observableArray([])

			this.findSavedList = function(name) {
				var lists = this.savedLists();
				return ko.utils.arrayFirst(lists, function(list) {
					return list.name === name;
				});
			};

			this.addUser = function() {
				if (this.userNameToAdd() && this.userNameToAddIsValid()) {
					this.editingList.userNames.push(this.userNameToAdd());
					this.userNameToAdd("");
				}
			};

			this.removeUser = function(userName) { 
				this.editingList.userNames.remove(userName) 
			}.bind(this);

			this.saveChanges = function() {
				var saveAs = prompt("次の名前で保存: ", this.editingList.name());
				if (saveAs) {
					var dataToSave = this.editingList.userNames().slice(0);
					var existingSavedList = this.findSavedList(saveAs);
					if (existingSavedList) existingSavedList.userNames = dataToSave; // 既存のリストは上書きする。
					else this.savedLists.push({
						name: saveAs,
						userNames: dataToSave
					}); // 新しいリストを追加
					this.editingList.name(saveAs);
				}
			};

			this.deleteList = function() {
				var nameToDelete = this.editingList.name();
				var savedListsExceptOneToDelete = $.grep(this.savedLists(), function(list) {
					return list.name != nameToDelete
				});
				this.editingList.name(savedListsExceptOneToDelete.length == 0 ? null : savedListsExceptOneToDelete[0].name);
				this.savedLists(savedListsExceptOneToDelete);
			};

			ko.computed(function() {
				// viewmodel.editingList.name() を監視する。
				// 変更があれば、savedList (=データ元) から一致するリスト名のユーザ名リストを取得し、
				// editingList.userNames にコピーする。
				var savedList = this.findSavedList(this.editingList.name());
				if (savedList) {
					var userNamesCopy = savedList.userNames.slice(0);
					this.editingList.userNames(userNamesCopy);
				} else {
					this.editingList.userNames([]);
				}
			}, this);

			this.hasUnsavedChanges = ko.computed(function() {
				if (!this.editingList.name()) {
					return this.editingList.userNames().length > 0;
				}
				var savedData = this.findSavedList(this.editingList.name()).userNames;
				var editingData = this.editingList.userNames();
				return savedData.join("|") != editingData.join("|");
			}, this);

			this.userNameToAddIsValid = ko.computed(function() {
				return (this.userNameToAdd() == "") || (this.userNameToAdd().match(/^\s*[a-zA-Z0-9_]{1,15}\s*$/) != null);
			}, this);

			this.canAddUserName = ko.computed(function() {
				return this.userNameToAddIsValid() && this.userNameToAdd() != "";
			}, this);

			// リスト内のユーザのツイートは、(非同期に) editingList.userNames から computed で自動取得する
			ko.computed(function() {
				twitterApi.getTweetsForUsers(this.editingList.userNames(), this.currentTweets);
			}, this);
		};

		ko.applyBindings(new TwitterListModel(savedLists, "Tech pundits"), document.getElementById('demo_1'));

		// 「読込中」の表示は jQuery でおこなう。- Knockout では何もしない。
		$(".loadingIndicator").ajaxStart(function() {
			$(this).fadeIn();
		}).ajaxComplete(function() {
			$(this).fadeOut();
		});
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;div class='configuration'&gt;
	&lt;div class='listChooser'&gt;
		&lt;button data-bind='click: deleteList, enable: editingList.name'&gt;削除&lt;/button&gt;
		&lt;button data-bind='click: saveChanges, enable: hasUnsavedChanges'&gt;保存&lt;/button&gt;
		&lt;select data-bind='options: savedLists, optionsValue: &quot;name&quot;, value: editingList.name'&gt; &lt;/select&gt;
	&lt;/div&gt;

	&lt;p&gt;表示中のユーザアカウント: &lt;span data-bind='text: editingList.userNames().length'&gt; &lt;/span&gt; 件&lt;/p&gt;
	&lt;div class='currentUsers' data-bind='with: editingList'&gt;
		&lt;ul data-bind='foreach: userNames'&gt;
			&lt;li&gt;
				&lt;button data-bind='click: $root.removeUser'&gt;削除&lt;/button&gt;
				&lt;div data-bind=&quot;text: $data&quot;&gt; &lt;/div&gt;
			&lt;/li&gt;
		&lt;/ul&gt;
	&lt;/div&gt;

	&lt;form data-bind='submit: addUser'&gt;
		&lt;label&gt;ユーザアカウントを追加:&lt;/label&gt;
		&lt;input data-bind='value: userNameToAdd, valueUpdate: &quot;keyup&quot;, css: { invalid: !userNameToAddIsValid() }' /&gt;
		&lt;button data-bind='enable: canAddUserName' type='submit'&gt;追加&lt;/button&gt;
	&lt;/form&gt;
&lt;/div&gt;
&lt;div class='tweets'&gt;
	&lt;div class='loadingIndicator'&gt;読込中...&lt;/div&gt;
	&lt;table width='100%' data-bind=&quot;foreach: currentTweets&quot;&gt;
		&lt;tr&gt;
			&lt;td&gt;&lt;img data-bind='attr: { src: profile_image_url }' /&gt;&lt;/td&gt;
			&lt;td&gt;
				&lt;a class='twitterUser' data-bind='attr: { href: &quot;http://twitter.com/&quot; + from_user }, text: from_user' href='http://twitter.com/${ from_user }' &gt; &lt;/a&gt;
				&lt;span data-bind=&quot;text: text&quot;&gt; &lt;/span&gt;
				&lt;div class='tweetInfo' data-bind='text: created_at'&gt; &lt;/div&gt;
			&lt;/td&gt;
		&lt;/tr&gt;
	&lt;/table&gt;
&lt;/div&gt;</pre>
	
	<h2>コード: ViewModel</h2>
	<pre class="brush: js;">// ViewModel は作業中のすべての状態を保持します。また、それらを編集できるようにする方法も持っており、
// さらに必要な状態を背後のデータから計算するために Computed Observable を使用しています。
// --
// View (= HTML UI) はこれらの状態を data-bind 属性を使ってバインドします。
// そのため常に ViewModel の最新の状態に保たれます。
// また、ViewModel は View にどのようにバインドされるかを意識せずに記述することができます。
// 
var savedLists = [
	{ name: &quot;Celebrities&quot;, userNames: ['JohnCleese', 'MCHammer', 'StephenFry', 'algore', 'StevenSanderson']},
	{ name: &quot;Microsoft people&quot;, userNames: ['BillGates', 'shanselman', 'ScottGu']},
	{ name: &quot;Tech pundits&quot;, userNames: ['Scobleizer', 'LeoLaporte', 'techcrunch', 'BoingBoing', 'timoreilly', 'codinghorror']}
];

var TwitterListModel = function(lists, selectedList) {
	this.savedLists = ko.observableArray(lists);
	this.editingList = {
		name: ko.observable(selectedList),
		userNames: ko.observableArray()
	};
	this.userNameToAdd = ko.observable(&quot;&quot;);
	this.currentTweets = ko.observableArray([])

	this.findSavedList = function(name) {
		var lists = this.savedLists();
		return ko.utils.arrayFirst(lists, function(list) {
			return list.name === name;
		});
	};

	this.addUser = function() {
		if (this.userNameToAdd() &amp;&amp; this.userNameToAddIsValid()) {
			this.editingList.userNames.push(this.userNameToAdd());
			this.userNameToAdd(&quot;&quot;);
		}
	};

	this.removeUser = function(userName) { 
		this.editingList.userNames.remove(userName) 
	}.bind(this);

	this.saveChanges = function() {
		var saveAs = prompt(&quot;次の名前で保存: &quot;, this.editingList.name());
		if (saveAs) {
			var dataToSave = this.editingList.userNames().slice(0);
			var existingSavedList = this.findSavedList(saveAs);
			if (existingSavedList) existingSavedList.userNames = dataToSave; // 既存のリストは上書きする。
			else this.savedLists.push({
				name: saveAs,
				userNames: dataToSave
			}); // 新しいリストを追加
			this.editingList.name(saveAs);
		}
	};

	this.deleteList = function() {
		var nameToDelete = this.editingList.name();
		var savedListsExceptOneToDelete = $.grep(this.savedLists(), function(list) {
			return list.name != nameToDelete
		});
		this.editingList.name(savedListsExceptOneToDelete.length == 0 ? null : savedListsExceptOneToDelete[0].name);
		this.savedLists(savedListsExceptOneToDelete);
	};

	ko.computed(function() {
		// viewmodel.editingList.name() を監視する。
		// 変更があれば、savedList (=データ元) から一致するリスト名のユーザ名リストを取得し、
		// editingList.userNames にコピーする。
		var savedList = this.findSavedList(this.editingList.name());
		if (savedList) {
			var userNamesCopy = savedList.userNames.slice(0);
			this.editingList.userNames(userNamesCopy);
		} else {
			this.editingList.userNames([]);
		}
	}, this);

	this.hasUnsavedChanges = ko.computed(function() {
		if (!this.editingList.name()) {
			return this.editingList.userNames().length &gt; 0;
		}
		var savedData = this.findSavedList(this.editingList.name()).userNames;
		var editingData = this.editingList.userNames();
		return savedData.join(&quot;|&quot;) != editingData.join(&quot;|&quot;);
	}, this);

	this.userNameToAddIsValid = ko.computed(function() {
		return (this.userNameToAdd() == &quot;&quot;) || (this.userNameToAdd().match(/^\s*[a-zA-Z0-9_]{1,15}\s*$/) != null);
	}, this);

	this.canAddUserName = ko.computed(function() {
		return this.userNameToAddIsValid() &amp;&amp; this.userNameToAdd() != &quot;&quot;;
	}, this);

	// リスト内のユーザのツイートは、(非同期に) editingList.userNames から computed で自動取得する
	ko.computed(function() {
		twitterApi.getTweetsForUsers(this.editingList.userNames(), this.currentTweets);
	}, this);
};

ko.applyBindings(new TwitterListModel(savedLists, &quot;Tech pundits&quot;));

// 「読込中」の表示は jQuery でおこなう。- Knockout では何もしない。
$(&quot;.loadingIndicator&quot;).ajaxStart(function() {
	$(this).fadeIn();
}).ajaxComplete(function() {
	$(this).fadeOut();
});</pre>
	
	<div class="tail_mini_text">
		<a href="http://jsfiddle.net/rniemeyer/rhQLj/" target="_blank">jsFiddle で試す</a> /
		原文は<a href="http://knockoutjs.com/examples/<?php echo $identifier?>.html">こちら</a>
	</div>
	
</article>
