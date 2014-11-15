# Mapping プラグイン

Knockout は、任意のJavaScriptオブジェクトをビューモデルとして使用できるように設計されています。ビューモデルのプロパティのうちいくつかが [observable](observables) である限り、あなたは KO をそれらのプロパティと UI をバインドするために使用でき、 observable なプロパティが変更されるたびに、 UI は自動的に更新されます。

ほとんどのアプリケーションは、バックエンドサーバからデータを取得する必要があります。サーバは observable についていかなる概念も持っていないので、生の JavaScript オブジェクト（通常は JSON としてシリアライズされます）を供給するのみです。mapping プラグインは、生の JavaScript オブジェクトを、適切な observable を持つビューモデルにマッピングする簡単な方法を提供します。これは、サーバから取得したなんらかのデータに基づいたビューモデルを構成するために、手動で独自の JavaScript コードを書く方法の代替手段です。


### ダウンロード{#download-1}

[Version 2.0](https://github.com/SteveSanderson/knockout.mapping/tree/master/build/output) (8.6kb minified)

### 例: ko.mapping プラグイン無しでの手動マッピング{#example-manual-mapping-without-the-ko-mapping-plugin}

現在のサーバー時刻とあなたのウェブページ上のユーザー数を表示したいとします。あなたは以下のビューモデルを使用してこの情報を表すことができます：

```javascript
var viewModel = {
    serverTime: ko.observable(),
    numUsers: ko.observable()
}
```

あなたは以下のように、このビューモデルをなんらかの HTML 要素に対してバインドすることができます：

```html
The time on the server is: <span data-bind='text: serverTime'></span>
and <span data-bind='text: numUsers'></span> user(s) are connected.
```

ビューモデルのプロパティは observable であるため、KO はそれらのプロパティが変更されるたびに、自動的にHTML要素を更新します。

次に、サーバから最新のデータを取得します。 あなたは5秒ごとにAjaxリクエストを発行します（例えば、 jQuery の `$.getJSON` または`$.ajax` 関数を使用します）。

```javascript
var data = getDataUsingAjax();          // Gets the data from the server
```

サーバは以下のような JSON データを返します。

```javascript
{
    serverTime: '2010-01-07',
    numUsers: 3
}
```

最後に、このデータを使用して（mapping プラグインを使用せずに）ビューモデルを更新するため、以下のように記述します。

```javascript
// 毎回サーバからデータを受け取ります:
viewModel.serverTime(data.serverTime);
viewModel.numUsers(data.numUsers);
```

あなたはページに表示するすべての変数に対して、これを行わなければならないでしょう。もし、データ構造がより複雑になると（例えば、子要素や配列を含む等）、手動で処理するのが非常に煩雑になります。mapping プラグインによって、通常の JavaScript オブジェクト（または JSON 構造）から observable なビューモデルへのマッピングを作成することが可能になります。

### 例: ko.mapping の使用{#using-ko-mapping}

mapping プラグインによってビューモデルを作成するには、上記のコードについて `viewModel` の作成を `ko.mapping.fromJS` 関数で置き換えます。

```javascript
var viewModel = ko.mapping.fromJS(data);
```

これは `data` の各プロパティについて、自動的に observable なプロパティを作成します。その後、サーバから新しいデータを受け取るたびに、あなたは `ko.mapping.fromJS` 関数を再び呼び出すことによって、ワンステップで `viewModel` 上の全てのプロパティを更新できます。

```javascript
// data は毎回サーバから受け取ります:
ko.mapping.fromJS(data, viewModel);
```

### どのようにマッピングされるのか{#how-things-are-mapped}

* オブジェクトの全てのプロパティは observable に変換されます。更新によって値が変更されると、それは observable な形で更新されます。
* 配列は [observable array](observableArrays) に変換されます。更新によって配列のいくつかの値が変更されると、適切な add / remove アクションが実行されます。また、元の JavaScript 配列と同じように並び順を維持しようと試みます。

### マッピングの解除{#unmapping}

もしすでにマッピングされたオブジェクトを、元の、通常の JS オブジェクトに変換したい場合は、以下の関数を使用します。

```javascript
var unmapped = ko.mapping.toJS(viewModel);
```

この関数は、マッピング済みオブジェクトのプロパティのうち、元のJSオブジェクトの一部であったもののみを含む、マッピングが解除されたオブジェクトを作成します。言い換えると、手動でビューモデルに追加されたプロパティまたは関数は無視されます。デフォルトでは、このルールの唯一の例外は `_destroy` プロパティが追加されることで、それはあなたが `ko.observableArray` から項目を破棄する際に Knockout が生成するプロパティです。これを設定する方法の詳細については、"高度な使い方" を参照してください。

### JSON 文字列での動作{#working-with-json-strings}

もし、あなたの Ajax 呼び出しが JSON 文字列を返す（そしてそれを JavaScript オブジェクトにデシリアライズしない）場合は、あなたのビューモデルを作成および更新するため、代わりに `ko.mapping.fromJSON` 関数を使用することができます。解除には、 `ko.mapping.toJSON` を使用することができます。

JS オブジェクトの代わりに JSON 文字列を使用することを除けば、これらの関数は `*JS` の対応する関数と完全に同一です。

### 高度な使い方{#advanced-usage}

場合によっては、マッピングが実行される方法をより詳細に制御する必要があるかもしれません。この目的はマッピングオプションを使用することで達成可能です。これらのオプションは `ko.mapping.fromJS` を呼び出す際に指定することができます。それ以降の呼び出しでは、それらを再度指定する必要はありません。

ここではマッピングオプションを使用したいと感じる状況について、いくつかの例を挙げます。

#### "key"を使用して、一意にオブジェクトを識別する{#uniquely-identifying-objects-using-keys}

例えば、あなたが以下のような JavaScript オブジェクトを扱っているとしましょう：

```javascript
var data = {
    name: 'Scot',
    children: [
        { id : 1, name : 'Alicw' }
    ]
}
```

あなたは特に問題なく、これをビューモデルにマッピングすることができます:

```javascript
var viewModel = ko.mapping.fromJS(data);
```

そして、データが何のタイプミスもない形に更新されたとしましょう:

```javascript
var data = {
    name: 'Scott',
    children: [
        { id : 1, name : 'Alice' }
    ]
}
```

ここでは二つの事が起こっています: `name` が `Scot` から `Scott` に変更され、 `children[0].name` は `Alicw` から、タイプミスのない `Alice` に変更されました。あなたはこの新しいデータに基づいて、 `viewModel` を更新できます。

```javascript
ko.mapping.fromJS(data, viewModel);
```

結果として、 `name` は期待どおりに変更されるでしょう。しかしながら、 `children` の配列からは子供（Alicw）が完全に削除され、新しい子供（Alice)が加えられました。これは、完全にあなたが期待していた通りではありません。あなたは子供そのものを置き換えるのではなく、 `name` プロパティのみが `Alicw` から `Alice` に更新されることを期待しているでしょう！

