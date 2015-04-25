# コンポーネントローダ

あなたが [`component` バインディング](./component-binding) または [カスタムエレメント](./component-custom-elements) を使用して [コンポーネント](./component-overview) を注入する時、Knockout は一つまたは複数のコンポーネントローダを使用して、対象コンポーネントのテンプレートとビューモデルを取得します。コンポーネントローダの仕事は与えられた何らかのコンポーネント名について、非同期にテンプレート/ビューモデルのペアを供給することです。

* [デフォルトのコンポーネントローダ](#default-component-loader)
* [コンポーネントローダのユーティリティ関数](#component-loader-utility-functions)
* [カスタムコンポーネントローダを実装する](#custom-component-loader)
  * [あなたが実装できる関数](#functions-you-can-implement)
    * [getConfig(name, callback)](#getconfigname-callback)
    * [loadComponent(name, componentConfig, callback)](#loadcomponentname-componentconfig-callback)
    * [loadTemplate(name, templateConfig, callback)](#loadtemplatename-templateconfig-callback)
    * [loadViewModel(name, templateConfig, callback)](#loadviewmodelname-templateconfig-callback)
  * [カスタムコンポーネントローダの登録](#registering-custom-component-loaders)
  * [優先順位の制御](#controlling-precedence)
  * [呼び出しの順番](#sequence-of-calls)
* [例1: 命名規則をセットアップするコンポーネントローダ](#example-1-a-component-loader-that-sets-up-naming-conventions)
* [例2: カスタムコードを使用して外部ファイルをロードするコンポーネントローダ](#example-2-a-component-loader-that-loads-external-files-using-custom-code)
* [注: カスタムコンポーネントローダとカスタムエレメント](#note-custom-component-loaders-and-custom-elements)
* [注: browserify での開発](#note-integrating-with-browserify)

### デフォルトのコンポーネントローダ {#default-component-loader}
ビルトインのデフォルトコンポーネントローダ `ko.components.defaultLoader` は、コンポーネント定義の中央 "レジストリ" に基づいています。それはあなたがそのコンポーネントを使用する前に、明示的に各コンポーネントの設定を登録することに依存しています。

[デフォルトローダにおけるコンポーネントの設定と登録についての詳細は、こちらを参照してください。](./component-registration)

### コンポーネントローダのユーティリティ関数 {#component-loader-utility-functions}
以下の関数はデフォルトのコンポーネントローダのレジストリを読み書きします:

* `ko.components.register(name, configuration)`
  * コンポーネントを登録します。 [完全なドキュメントはこちら](./component-registration) を参照してください。
* `ko.components.isRegistered(name)`
  * もし、コンポーネントに対して特定の名前が既に登録済みの場合、 `true` を返します。それ以外の場合は `false` です。
* `ko.components.unregister(name)`
  * レジストリから、名前付きのコンポーネントを除去します。または、そのようなコンポーネントが登録されていない場合、何もしません。

以下の関数は、(デフォルトローダのみではない)登録済みコンポーネントローダの完全なリストに対して動作します:

* `ko.components.get(name, callback)`
  * 登録済みの各ローダを順番に調べて(デフォルトでは、それはデフォルトローダのみです)、名前付きコンポーネントに供給されたビューモデル / テンプレート定義の最初の一つを発見して、ビューモデル / テンプレート定義を返すために `callback` を呼び出します。もし、登録済みローダがいずれもこのコンポーネントについて知らない場合、 `callback(null)` を呼び出します。
* `ko.components.clearCachedDefinition(name)`
  * 通常、Knockout はコンポーネント名ごとに一度づつローダを調査し、結果の定義をキャッシュします。これにより、多数のコンポーネントをとてもすばやくインスタンス化することができます。もし、あなたが与えられたコンポーネントについてのキャッシュエントリをクリアしたい場合、この関数を呼び出すと、ローダは次回そのコンポーネントが必要になった際、再び調査されるようになります。

また、 `ko.components.defaultLoader` はコンポーネントローダであるため、以下の標準コンポーネントローダ関数を実装しています。例えば、あなたがカスタムローダを実装する際にその一部として使用する場合など、これらの関数を直接呼び出すことができます:

* `ko.components.defaultLoader.getConfig(name, callback)`
* `ko.components.defaultLoader.loadComponent(name, componentConfig, callback)`
* `ko.components.defaultLoader.loadTemplate(name, templateConfig, callback)`
* `ko.components.defaultLoader.loadViewModel(name, viewModelConfig, callback)`

標準コンポーネントローダ関数のドキュメントについては、 [カスタムコンポーネントローダの実装](#custom-component-loader) を参照してください。

### カスタムコンポーネントローダを実装する {#custom-component-loader}

あなたがコンポーネントをロードするために、明示的な登録よりも暗黙的な命名規則を使用したい場合、カスタムコンポーネントローダを実装することをお勧めします。または、外部のロケーションからコンポーネントのビューモデルやテンプレートを取得するため、サードパーティの"ローダ"ライブラリを使用したい場合などです。

#### あなたが実装できる関数 {#functions-you-can-implement}

カスタムコンポーネントローダは単に以下の関数の *任意の組み合わせ* をプロパティに持つオブジェクトです。

##### `getConfig(name, callback)` {#getconfigname-callback}

*これを定義するのは* : 例えば命名規則を実装する場合など、名前に基づいてプログラム的に設定を供給したい場合です。

もし定義されている場合、各コンポーネントをインスタンス化する際、Knockoutは設定オブジェクトを取得するためにこの関数を呼び出します。

* 設定を供給するために、 `callback(componentConfig)` を呼び出し、そこで `componentConfig` はあなたのローダまたは他のローダ上にある `loadComponent` 関数で理解可能な何らかのオブジェクトです。デフォルトローダは単に `ko.components.register` を使用して登録されたオブジェクトを供給します。
* 例えば、 `componentConfig` のような `{ template: 'someElementId', viewModel: { require: 'myModule' } }` は理解することができ、デフォルトローダによってインスタンス化されます。
* あなたは、設定オブジェクトの供給について、いかなる標準の形式にも制限されることはありません。あなたの `loadComponent` 関数がそれらを理解できる限り、任意のオブジェクトを渡すことが可能です。
* もし、あなたのローダについて名前付きコンポーネントのために設定を供給したくない場合、 `callback(null)` を呼び出します。Knockoutはその後、 `null` でない値が供給されるまで、順番に他の登録済みローダと相談します。

##### `loadComponent(name, componentConfig, callback)` {#loadcomponentname-componentconfig-callback}

*これを定義するのは* : 例えば、 標準の `viewModel` / `template` ペア形式を使用したくない場合など、どのようにコンポーネントの設定が解釈されるかを制御したい場合です。

もし定義されている場合、Knockout はビューモデル / テンプレートのペアに対して `componentConfig` オブジェクトを変換するために、この関数を呼び出します。

* ビューモデル / テンプレートのペアを供給するため、 `callback(result)` を呼び出し、ここで `result` は以下のプロパティを持つオブジェクトです。
  * `template` - *必須* 。 DOMノードの配列です。
  * `createViewModel(params, componentInfo)` - *オプション* 。後でこのコンポーネントの各インスタンスにビューモデルオブジェクトを供給する際に呼び出されます。
* もし、あなたのローダについて、与えられたパラメータによってビューモデル / テンプレートのペアを供給したくない場合、 `callback(null)` を呼び出します。Knockout は `null` でない値が供給されるまで、順番に他の登録済みローダと相談します。

##### `loadTemplate(name, templateConfig, callback)` {#loadtemplatename-templateconfig-callback}

*これを定義するのは* : 与えられたテンプレート設定によってDOMノードを供給するためにカスタムロジックを使用したい場合です(例えば、URLからテンプレートを取得する際、AJAX リクエストを使用する等)。

デフォルトのコンポーネントローダは、コンポーネント設定の `template` 部分をDOMノードの配列に変換するため、この関数が定義されている登録済みのローダに対して、この関数を呼び出します。そして、このノードはキャッシュされ、複製された後にコンポーネントの各インスタンスに渡されます。

`templateConfig` の値は、単に何らかの `componentConfig` オブジェクトの `template` プロパティです。例えば、それは `"何らかのマークアップ"` または `{ element: "someId" }` 、あるいは `{ loadFromUrl: "someUrl.html" }` のようなカスタムフォーマットかもしれません。

* DOMノードの配列を供給するためには、 `callback(domNodeArray)` を呼び出します。

* もし、あなたのローダについて与えられたパラメータによってテンプレートを供給したくない場合 (例えば、それが 設定フォーマットを理解しない場合など)、 `callback(null)` を呼び出します。Knockout は `null` でない値が供給されるまで、順番に他の登録済みローダと相談します。

##### `loadViewModel(name, templateConfig, callback)` {#loadviewmodelname-templateconfig-callback}

*これを定義するのは* : 与えられたビューモデルの設定によって供給されるビューモデルのファクトリに対して、カスタムロジックを使用したい場合です(例えば、サードパーティモジュールのローダとの統合や、依存性注入のシステムなど)。

デフォルトのコンポーネントローダは、コンポーネント設定の `viewModel` 部分を `createViewModel` ファクトリ関数に変換するため、この関数が定義されている登録済みのローダに対して、この関数を呼び出します。この関数はキャッシュされ、ビューモデルを必要とするコンポーネントの新しいインスタンスごとに呼び出されます。

`viewModelConfig` の値は、単に何らかの `componentConfig` オブジェクトの `viewModel` プロパティです。例えば、それはコンストラクタ関数、または `{ myViewModelType: 'Something', options: {} }` のようなカスタムフォーマットかもしれません。

* `createViewModel` 関数を供給するためには、 `callback(yourCreateViewModelFunction)` を呼び出します。この `createViewModel` 関数は、引数 `(params, componentInfo)` を許容する必要があり、それが呼び出されるたびに、新しいビューモデルのインスタンスを同期的に返す必要があります。

* もし、あなたのローダについて与えられたパラメータによって `createViewModel` 関数を供給したくない場合 (例えば、それが 設定フォーマットを理解しない場合など)、 `callback(null)` を呼び出します。Knockout は `null` でない値が供給されるまで、順番に他の登録済みローダと相談します。

### カスタムコンポーネントローダの登録 {#registering-custom-component-loaders}

Knockout はあなたが複数のコンポーネントローダを同時に使用することを許可します。これは、例えば異なる機構を実装したローダをプラグインして使用し(例えば、片方はテンプレートをバックエンドサーバから命名規則に従って取得し、もう片方は依存性注入の仕組みを使用してビューモデルをセットアップする)、それらを協調して動作させる場合などに有用です。

従って、 `ko.components.loaders` は 現在有効になっている全てのローダを含む配列です。デフォルトでは、この配列はただ一つの項目を持ちます: `ko.components.defaultLoader` 。 ローダを追加するには、単に `ko.components.loaders` 配列に対してそのローダを挿入します。

### 優先順位の制御 {#controlling-precedence}

もし、あなたがデフォルトローダよりもカスタムローダの優先順位を上げたい場合(設定 / 値の供給について、最優先されるようにしたい場合)、そのカスタムローダを配列の最初に追加します。デフォルトローダの優先順位を上げたい場合(あなたのカスタムローダが、コンポーネントが明示的に登録されていない場合のみ呼び出されるようにしたい場合)は、カスタムローダを配列の最後に追加します。

例:

```javascript
// Adds myLowPriorityLoader to the end of the loaders array.
// It runs after other loaders, only if none of them returned a value.
ko.components.loaders.push(myLowPriorityLoader);

// Adds myHighPriorityLoader to the beginning of the loaders array.
// It runs before other loaders, getting the first chance to return values.
ko.components.loaders.unshift(myHighPriorityLoader)
```

もし必要であれば、ローダの配列から `ko.components.defaultLoader` を除去することも可能です。

### 呼び出しの順番 {#sequence-of-calls}

初回は、Knockout与えられた名前によってコンポーネントを生成する必要があります。それは:

* nullではない `componentConfig` が最初に供給されるまで、順番に登録済みローダの `getConfig` 関数を呼び出します。
* そして、 この `componentConfig` オブジェクトと共に、 最初にnullでない `template` / `createViewModel` のペアが供給されるまで、登録済みローダの `loadComponent` 関数を順番に呼び出します。

デフォルトローダの `loadComponent` が実行される際、それは同時に:

* 最初に null ではない DOM配列が供給されるまで、 登録済みローダの `loadTemplate` 関数を順番に呼び出します。
  * デフォルトローダ自体が、DOM配列に対して適用するテンプレート設定フォーマットの範囲を解決するための、`loadTemplate` 関数を持っています。
* 最初にnullでない `createViewModel` 関数が供給されるまで、順番に 登録済みローダの `loadViewModel` 関数を呼び出します。
  * デフォルトローダ自身が、 `createViewModel` 関数に対して適用するための ビューモデル設定フォーマットの範囲を解決するための、 `loadViewModel` 関数を持っています。

カスタムローダはこのプロセスのどの部分にでも差し込むことができるので、あなたは、設定の供給および解釈、DOMノードの供給、またはビューモデルのファクトリ関数の供給について、完全に制御することができます。 `ko.components.loaders` 内に選択された順序でカスタムローダを配置することにより、異なるローディング機構の優先順位を制御することができます。

### 例1: 命名規則をセットアップするコンポーネントローダ {#example-1-a-component-loader-that-sets-up-naming-conventions}

命名規則を実装するために必要なのは、あなたのカスタムコンポーネントローダが `getConfig` を実装することのみです。例えば：

```javascript
var namingConventionLoader = {
    getConfig: function(name, callback) {
        // 1. Viewmodels are classes corresponding to the component name.
        //    e.g., my-component maps to MyApp.MyComponentViewModel
        // 2. Templates are in elements whose ID is the component name
        //    plus '-template'.
        var viewModelConfig = MyApp[toPascalCase(name) + 'ViewModel'],
            templateConfig = { element: name + '-template' };

        callback({ viewModel: viewModelConfig, template: templateConfig });
    }
};

function toPascalCase(dasherized) {
    return dasherized.replace(/(^|-)([a-z])/g, function (g, m1, m2) { return m2.toUpperCase(); });
}

// Register it. Make it take priority over the default loader.
ko.components.loaders.unshift(namingConventionLoader);
```

これだけでコンポーネントは登録済みになり、どのような名前であれ（それらを事前に登録することなく）コンポーネントを参照することができます。例えば:

```html
<div data-bind="component: 'my-component'"></div>

<!-- Declare template -->
<template id='my-component-template'>Hello World!</template>

<script>
    // Declare viewmodel
    window.MyApp = window.MyApp || {};
    MyApp.MyComponentViewModel = function(params) {
        // ...
    }
</script>
```

### 例2: カスタムコードを使用して外部ファイルをロードするコンポーネントローダ {#example-2-a-component-loader-that-loads-external-files-using-custom-code}

もし、あなたのカスタムローダが `loadTemplate` または `loadViewModel` を実装する場合、あなたは ローディングプロセスにカスタムコードをプラグインできます。そして、これらの関数をカスタム設定フォーマットを妨害するために使用できます。

例えば、あなたは以下のように設定フォーマットを有効化したいかもしれません:

```javascript
ko.components.register('my-component', {
    template: { fromUrl: 'file.html', maxCacheAge: 1234 },
    viewModel: { viaLoader: '/path/myvm.js' }
});
```

... そして、あなたはカスタムローダを使用することにより、それを行うことが可能です。

以下のカスタムローダは `fromURL` の値の設定により、テンプレートをロードします。

```javascript
var templateFromUrlLoader = {
    loadTemplate: function(name, templateConfig, callback) {
        if (templateConfig.fromUrl) {
            // Uses jQuery's ajax facility to load the markup from a file
            var fullUrl = '/templates/' + templateConfig.fromUrl + '?cacheAge=' + templateConfig.maxCacheAge;
            $.get(fullUrl, function(markupString) {
                // We need an array of DOM nodes, not a string.
                // We can use the default loader to convert to the
                // required format.
                ko.components.defaultLoader.loadTemplate(name, markupString, callback);
            });
        } else {
            // Unrecognized config format. Let another loader handle it.
            callback(null);
        }
    }
};

// Register it
ko.components.loaders.unshift(templateFromUrlLoader);
```

... そして、以下のカスタムローダは `viaLoader` 値の設定によって、ビューモデルをロードします。

```javascript
var viewModelCustomLoader = {
    loadViewModel: function(name, viewModelConfig, callback) {
        if (viewModelConfig.viaLoader) {
            // You could use arbitrary logic, e.g., a third-party
            // code loader, to asynchronously supply the constructor.
            // For this example, just use a hard-coded constructor function.
            var viewModelConstructor = function(params) {
                this.prop1 = 123;
            };

            // We need a createViewModel function, not a plain constructor.
            // We can use the default loader to convert to the
            // required format.
            ko.components.defaultLoader.loadViewModel(name, viewModelConstructor, callback);
        } else {
            // Unrecognized config format. Let another loader handle it.
            callback(null);
        }
    }
};

// Register it
ko.components.loaders.unshift(viewModelCustomLoader);
```

もし必要であれば、あなたは単一のオブジェクト上に `loadTemplate` と `loadViewModel` 関数を配置することで、 `templateFromUrlLoader` と `viewModelCustomLoader` を単一のローダに組み合わせることもできます。しかし、これらの関心事を分割するのは、その実装が独立したものになるため、非常に好ましい事です。


### 注: カスタムコンポーネントローダとカスタムエレメント {#note-custom-component-loaders-and-custom-elements}

もし、あなたがコンポーネントを取得するため、命名規則によってコンポーネントローダを使用しており、 `ko.components.register` によるコンポーネントの登録を行っていない場合、それらのコンポーネントは自動的にカスタムエレメントとして使用できるようにはなりません(なぜなら、あなたはKnockout にそれが存在していることを伝えていないからです)。

参考: [明示的なコンポーネントの登録に対応していない名前によるカスタムエレメントを有効化する方法](./component-custom-elements#registering-custom-elements)

### 注: browserify での開発 {#note-integrating-with-browserify}

[Browserify](http://browserify.org/) は、Node スタイルの同期的な `require` 構文によってJavaScriptライブラリを参照するための、ポピュラーなライブラリです。これは、しばしば require.js のようなAMD ローダの代替として見なされています。しかしながら、Browserify はかなり異なる問題を解決します: AMDによって扱われる非同期的な実行時の参照解決ではなく、同期的なビルド時の参照解決です。

Browserify はビルド時のツールであり、実際のところKO コンポーネントについて特別な統合手段を必要としないため、Browserifyで動作させるための、いかなる種類のカスタムコンポーネントローダ実装も必要としません。単に Browserify の `require` 構文を、あなたのコンポーネントのビューモデルインスタンスを取得するために使用することができ、明示的にそれを登録します。例えば:

```javascript
// Note that the following *only* works with Browserify - not with require.js,
// since it relies on require() returning synchronously.

ko.components.register('my-browserify-component', {
    viewModel: require('myViewModel'),
    template: require('fs').readFileSync(__dirname + '/my-template.html', 'utf8')
});
```

ここでは自動的に .htmlファイルをインライン化するため、 [brfs Browserify plugin](https://github.com/substack/brfs) を使用するので、あなたは次のようなコマンドを使用してスクリプトファイルを構築する必要があります：

```sh
npm install brfs
browserify -t brfs main.js > bundle.js
```
