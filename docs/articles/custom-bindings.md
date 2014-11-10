# カスタムバインディングの作成

あなたは `click` や `value` のような組み込みのバインディングの利用に留まらず、あなた自身のバインディングを作成することができます。これはDOM要素と対話するobservableを制御して、簡単に再利用できる、洗練された振る舞いをカプセル化するための多くの柔軟性を提供する方法です。例えば、カスタムバインディングの形でグリッドやタブセットのような対話的なコンポーネントを作成することができます([ページング機能付きの表](/tips/grid) を参照してください)。

### バインディングの登録 {#registering-your-binding}

バインディングを登録するには、`ko.bindingHandlers`のサブプロパティとして追加してください:

```javascript
ko.bindingHandlers.yourBindingName = {
    init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
        // This will be called when the binding is first applied to an element
        // Set up any initial state, event handlers, etc. here
    },
    update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
        // This will be called once when the binding is first applied to an element,
        // and again whenever any observables/computeds that are accessed change
        // Update the DOM element based on the supplied values here.
    }
};
```

...これでいくつでも DOM 要素に対して使用することができます:

```html
<div data-bind="yourBindingName: someValue"> </div>
```

注: `init` と `update` コールバックは必要に応じて実装してください。必ずしも両方実装すべきというわけではありません。

### “update” コールバック {#the-update-callback}

KnockoutはバインディングがDOM要素に適用された時、最初に `update` コールバックを呼び出して、すべての依存関係（observables/computeds）を追跡します。これら依存関係のなんらかの変更によって、`update` コールバックが再び呼び出されます。この際、次のパラメータが渡されます。

* `element` — このバインディングに関連付けられたDOM要素
* `valueAccessor` — このバインディングに関連付けられた現在のモデルのプロパティを取得するためのJavaScript関数。パラメータを渡さずにこのメソッドを呼び出した場合(つまり、`valueAccessor()` の呼び出し)、現在のモデルのプロパティ値を取得します。簡単にobservableと生の値の両方を受け取るには、戻り値に対して `ko.unwrap` を呼び出します。
* `allBindings` — このDOM要素にバインドされている全てのモデル値にアクセスするためのJavaScriptオブジェクト。`allBindings.get('name')` の呼び出しにより、対象の `name` バインディングの値を取得します(バインディングが存在しない場合、`undefined` が返されます); また、`allBindings.has('name')` は現在のDOM要素に対し、対象の `name` バインディングが存在するかどうかを確認します。
* `viewModel` — このパラメータは、Knockout 3.xでは廃止予定です。ビューモデルにアクセスするには代わりに `bindingContext.$data` か、`bindingContext.$rawData` を使用してください。
* `bindingContext` — このDOM要素のバインディングで利用可能な、[バインディングコンテキスト](binding-context)を保持するオブジェクト。このオブジェクトは、このコンテキストの祖先に対してバインドされているデータにアクセスするための `$parent`、`$parents`、そして`$root` 等の、特別なプロパティを含んでいます。

例えば、あなたが今まで `visible` バインディングを使用してDOM要素の可視性を制御しており、さらに一歩進んでトランジションのアニメーションを行いたいとします。DOM 要素が observable の値に応じてスライドイン・アウトで表示されるようにしたいとしましょう。jQuery の `slideUp/slideDown` 関数を呼び出すカスタムバインディングを書く事で、これを行うことができます：

```javascript
ko.bindingHandlers.slideVisible = {
    update: function(element, valueAccessor, allBindings) {
        // First get the latest data that we're bound to
        var value = valueAccessor();

        // Next, whether or not the supplied model property is observable, get its current value
        var valueUnwrapped = ko.unwrap(value);

        // Grab some more data from another binding property
        var duration = allBindings.get('slideDuration') || 400; // 400ms is default duration unless otherwise specified

        // Now manipulate the DOM element
        if (valueUnwrapped == true)
            $(element).slideDown(duration); // Make the element visible
        else
            $(element).slideUp(duration);   // Make the element invisible
    }
};
```