このことが起こる理由として、デフォルトでは、mapping プラグインは単純に配列内の2つのオブジェクトを比較します。そして、 JavaScript においてはオブジェクト `{ID: 1、name： 'Alicw'}` は `{ID: 1、name: 'Alice'}` と等しくないため、子供自体が削除され、新しいものと置き換えられる必要があると判断されます。

これを解決するには、mapping プラグインに対して、オブジェクトの新旧を判断するために使用すべき `key` を指定することができます。あなたは以下のようにそれを設定できます：

```javascript
var mapping = {
    'children': {
        key: function(data) {
            return ko.utils.unwrapObservable(data.id);
        }
    }
}
var viewModel = ko.mapping.fromJS(data, mapping);
```

これによって、mapping プラグインは `children` 配列内の項目をチェックするたび、オブジェクト全体を置き換えるか、それとも単に更新が必要なのかを判断するために `id` プロパティのみを参照します。

#### "create"を使用して、オブジェクト構築をカスタマイズする{#customizing-object-construction-using-create}

もし、あなたがマッピングの一部を自分で操作したい場合、 `create` コールバックを提供することができます。
このコールバックが存在する場合、mapping プラグインはあなたに、マッピングの一部を自分で行うことを許可します。

例えば、あなたが以下のようなJavaScriptオブジェクトを扱っているとしましょう：

```javascript
var data = {
    name: 'Graham',
    children: [
        { id : 1, name : 'Lisa' }
    ]
}
```

あなたが `children` の配列を自分でマッピングしたい場合は、以下のように指定することができます。

```javascript
var mapping = {
    'children': {
        create: function(options) {
            return new myChildModel(options.data);
        }
    }
}
var viewModel = ko.mapping.fromJS(data, mapping);
```

あなたの `create` コールバックに渡される `options` 引数は、以下を含むJavaScriptオブジェクトです:
* `data`: この子要素のためのデータを含む JavaScript オブジェクト
* `parent`: この子要素が属する親のオブジェクトまたは配列

