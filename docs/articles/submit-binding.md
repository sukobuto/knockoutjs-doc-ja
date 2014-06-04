# "submit" バインディング

### 用途 {#purpose}

`submit` バインディングは、関連付けられた DOM エレメントが submit されたときに
指定した JavaScript 関数を実行するイベントハンドラを追加します。

form で `submit` バインディングを使用した場合、
Knockout はその form の、ブラウザの通常の動作を妨げます (`preventDefault`)。
つまり、ブラウザは指定された関数を実行しますが、form の内容をサーバに送信することはありません。
この仕様の理由は、通常 `submit` バインディングを使う場合、form は ViewModel のインターフェイスとして使い、
標準の HTML form としては使わないからです。
もしも標準の HTML form としてフォームを送信させたいのであれば、`submit` にバインドした関数が
`true` を返却するようにして下さい。
	
### 例 {#example}
	
```html
<!-- View -->
<form data-bind="submit: doSomething">
	...フォーム部品をここに配置...
	<button type="submit">登録</button>
</form>
```

```javascript
// ViewModel
var viewModel = {
	doSomething : function(formElement) {
		// ... なにかする
	}
};
```
	
上記の例を見ていただければわかるように、submit にバインドした関数にはパラメタとして form エレメントが渡されます。
このパラメタは無視できますが、<code>ko.utils.postJson</code> のようなユーティリティで利用します。
	
<blockquote>
	<h3>訳者注 - ko.utils.postJson</h3>
	<p>
		<code>ko.utils.postJson</code> は form エレメントから、そのフォームのデータを
		JSON にしてサーバに POST 送信するユーティリティ機能です。
		現在、本家ドキュメントにおいてこの項目はリンク切れとなっており、
		修正され次第翻訳する予定です。
	</p>
</blockquote>

### Submit ボタンに <code>cilck</code> バインディングすればいいのでは？ {#why_not_just_put_a__handler_on_the_submit_button}

確かに、form で `submit` を使う代わりに、Submit ボタンで `click` を使うことができます。
`submit` ならではの利点は、Submit をするのにボタンクリック以外の手段があるということです。
たとえばテキストボックスに入力中、Enter キーで Submit できます。

<div class="demo" id="demo_1">
	<h3>訳者注 - 検索ボックスでデモ</h3>
	↓click バインディングだと、ボタンでしか反応しない。<br>
	<div>
		<input type="text" data-bind="value: Keyword" placeholder="キーワードを入力"/>
		<button data-bind="click: Search">検索</button>
	</div>
	↓submit バインディングだと、テキストボックスで Enter キーをタイプしても実行される。<br>
	<form data-bind="submit: Search">
		<input type="text" data-bind="value: Keyword, valueUpdate: 'afterkeydown'" placeholder="キーワードを入力"/>
		<button type="submit">検索</button>
	</form>
	<div data-bind="text: Result" style="background-color: #2a371d"> </div>
</div>

```html
<!-- View -->
<h3>訳者注 - 検索ボックスでデモ</h3>
↓click バインディングだと、ボタンでしか反応しない。<br>
<div>
	<input data-bind="value: Keyword" placeholder="キーワードを入力"/>
	<button data-bind="click: Search">検索</button>
</div>
↓submit バインディングだと、テキストボックスで Enter キーをタイプしても実行される。<br>
<form data-bind="submit: Search">
	<input data-bind="value: Keyword, valueUpdate: 'afterkeydown'" placeholder="キーワードを入力"/>
	<button type="submit">検索</button>
</form>
<div data-bind="text: Result"> </div>
```

```javascript
// ViewModel
function SearchBoxViewModel() {
	var self = this;
	self.Keyword = ko.observable("");
	self.Result = ko.observable();
	self.Search = function() {
		if (self.Keyword() === "") {
			alert("キーワードを入力して下さい");
			return;
		}
		self.Result(self.Keyword() + " の検索結果...");
		self.Keyword("");
	};
}
ko.applyBindings(new SearchBoxViewModel());
```
		
<script type="text/javascript">
	function SearchBoxViewModel() {
		var self = this;
		self.Keyword = ko.observable("");
		self.Result = ko.observable();
		self.Search = function() {
			if (self.Keyword() === "") {
				alert("キーワードを入力して下さい");
				return;
			}
			self.Result(self.Keyword() + " の検索結果...");
			self.Keyword("");
		};
	}
	ko.applyBindings(new SearchBoxViewModel(), document.getElementById('demo_1'));
</script>
	
### パラメタ {#parameters}

- 主パラメタ

	エレメントの `submit` イベントにバインドしたい関数です。
	
	どんな JavaScript 関数も使用できます。ViewModel の関数である必要は、必ずしもありません。  
	`submit: someObject.someFunction` のように書くことで、どんなオブジェクトの関数も参照できます。

- 追加パラメタ

	なし
	
### 備考 {#notes}

`submit` にバインドした関数に追加の引数を渡す方法や、ViewModel に属さない関数が呼び出された際の `this` を制御する方法については
[`click` バインディング](click-binding) を参照してください。`click` バインディングの（注）はすべて `submit` バインディングにも適用できます。

### 依存 {#dependencies}

Knockout コアライブラリ以外、なし。
