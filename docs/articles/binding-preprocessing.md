# プリプロセスで Knockout の<br>バインディング記法を拡張

注: これは高度な技術であり、通常では再利用可能なバインディングのライブラリや、拡張構文を作成する場合にのみ使用されます。普通、Knockout でアプリケーションを構築する際には必要になりません。

Knockout 3.0 以降では、開発者は、バインド処理中のDOMノードやバインディング文字列を書き換えるコールバックを提供することで、カスタムの構文を定義することができます。

### バインディング文字列のプリプロセス {#preprocessing-binding-strings}

あなたは、特定のバインディングハンドラ(`click`, `visible`, またはその他の[カスタムバインディングハンドラ](custom-bindings))のためにバインディングプロセッサによって提供された `data-bind` 属性に対して、Knockoutのロジックにフックすることで割り込みできます。

これを行うには、`preprocess` 関数をバインディングハンドラに追加します:

```javascript
ko.bindingHandlers.yourBindingHandler.preprocess = function(stringFromMarkup) {
    // もし、なにも変更したくない場合は stringFromMarkup を返します。
    // または、他の文字列を返すと、Knockout があたかも元のHTMLが
    // その構文を提供しているかのように振舞います
}
```

APIリファレンスについては、このページの下部を参照してください。

#### 例 1: バインディングのデフォルト値を設定する {#example-1-setting-a-default-value-for-a-binding}

もし、バインディングの値を何も設定しない場合、デフォルトでは`undefined` がバインドされます。
もし、あなたがバインディングに別のデフォルト値を設定したい場合、プリプロセッサによってそれを行うことができます。
例えば、 `uniqueName` を値なしでバインドして、そのデフォルト値を `true` にすることができます。

```javascript
ko.bindingHandlers.uniqueName.preprocess = function(val) {
    return val || 'true';
}
```

今、あなたはそれを以下のようにバインドできます。

```html
<input data-bind="value: someModelProperty, uniqueName" />
```

#### 例 2: イベントに式をバインディングする {#example-2-binding-expressions-to-events}

もし、あなたが `click` イベントに式をバインドしたい（Knockout の予期している関数のリファレンスではなく）場合、`click` ハンドラがこの構文をサポートするよう、プリプロセッサを設定できます。

```javascript
ko.bindingHandlers.click.preprocess = function(val) {
    return 'function($data,$event){ ' + val + ' }';
}
```

今、あなたは次のように `click` をバインドできます。

```html
<button type="button" data-bind="click: myCount(myCount()+1)">Increment</button>
```

#### バインディングプリプロセッサリファレンス {#binding-preprocessor-reference}

* `ko.bindingHandlers.<name>.preprocess(value, name, addBindingCallback)`

  もしこのハンドラが定義されている場合、各　`<name>` バインディングから、そのバインディングが評価される前に呼び出されます。

  ** パラメータ: **

  * `value`: それをKnockout が解析する前の、バインディング値に関連付けられた構文です（例えば、 `yourBinding: 1 + 1` の場合、文字列として `"1 + 1"` が関連付けられます）。

  * `name`: バインディングの名前（例えば、 `yourBinding: 1 + 1` の場合、関連付けられた値は文字列の `"yourBinding"` ）です。

  * `addBinding`: あなたが任意で使用できる、現在の要素に他のバインディングを挿入するためのコールバック関数です。この関数は `name` と `value` の2つのパラメータが必要です。例えば、あなたの `preprocess` 関数内で `addBinding（'visible', 'acceptsTerms()'）` を呼び出すと、あたかも要素に `visible: acceptsTerms()` がバインディングされているかのようにKnockout が振る舞います。

  ** 戻り値: **

  あなたの `preprocess` 関数は、バインディングに渡されて解析されるための新しい文字列を返すか、またはバインディングを削除するため `undefined` を返す必要があります。

  例えば、もしあなたが `value + ".toUpperCase()"` を文字列として返すと、 `yourBinding: "Bert"` はマークアップが `yourBinding: "Bert".toUpperCase()` を含んでいたかのように割り込まれます。Knockout が戻り値を通常の方法で解析するために、それは正式な JavaScript 式である必要があります。

  文字列以外の値を返さないでください。マークアップは常に文字列であるため、それは意味を持ちません。

### DOMノードのプリプロセス {#preprocessing-dom-nodes}

あなたはノードプリプロセッサを提供することによって、 Knockout が DOM ツリーを横断するためのロジックにフックすることができます。この関数は、UI が最初にバインドされた時と、後で何らかの新しい DOM サブツリーが挿入された時(例えば、 [foreach バインディング](foreach-binding) を経由して)、それぞれの完了時に Knockout が各 DOM ノードに対して一度呼び出します。

これを行うには、あなたのバインディングプロバイダに対して、 `preprocessNode` 関数を定義します:

```javascript
ko.bindingProvider.instance.preprocessNode = function(node) {
    // もし望むのであれば、 setAttribute のような DOM API を使用して 'node' を修正します。
    // DOM に 'node' を残したい場合、 null を返すか、または 'return' 行を書かないでください。
    // もし 'node' を他のノード一式で置き換える場合、
    //    -  insertChild のような DOM APIを使用して、新しいノードを 'node' の直前に挿入してください。
    //    - もし必要であれば、'node' を削除するために removeChild のような DOM API を使用してください。
    //    - あなたが挿入した何らかの新しいノードの配列を返すと、
    //      Knockout はそれらにバインディングを適用します
}
```

APIリファレンスについては、このページの下部を参照してください。

#### 例 3: 仮想のテンプレート要素 {#example-3-virtual-template-elements}

一般的に、仮想のDOM要素を使用したテンプレートが含まれている場合、通常の構文は少し冗長に感じることがあります。プリプロセスを使用すると、単一のコメントによる新しいテンプレート形式を追加することができます:

```javascript
ko.bindingProvider.instance.preprocessNode = function(node) {
    // もしこれがコメントノードの形式である場合にのみ反応します <!-- template: ... -->
    if (node.nodeType == 8) {
        var match = node.nodeValue.match(/^\s*(template\s*:[\s\S]+)/);
        if (match) {
            // 単一のコメントを置き換えるために、コメントのペアを作成します
            var c1 = document.createComment("ko " + match[1]),
                c2 = document.createComment("/ko");
            node.parentNode.insertBefore(c1, node);
            node.parentNode.replaceChild(c2, node);

            // Knockout に 新しいノードについて伝えることで、それらにバインディングを適用できます
            return [c1, c2];
        }
    }
}
```

今、あなたは以下のようにビューに対してテンプレートを含めることができます。

```html
<!-- template: 'some-template' -->
```

#### プリプロセスリファレンス {#preprocessing-reference}

* `ko.bindingProvider.instance.preprocessNode(node)`

もしこのハンドラが定義されている場合、バインディングが処理される前に、各DOMノードに対して呼び出されます。この関数は、`node` を変更、削除、または置き換えることができます。新しいノードを追加する場合は `node` の直前に挿入する必要があり、何らかのノードが追加されるか、または `node` が削除される場合、この関数はドキュメントの `node` の位置に入る新しいノードの配列を返す必要があります。