もちろん、もし望むのであれば、 `create` コールバックの内側で別の `ko.mapping.fromJS` 呼び出しを行うこともできます。典型的なユースケースとしては、元のJavaScriptオブジェクトに対して、他にいくつか [computed observable](computedObservables) を追加したい場合等でしょう。

```javascript
var myChildModel = function(data) {
    ko.mapping.fromJS(data, {}, this);

    this.nameLength = ko.computed(function() {
        return this.name().length;
    }, this);
}
```

#### "update"を使用して、オブジェクトの更新をカスタマイズする{#customizing-object-updating-using-update}

あなたは、 `update` コールバックで指定することにより、オブジェクトがどのように更新されるかをカスタマイズすることもできます。これは更新対象のオブジェクトと、`create` コールバックで使用されるものと同じ、 `options` オブジェクトを受け取ります。あなたは更新された値を `return` する必要があります。

あなたの `update` コールバックに渡された `options` 引数は、以下を含む JavaScript オブジェクトです:
* `data`: この子要素のためのデータを含む JavaScript オブジェクト
* `parent`: この子要素が属する親のオブジェクトまたは配列
* `observable`: もしプロパティがobservableである場合、こちらに実際の observable が設定されます。

以下は、更新前の入力データにいくつかテキストを追加する設定例です。

```javascript
var data = {
    name: 'Graham',
}

var mapping = {
    'name': {
        update: function(options) {
            return options.data + 'foo!';
        }
    }
}
var viewModel = ko.mapping.fromJS(data, mapping);
alert(viewModel.name());
```

これは `Grahamfoo` をアラートします！

#### "ignore" を使用して特定のプロパティを無視する{#ignoring-certain-properties-using-ignore}

もしあなたが、mapping プラグインに対し JS オブジェクトのいくつかのプロパティを無視させたい（つまり、それらをマッピングしない）場合、無視するプロパティ名の配列を指定できます。

```javascript
var mapping = {
    'ignore': ["propertyToIgnore", "alsoIgnoreThis"]
}
var viewModel = ko.mapping.fromJS(data, mapping);
```

あなたがマッピングオプションで指定した `ignore` 配列は、デフォルトの `ignore` 配列に結合されます。あなたは以下のように、デフォルトの配列を操作することができます：

```javascript
var oldOptions = ko.mapping.defaultOptions().ignore;
ko.mapping.defaultOptions().ignore = ["alwaysIgnoreThis"];
```

#### “include” を使用して特定のプロパティを読み込む{#including-certain-properties-using-include}

あなたのビューモデルを元の JS オブジェクトに変換する場合、デフォルトでは、 mapping プラグインは元のビューモデルに含まれていたプロパティのみを含めます。例外は、それが元のオブジェクトの一部でない場合にも含まれる、 Knockout によって生成された `_destroy` プロパティです。しかしながら、あなたは以下の配列をカスタマイズすることができます。

```javascript
var mapping = {
    'include': ["propertyToInclude", "alsoIncludeThis"]
}
var viewModel = ko.mapping.fromJS(data, mapping);
```

あなたがマッピングオプションで指定した `include` 配列は、最初は `_destroy` のみが含まれる、デフォルトの `include` 配列に結合されます。あなたは以下のように、このデフォルトの配列を操作することができます：

```javascript
var oldOptions = ko.mapping.defaultOptions().include;
ko.mapping.defaultOptions().include = ["alwaysIncludeThis"];
```

#### "copy" を使用して特定のプロパティをコピーする{#copying-certain-properties-using-copy}

