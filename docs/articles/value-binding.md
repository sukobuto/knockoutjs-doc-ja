# "value" バインディング

### 用途 {#purpose}

<code>value</code> バインディングは関連付けられた DOM エレメントの値と ViewModel のプロパティーをリンクさせます。
<code>&lt;input&gt;</code> や <code>&lt;select&gt;</code>, <code>&lt;textarea&gt;</code>
などのフォーム部品で使用します。

ユーザがフォームの値を編集すると、ViewModel の値も更新されます。
同様に、ViewModel の値を変更すると、フォームの値に反映されます。

(注) チェックボックスまたはラジオボタンを使う場合は <code>value</code>
バインディングではなく、<a href="checked-binding">"checked" バインディング</a>
を使ってチェック状態を管理します。

### 例 {#example}

```html
<p>ユーザ名: <input data-bind="value: userName" /></p>
<p>パスワード: <input type="password" data-bind="value: userPassword" /></p>
```

```javascript
var viewModel = {
	userName: ko.observable(""),        // 最初は空欄
	userPassword: ko.observable("abc"), // 事前に挿入
};
```

### パラメタ {#parameters}

- 主パラメタ
	
	Knockout はエレメントの <code>value</code> プロパティにこのパラメタの値をセットします。
	以前の値は上書きされます。
	
	このパラメタが Observable である場合、このバインディングは値が変更される度にエレメントの値をを更新します。
	Observable でない場合は、エレメントの値は一度だけ設定され、以降は更新されません。
	
	数値や文字列以外の値 (オブジェクトもしくは配列) を指定した場合、
	表示されるテキストは <code>指定したパラメタ.toString()</code> を実行した結果となります。
	(この機能に大した利用価値はないので、文字列か数値を指定するのがベストです。)
	
	ユーザが対象のフォーム部品を編集したとき、Knockout は ViewModel 上のプロパティを更新します。
	通常 Knockout は、(1) フォーム部品の値が変更され、(2) かつ他の DOM ノードにフォーカスが移った時点で
	ViewModel を更新します (つまり、<code>change</code> イベント発生時)。<br>
	しかし <code>valueUpdate</code> という追加パラメタを使うことで、更新を発生させるためのイベントを設定することができます。
	