これで以下のようにバインディングを使用できます:

```html
<div data-bind="slideVisible: giftWrap, slideDuration:600">You have selected the option</div>
<label><input type="checkbox" data-bind="checked: giftWrap" /> Gift wrap</label>

<script type="text/javascript">
    var viewModel = {
        giftWrap: ko.observable(true)
    };
    ko.applyBindings(viewModel);
</script>
```

もちろん、これは最初に眺めるにしては大量のコードですが、一度カスタムバインディングを作成しておけば、とても簡単に多くの場所で再利用することができます。

### “init” コールバック {#the-init-callback}

Knockout はバインディング対象の各DOM要素に対して一度、`init` 関数を呼び出します。`init` には2つの主な用途があります：

* DOM 要素に対し、任意の初期状態を設定する
* 任意のイベントハンドラを登録することで、ユーザーが DOM 要素をクリックしたり変更した場合に、関連する observable の状態を変更する

Knockout は、それが[updateコールバック](#the-update-callback)に渡すものと、完全に同じパラメータのセットを渡します。

前述の例を続けると、あなたはページが最初に（どのようなアニメーションのスライドもなしで）表示された時、`slideVisible` を要素に設定し、即座に表示または非表示にして、ユーザがモデルの状態を変更したときのみアニメーションが実行されるようにしたいでしょう。以下のようにそれを行うことができます:

```javascript
ko.bindingHandlers.slideVisible = {
    init: function(element, valueAccessor) {
        var value = ko.unwrap(valueAccessor()); // Get the current value of the current property we're bound to
        $(element).toggle(value); // jQuery will hide/show the element depending on whether "value" or true or false
    },
    update: function(element, valueAccessor, allBindings) {
        // Leave as before
    }
};
```

これは、もし `giftWrap` が初期状態 `false` で定義されている場合(つまり、`giftWrap：ko.observable(false)`)、関連付けられた DIV は最初は非表示で、後でユーザーがボックスをチェックした時にビューにスライドインされることを意味します。

### DOM イベントの後に observable を修正する {#modifying-observables-after-dom-events}

あなたは既にこれまで `update` の使用方法を見てきたので、observable の変更の際に、関連付けられた DOM 要素を更新することができます。しかし、他の方向からのイベントについてはどうしたらよいでしょうか？ユーザーが DOM 要素にいくつかのアクションを実行した時、関連するobservable を更新したいかもしれません。

あなたは、関連する observable の変更を引き起こすイベントハンドラを登録する場所として、`init` コールバックを使用することができます。例えば、

```javascript
ko.bindingHandlers.hasFocus = {
    init: function(element, valueAccessor) {
        $(element).focus(function() {
            var value = valueAccessor();
            value(true);
        });
        $(element).blur(function() {
            var value = valueAccessor();
            value(false);
        });
    },
    update: function(element, valueAccessor) {
        var value = valueAccessor();
        if (ko.unwrap(value))
            element.focus();
        else
            element.blur();
    }
};
```

これで、DOM 要素の "focusedness" を observable として登録すれば読み書きの両方を行うことができます。

```html
<p>Name: <input data-bind="hasFocus: editingName" /></p>

<!-- Showing that we can both read and write the focus state -->
<div data-bind="visible: editingName">You're editing the name</div>
<button data-bind="enable: !editingName(), click:function() { editingName(true) }">Edit name</button>

<script type="text/javascript">
    var viewModel = {
        editingName: ko.observable()
    };
    ko.applyBindings(viewModel);
</script>
```

### 注: 仮想エレメントのサポート {#note-supporting-virtual-elements}

もし、カスタムバインディングを Knockout の仮想エレメントの構文で使用したい場合、以下のようにします。

```
<!-- ko mybinding: somedata --> ... <!-- /ko -->
```

… 詳しくは、[仮想エレメントのドキュメント](custom-bindings-for-virtual-elements)を参照してください。
