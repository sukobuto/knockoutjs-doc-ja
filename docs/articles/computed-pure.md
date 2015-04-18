# PureComputedObservable

Knockout 3.2.0 で導入された PureComputedObservables は、ほとんどのアプリケーションにおいて 通常の ComputedObserfable よりも優れたパフォーマンスとメモリ上の利点を提供します。
PureComputedObservable は自身の購読者が存在しない場合、その依存関係へのサブスクリプションを維持しないためです。この機能は：

* すでにアプリケーションで参照されていないが、その依存関係がまだ存在している ComputedObservable によるメモリリークを防止します。
* その値が観測されていない場合、ComputedObservable を再計算しないことで、計算によるオーバーヘッドを削減します。

PureComputedObservable は、その`change` サブスクライバに基づいて自動的に2つの状態の間で切り替わります。

1. `change` サブスクライバを持っていないときは常に、 * sleeping * 状態です。 sleeping 状態に入る時は、その依存関係の全てのサブスクリプションは破棄されます。
この状態の間は、評価関数内でアクセスしている全てのObservable は観測されません (Observable のトラッキングは保持され続けます)。もし、 ComputedObservable の値が sleeping 状態中に読み取られた場合、 その値はその依存関係のいずれかが変更されると自動的に再評価されます。

2. 何らかの `change` サブスクライバを持っている時は、目を覚ましており * listening * 状態です。リスニング状態に入ると、即座にすべての依存関係をサブスクライブします。
この状態では、 [依存トラッキングの仕組み](./computed-dependency-tracking) で説明されているように、通常のComputedObservable のように動作します。

#### 何故 "pure" ?

私たちはこの単語を、[pure functions](http://en.wikipedia.org/wiki/Pure_function) から借りてきました。なぜなら、この機能は一般的に、 その評価関数が以下のように pure function である ComputeObservable  のみに適用できるからです。

1. ComputedObservable の評価がいかなる副作用も引き起こさない。
2. ComputedObservable の値は、 評価の数や、他の "隠された" 情報に基づいて変化すべきではない。その値は　pure function の定義において、その引数によって値が考慮されるのと同様に、アプリケーション内の他のobservable の値のみに基づく べきである。

#### 構文

PureComputedObservable を定義する標準的な方法は、 `ko.pureComputed` を使用することです:

```javascript
this.fullName = ko.pureComputed(function() {
    return this.firstName() + " " + this.lastName();
}, this);
```

他の方法としては、 `ko.computed` と共に `pure` オプションを使用することも出来ます:

```javascript
this.fullName = ko.computed(function() {
    return this.firstName() + " " + this.lastName();
}, this, { pure: true });
```

完全な文法は、[ComputedObservable リファレンス](./computed-reference) を参照してください。

### PureComputedObservable を使用すべき時 {#when-to-use-a-pure-computed-observable}

あなたは [pure function ガイドライン](./computed-pure#pure-computed-function-defined) に従って、全てのComputedObservable に対して Pure 機能を使用することができます。

一時的なビューとビューモデルによって使用され、共有される永続的なビューモデルを含むアプリケーションの設計に適用された場合、ほとんどの利益を得るでしょう。
PureComputedObservable を永続的なビューモデル内で使用することは、計算のパフォーマンス上の利点を提供します。

一時的なビューモデル内でそれらを使用することは、メモリ管理上の利点を提供します。

以下のシンプルなウィザードインターフェイスの例では、`fullName` PureComputedObservable はその最終ステップのみでビューにバインドされ、そのステップがアクティブである時のみ更新されます。

# (ここにライブビューが入ります)

#### ソースコード: ビュー

```html
<div class="log" data-bind="text: computedLog"></div>
<!--ko if: step() == 0-->
    <p>First name: <input data-bind="textInput: firstName" /></p>
<!--/ko-->
<!--ko if: step() == 1-->
    <p>Last name: <input data-bind="textInput: lastName" /></p>
<!--/ko-->
<!--ko if: step() == 2-->
    <div>Prefix: <select data-bind="value: prefix, options: ['Mr.', 'Ms.','Mrs.','Dr.']"></select></div>
    <h2>Hello, <span data-bind="text: fullName"> </span>!</h2>
<!--/ko-->
<p><button type="button" data-bind="click: next">Next</button></p>
```

#### ソースコード: ビューモデル

```javascript
function AppData() {
    this.firstName = ko.observable('John');
    this.lastName = ko.observable('Burns');
    this.prefix = ko.observable('Dr.');
    this.computedLog = ko.observable('Log: ');
    this.fullName = ko.pureComputed(function () {
        var value = this.prefix() + " " + this.firstName() + " " + this.lastName();
        // Normally, you should avoid writing to observables within a pure computed
        // observable (avoiding side effects). But this example is meant to demonstrate
        // its internal workings, and writing a log is a good way to do so.
        this.computedLog(this.computedLog.peek() + value + '; ');
        return value;
    }, this);

    this.step = ko.observable(0);
    this.next = function () {
        this.step(this.step() === 2 ? 0 : this.step()+1);
    };
};
ko.applyBindings(new AppData());
```

### PureComputedObservable を使用しないほうがよい時 {#when-not-to-use-a-pure-computed-observable}

#### 副作用

あなたは、その依存関係が変更されたときにアクションを実行することを意図する ComputedObservable では、PureComputedObservable を使用すべきではありません。例としては：

* 複数の Observable に基づいてコールバックを実行するために ComputedObservable を使用している場合。

```javascript
ko.computed(function () {
    var cleanData = ko.toJS(this);
    myDataClient.update(cleanData);
}, this);
```

* バインディングの init 関数内で、バインドされた要素を更新するために ComputedObservable を使用している場合。

```javascript
ko.computed({
    read: function () {
        element.title = ko.unwrap(valueAccessor());
    },
    disposeWhenNodeIsRemoved: element
});
```

もし、評価が重要な副作用を持っている場合、PureComputedObservable を使用すべきでない理由としては、単にPureComputedObservable がアクティブなサブスクライバを持たない (つまり sleeping 状態である) 場合、評価が実行されないからです。
依存関係の変更によって、常に評価を実行することが重要な場合は、代わりに [通常の ComputedObservable](./computedObservables) を使用します。

### 状態変化の通知 {#state-change-notifications}

PureComputedObservableは、それが `listening` 状態に入るたびに  (その現在の値を使用して) `awake` イベントを通知し、それが `sleeping` 状態に入るたびに（ `undefined`値を使用して）`asleep`イベントを通知します。
通常は、あなたは ComputedObservable の内部状態について知る必要はありません。しかし、内部状態はComputedObservable がビューにバインドされているかどうかに準ずるため、その情報を何らかのビューモデルの初期化やクリーンアップのために使用できるかもしれません。

```javascript
this.someComputedThatWillBeBound = ko.pureComputed(function () {
    ...
}, this);

this.someComputedThatWillBeBound.subscribe(function () {
    // do something when this is bound
}, this, "awake");

this.someComputedThatWillBeBound.subscribe(function () {
    // do something when this is un-bound
}, this, "asleep");
```

( `awake`イベントは` deferEvaluation`オプションによって作成された通常の ComputedObservable にも適用されます。)
