# "enable" バインディング

### 用途 {#purpose}

`enable` バインディングは、値が `true`
のときだけ関連付けられた DOM エレメントを使用可能にします。
`input` や `select`, `textarea` などの form 部品でよく使います。

### 例 {#example}

```html
<!-- View -->
<p>
    <input type='checkbox' data-bind="checked: hasCellphone" />
    携帯電話を持っている
</p>
<p>
    電話番号:
    <input type='text' data-bind="value: cellphoneNumber, enable: hasCellphone" />
</p>
```

```javascript
// ViewModel
var viewModel = {
    hasCellphone : ko.observable(false),
    cellphoneNumber: ""
};
```

最初 "電話番号" のテキストボックスは disabled の状態で入力できません。
"携帯電話を持っている" のチェックボックスにチェックを入れたときのみ入力できるようになります。

### パラメタ {#parameters}

- 主パラメタ
	
	関連付けられた DOM エレメントを使用可能にするか否かを制御するための値です。
	
	boolean でない値は妥当に解釈されます。
	例えば `0` と `null` は `false` として扱われ、
	`21` と `null` でないオブジェクトは `true` として扱われます。
	
	このパラメタの値が Observable である場合、このバインディングは値が変更される度にエレメントの 使用可 / 使用不可 を更新します。
	Observable でない場合は、エレメントの 使用可 / 使用不可 は一度だけ設定され、以降は更新されません。

- 追加パラメタ

	なし

### (注1) 任意の式を使う {#note-using-arbitrary-javascript-expressions}

変数（プロパティ）への参照だけでなく、エレメントの使用可否を式を使って制御することもできます。

```html
<button data-bind="enable: parseAreaCode(viewModel.cellphoneNumber()) != '555'">
    なにかする
</button>
```

### 依存 {#dependencies}

Knockout コアライブラリ以外、なし。

<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/<?php echo $identifier?>.html">こちら</a></div>