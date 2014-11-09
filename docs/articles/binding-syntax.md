#data-bind の書き方について

Knockoutの宣言的バインディング機構は、UIとデータをリンクするための簡潔で強力な方法を提供します。これは通常、シンプルなデータプロパティや、単一のバインディングを使用する場合には、簡単であり明白です。より複雑なバインディングのために、それはKnockoutのバインディング機構の振る舞いと書き方を、よりよく理解するのに役立ちます。

## バインディングの書き方

バインディングはコロンで区切られた2つのアイテム、バインディング名と値によって構成されています。こちらは単一の、簡単なバインディングの例です：

```html
Today's message is: <span data-bind="text: myMessage"></span>
```

個々のバインディングをカンマで区切ることにより、DOM要素は複数のバインディング（関連の有無を問わず）を含むことができます。こちらはいくつかの例です。

```html
<!-- 関連のあるバインディング: valueUpdate は value のためのパラメータです -->
Your value: <input data-bind="value: someValue, valueUpdate: 'afterkeydown'" />

<!-- 関連のないバインディング -->
Cellphone: <input data-bind="value: cellphoneNumber, enable: hasCellphone" />
```

バインディング名は通常、登録されているバインディングのハンドラと一致するか（組み込みまたは[カスタム](./custom-bindings)のいずれか）、または他のバインディングのパラメータである必要があります。名前がそれらのいずれにも一致しない場合、Knockoutはそのバインディングを無視します（いかなるエラーや警告も出力しません）。そのため、もしもバインディングが動作しないように見える場合は、最初にバインディングの名前が正しいことを確認してください。

### バインディング値

バインディング値は、単一の[値、変数、リテラル](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Values,_variables,_and_literals)、またはほとんど全ての有効な[JavaScript式](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Expressions_and_Operators)を指定できます。こちらは様々なバインディング値の例です。

```html
<!-- 値 (通常は、現在のビューモデルのプロパティです） -->
<div data-bind="visible: shouldShowMessage">...</div>

<!-- 比較と条件 -->
The item is <span data-bind="text: price() > 50 ? 'expensive' : 'cheap'"></span>.

<!-- 関数呼び出しと比較 -->
<button data-bind="enable: parseAreaCode(cellphoneNumber()) != '555'">...</button>

<!-- 関数式 -->
<div data-bind="click: function (data) { myFunction('param1', data) }">...</div>

<!-- オブジェクトリテラル (引用符無し、または有りのプロパティ名) -->
<div data-bind="with: {emotion: 'happy', 'facial-expression': 'smile'}">...</div>なんらか
```

これらの例は、値がほとんどなんらかのJavaScript式にできることを示しています。中括弧、角括弧、または丸括弧で囲んだ場合にはカンマも使用できます。値がオブジェクトリテラルであるとき、オブジェクトのプロパティ名は有効なJavaScriptの識別子であるか、または引用符で囲まれていなければいけません。もしバインディング値が無効な式や未知の変数を参照している場合、Knockoutはエラーを出力し、バインディングの処理を停止します。

### 空白

バインディングは空白（スペース、タブ、改行）をいくつでも含むことができるので、あなたが好きなようにバインディングを整えるため、自由に使用できます。以下の例はすべて同等です。

```html
<!-- スペースなし -->
<select data-bind="options:availableCountries,optionsText:'countryName',value:selectedCountry,optionsCaption:'Choose...'"></select>

<!-- いくつかのスペース -->
<select data-bind="options : availableCountries, optionsText : 'countryName', value : selectedCountry, optionsCaption : 'Choose...'"></select>

<!-- スペースと改行 -->
<select data-bind="
    options: availableCountries,
    optionsText: 'countryName',
    value: selectedCountry,
    optionsCaption: 'Choose...'"></select>
```

### バインディングの値を省略する

Knockout 3.0 からは値を指定せず特定のバインディングを行うことが可能になり、この場合は**undefined**値がバインディングされます。例えば:

```html
<span data-bind="text">Text that will be cleared when bindings are applied.</span>
```

この機能は特に[バインディングの前処理](./binding-preprocessing)と組み合わせた際に有用で、バインディングにデフォルト値を割り当てることができます。
