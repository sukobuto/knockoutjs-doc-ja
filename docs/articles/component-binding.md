# "component" バインディング

`component` バインディングは特定の [コンポーネント](./component-overview) をエレメントに注入し、オプションでそれにパラメータを渡すことができます。

* [実行例](#live-example)
* [API](#api)
* [コンポーネントのライフサイクル](#component-lifecycle)
* [注: テンプレートのみのコンポーネント](#note-template-only-components)
* [注: コンテナエレメント無しでの `component` の使用](#note-using-component-without-a-container-element)
* [注: コンポーネントへのマークアップの受け渡し](#note-passing-markup-to-components)
* [廃棄とメモリマネジメント](#disposal-and-memory-management)

### 実行例 {#live-example}

# (ここにライブビューが入ります)

##### ソースコード: ビュー

```html
<h4>First instance, without parameters</h4>
<div data-bind='component: "message-editor"'></div>

<h4>Second instance, passing parameters</h4>
<div data-bind='component: {
    name: "message-editor",
    params: { initialText: "Hello, world!" }
}'></div>
```

##### ソースコード: ビューモデル

```javascript
ko.components.register('message-editor', {
    viewModel: function(params) {
        this.text = ko.observable(params && params.initialText || '');
    },
    template: 'Message: <input data-bind="value: text" /> '
            + '(length: <span data-bind="text: text().length"></span>)'
});

ko.applyBindings();
```

注: 多くの現実的なケースでは、通常あなたは登録内容としてそれをハードコーディングするのではなく、外部ファイルからコンポーネントのビューモデルとテンプレートをロードします。
[実行例](./component-overview#example-loading-the-likedislike-widget-from-external-files-on-demand)と [登録についてのドキュメント](./component-registration) を参照してください。

#### API {#api}

`component` バインディングの使用には、2つの方法があります:

  * ショートハンドシンタックス

もし、あなたが単に文字列を渡すと、コンポーネント名として解釈されます。名前の付けられたコンポーネントは、いかなるパラメータも提供されずに注入されます。例えば:

```html
<div data-bind='component: "my-component"'></div>
```

ショートハンドの値はobservable にすることもできます。この場合、もしそれが変更されると `component` バインディングは古いコンポーネントインスタンスを [破棄](#disposal-and-memory-management) して、新しく参照されたコンポーネントを注入します。例えば:

```html
<div data-bind='component: observableWhoseValueIsAComponentName'></div>
```

  * 完全なシンタックス

  コンポーネントにパラメータを供給するには、以下のプロパティを含むオブジェクトを渡します。

    * `name` - 注入されるコンポーネントの名前。繰り返しますが、これはobservable にできます。
    * `params` - コンポーネントに渡されるオブジェクト。通常はこれは複数のパラメータを含むキーバリューオブジェクトで、コンポーネントはビューモデルのコンストラクタ経由でこれを受け取ります。

例:

```html
<div data-bind='component: {
    name: "shopping-cart",
    params: { mode: "detailed-list", items: productsList }
}'></div>
```

注意点として、コンポーネントが除去される際は常に（ `name` のobservable値が変更された場合や、制御フローバインディングで囲われた内部でエレメント全体が削除された場合など）、除去されたコンポーネントは [破棄](#disposal-and-memory-management) されます。

#### コンポーネントのライフサイクル {#component-lifecycle}

`component` バインディングがコンポーネントを注入する時、

1. *あなたのコンポーネントローダはビューモデルのファクトリとテンプレートを供給するかを尋ねます*

  * 複数のコンポーネントローダは、最初の一つがコンポーネント名を認識してビューモデル/テンプレートを供給するまで、参照されることができます。Knockoutがメモリ上に結果となる定義をキャッシュするため、このプロセスは、 *コンポーネントの種類ごとに一度* 行われます。

  * デフォルトのコンポーネントローダは [あなたの登録した内容](./component-registration) に基づいてビューモデル/テンプレートを供給します。もしそれ適切であれば、このフェーズであなたのAMDローダから、なんらかの指定されたAMDモジュールをリクエストします。

通常、これは非同期のプロセスです。ここでは、サーバーへのリクエストが発生するかもしれません。 APIの一貫性を保つため、Knockoutはデフォルトで、コンポーネントがすでにロードされ、メモリにキャッシュされている場合でも、非同期コールバックとしてロードのプロセスが完了することを保証します。この詳細と、同期的なロードを許可する方法については、 [同期/非同期ロードの制御](./component-registration#controlling-synchronousasynchronous-loading) を参照してください。

2. *コンポーネントのテンプレートが複製され、コンテナエレメント内に注入されます。*

既に存在する内容は、全て除去および破棄されます。

3. *コンポーネントがビューモデルを持つ場合、インスタンス化されます。*

もしビューモデルがコンストラクタ関数として与えられた場合、Kkockout は `new YourViewModel(params)` を呼び出します。

もし、ビューモデルが `createViewModel` ファクトリ関数として与えられた場合、Knockout は `componentInfo.element` に未バインドのテンプレートが既に注入された状態で、 `createViewModel(params, componentInfo)` を呼び出します。

このフェーズは常に同期的に完了するので (コンストラクタとファクトリ関数は非同期であることを許可されません)、もしそこでネットワークリクエストの待ち時間が発生する場合、毎回コンポーネントがインスタンス化され、パフォーマンスは受け入れがたいでしょう。

4. *ビューモデルがビューにバインドされます。*

または、コンポーネントがビューモデルを持たない場合、ビューはあなたが `component` バインディングに提供した、何らかの `params` にバインドされます。

5. *コンポーネントがアクティブになります。*

コンポーネントは動作しており、必要である限り、画面上に残り続けることができます。

コンポーネントに渡されたパラメータのいずれかがobservableである場合、コンポーネントは、もちろん全ての変更を監視することができ、さらにバック変更された値を書き戻すことが可能です。これは、そのコンポーネントを使用するいかなる親ともコンポーネントのコードを密結合することなしに、クリーンに通信できる方法です。

6. *コンポーネントが取り壊され、ビューモデルが破棄されます。*

もし、 `component` バインディングの `name` 値が observable に変更され、または制御フローバインディングの囲みでコンテナエレメントが除去されると、コンテナエレメントがDOMから除去される前に、ビューモデル上の何らかの `dispose` 関数が呼び出されます。
[破棄とメモリマネジメント](#disposal-and-memory-management) も合わせて参照してください。

注: ユーザーが全く別のWebページに移動する場合、ブラウザは、クリーンアップするページ上で動作しているコードに配慮せず、これを行います。従って、この場合は `dispose` 関数が呼び出されません。ブラウザは自動的に、利用中の全てのオブジェクトが使用しているメモリを解放しますので、これは問題ありません。

#### 注: テンプレートのみのコンポーネント {#note-template-only-components}

コンポーネントは通常ビューモデルを持ちますが、必須ではありません。コンポーネントはテンプレートのみを指定することができます。

この場合、コンポーネントのビューがバインドされるのは、あなたが `component` バインディングに渡した `params` オブジェクトになります。
例えば、

```javascript
ko.components.register('special-offer', {
    template: '<div class="offer-box" data-bind="text: productName"></div>'
});
```

... 以下のように注入することができ、

```html
<div data-bind='component: {
     name: "special-offer-callout",
     params: { productName: someProduct.name }
}'></div>
```

... または、より便利な形で、[カスタムエレメント](./component-custom-elements) にできます。

```html
<special-offer params='productName: someProduct.name'></special-offer>
```


#### 注: コンテナエレメント無しでの `component` の使用 {#note-using-component-without-a-container-element}

時には、余分なコンテナエレメントを使用せず、ビューにコンポーネントを注入したいことがあるでしょう。あなたはコメントタグに基づいたコンテナレスの制御フロー構文を使用して、これを行うことができます。
例えば、

```javascript
<!-- ko component: "message-editor" -->
<!-- /ko -->
```

... またはパラメータを渡します:

```javascript
<!-- ko component: {
    name: "message-editor",
    params: { initialText: "Hello, world!", otherParam: 123 }
} -->
<!-- /ko -->
```

`<!-- ko -->` および `<!-- /ko -->` コメントは、内部にマークアップを含んだ"仮想エレメント"を定義するための、開始/終了マーカーとして機能します。Knockoutはこの仮想エレメント構文を理解し、現実のコンテナエレメントが存在していたかのようにバインドします。


#### 注: コンポーネントへのマークアップの受け渡し {#note-passing-markup-to-components}

あなたが `component` バインディングの対象とするエレメントは、さらにマークアップを含むかもしれません。例えば、

```html
<div data-bind="component: { name: 'my-special-list', params: { items: someArrayOfPeople } }">
    <!-- Look, here's some arbitrary markup. By default it gets stripped out
         and is replaced by the component output. -->
    The person <em data-bind="text: name"></em>
    is <em data-bind="text: age"></em> years old.
</div>
```

このエレメント内のDOMノードは、デフォルトでは取り除かれバインドされませんが、失われるわけではありません。代わりに、それはコンポーネント（この場合、 `my-special-list`）に供給され、希望する出力結果に含めることができます。

これは、グリッド、リスト、ダイアログ、またはタブセットなど、共通な構造の内部に任意のマークアップをバインドする必要がある、"コンテナ"のUIエレメントを表すコンポーネントを構築したい場合に便利です。
[カスタムエレメント](./component-custom-elements.html#passing-markup-into-components) の完全な例を参照してください。これはまた、上記の構文を使用することでカスタムエレメント無しでも動作します。


#### 廃棄とメモリマネジメント {#disposal-and-memory-management}

必要に応じて、あなたのビューモデルクラスは `dispose` 関数を持つことができます。もしこれが実装されている場合、Knockout はコンポーネントが取り壊され、DOMから除去されるたびにこれを呼び出します(例えば、対応する項目が `foreach` から除去された場合や、 `if` バインディングが `false` になった場合など)。

あなたは本質的にガーベージコレクションが可能ではない、なんらかのリソースを解放するために `dispose` 関数を使用する必要があります。例えば：

* `setInterval` コールバックは明示的にクリアされない限り、継続的に呼び出されます。
  * それらを停止するために `clearInterval(handle)` を使用しない限り、あなたのビューモデルはメモリ上に保持されるでしょう。

* `ko.computed` プロパティは明示的に破棄されない限り、依存対象からの通知を受け取り続けます。
  * もし、依存対象が外部オブジェクトの場合、computed プロパティに対して確実に `.dispose()` を使用しない限り、それは(そして、おそらくあなたのビューモデルも)メモリに保持されます。他の方法として、手動で破棄を行う必要性を回避するために、 [pure computed](./computed-pure) の使用を検討してください。

* observable の *サブスクリプション* は、明示的に破棄されるまで通知され続けます。
  * もし、あなたが外部のobservable をsubscribeしている場合、サブスクリプションに対して確実に `.dispose()` を使用しない限り、そのコールバックは(そしておそらくあなたのビューモデルも)メモリに保持されます。

* 手動で作成された外部DOMエレメント上の *イベントハンドラ* が、もし `createViewModel` 関数の内部 (または、MVVMパターンに合わないためこれをすべきではありませんが、通常のコンポーネントビューモデル内)で作成された場合、それは除去される必要があります。
  * もちろん、あなたのビュー内で、通常のKnockout バインディングによって生成されたイベントハンドラについては、 エレメントが除去された際に KOが自動的にそれらを解除するため、イベントハンドラの開放を気にする必要はありません。

例えば:

```javascript
var someExternalObservable = ko.observable(123);

function SomeComponentViewModel() {
    this.myComputed = ko.computed(function() {
        return someExternalObservable() + 1;
    }, this);

    this.myPureComputed = ko.pureComputed(function() {
        return someExternalObservable() + 2;
    }, this);

    this.mySubscription = someExternalObservable.subscribe(function(val) {
        console.log('The external observable changed to ' + val);
    }, this);

    this.myIntervalHandle = window.setInterval(function() {
        console.log('Another second passed, and the component is still alive.');
    }, 1000);
}

SomeComponentViewModel.prototype.dispose = function() {
    this.myComputed.dispose();
    this.mySubscription.dispose();
    window.clearInterval(this.myIntervalHandle);
    // this.myPureComputed doesn't need to be manually disposed.
}

ko.components.register('your-component-name', {
    viewModel: SomeComponentViewModel,
    template: 'some template'
});
```

同じビューモデルのプロパティのみに依存した computed と サブスクリプションについては、厳密にはこれを破棄する必要はありません。
これは単一の循環参照を作成しているので、JavaScriptのガベージコレクタは、リリースする方法を知っています。
しかし、破棄が必要なものの記憶が必要になることを避けるため、あなたは可能な限り `pureComputed` を使用したほうがよく、他の全ての computed / サブスクリプションについては、技術的に必要かどうかに関わらず、明示的に破棄したほうがよいでしょう。
