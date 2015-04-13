# コンポーネントの登録

Knockout があなたのコンポーネントをロードおよびインスタンスかできるようにするため、あなたは `ko.components.register` を使用してコンフィギュレーションを記述し、そのコンポーネントを登録する必要があります。

注: 他の方法として、明示的なコンフィギュレーションの替わりに、コンポーネントを独自の規則に沿って取得する [カスタムコンポーネントローダ](./component-loaders) を実装することも可能です。

* [ビューモデル/テンプレートの組み合わせによるコンポーネントの登録](#registering-components-as-a-viewmodeltemplate-pair)
  * [ビューモデルの指定](#specifying-a-viewmodel)
    * [コンストラクタ関数](#a-constructor-function)
    * [共有オブジェクトのインスタンス](#a-shared-object-instance)
    * [createViewModel ファクトリ関数](#a-createviewmodel-factory-function)
    * [ビューモデルを記述したAMDモジュール](#an-amd-module-whose-value-describes-a-viewmodel)


  * [テンプレートの指定](#specifying-a-template)
    * [存在しているエレメントのID](#an-existing-element-id)
    * [存在しているエレメントのインスタンス](#an-existing-element-instance)
    * [マークアップの文字列](#a-string-of-markup)
    * [DOMノードの配列](#an-array-of-dom-nodes)
    * [ドキュメントの断片](#a-document-fragment)
    * [テンプレートを記述したAMDモジュール](#an-amd-module-whose-value-describes-a-template)


  * [追加コンポーネントオプションの指定](#specifying-additional-component-options)
    * [同期/非同期ローディングのコントロール](#controlling-synchronousasynchronous-loading)


  * [KnockoutがAMD経由でコンポーネントをロードする方法](#how-knockout-loads-components-via-amd)
    * [AMDモジュールは必要に応じてのみロードされる](#amd-modules-are-loaded-only-on-demand)


  * [単一のAMDモジュールによるコンポーネントの登録](#registering-components-as-a-single-amd-module)
    * [推奨されるAMDモジュールのパターン](#a-recommended-amd-module-pattern)


### ビューモデル/テンプレートの組み合わせによるコンポーネントの登録 {#registering-components-as-a-viewmodeltemplate-pair}

あなたは、以下のようにコンポーネントを登録できます:

```javascript
ko.components.register('some-component-name', {
    viewModel: <see below>,
    template: <see below>
});
```

* コンポーネント名は空でない何らかの文字列にできます。コンポーネント名は [カスタムエレメント](./component-custom-elements) ( `<your-component-name>` のような)として使用するのに適切であるように、小文字でハイフンで区切られた文字列( `your-component-name` のような)の使用を推奨しますが、義務ではありません。
* `viewModel` はオプションで、 [以下で説明するビューモデル形式](#specifying-a-viewmodel) のいずれかにできます。
* `template` は必須で、 [以下で説明するテンプレート形式](#specifying-a-template) のいずれかにできます。

もし、viewmodel が指定されていない場合、コンポーネントはHTMLの単純なブロックとして扱われ、コンポーネントに渡された何らかのパラメータにバインドされます。


#### ビューモデルの指定 {#specifying-a-viewmodel}

ビューモデルは、次のいずれかの形式で指定できます:

##### コンストラクタ関数 {#a-constructor-function}

```javascript
function SomeComponentViewModel(params) {
    // 'params' is an object whose key/value pairs are the parameters
    // passed from the component binding or custom element.
    this.someProperty = params.something;
}

SomeComponentViewModel.prototype.doSomething = function() { ... };

ko.components.register('my-component', {
    viewModel: SomeComponentViewModel,
    template: ...
});
```

Knockout はコンポーネントの各インスタンスごとに一度コンストラクタを呼び出し、それぞれに独立したビューモデルを提供します。結果のオブジェクトまたはプロトタイプチェインのプロパティ(例えば、上記の例では `someProperty` と `doSomething` )は、コンポーネントのビューへのバインディングとして使用可能です。

##### 共有オブジェクトのインスタンス {#a-shared-object-instance}

もしあなたがコンポーネントの全インスタンスで同じビューモデルオブジェクトのインスタンスを共有したい場合(これは通常望ましくありません):

```javascript
var sharedViewModelInstance = { ... };

ko.components.register('my-component', {
    viewModel: { instance: sharedViewModelInstance },
    template: ...
});
```

注意点として、 `viewModel: object` だけではなく、 `viewModel: { instance: object }` のように指定が必要です。これは以下の他のケースと区別されます。

##### createViewModel ファクトリ関数 {#a-createviewmodel-factory-function}

もしあなたが関連付けられたエレメントに対して、ビューモデルにバインドされる前に何らかのセットアップロジックを実行したい場合、またはどのビューモデルクラスをインスタンス化するかを決定するために任意のロジックを使用したい場合:

```javascript
ko.components.register('my-component', {
    viewModel: {
        createViewModel: function(params, componentInfo) {
            // - 'params' is an object whose key/value pairs are the parameters
            //   passed from the component binding or custom element
            // - 'componentInfo.element' is the element the component is being
            //   injected into. When createViewModel is called, the template has
            //   already been injected into this element, but isn't yet bound.

            // Return the desired view model instance, e.g.:
            return new MyViewModel(params);
        }
    },
    template: ...
});
```

注意点として、典型的には `createViewModel` の内部から `componentInfo.element` を実行するよりも、 [カスタムバインディング](./custom-bindings) を通して直接DOM操作を行うのがベストです。これによって、よりモジュール化された再利用可能なコードになります。

`componentInfo.templateNodes` 配列は、出力に影響を与える任意のマークアップを許可するコンポーネントを構築したい場合に有用です(例えば、グリッド、リスト、ダイアログ、またはタブセットは供給されたマークアップを自身に注入します)。完全な例は、[コンポーネントにマークアップを渡す](./component-custom-elements#passing-markup-into-components) を参照してください。

##### ビューモデルを記述したAMDモジュール {#an-amd-module-whose-value-describes-a-viewmodel}

もし、既にあなたのページ内でAMDローダ( [require.js](http://requirejs.org/) のような)を読み込んでいる場合、それをビューモデルを取得するために使用できます。これがどのように動作するかのより詳細な説明は、以下の [KnockoutがAMD経由でコンポーネントをロードする方法](#how-knockout-loads-components-via-amd) を参照してください。例えば:

```javascript
ko.components.register('my-component', {
    viewModel: { require: 'some/module/name' },
    template: ...
});
```

戻り値のAMDモジュールオブジェクトは、ビューモデルに対して許可される何らかの形式にできます。従って、コンストラクタ関数にできます。例えば:

```javascript
// AMD module whose value is a component viewmodel constructor
define(['knockout'], function(ko) {
    function MyViewModel() {
        // ...
    }

    return MyViewModel;
});
```

... または共有オブジェクトインスタンスにもできます:

```javascript
// AMD module whose value is a shared component viewmodel instance
define(['knockout'], function(ko) {
    function MyViewModel() {
        // ...
    }

    return { instance: new MyViewModel() };
});
```

... またはcreateViewModel関数にもできます:

```javascript
// AMD module whose value is a 'createViewModel' function
define(['knockout'], function(ko) {
    function myViewModelFactory(params, componentInfo) {
        // return something
    }

    return { createViewModel: myViewModelFactory };
});
```

... または、もしあなたがこれを望んでいないとしても、別のAMDモジュールを参照することですら可能です:

```javascript
// AMD module whose value is a reference to a different AMD module,
// which in turn can be in any of these formats
define(['knockout'], function(ko) {
    return { module: 'some/other/module' };
});
```

#### テンプレートの指定 {#specifying-a-template}

テンプレートは以下のいずれかの形式で指定できます。ほとんどの場合に有用なのは、 [存在しているエレメントのID](#an-existing-element-id) または [AMDモジュール](#an-amd-module-whose-value-describes-a-template) です。

#### 存在しているエレメントのID {#an-existing-element-id}

例えば以下のエレメントは、

```html
<template id='my-component-template'>
    <h1 data-bind='text: title'></h1>
    <button data-bind='click: doSomething'>Click me right now</button>
</template>
```

... そのIDを指定して、コンポーネントのためにテンプレートとして使用することができます:

```javascript
ko.components.register('my-component', {
    template: { element: 'my-component-template' },
    viewModel: ...
});
```

指定されたエレメント内のノードのみが、コンポーネントの各インスタンスに複製されることに注意してください。コンテナエレメント（この例では、 `<template>` 要素）は、コンポーネントテンプレートの一部として扱われません。

あなたは `<template>` エレメントを使用することのみに限定されませんが、それ自身がレンダリングされないため（これをサポートするブラウザでは）便利です。他のエレメントタイプもまた、機能します。

##### 存在しているエレメントのインスタンス {#an-existing-element-instance}

もし、あなたのコード内でDOM要素への参照を持っている場合は、テンプレートマークアップのためのコンテナとして使用することができます。

```javascript
var elemInstance = document.getElementById('my-component-template');

ko.components.register('my-component', {
    template: { element: elemInstance },
    viewModel: ...
});
```

ここでも、指定されたエレメント内のノードは、コンポーネントのテンプレートとして使用するために複製されます。

##### マークアップの文字列 {#a-string-of-markup}

```javascript
ko.components.register('my-component', {
    template: '<h1 data-bind="text: title"></h1>\
               <button data-bind="click: doSomething">Clickety</button>',
    viewModel: ...
});
```

これは主に、JavaScriptの文字列リテラルとして手動でHTMLを編集するのがあまり便利ではないため、何らかのプログラム的な方法(例えば [AMD - 以下を参照](#a-recommended-amd-module-pattern) )でマークアップを取得していたり、またはディストリビューション用にコンポーネントをパッケージ化するビルドシステムの出力である場合に有用です。

##### DOMノードの配列 {#an-array-of-dom-nodes}

もし、あなたがコンフィギュレーションをプログラム的に構築しており、DOMノードの配列を持っている場合、コンポーネントのテンプレートとしてそれを使用することができます。

```javascript
var myNodes = [
    document.getElementById('first-node'),
    document.getElementById('second-node'),
    document.getElementById('third-node')
];

ko.components.register('my-component', {
    template: myNodes,
    viewModel: ...
});
```

このケースでは、全ての指定されたノード(そしてその配下)は複製され、インスタンスかされたコンポーネントの各コピーに連結されます。

##### ドキュメントの断片 {#a-document-fragment}

あなたがコンフィギュレーションをプログラム的に構築しており、 `DocumentFragment` オブジェクトを持っている場合、コンポーネントのテンプレートとして使用することができます。

```javascript
ko.components.register('my-component', {
    template: someDocumentFragmentInstance,
    viewModel: ...
});
```

ドキュメントの断片は複数の最上位ノードを持つことができるので、（最上位ノードの子孫だけでなく）全体のドキュメントフラグメントはコンポーネントのテンプレートとして扱われます。

##### テンプレートを記述したAMDモジュール {#an-amd-module-whose-value-describes-a-template}

もし、既にあなたのページ内でAMDローダ( [require.js](http://requirejs.org/) のような)を読み込んでいる場合、それをテンプレートを取得するために使用できます。これがどのように動作するかのより詳細な説明は、以下の [KnockoutがAMD経由でコンポーネントをロードする方法](#how-knockout-loads-components-via-amd) を参照してください。例えば:

```javascript
ko.components.register('my-component', {
    template: { require: 'some/template' },
    viewModel: ...
});
```

戻り値のAMDモジュールオブジェクトは、ビューモデルに対して許可される何らかの形式にできます。従って、マークアップ文字列にできます。例えば、[require.js の text プラグイン](http://requirejs.org/docs/api.html#text) を使用して取得できます:

```javascript
ko.components.register('my-component', {
    template: { require: 'text!path/my-html-file.html' },
    viewModel: ...
});
```

... または、AMDを経由してテンプレートを取得する際に有用になることは珍しいでしょうが、ここで記述されている他の形式にできます。

#### 追加コンポーネントオプションの指定 {#specifying-additional-component-options}

`template` と `viewModel` と同様に（または代替として）、あなたのコンポーネントのコンフィギュレーションオブジェクトは、任意の他のプロパティを有することができます。このコンフィギュレーションオブジェクトは、あなたが使用する何らかの [カスタムコンポーネントローダ](./component-loaders) で利用できます。

##### 同期/非同期ローディングのコントロール {#controlling-synchronousasynchronous-loading}

もし、あなたのコンポーネントのコンフィギュレーションがブール値の `synchronous` プロパティを持つ場合、Knockout はコンポーネントがロードおよび注入を動機的に行うかどうか決定するため、この値を使用します。これはデフォルトでは `false` です(つまり、強制的に非同期になります)。例えば、

```javascript
ko.components.register('my-component', {
    viewModel: { ... anything ... },
    template: { ... anything ... },
    synchronous: true // Injects synchronously if already loaded, otherwise still async
});
```

##### 何故、通常コンポーネントは強制的に非同期でロードされるのか?

通常、Knockout はコンポーネントのロード、そしてコンポーネントの注入が常に完全に非同期で行われることを確保します。なぜなら、時々、それが選択の余地なく非同期である必要があるからです(例えば、サーバへのリクエストを含む場合)。これは、たとえ特定のコンポーネントインスタンスが同期的に注入できる場合でも同様です(例えば、コンポーネントの定義が既にロードされている場合)。この、常時非同期のポリシーは一貫性の問題であり、例えばAMDのような、他のモダンな非同期のJavaScriptテクノロジーから継承されている、確立した慣習です。この慣習は安全なデフォルトであり、開発者が典型的な非同期プロセスが時に同期的に完了したり、またはその逆の場合について考慮しないことによる、潜在的なバグの可能性を軽減します。

##### 何故あなたは同期ローディングを有効にするのか？

もし、特定のコンポーネントのためにポリシーを変更したい場合は、コンポーネントのコンフィギュレーションで `synchronous: true` を指定することもできます。すると、初回の利用時は非同期でロードされるかもしれませんが、それ以降のすべてのロードが同期的に行われます。あなたがこれを行う場合、コンポーネントのロードを待つ全てのコードに対して、この変更可能な振る舞いを考慮する必要があります。

`synchronous: true` の主要な利点として、あなたが特定コンポーネントのコピーの長いリストを注入しようとしており(例えば `foreach` バインディングの内部で)、そしてコンポーネント定義がそれまでの使用によって既にメモリ内に存在している場合、全ての新しいコピーは同期的に注入され、単一のDOM再構築のみを引き起こすので、特にモバイルでのパフォーマンスのために適しています。

#### KnockoutがAMD経由でコンポーネントをロードする方法 {#how-knockout-loads-components-via-amd}

あなたが `require` 定義によってビューモデルやテンプレートをロードする場合、例えば、

```javascript
ko.components.register('my-component', {
    viewModel: { require: 'some/module/name' },
    template: { require: 'text!some-template.html' }
});
```

... 全てのKnockoutは `require(['some/module/name'], callback)` と `require(['text!some-template.html'], callback)` を呼び出し、非同期で取得した戻り値のオブジェクトをビューモデルとテンプレートの定義として使用します。従って、

* これは [require.js](http://requirejs.org/) 、または他の特定のモジュールローダに *強く依存していません* 。モジュールローダの提供する、AMD-スタイルの `require` APIが実行されます。もし、APIの異なるモジュールローダを統合したい場合は、 [カスタムコンポーネントローダ](./component-loaders) を実装することができます。

* いかなる方法によっても *Knockout はモジュール名を解析しません* - 単に `require()` を通してそれを渡すだけです。したがって、Knockout はあなたのモジュールファイルがどこからロードされたかを知らず、気にもしません。それはあなたのAMDローダと、それを設定した方法次第です。

* *Knockout はあなたのAMDモジュールが匿名かどうかを知らず、気にしません。* 一般的には、匿名モジュールとして定義することがコンポーネントにとって便利であることが多いですが、この関心事は、KOとは完全に分離されています。

##### AMDモジュールは必要に応じてのみロードされる {#amd-modules-are-loaded-only-on-demand}

Knockout は、あなたのコンポーネントがインスタンス化されるまで、 `require([moduleName], ...)` を呼び出しません。これが、コンポーネントが事前ではなく、必要に応じてロードされる方法です。

例えば、もしあなたのコンポーネントが [if バインディング](./if-binding) (または他の制御フローバインディング)の適用された他のエレメントの内部に存在する場合、 `if` の状態がtrue になるまで、AMDモジュールはロードされません。もちろん、もしAMDモジュールが既にロードされている場合(例えば、プリロードされたバンドル内など)、 `require` は いかなる追加的なHTTPリクエストを発生させることはないので、あなたは何がプリロードされ、何が必要に応じてロードされるかをコントロールできます。

#### 単一のAMDモジュールによるコンポーネントの登録 {#registering-components-as-a-single-amd-module}

さらに優れたカプセル化のために、単一の自己定義されたAMDモジュールにコンポーネントをパッケージ化することができます。そして、以下のように簡単にコンポーネントを参照できます:

```javascript
ko.components.register('my-component', { require: 'some/module' });
```

注意点として、 ビューモデル/テンプレートのペアは指定されていません。これまでに説明を行った定義形式のいずれかを使用して、AMDモジュール自身がビューモデル/テンプレートのペアを提供します。例えば、 `some/module.js` ファイルは以下のように定義できます。

```javascript
// AMD module 'some/module.js' encapsulating the configuration for a component
define(['knockout'], function(ko) {
    function MyComponentViewModel(params) {
        this.personName = ko.observable(params.name);
    }

    return {
        viewModel: MyComponentViewModel,
        template: 'The name is <strong data-bind="text: personName"></strong>'
    };
});
```

#### 推奨されるAMDモジュールのパターン {#a-recommended-amd-module-pattern}

実際に最も有用であることが多いのは、インラインのビューモデルクラスを持ち、かつ外部のテンプレートファイル上に明示的にAMDの依存関係を持っている、AMDのモジュールを作成することです。

例えば、以下のコードが `path/my-component.js` 内に存在する場合、

```javascript
// Recommended AMD module pattern for a Knockout component that:
//  - Can be referenced with just a single 'require' declaration
//  - Can be included in a bundle using the r.js optimizer
define(['knockout', 'text!./my-component.html'], function(ko, htmlString) {
    function MyComponentViewModel(params) {
        // Set up properties, etc.
    }

    // Use prototype to declare any public methods
    MyComponentViewModel.prototype.doSomething = function() { ... };

    // Return component definition
    return { viewModel: MyComponentViewModel, template: htmlString };
});
```

... そして、テンプレートのマークアップが `path/my-component.html` ファイル内に存在することで、あなたは以下の利益を得ます。

* アプリケーションが自明にこれを参照することができます。例えば、 `ko.components.register('my-component', { require: 'path/my-component' });`

* コンポーネントのために必要となるのが2ファイルのみです - ビューモデル `(path/my-component.js)` とテンプレート `(path/my-component.html)` - これは開発中、とても自然な配置です。

* テンプレート上の依存関係は `define` 呼び出し内で明示的に指定するため、 [r.js オプティマイザ](http://requirejs.org/docs/optimization.html) や同様のバンドルツールで自動的に動作します。従って、コンポーネント全体 - ビューモデルとテンプレート - を、ビルドステップでバンドルファイル内に明示的に含むことができます。

* 注: r.js オプティマイザはとても柔軟であるため、たくさんのオプションを持ち、セットアップにいくらかの時間を必要とするかもしれません。あなたが、完成済みのr.js に最適化された Knockout コンポーネントの例から始めたい場合、 [Yeoman](http://yeoman.io/) と [generator-ko](https://www.npmjs.org/package/generator-ko) ジェネレータを参照してください。ブログ記事を近日中に用意します。
