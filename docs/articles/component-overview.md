# コンポーネントとカスタムエレメント - 概要

*コンポーネント* はあなたのUIコードを自己完結した再利用可能なチャンクに整理するための、強力でクリーンな方法です。

コンポーネントは:

* ...あなたのアプリケーションの、独立したコントロール/ウィジェット、またはセクション全体を表すことができます。
* ...独自のビューと、通常は（しかしながら任意で）独自のビューモデルを含んでいます。
* ...AMDや他のモジュールシステムを利用して、プリロード、または（必要に応じて）非同期にロードされることができます。
* ...パラメータを受け取り、任意で変更をそれらに書き戻したり、コールバックを呼び出すことができます。
* ...お互いに組み合わせたり（ネスト）、または他のコンポーネントを継承することができます
* ...プロジェクト間で再利用するために、簡単にパッケージすることができます
* ...コンフィグレーションおよびロードについて、あなたが独自の規則/ロジックを定義することができます


これらのパターンは、大規模なアプリケーションのために有益です。
なぜなら、構成の明確化とカプセル化によって *開発を簡潔にする* ことができ、また、アプリケーションコードとテンプレートを、必要に応じて差分ロードすることによって、 *ランタイム性能を向上させる* ために役立つからです。

*カスタムエレメント* は、コンポーネントを利用するためのオプションながらも便利な構文です。バインディングによってコンポーネントが注入される対象として、必要とされるプレースホルダーの `<div>` の替わりに、カスタムエレメント名としてより自己記述的なマークアップを使用することができます(例えば、 `<voting-button>` または `<product-editor>` のような)。Knockout はIE6のような古いブラウザでさえ、互換性を確保することに注意しています。


### 例: like/dislike ウィジェット {#example-a-likedislike-widget}

最初に、あなたは `ko.components.register` を使用することでコンポーネントを登録できます (技術的には登録は任意ですが、開始するための最も簡単な方法です)。コンポーネント定義では `viewModel` と `template` を指定します。
例えば:

```javascript
ko.components.register('like-widget', {
    viewModel: function(params) {
        // Data: value is either null, 'like', or 'dislike'
        this.chosenValue = params.value;

        // Behaviors
        this.like = function() { this.chosenValue('like'); }.bind(this);
        this.dislike = function() { this.chosenValue('dislike'); }.bind(this);
    },
    template:
        '<div class="like-or-dislike" data-bind="visible: !chosenValue()">\
            <button data-bind="click: like">Like it</button>\
            <button data-bind="click: dislike">Dislike it</button>\
        </div>\
        <div class="result" data-bind="visible: chosenValue">\
            You <strong data-bind="text: chosenValue"></strong> it\
        </div>'
});
```

通常、このようにインラインで定義をするのではなく、 *ビューモデルとテンプレートは外部ファイルからロードします* 。このことは後ほど説明します。

このコンポーネントを使用するには、 [`component` バインディング](./component-binding) または [カスタムエレメント](./component-custom-elements) によって、あなたのアプリケーション内に存在する他のビューから参照することができます。


<div class="liveExample" id="component-inline">

    <ul data-bind="foreach: products">
        <li class="product">
            <strong data-bind="text: name"></strong>
            <like-widget params="value: userRating"></like-widget>
        </li>
    </ul>

<script type="text/javascript">

// Temporarily redirect ko.applyBindings to scope it to this live example
var realKoApplyBindings = ko.applyBindings;
ko.applyBindings = function() {
	if (arguments.length === 1)
		return ko.applyBindings(arguments[0], document.getElementById('component-inline'));
	return realKoApplyBindings.apply(ko, arguments);
}

/*<![CDATA[*/
    function Product(name, rating) {
        this.name = name;
        this.userRating = ko.observable(rating || null);
    }

    function MyViewModel() {
        this.products = [
            new Product('Garlic bread'),
            new Product('Pain au chocolat'),
            new Product('Seagull spaghetti', 'like') // This one was already 'liked'
        ];
    }

    ko.applyBindings(new MyViewModel());
/*]]>*/

ko.applyBindings = realKoApplyBindings;

</script>
</div>


#### ソースコード: ビュー

```html
<ul data-bind="foreach: products">
    <li class="product">
        <strong data-bind="text: name"></strong>
        <like-widget params="value: userRating"></like-widget>
    </li>
</ul>
```

#### ソースコード: ビューモデル

