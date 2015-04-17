
# ComputedObservable リファレンス

以下のドキュメントでは、ComputedObservable を生成し、使用する方法について記述します。

### ComputedObservable の生成 {#constructing-a-computed-observable}

ComputedObservable は次の形式により生成できます。

1. `ko.computed(evaluator[,targetObject,options])` — この形式はほとんどの利用方法に対応しています。

	* `evaluator` — ComputedObservable の現在の値を評価するための関数です。

	* `targetObject` — この値が与えられた場合、評価関数を実行する際の `this` の値として定義します。詳細は ['this'の管理](./computedObservables#managing-this) のセクションを読んで下さい。

	* `options` — 付加的なプロパティを含むオブジェクトです。内容は。次の完全なリストを参照して下さい。

2. `ko.computed(options)` — 次のプロパティを含むJavaScriptオブジェクトにより生成するシングルパラメータ形式です。

	* `read` — (必須) ComputedObservable の現在の値を評価するための関数です。

	* `write` — (任意)与えられた場合、ComputedObservable は書き込み可能となります。この引数は、 ComputedObservable に値を書き込む関数です。典型的には、受け取った値をもとに背後にあるいくつかの Observable に値を書き込むためのカスタムロジックとなります。

	* `owner` — (任意)与えられた場合、 `read` および `write` コールバック実行の際 `this` の値として定義します。

	* `pure` — (任意) もし、このオプションが `true` の場合、ComputedObservable は [PureComputedObservable](./computed-pure) としてセットアップされます。このオプションは、 `ko.pureComputed` コンストラクタの代替手段です。

	* `deferEvaluation` — (任意)このオプションが `true` であれば、この ComputedObservable はアクセスされるまで現在の値の評価を行わなくなります。デフォルトでは、作成された時点から即時評価されます。

	* `disposeWhen` — (任意)この ComputedObservable を破棄するかどうかを判定する評価関数です。与えられた場合、この関数は再評価の度に ComputedObservable が破棄されるべきかを判定するために実行されます。この関数が `true` またはそれに相当する値を返却した場合、 ComputedObservable は破棄されます。

	* `disposeWhenNodeIsRemoved` — (任意)DOMノードを指定します。与えられた場合、指定されたDOMノードがKnockoutにより削除された時に、この ComputedObservable は破棄されます。このオプションは、 `template` およびフロー制御バインディングによってノードが削除された時にバインディングで使用されていた ComputedObservable を破棄する目的で使います。

3. `ko.pureComputed( evaluator [, targetObject] )` — 与えられた評価関数とオプションのオブジェクトを 'this' として使用し、 [PureComputedObservable](./computed-pure) を生成します。 `ko.computed` とは異なり、このメソッドは `options` のパラメータを受け入れません。

4. `ko.pureComputed( options )` — `options` のオブジェクトを使用してPureComputedObservable を生成します。このメソッドは、上記で説明した `read` 、 `write` 、 `owner` のオプションを受け入れます。

### ComputedObservable の使用 {#using-a-computed-observable}

ComputedObservable は次の関数を提供しています。

	* `dispose()` — 手動により ComputedObservable を破棄し、依存のためのすべてのサブスクリプションをクリアします。これは ComputedObservable が更新されるのを止めたい場合や、破棄されない Observable への依存をもつ ComputedObservable に使用されているメモリを解放したい場合に便利な機能です。

	* `extend(extenders)` — ComputedObservable に、与えられた[extenders](./extenders) を適用します。

	* `getDependenciesCount()` — ComputedObservable が現在依存している対象の数を返却します。

	* `getSubscriptionsCount( [event] )` — 対象のComputedObservable について、現在設定されているサブスクリプションの数(他の ComputedObservable か、または手動サブスクリプションによる)を返します。オプションでイベント名( `"change"` のような)を渡すことにより、そのイベントに対するサブスクリプションの数のみを返します。

	* `isActive()` — ComputedObservable が更新される可能性がある場合、trueを返却します。ほかの Observable に対する依存を持たない ComputedObservable であればfalseを返却します。

	* `peek()` — 依存を生成せずに、ComputedObservable の現在の値を取得します。( [peek](./computed-dependency-tracking#controlling-dependencies-using-peek) のセクションを参照して下さい。)

	* `subscribe(callback[,callbackTarget,event])` — ComputedObservableからの変更通知を受け取るために [手動サブスクリプション](./observables#explicitly-subscribing-to-observables) を登録します。

### computedContext の使用 {#using-the-computed-context}

ComputedObservable の評価関数の実行中に、現在の ComputedObservable のプロパティに関する情報を取得するため、 `ko.computedContext` にアクセスすることができます。このメソッドは、以下の機能を提供します:

* `isInitial()` — この関数は、現在の ComputedObservable に対して初回の評価中に呼び出された場合は `true` 、それ以外は `false` を返します。 PureComputedObservable では、 `isInitial()` は常に `undefined` になります。

* `getDependenciesCount()` — 現在の評価中に検出された、ComputedObservable の依存関係の数を返します。

  * 注: `ko.computedContext.getDependenciesCount()` は、ComputedObservable 自身によって呼び出された`getDependenciesCount()` と同じです。`ko.computedContext` 上にこのメソッドが存在する理由は、ComputedObservable の生成が完了する前の、初回の評価時に依存関係をカウントする方法を提供するためです。

例:

```javascript
var myComputed = ko.computed(function() {
    // ... Omitted: read some data that might be observable ...

    // Now let's inspect ko.computedContext
    var isFirstEvaluation = ko.computedContext.isInitial(),
        dependencyCount = ko.computedContext.getDependenciesCount(),
    console.log("Evaluating " + (isFirstEvaluation ? "for the first time" : "again"));
    console.log("By now, this computed has " + dependencyCount + " dependencies");

    // ... Omitted: return the result ...
});
```

これらの機能は典型的には高度なシナリオでのみ有用であり、例えばあなたのComputedObservable の主目的がその評価の間にいくつかの他の作用をトリガすることであり、何らかのセットアップロジックを実行するのを初回の実行時のみ、
または少なくとも1つ以上の依存関係を持っている時のみ（そして、将来的に再評価される場合がある）にしたい場合等です 。ほとんどの ComputedObservable のプロパティは、それが以前に評価されているかどうか、またはそれがいくつの依存関係を持っているかを気にする必要はありません。
