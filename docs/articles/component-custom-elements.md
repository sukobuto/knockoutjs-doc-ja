# カスタムエレメント

カスタムエレメントは、あなたのビューに [コンポーネント](./component-overview) を注入する、便利な方法を提供します。

* [導入](#introduction)
* [例](#example)
* [パラメータの受け渡し](#passing-parameters)
  * [コンポーネントの親子間の通信](#communication-between-parent-and-child-components)
  * [observable な式を渡す](#passing-observable-expressions)
* [コンポーネントにマークアップを渡す](#passing-markup-into-components)
* [カスタムエレメントのタグ名を制御する](#controlling-custom-element-tag-names)
* [カスタムエレメントの登録](#registering-custom-elements)
* [注: 通常のバインディングとカスタムエレメントを組み合わせる](#note-combining-custom-elements-with-regular-bindings)
* [注: カスタムエレメントは自己完結できない](#note-custom-elements-cannot-be-self-closing)
* [注: カスタムエレメントとInternet Explorer 6 ～ 8](#note-custom-elements-and-internet-explorer-6-to-8)
* [上級: $raw パラメータへのアクセス](#advanced-accessing-raw-parameters)

### 導入 {#introduction}

カスタムエレメントは、[`component` バインディング](./component-binding) に対する構文上の代替手段です (そして実際には、カスタムエレメントは舞台裏で `component` バインディングを利用します)。

例えば、以下のように記述する代わりに:

```html
<div data-bind='component: { name: "flight-deals", params: { from: "lhr", to: "sfo" } }'></div>
```

... このように記述することができます:

```html
<flight-deals params='from: "lhr", to: "sfo"'></flight-deals>
```

これによって、非常に古いブラウザのサポートを維持しながら、とても現代的な[WebComponents](http://www.w3.org/TR/components-intro/) 的な方法で、コードを構成することができます( [カスタムエレメントとInternet Explorer 6 ～ 8](#note-custom-elements-and-internet-explorer-6-to-8) を参照してください)。

### 実行例 {#example}

こちらの例ではコンポーネントを宣言してから、ビューにその2つのインスタンスを注入します。下記のソースコードを参照してください。

# (ここにライブビューが入ります)

##### ソースコード: ビュー

```html
<h4>First instance, without parameters</h4>
<message-editor></message-editor>

<h4>Second instance, passing parameters</h4>
<message-editor params='initialText: "Hello, world!"'></message-editor>
```

##### ソースコード: ビューモデル

```javascript
ko.components.register('message-editor', {
    viewModel: function(params) {
        this.text = ko.observable(params.initialText || '');
    },
    template: 'Message: <input data-bind="value: text" /> '
            + '(length: <span data-bind="text: text().length"></span>)'
});

ko.applyBindings();
```

注: より現実的なケースでは、通常、登録時にコンポーネントのビューモデルとテンプレートをハードコーディングする代わりに、外部ファイルからそれらをロードするでしょう。 [実行例](./component-overview#example-loading-the-likedislike-widget-from-external-files-on-demand) と、[登録についてのドキュメント](./component-registration) を参照してください。

### パラメータの受け渡し {#passing-parameters}

上記の例で見てきたように、コンポーネントのビューモデルにパラメータを供給するため、 `params` 属性を使用することができます。 `params` 属性の内容はJavaScriptのオブジェクトリテラルとして解釈され（ `data-bind` 属性と同様です）、任意の型でどのような値でも渡すことができます。例えば：

```html
<unrealistic-component
    params='stringValue: "hello",
            numericValue: 123,
            boolValue: true,
            objectValue: { a: 1, b: 2 },
            dateValue: new Date(),
            someModelProperty: myModelValue,
            observableSubproperty: someObservable().subprop'>
</unrealistic-component>
```

#### コンポーネントの親子間の通信 {#communication-between-parent-and-child-components}

もし、あなたが `params` 属性内でモデルのプロパティを参照する場合、コンポーネント自体がまだインスタンス化されていないので、もちろんコンポーネント外のビューモデル('親' または 'ホスト' のビューモデル)上のプロパティを参照しています。上記の例では、 `myModelValue` は親のビューモデルのプロパティになり、そして子コンポーネントのビューモデルのコンストラクタで `params.someModelProperty` のよう受け取ることになります。

これが、子コンポーネントに親のビューモデルからプロパティを渡すことができる方法です。プロパティがそれ自体observableである場合には、親のビューモデルはそのプロパティを観察し、子コンポーネントによって挿入された新しい値に反応することができるようになります。

#### observable な式を渡す {#passing-observable-expressions}

以下の例では、

```html
<some-component
    params='simpleExpression: 1 + 1,
            simpleObservable: myObservable,
            observableExpression: myObservable() + 1'>
</some-component>
```

...コンポーネントのビューモデルにある `params` パラメータは3つの値を含みます。

* `simpleExpression`

  * これは数値の `2` のような類です。observable または computed な値ではなく、observable と関連しません。

     一般的に、パラメータの評価がobservable の評価を伴わない場合(この場合、値は全くobservableを伴いません)、値はリテラルとして渡されます。もし値がobject であった場合、子のコンポーネントはそれを変化させることができますが、それがobservableではないため、親のコンポーネントは子がそれを行ったことを知ることができません。

* `simpleObservable`

  * これは、親のビューモデルで定義された `myObservable` のような、 [ko.observable](./observables) インスタンスです。これはラッパーではありません - 実際に親で参照されているものと同じインスタンスです。したがって、もし子のビューモデルがこのobservable に書き込むと、親のビューモデルはその変更を受け取ります。

  一般的に、もしパラメータの評価がobservable の評価を伴わない場合(この場合、observable は単に評価されずに渡されます)、値はリテラルとして渡されます。

* `observableExpression`

  * これはトリッキーです。式自体は、評価されたときに、observableを読み込みます。そのobservableな値は、時間の経過とともに変化する可能性があるため、式の結果も、時間の経過とともに変化できます。

    子コンポーネントが式の値の変化に反応できることを保証するため、Knockoutは、 *自動的にこのパラメータをcomputed プロパティにアップグレードします* 。従って、子コンポーネントは `params.observableExpression()` を読むことで現在の値を取得することができ、または `params.observableExpression.subscribe(...)` 等が使用できます。

    一般的に、カスタムエレメントにおいて、パラメータの評価が observable の評価を伴う場合、knockout は自動的に、与えられた式の結果として `ko.computed` の値を作成し、コンポーネントに供給します。

要約すると、一般的なルールは次のようになります:

1. もしパラメータの評価がobservable/computed の評価を *伴わない* 場合、それはリテラルとして渡されます。
2. もし、パラメータの評価が 1つまたは複数の observable/computed の評価を *伴う* 場合、それは computed プロパティとして渡され、パラメータ値の変化に反応させることができます。

### コンポーネントにマークアップを渡す {#passing-markup-into-components}

時には、マークアップを受信し、出力の一部としてそれを使用するコンポーネントを作成したいことがあるでしょう。例えば、グリッド、リスト、ダイアログ、またはタブセットのような、それ自身の内部で任意のマークアップをバインドする "コンテナ" UIエレメントを構築したい場合などです。

以下のように呼び出すことのできる、特殊なリストコンポーネントについて考えてみましょう:

```html
<my-special-list params="items: someArrayOfPeople">
    <!-- Look, I'm putting markup inside a custom element -->
    The person <em data-bind="text: name"></em>
    is <em data-bind="text: age"></em> years old.
</my-special-list>
```

デフォルトでは、 `<my-special-list>` 内部のDOMノードは（いかなるビューモデルにもバインドされずに）取り除かれ、コンポーネントの出力に置き換えられます。
しかし、それらのDOMノードは失われません: それは記憶され、コンポーネントに二つの方法で供給されます。

* `$componentTemplateNodes` の配列として、コンポーネントのテンプレート内においてなんらかのバインド式に対して(つまり、 [バインディングコンテキスト](./binding-context) のプロパティとして)使用できます。通常これは供給されたマークアップを使用するもっとも便利な方法です。以下の例を参照してください。

* `componentInfo.templateNodes` の配列として、その [createViewModel関数](./component-registration#a-createviewmodel-factory-function) に渡されます。

コンポーネントは供給されたDOMノードを、コンポーネントのテンプレート内のなんらかの要素に対して、 `template: { nodes: $componentTemplateNodes }` のように使用することで、希望する出力の一部として使用するように選択することができます。

例えば、 `my-special-list` コンポーネントのテンプレートは `$componentTemplateNodes` を参照することができ、その出力は供給されたマークアップを含むことがｄけいます。以下が、完全な動作例です。

# (ここにライブビューが入ります)

##### ソースコード: ビュー

```html
<!-- This could be in a separate file -->
<template id="my-special-list-template">
    <h3>Here is a special list</h3>

    <ul data-bind="foreach: { data: myItems, as: 'myItem' }">
        <li>
            <h4>Here is another one of my special items</h4>
            <!-- ko template: { nodes: $componentTemplateNodes, data: myItem } --><!-- /ko -->
        </li>
    </ul>
</template>

<my-special-list params="items: someArrayOfPeople">
    <!-- Look, I'm putting markup inside a custom element -->
    The person <em data-bind="text: name"></em>
    is <em data-bind="text: age"></em> years old.
</my-special-list>
```

##### ソースコード: ビューモデル

```javascript
ko.components.register('my-special-list', {
    template: { element: 'my-special-list-template' },
    viewModel: function(params) {
        this.myItems = params.items;
    }
});

ko.applyBindings({
    someArrayOfPeople: ko.observableArray([
        { name: 'Lewis', age: 56 },
        { name: 'Hathaway', age: 34 }
    ])
});
```

この "特殊なリスト"の例は 、それぞれのリスト項目上にヘッダを挿入する以上のことはしません。しかし、同様のテクニックを、洗練されたグリッド、ダイアログ、タブセットやその類に使用することができ、このようなUIエレメント全てに必要とされるのは、供給されたマークアップによって囲われた共通のUIマークアップ(例えば、グリッドの定義やダイアログのヘッダと枠線など)です。

このテクニックはまた、例えば、 []`component` バインディングを直接使用する際にマークアップを渡す場合](./component-binding#note-passing-markup-to-components) など、カスタムエレメントなしでコンポーネントを使用するためにも使用できます。

### カスタムエレメントのタグ名を制御する {#controlling-custom-element-tag-names}

デフォルトでは、Knockout はあなたのカスタムエレメントのタグ名が、`ko.components.register` によって登録されたコンポーネント名と正確に対応していることを前提としています。この"設定より規約" 戦略は、ほとんどのアプリケーションに最適です。

もし、あなたが異なるカスタムエレメントのタグ名を使用したい場合、それを制御するために `getComponentNameForNode` をオーバーライドすることができます。例えば、

```javascript
ko.components.getComponentNameForNode = function(node) {
    var tagNameLower = node.tagName && node.tagName.toLowerCase();

    if (ko.components.isRegistered(tagNameLower)) {
        // If the element's name exactly matches a preregistered
        // component, use that component
        return tagNameLower;
    } else if (tagNameLower === "special-element") {
        // For the element <special-element>, use the component
        // "MySpecialComponent" (whether or not it was preregistered)
        return "MySpecialComponent";
    } else {
        // Treat anything else as not representing a component
        return null;
    }
}
```

あなたはこのテクニックを例えば、登録されたコンポーネントのサブセットを制御して、カスタムエレメントとして使用したい場合などに使用できます。

### カスタムエレメントの登録 {#registering-custom-elements}

もし、あなたがデフォルトのコンポーネントローダを使用しており、それ故に `ko.components.register` を使用してあなたのコンポーネントを登録している場合、あなたはそれ以上何かを行う必要はありません。この方法で登録されたコンポーネントは、直ちにカスタムエレメントとして使用することができます。

もし、あなたが [カスタムコンポーネントローダ](./component-loaders) を実装しており、それが `ko.components.register` を使用していない場合、カスタムエレメントとして使用したいエレメント名をKnockout に伝える必要があります。これを行うためには、単に `ko.components.register` を呼び出します - あなたのカスタムコンポーネントローダがそれを全く使用しないため、あなたは呼び出しで特になんらかの設定を行う必要はありません。例えば、

```javascript
ko.components.register('my-custom-element', { /* No config needed */ });
```

他の方法としては、[getComponentNameForNode をオーバーライド](#controlling-custom-element-tag-names) して、事前の登録とは独立した形で、動的にコンポーネント名とエレメントのマッピングを制御することができます。

#### 注: 通常のバインディングとカスタムエレメントを組み合わせる {#note-combining-custom-elements-with-regular-bindings}

カスタムエレメントは、もし必要であれば通常の `data-bind` 属性 (加えてなんらかの `params` 属性)を持つことができます。例えば、

```html
<products-list params='category: chosenCategory'
               data-bind='visible: shouldShowProducts'>
</products-list>
```

しかしながら、例えば [text](./text-binding) や [template](./template-binding) バインディングのような要素の内容を修正するバインディングを使用しても、それはコンポーネントによって注入されたテンプレートを上書きするので、あまり意味がありません。

Knockout は、ビューモデルと注入されたテンプレートをバインドする際にコンポーネントと衝突してしまうため、[controlsDescendantBindings](./custom-bindings-controlling-descendant-bindings) を使用している全てのバインディングの使用を妨害します。従って、もしあなたが `if` や `foreach` のような制御フローバインディングを使用したい場合、カスタムエレメント上で直接使用する代わりに、カスタムエレメントの周りを囲う形で使用する必要があります。例えば:


```html
<!-- ko if: someCondition -->
    <products-list></products-list>
<!-- /ko -->
```

または:

```html
<ul data-bind='foreach: allProducts'>
    <product-details params='product: $data'></product-details>
</ul>
```

### 注: カスタムエレメントは自己完結できない {#note-custom-elements-cannot-be-self-closing}

あなたは `<my-custom-element />` *ではなく* 、 `<my-custom-element></my-custom-element>` のように記述する必要があります。さもないと、カスタムエレメントは閉じられたとみなされず、それ以下の要素が子要素として処理されるでしょう。

これはHTML 仕様上の制限であり、Knockout が制御できるスコープの範囲外です。HTML仕様に準拠しているHTMLパーサは、 [自己完結したスラッシュを無視します](http://dev.w3.org/html5/spec-author-view/syntax.html#syntax-start-tag) (パーサにハードコーディングされている、少数の特別な"仕様外の要素" を除きます)。HTMLはXMLと同じではありません。

### 注: カスタムエレメントとInternet Explorer 6 ～ 8 {#note-custom-elements-and-internet-explorer-6-to-8}

Knockout は 特に古いブラウザに関連するクロスブラウザの互換性について、開発者がこの問題を扱う痛みを和らげることに挑戦しています。カスタムエレメントはWeb開発のとても現代的なスタイルを提供するだけでなく、一般的に遭遇する全てのブラウザ上で動作します。

* *Internet Explorer9* およびそれ以降を含む、HTML5時代のブラウザでは何ら難しいことなく自動的にカスタムエレメントが許可されます。

* *Internet Explorer 6 ～ 8* もまたカスタムエレメントをサポートしますが、しかしそれはHTMLパーサがそれらのエレメントのいずれかに遭遇する以前に登録されていることが条件です。

IE 6-8 のHTMLパーサは、認識できない要素は全て破棄します。あなたのカスタムエレメントが放り出されないようにするためには、以下のいずれかを行う必要があります。

* `ko.components.register('your-component')` の呼び出しを、HTMLパーサが `<your-component>` エレメントのいずれかに出会う前に、確実に行うようにします。

* または、少なくとも `document.createElement('your-component')` の呼び出しを、HTMLパーサが `<your-component>` エレメントのいずれかに出会う前に行うようにします。あなたは `createElement` 呼び出しの結果について無視することができます - 必要なのは、あなたがそれを呼び出すことです。

例えば、あなたがページを以下のように構成している場合、全てが上手くいきます:

```html
<!DOCTYPE html>
<html>
    <body>
        <script src='some-script-that-registers-components.js'></script>

        <my-custom-element></my-custom-element>
    </body>
</html>
```

もし、あなたが AMDを使用している場合、 以下のような構成がよいでしょう:

```html
<!DOCTYPE html>
<html>
    <body>
        <script>
            // Since the components aren't registered until the AMD module
            // loads, which is asynchronous, the following prevents IE6-8's
            // parser from discarding the custom element
            document.createElement('my-custom-element');
        </script>

        <script src='require.js' data-main='app/startup'></script>

        <my-custom-element></my-custom-element>
    </body>
</html>
```

または、もしあなたが `document.createElement` 呼び出しのハックを好まない場合、カスタムエレメントの代わりに トップレベルのコンポーネントに対する [`component` バインディング](./component-binding) を使用することができます。 他のコンポーネントが `ko.applyBindings` 呼び出しの前に登録されている限り、それらは IE6-8 上で問題を起こすことなく、カスタムエレメントとして使用できます。

```html
<!DOCTYPE html>
<html>
    <body>
        <!-- The startup module registers all other KO components before calling
             ko.applyBindings(), so they are OK as custom elements on IE6-8 -->
        <script src='require.js' data-main='app/startup'></script>

        <div data-bind='component: "my-custom-element"'></div>
    </body>
</html>
```

### 上級: `$raw` パラメータへのアクセス {#advanced-accessing-raw-parameters}

以下のように、 `useObservable1` 、 `observable1` 、そして `observable2` の全てが observable であるような、特殊な場合について考えてみましょう:

```html
<some-component
    params='myExpr: useObservable1() ? observable1 : observable2'>
</some-component>
```

`myExpr` の評価がobservable の読出しを伴うため( `useObservable1` )、KO はパラメータを computed プロパティとしてコンポーネントに供給します。

しかしながら、 computed プロパティの値はそれ自体が observable です。これは二重のアンラッピングを伴うことになり、厄介なシナリオにつながるように思われます(例えば、 `params.myExpr()()` 、ここでは最初の括弧で式の値を取得し、二番目のかっこではobservable インスタンスの結果の値を取得します)。

二重のアンラッピングは醜く不便であり、予想外であるため、Knockout は自動的にcomputed プロパティがアンラップされた値 ( `params.myExpr` ) として生成されるようセットアップして渡します。つまり、コンポーネントは二重のアンラッピングを必要とせず、どちらのobservable が選択されていても( `observable1` または `observable2` )、 `params.myExpr()` の値を読み取ることができます。

万が一、あなたが自動アンラッピングを望んでおらず、 `observable1` / `observable2` インスタンスに直接アクセスしたい場合、 `params.$raw` から値を読み取ることができます。例えば、

```javascript
function MyComponentViewModel(params) {
    var currentObservableInstance = params.$raw.myExpr();

    // Now currentObservableInstance is either observable1 or observable2
    // and you would read its value with "currentObservableInstance()"
}
```

これはかなり特殊なシナリオであるべきですので、通常はあなたが `$raw` を扱う必要はないでしょう。