あなたのビューモデルを元の JS オブジェクトに変換する場合、デフォルトでは、mapping プラグインは[上記](#how-things-are-mapped)で説明したルールに基づいて observable を作成します。もし、あなたがプロパティに対してobservable にするのではなく単にコピーするよう mapping プラグインに強制したい場合、"copy" 配列にそのプロパティ名を追加します:

```javascript
var mapping = {
    'copy': ["propertyToCopy"]
}
var viewModel = ko.mapping.fromJS(data, mapping);
```

あなたがマッピングオプション内で指定した `copy` 配列は、最初は空である、デフォルトの `copy` 配列に結合されます。あなたは以下のように、デフォルトの配列を操作することができます：

```javascript
var oldOptions = ko.mapping.defaultOptions().copy;
ko.mapping.defaultOptions().copy = ["alwaysCopyThis"];
```

#### “observe” を使用して特定のプロパティのみを観察する{#observing-only-certain-properties-using-observe}

あなたが mapping プラグインに対して、 JS オブジェクトのいくつかのプロパティの observable のみを作成して残りをコピーさせたい場合、observable にするプロパティ名の配列を指定することができます。

```javascript
var mapping = {
    'observe': ["propertyToObserve"]
}
var viewModel = ko.mapping.fromJS(data, mapping);
```

あなたがマッピングオプションで指定した `observe` 配列は、最初は空であるデフォルトの `observe` 配列と結合されます。あなたは以下のように、このデフォルトの配列を操作することができます：

```javascript
var oldOptions = ko.mapping.defaultOptions().observe;
ko.mapping.defaultOptions().observe = ["onlyObserveThis"];
```

`ignore` と `include` 配列は通常通りに動作します。`copy` 配列は、子要素に含まれる配列やオブジェクトのプロパティをコピーするために、効果的に使用することができます。配列やオブジェクトのプロパティが `copy` または `observable` として指定されていない場合、それは再帰的にマッピングされます。

```javascript
var data = {
    a: "a",
    b: [{ b1: "v1" }, { b2: "v2" }]
};

var result = ko.mapping.fromJS(data, { observe: "a" });
var result2 = ko.mapping.fromJS(data, { observe: "a", copy: "b" }); //will be faster to map.
```

`result` と `result2` はそれぞれ次のようになります。

```javascript
{
    a: observable("a"),
    b: [{ b1: "v1" }, { b2: "v2" }]
}
```

配列/オブジェクトの子孫要素へのマッピングは動作しますが、copyやobserve は競合する可能性があります:

```javascript
var data = {
    a: "a",
    b: [{ b1: "v1" }, { b2: "v2" }]
};
var result = ko.mapping.fromJS(data, { observe: "b[0].b1"});
var result2 = ko.mapping.fromJS(data, { observe: "b[0].b1", copy: "b" });
```

`result` は次のようになります。

```javascript
{
    a: "a",
    b: [{ b1: observable("v1") }, { b2: "v2" }]
}
```

そして、`result2` は次のようになります。

```javascript
{
    a: "a",
    b: [{ b1: "v1" }, { b2: "v2" }]
}
```

#### 更新対象の指定{#specifying-the-update-target}

もし上記の例のように、あなたがクラスの内部でマッピングを実行する場合、あなたは `this` を、あなたのマッピング操作の対象にしたいかもしれません。 `ko.mapping.fromJS` の三番目のパラメータは対象を示しています。例えば、

```javascript
ko.mapping.fromJS(data, {}, someObject); // someObject のプロパティを上書きします
```

ですので、もしあなたが JavaScript オブジェクトを `this` にマッピングしたいのであれば、 `this` を三番目の引数として渡すことができます。

```javascript
ko.mapping.fromJS(data, {}, this);
```

### 複数のソースからのマッピング{#mapping-from-multiple-sources}

あなたは複数の `ko.mapping.fromJS` 呼び出しを適用することで、一つのビューモデルに対して複数の JS オブジェクトを組み合わせることができます。例えば:

```javascript
var viewModel = ko.mapping.fromJS(alice, aliceMappingOptions);
ko.mapping.fromJS(bob, bobMappingOptions, viewModel);
```

あなたがそれぞれの呼び出しで指定したマッピングオプションは、マージされます。

### マッピングされた observable array{#mapped-observable-array}

mapping　プラグインによって生成された observable array は、`key` マッピングを使用可能な、いくつかの関数で拡張されています。

* mappedRemove
* mappedRemoveAll
* mappedDestroy
* mappedDestroyAll
* mappedIndexOf

これらは、通常の `ko.observableArray` の関数と機能的には同等ですが、オブジェクトのキーに基づいて実行することができます。例えば、以下のように動作します：

```javascript
var obj = [
    { id : 1 },
    { id : 2 }
]

var result = ko.mapping.fromJS(obj, {
    key: function(item) {
        return ko.utils.unwrapObservable(item.id);
    }
});

result.mappedRemove({ id : 2 });
```

また、マッピングされた observable array は `mappedCreate` 関数を公開します。

```javascript
var newItem = result.mappedCreate({ id : 3 });
```

この関数は最初にキーをチェックして、キーが既に存在する場合は例外を発生させます。次に、新しいオブジェクトを作成するために、もし存在する場合は create および update コールバックを実行します。最後に、このオブジェクトを配列に追加し、それを返します。

### ダウンロード{#download-2}

[Version 2.0](https://github.com/SteveSanderson/knockout.mapping/tree/master/build/output) (8.6kb minified)
