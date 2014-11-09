# RequireJsを用いたAMD (非同期モジュール定義)

## AMDの概要

[Writing Modular JavaScript With AMD, CommonJs & ES Harmony](http://addyosmani.com/writing-modular-js/) からの抜粋:

```
我々がアプリケーションがモジュール形式であると言うとき、私たちは一般的に、それが高度に分離された一式であり、モジュールに格納されている機能性の明確な部品によって構成されていることを意味します。おそらくご存じのとおり、疎結合は可能な限り依存関係を除去することによって、アプリケーションの保守をより容易にします。これが効率的に実装されると、システムの一部への変更が他にどのように影響するのか確認することが非常に簡単になります。

しかしながら、いくつかのより伝統的なプログラミング言語とは異なり、JavaScriptの現在のイテレーション（ECMA-262）はこのようなコードのモジュールをクリーンで組織的にインポートするための手段を開発者に提供していません。それは、より組織的なJavaScriptアプリケーションの必要性が明らかになったより近年に至るまで偉大な思想を必要としていない、仕様についての関心事の一つです。

代わりに現時点の開発者は、モジュールのバリエーションやオブジェクトリテラルのパターンによるフォールバックにとり残されています。
これらの多くでは、モジュールスクリプトは単一のグローバルオブジェクトとして定義された名前空間によってDOM内に共存するため、あなたのアーキテクチャ内で名前の衝突を招く可能性があります。また、いくつかの手動作業やサードパーティのツールなしで依存関係の管理を処理するための、完全にクリーンな方法もありません。

これらの問題へのネイティブな解決策はES Harmonyによってもたらされるでしょうが、良いニュースとしてモジュール形式のJavaScriptを書くことはかつてないほど簡単になり、あなたは今日からそれを開始することができます。

```

## Knockout.js とViewModelクラスのRequireJS経由での読み込み

HTML

```html
<html>
    <head>
        <script type="text/javascript" data-main="scripts/init.js" src="scripts/require.js"></script>
    </head>
    <body>
        <p>First name: <input data-bind="value: firstName" /></p>
        <p>First name capitalized: <strong data-bind="text: firstNameCaps"></strong></p>
    </body>
</html>
```

scripts/init.js
```javascript
require(['knockout-x.y.z', 'appViewModel', 'domReady!'], function(ko, appViewModel) {
    ko.applyBindings(new appViewModel());
});
```

scripts/appViewModel.js
```javascript
// Main viewmodel class
define(['knockout-x.y.z'], function(ko) {
    return function appViewModel() {
        this.firstName = ko.observable('Bert');
        this.firstNameCaps = ko.pureComputed(function() {
            return this.firstName().toUpperCase();
        }, this);
    };
});
```

もちろん、**x.y.z**はあなたが読み込んでいるKnockoutスクリプト（例えば、**Knockout-3.1.0**）のバージョン番号に置き換えてください。

## Knockout.js、バインディングハンドラ、ViewModelクラスのRequireJS経由での読み込み

一般的なバインディングハンドラについてのドキュメントは、[こちら](./custom-bindings)で見つけることができます。このセクションでは、カスタムハンドラを保守する上でAMDモジュールが提供するパワーについて説明することを意図しています。私たちはバインディングハンドラのドキュメントから**ko.bindingHandlers.hasFocus**を一例として取り上げます。このハンドラを自身のモジュールでラップすることにより、それを必要とするページでのみ使用されるよう制限することができます。ラップされたモジュールは次のようになります:

```javascript
define(['knockout-x.y.z'], function(ko){
    ko.bindingHandlers.hasFocus = {
        init: function(element, valueAccessor) { ... },
        update: function(element, valueAccessor) { ... }
    }
});
```

あなたはモジュールを定義した後、上記のHTMLの例からinput要素を更新します。

```html
<p>First name: <input data-bind="value: firstName, hasFocus: editingName" /><span data-bind="visible: editingName"> You're editing the name!</span></p>
```

あなたのビューモデルについて、依存関係のリストをモジュールに含めます:

```javascript
define(['knockout-x.y.z', 'customBindingHandlers/hasFocus'], function(ko) {
    return function appViewModel(){
        ...
        // Add an editingName observable
        this.editingName = ko.observable();
    };
});
```

カスタムバインディングハンドラモジュールはそれ自身は何も返さないので、私たちのViewModelモジュールに何も注入しないことに注意してください。それはKnockoutモジュールに追加の振る舞いを定義するだけです。

## RequireJs のダウンロード

RequireJs は http://requirejs.org/docs/download.html からダウンロードできます。