- 追加パラメタ
	
	- `valueUpdate`
		
		バインディングに <code>valueUpdate</code> というパラメタが含まれる場合、
		<code>change</code> イベントのほかにフォーム部品の値の変更を検知する追加のイベントが定義されます。
		一般的によく使われる値は次の文字列です。
		
		- <code>'keyup'</code> &nbsp;-&nbsp; ユーザがキーを離したタイミングで更新</li>
		- <code>'keypress'</code> &nbsp;-&nbsp; ユーザがキーを押さえたタイミングで更新</li>
		- <code>'afterkeydown'</code> &nbsp;-&nbsp; ユーザがキーボードで入力を開始したらすぐに更新  
			ブラウザの <code>keydown</code> イベントをキャッチし、非同期で処理します。
		
		ViewModel の値をリアルタイムで同期するのが目的であれば、<code>'afterkeydown'</code> が最適です。
		
		#### 例
		
		```html
		<p>値: <input data-bind="value: someValue, valueUpdate: 'input'" /></p>
        <p>入力された値: <span data-bind="text: someValue"></span></p> <!-- リアルタイムで更新される -->
         
        <script type="text/javascript">
            var viewModel = {
                someValue: ko.observable("この値を編集")
            };
        </script>
		```
	
	- `valueAllowUnset`
		
		[(注1)](#note-1-working-with-drop-down-lists-ie-select-elements) 参照。
		`valueAllowUnset` は `<select>` エレメントに `value` バインディングを使う場合にのみ適用されます。
		その他のエレメントには何も影響しません。
		
### (注1)ドロップダウンリストで使う (`<select>` エレメント)

Knockout にはドロップダウンリスト (`<select>`) のための専用機能があります。
`value` バインディングは `options` バインディングと同時に使うことで、
単なる文字列値ではなく任意の JavaScript オブジェクトがバインドできるようになります。
これにより、モデルオブジェクトの集合からユーザを選択させるといった機能がかんたんに作れます。
この例として [`options` バインディング](options-binding) または複数選択リストであれば
[`selectedOptions` バインディング](selectedOptions-binding) をご覧ください。

`options` バインディングを使わなくとも、`<select>` エレメントで `value` バインディングを使うことができます。
この場合、`<option>` エレメントを自分でマークアップするか `foreach`, `template` バインディングで構築する方法があります。
`<optgroup>` を使った入れ子を作ることも可能で、Knockout はちゃんと初期値を選択状態にセットしてくれます。

#### `<select>` エレメントで `valueAllowUnset` を使う {#using-valueallowunset-with-select-elements}

通常 `<select>` エレメントで `value` バインディングを使うということは、
`<select>` のどのアイテムが選択されているのかを、紐付けられたプロパティで表現することを意味します。
では、プロパティにセットされたアイテムが `<select>` の選択肢に存在しなかったら何が起きるでしょうか。
Knockout はデフォルトで、既に選択されていたアイテムでプロパティを上書きすることにより、
ViewModel と View の同期がとれなくなることを抑止します。

しかし、この挙動が好ましくないときもあるでしょう。
`<select>` 選択肢に該当しないアイテムがセットされることを許可する場合は、`valueAllowUnset: true` を指定します。
この場合 `<select>` に該当する選択肢がなければ `<select>` は未選択状態となり、見た目は空欄になります。
その後ユーザがアイテムを選択すると、通常通り ViewModel に反映されます。

```html
<p>
    国を選択:
    <select data-bind="options: countries,
                       optionsCaption: '-選択してください-',
                       value: selectedCountry,
                       valueAllowUnset: true"></select>
</p>
```

```javascript
var viewModel = {
	countries: ['日本', 'ボリビア', 'ニュージーランド'],
	selectedCountry: ko.observable('ラトビア')
};
```

上記の例では `selectedCountry` は `'ラトビア'` という値を保持しつづけ、
該当する選択肢が無いためにドロップダウンは空欄となります。

もし `valueAllowUnset: true` を指定しなかったら、Knockout は `selectedCountry` を
`undefined` で上書きするため、`'-選択してください-'` という選択肢にマッチします。

> ### 訳者注
この節 (select エレメントで valueAllowUnset を使う) の翻訳は内容に確信が持てないため、ご協力を募集しております。  
内容は [JSFIDDLE](http://jsfiddle.net/sukobuto/W7nkM/1/) で実験しましたが、結果が変わらないため悩んでおります。  
お気づきの点がございましたら、[Github](https://github.com/sukobuto/knockoutjs-doc-ja/blob/master/docs/articles/value-binding.md)
にて Issue または Pull Request を送っていただけると非常に助かります。

### (注2) Observable および通常のプロパティの更新

`value` を使って FORM 部品と Observable プロパティを紐付けた場合、
Knockout は双方向バインディングを構築します。
つまりどちらかが変更されれば、もう一方へ反映されます。

ただし Observable ではないプロパティ (単なる文字列や式など) と FORM 部品を紐付けた場合、
Knockout は以下のように処理します。

- 単なる ViewModel のプロパティなど単純なプロパティを参照した場合、
	Knockout は FORM 部品の初期状態としてそのプロパティの値をセットします。
	FORM 部品が編集されたら、 Knockout はプロパティへ値を書き戻します。
	プロパティは Observable ではないため変更を感知することはできず、
	FORM からプロパティへの単方向バインディングとなります。
- 関数呼び出しの結果や比較演算の結果など、単純なプロパティではないものを参照した場合、
	Knockout は FORM 部品の初期状態としてその値をセットします。
	ただしどんな FORM 部品の変更も書き戻すことはできません。
	この場合は一度きりの value setter として機能し、変更に対応するバインディングではなくなります。

#### 例:

```html
<!-- 双方向バインディング。テキストの初期値をセットし、お互いに同期する。-->
<p>First value: <input data-bind="value: firstValue" /></p>
 
<!-- 単方向バインディング。テキストの初期値をセットし、テキストボックスからのみ同期する。-->
<p>Second value: <input data-bind="value: secondValue" /></p>
 
<!-- バインディングではない。テキストの初期値をセットするが、いかなる変更にも対応しない。-->
<p>Third value: <input data-bind="value: secondValue.length > 8" /></p>
```

```js
var viewModel = {
	firstValue: ko.observable("hello"), // Observable
	secondValue: "hello, again"         // Not observable
};
```

### 依存 {#dependencies}

Knockout コアライブラリ以外、なし。

<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>