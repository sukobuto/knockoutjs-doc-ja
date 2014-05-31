# Knockout ES5 でより自然な書き方へ

> この記事は [Knockout-ES5: a plugin to simplify your syntax / Steven Sanderson's blog](http://blog.stevensanderson.com/2013/05/20/knockout-es5-a-plugin-to-simplify-your-syntax/) の日本語訳です。

[Knockout-ES5](https://github.com/SteveSanderson/knockout-es5) は Knockout.js のプラグインで、
これを使うと ViewModel と View でのバインディングをより自然に記述できるようになります。

例えば下記のように、Knockout ではプロパティが ko.observable だったり ko.observableArray だったり、
結局のところ関数なのですが...

```javascript
// 参照するとき
var latestOrder = this.orders()[this.orders().length - 1];
// 変更するとき
latestOrder.isShipped(true);
```

Knockout-ES5 を使うことで、次のように記述できます。

```javascript
// 参照するとき
var latestOrder = this.orders[this.orders.length - 1];
// 変更するとき
latestOrder.isShipped = true;
```

これでも、自動的に UI に反映されたり依存しているプロパティが更新される、
という Knockout の基本機能は正しく動作します。

Knockout の良いところは何も変えず、 **括弧をつけることを忘れないようにすることから開放されます。**
ただし、ある程度新しいブラウザである必要があります。(後述)

### 使い方 {#getting-started}

[knockout-es5.min.js](https://raw.github.com/SteveSanderson/knockout-es5/master/dist/knockout-es5.min.js)
をダウンロードし `<script>` タグを追記します。場所は KnockoutJS 本体の読み込みよりも後です。

```html
<script src='knockout-3.0.1.js'></script>
<script src='knockout-es5.min.js'></script>
```

これで ViewModel のプロパティを `ko.observable` ではなく通常の変数として定義できます。

```javascript
function OrderLine(data) {
	this.item = data.item;
	this.price = data.price;
	this.quantity = data.quantity;
	
	this.getSubtotal = function() {
		return "$" + (this.price * this.quantity).toFixed(2);	
	}
	
	// ko.observable で宣言する代わりに、ko.track を一度だけ呼び出します。
	ko.track(this);
}
```

宣言されたプロパティは ko.observable でラップされていない、単なる（プレーンな）プロパティです。
つまり `getSubtotal` の中で計算する際、`this.quantity()` のように関数として呼び出す必要はありません。
同様に値を変更するときも、単なる変数として扱うことができます。

```javascript
someOrderLine.quantity += 1;
```

... `someOrderLine.quantity(someOrderLine.quantity() + 1); ` と書かずに済みます。

### 仕組み {#how-it-works}

とても単純です。`ko.track` は ViewModel のプロパティを列挙し、それぞれを
背後に隠蔽された observable に対する read/write アクセサとして
[ES5 getter/setter](https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Object/defineProperty)
ペアに置き換えます。(初期値も反映されます)

重要なポイントは Knockout.js コアライブラリが次の事柄に関して何も知る必要がないということです。:  
あるプロパティを参照・変更したとき、いくつかの Observable が読み書きされ、
それに従って Knockout のバインディング, computed, 依存検知機能は全て完璧に動作します。
Knockout-ES5 は Knockout 内部構造に対する修正をする必要がなく、
結果的にいかなる新たなバージョンの Knockout でも動作します。

#### どのプロパティをアップグレードするかを制御することもできます {#controlling-which-properties-are-upgraded}

もしどのプロパティを監視できるようにするかを厳密にしたい場合は、プロパティ名を配列として渡します。

```javascript
ko.track(someModelObject, ['firstName', 'lastName', 'email']);
```

意図的に `ko.track` は子オブジェクトまで再帰しないようになっています。
従って子オブジェクトに関しては、コンストラクタ内で `ko.track` を呼び出すように記述したクラスのインスタンスとして
定義することをお勧めします。これにより、どこまでが監視できるのかを制御できるようになります。

#### Observable にアクセスすることもできます {#accessing-the-observables}

プロパティの背後に隠蔽された Observable を [`subscribe`](/docs/observables#explicitly_subscribing_to_observables)
したいときなど、Observable に直接アクセスする場合は `ko.getObservable` を使います。

```javascript
ko.getObservable(someModel, 'email').subscribe(function(newValue) {
    console.log('新しいメールアドレス:' + newValue);
});
```

#### 配列について {#about-arrays}

配列のプロパティは特別です。監視できるようアップグレードする際に、
Knockout-ES5 は配列操作による変更を通知するように `push, pop, splice` などの配列操作メソッドにインターセプトします。
つまり一覧などの配列に基づいた UI は、配列プロパティへアイテムを追加・削除した際に自動的に更新されます。

Knockout-ES5 では、配列のサブプロパティに直接アクセスできます。

```javascript
var numItems = myArray.length; // myArray().length と書かなくていい
```

また Knockout-ES5 は ko.observableArray と同等の機能性を持たせるため、
以下の配列操作関数を追加します。  
[remove, removeAll, destroy, destroyAll, replace.](/docs/observableArrays#remove_and_removeall)

### Computed プロパティ

Knockout の最も重要な特徴であり、他の Model-View JavaScript ライブラリとの違いは「リアクティブ」な依存検知能力です。
これにより Computed プロパティを連鎖することができ、変更を任意のオブジェクトグラフを通して UI に伝播できるのです。
とある変更を最終的に UI に伝播するための依存関係について、開発者が明示的に定義する必要がありません。

通常、Knockout では `ko.computed` を使ってこれを実現します。Knockout-ES5 ではどうなるでしょうか？
2つの方法があります。

1. 単純な関数として ViewModel に定義する

	[使い方](#getting-started) で示した例では、`getSubtotal` は通常の関数でした。
	バインディング時にこれを呼び出すことで、Knockout は関数内で発生する依存
	(この例では `price` と `quantity`) を検知し、いずれかが変更されたタイミングで
	UI を自動的に更新します。
	
	```html
	<span data-bind="text: getSubtotal()"></span>
	```
	
2. `ko.defineProperty` を使う
	
	`ko.defineProperty` は Knockout-ES5 により提供される、
	[`Object.defineProperty`](https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Object/defineProperty)
	の Knockout 版といった機能です。
	ES5 の `get` を (場合によっては `set` も) 使って Computed なプロパティを定義できます。
	
	```javascript
	ko.defineProperty(this, 'subtotal', function() {
        return this.price * this.quantity;
    });
     
    // 第三引数を { get: function() { ... }, set: ... } のようにすることで
    // 書き込み可能にできます。
	```
	
	利点は、(A) `getSubtotal()` のように関数呼び出しをすることなく `subtotal`
	プロパティを読むことができ、(B) 値がキャッシュされ、依存対象に変更があるまでの間は
	再利用されるため、`get` が毎回呼び出され再評価されることがない、ということです。
	
	```html
	<span data-bind="text: subtotal"></span>
	```
	
	依存検知が正しく動作するようにするため、`ko.defineProperty` の呼び出しは
	`ko.track` の呼び出しよりも後に行ってください。
	どうしても `ko.defineProperty` を先に呼び出したい場合は、
	`ko.track` の実行前に定義したプロパティが評価されないようにしてください。
	
	> #### 訳者注
	`ko.track` はプロパティを監視できるように、また依存していること (=参照されたこと)
	 を Knockout が知ることができるようにする機能です。
	`ko.track` が呼び出される前に `ko.defineProperty` 定義が評価されてしまうと、
	内部でまだ依存検知ができない状態のプロパティを参照することになってしまいます。
	結果的に、参照しているプロパティに変更があっても依存がトラッキングできていないために、
	Computed なプロパティが更新されないということが起きてしまいます。
	
### ブラウザサポート {#browser-support}

Knockout-ES5 は ECMAScript 5 に対応したブラウザで機能します。
あなたのプロジェクトに適用できるか検討してみてください。

いにしえの時代、IE6 のようなブラウザは ECMAScript 3 (ES3) JavaScript 仕様をサポートしました。
仕様界では引退していて当然の古さです。それ以来、全てのモダンブラウザは ECMAScript 5 (ES5) へと移行しました。
IE9/FireFox 4/Chrome 6 がそれぞれサポートしてから、あなたはもう何年も ES5 を実行してきました。
ES5 では、新たな可能性の扉を開く[言語とランタイムプリミティブ](http://kangax.github.io/compat-table/es5/)
が追加されます。[jQuery 2.0](http://blog.jquery.com/2013/04/18/jquery-2-0-released/)
が古い IE を置き去りにし、IE9 以降をサポートすると決めたことも不思議ではありません。

もちろん、Knockout.js 自体は非常に真剣に後方互換性を保っています。
IE6 と Firefox 2 以降に対して 100% の互換性があります。
ほぼ全ての Web アプリケーションに対して、自信を持って導入していただける、ということは変えません。
ただ、もうそろそろ ES5 環境を対象としたプロジェクトも多くなってきたことも確かです。例えば:

- 大規模で、洗練されたパブリックな Web app ([私が取り組んでいる](http://weblogs.asp.net/scottgu/more-great-improvements-to-the-windows-azure-management-portal)サイトのような)
	では既に IE9 以降またはその他のモダンブラウザを対象としています。
- 健全な企業環境向けのイントラネットアプリケーション
- iOS/Android/WP8 を対象とした PhoneGap アプリ
- Node.js で実行するサーバサイド・コード

**まとめ: Knockout.js はこれからも IE6 のような ES3 ブラウザをサポートし続ける。
そして Knockout-ES5 は ES5 が確実に選択できるプロジェクトに適用できるオプションとしてのプラグインである**

### ソースコード

Knockout をもうよく知っており、Knockout-ES5 の実装・詳細に興味がある方はぜひ
[ソースを読んでください](https://github.com/SteveSanderson/knockout-es5/blob/master/src/knockout-es5.js)
このページを読むより手っ取り早いです。