```javascript
function Product(name, rating) {
    this.name = name;
    this.userRating = ko.observable(rating || null);
}

function MyViewModel() {
    this.products = [
        new Product('Garlic bread'),
        new Product('Pain au chocolat'),
        new Product('Seagull spaghetti', 'like') // This one was already 'liked'
    ];
}

ko.applyBindings(new MyViewModel());
```

この例では、`Product` ビューモデルクラスに存在する、 `userRating` という名前のobservableプロパティをコンポーネントから表示および編集することができます。

### 例: 必要に応じて外部ファイルから like/dislike ウィジェットを読み込む {#example-loading-the-likedislike-widget-from-external-files-on-demand}

ほとんどのアプリケーションでは、コンポーネントのビューモデルとテンプレートを外部ファイルに保持したいでしょう。
もし、あなたが [require.js](http://requirejs.org/) のようなAMDモジュールローダを介してそれらを取得するようにKnockoutを設定した場合、それらをプリロード(バンドル/圧縮 されたデータでも可能)、または必要に応じて差分ロードすることができます。

こちらがコンフィギュレーションの例です：

```javascript
ko.components.register('like-or-dislike', {
    viewModel: { require: 'files/component-like-widget' },
    template: { require: 'text!files/component-like-widget.html' }
});
```

#### 必要条件

これが動作するためには、 [files/component-like-widget.js](http://knockoutjs.com/documentation/files/component-like-widget.js) と [files/component-like-widget.html](http://knockoutjs.com/documentation/files/component-like-widget.html) のファイルが存在している必要があります。これらをチェックしてください（そして、.html についてはソースを確認してください） - 見ての通り、これは定義内にインラインでコードを含む場合に比べて、よりクリーンで便利な方法です。

また、あなたは( [require.js](http://requirejs.org/) のような) 適切なモジュールローダライブラリを参照するか、またはあなたのファイルを取得する方法を知っている、 [カスタムコンポーネントローダ](./component-loaders) を実装している必要があります。

#### コンポーネントの使用

この *like-or-dislike* は、 [`component` バインディング](./component-binding) または [カスタムエレメント](./component-custom-elements) のいずれかによって、以前と同じように使用することができます。


<div class="liveExample" id="component-amd">

    <ul data-bind="foreach: products">
        <li class="product">
            <strong data-bind="text: name"></strong>
            <like-or-dislike params="value: userRating"></like-or-dislike>
        </li>
    </ul>
    <button data-bind="click: addProduct">Add a product</button>

<script type="text/javascript">

// Temporarily redirect ko.applyBindings to scope it to this live example
var realKoApplyBindings = ko.applyBindings;
ko.applyBindings = function() {
	if (arguments.length === 1)
		return ko.applyBindings(arguments[0], document.getElementById('component-amd'));
	return realKoApplyBindings.apply(ko, arguments);
}

/*<![CDATA[*/
    function Product(name, rating) {
        this.name = name;
        this.userRating = ko.observable(rating || null);
    }

    function MyViewModel() {
        this.products = ko.observableArray(); // Start empty
    }

    MyViewModel.prototype.addProduct = function() {
        var name = 'Product ' + (this.products().length + 1);
        this.products.push(new Product(name));
    };

    ko.applyBindings(new MyViewModel());
/*]]>*/

ko.applyBindings = realKoApplyBindings;

</script>
</div>


#### ソースコード: ビュー

```html
<ul data-bind="foreach: products">
    <li class="product">
        <strong data-bind="text: name"></strong>
        <like-or-dislike params="value: userRating"></like-or-dislike>
    </li>
</ul>
<button data-bind="click: addProduct">Add a product</button>
```

#### ソースコード: ビューモデル

```javascript
function Product(name, rating) {
    this.name = name;
    this.userRating = ko.observable(rating || null);
}

function MyViewModel() {
    this.products = ko.observableArray(); // Start empty
}

MyViewModel.prototype.addProduct = function() {
    var name = 'Product ' + (this.products().length + 1);
    this.products.push(new Product(name));
};

ko.applyBindings(new MyViewModel());
```

もし、あなたがAdd Prodctを最初にクリックする前にブラウザの開発者ツールでネットワークインスペクタを開くと、コンポーネントの `.js` / `.html` ファイルが最初に必要になった際にオンデマンドで取得され、その後再利用のために保持されることを確認できます。


### より詳しく知る {#learn-more}

さらに詳細な情報については、以下を参照してください。

* [コンポーネントの登録](./component-registration)
* ["component" バインディング](./component-binding)
* [カスタムエレメント](./component-custom-elements)
* [上級: カスタムコンポーネントローダ](./component-loaders)
