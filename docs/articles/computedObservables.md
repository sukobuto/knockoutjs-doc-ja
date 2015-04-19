
# ComputedObservable

`firstName` と `lastName` の [Observable](./observables) プロパティがあるとします。フルネームを画面に表示するにはどうしましょう？そこで ComputedObservable の出番です - ComputedObservable は一つ以上の Observable に依存する関数であり、依存している Observable の変更により自動的に更新されます。

例えば、次のような ViewModel クラスがあるとします。

```javascript
function AppViewModel() {
    this.firstName = ko.observable('Bob');
    this.lastName = ko.observable('Smith');
}
```

次のように、フルネームを返す ComputedObservable を追加することができます。

```javascript
function AppViewModel() {
    // ... leave firstName and lastName unchanged ...

    this.fullName = ko.computed(function() {
        return this.firstName() + " " + this.lastName();
    }, this);
}
```

そして、これをUIエレメントにバインドできます。

```html
The name is <span data-bind="text: fullName"></span>
```

これで、 `firstName` か `lastName` のいずれかに変更があれば表示が更新されます。(ko.computed に渡した評価関数は、依存しているいずれかのプロパティが変更される度に呼び出されます。評価関数の戻り値もまた、その都度UIエレメントや別の ComputedObservable など監視側に通知されます。)

### 依存チェーンは続くよ　(あなたが望む限り)　どこまでも {#dependency-chains-just-work}

次の例のように、 ComputedObservable を繋いでいくことができます。

* 項目のセットを表す配列の、 `items` という名前の * Observable *
* ユーザによって選択されている項目のインデックスを配列で保持する、`selectedIndexes` という名前の * Observable *
* items の中から selectedIndexes に一致するアイテムを全て抜き出して配列として返す、`selectedItems` という名前の * ComputedObservable *
* selectedItems のいずれかの項目が何かのプロパティを持っているかどうかによって、 `true` または `false` を返す * ComputedObservable * (新規追加や未保存など)。例えばボタンのような、ある種のUI要素は、この値に基づいて 有効・無効化されるかもしれません。

`items` や `selectedIndexes` の変更は波紋のように ComputedObservable のチェーンを伝っていき、それらの変更は UI に反映されます。

### 'this'の管理 {#managing-this}

上記の例で、 `ko.computed` の２つ目の引数( `this` を渡していること)について疑問を持たれた方がいらっしゃるかと思います。これは ComputedObservable 内部で使用される `this` を指定しています。このthisを渡さない場合、 `this.firstName()` や `this.lastName()` を参照できなくなってしまいます。JavaScriptに精通している方にとっては明白ですが、そうでない方にとってはやや不可思議に見えるかもしれません。(C#やJavaではプログラマが `this` の値を設定することはありません。しかしJavaScriptでは、functionというものが本質的にはどんなオブジェクトにも属さないため、thisの値を設定できるようになっているのです。)

#### 簡略化のための常套手段 {#a-popular-convention-that-simplifies-things}

あらゆる場面で `this` に気を払うのは骨が折れます。これを簡略化するための常套手段は、ViewModelのコンストラクタで最初に `this` の参照を別の変数にコピーしておくことです。(伝統的な例: `self` )これで、ViewModel内で一貫して `self` を使うことができます。

```javascript
function AppViewModel() {
    var self = this;

    self.firstName = ko.observable('Bob');
    self.lastName = ko.observable('Smith');
    self.fullName = ko.computed(function() {
        return self.firstName() + " " + self.lastName();
    });
}
```

`self` はfunctionクロージャの内部で保持されるため、ko.computedに渡した評価関数のようなネストされたどのfunctionでも使えます。この常套手段は [LiveExamples](./tips/) で紹介されているように、イベントハンドラ等を追加した際にも非常に便利です。

### PureComputedObservables {#pure-computed-observables}

もし、あなたのComputedObservablesが、単純にいくつかのObservablesの依存関係に基づいて計算して値を返す場合、 `ko.computed` の代わりに `ko.pureComputed` としてそれを宣言する方が良いでしょう。例えば：

```javascript
this.fullName = ko.pureComputed(function() {
    return this.firstName() + " " + this.lastName();
}, this);
```

この ComputedObservable は Pure として宣言されているため (つまり、その評価によって直接他のオブジェクトや状態を変更しない)、Knockoutは、その再評価とメモリ使用の管理をより効率的に行うことができます。Knockout は対象のPureComputedObservableに他のコードがアクティブな依存関係を持っていない場合、それを自動的にサスペンドまたは解放します。

[PureComputedObservables](./computed-pure) は Knockout 3.2.0 で導入されました。より詳細な情報は、こちらを参照してください。

### ComputedObserfable が常に購読者に通知するよう強制する {#forcing-computed-observables-to-always-notify-subscribers}

ComputedObserfable がプリミティブな値を返す場合 (数値、文字列、ブール値、またはnull)、その依存対象は、値が実際に変更された場合のみ通知を受け取ります。しかし、組み込みの [`notify`拡張](./extenders) を使用することで、 ComputedObservable の購読者が、たとえ変更後の値が同じでも、更新のたびに通知を受け取るようにすることが可能です。
あなたは以下のように、この拡張を適用できます：

```javascript
myViewModel.fullName = ko.pureComputed(function() {
    return myViewModel.firstName() + " " + myViewModel.lastName();
}).extend({ notify: 'always' });
```

### 変更通知を遅延または抑制する {#delaying-andor-suppressing-change-notifications}

通常、ComputedObservable は、その依存関係が変更されると、更新と通知をその購読者に対して直ちに行います。
しかし、ComputedObservable が多くの依存関係を持っていたり、負荷の高い更新処理を伴う場合、ComputedObservable の更新と通知を制限したり遅延させることで、より良いパフォーマンスを得ることができます。
これは、以下のように [ `rateLimit` 拡張](./rateLimit-observable) を使用することで達成されます。

```javascript
// Ensure updates no more than once per 50-millisecond period
myViewModel.fullName.extend({ rateLimit: 50 });
```

### プロパティが ComputedObservable かどうかを判定する {#determining-if-a-property-is-a-computed-observable}

ComputedObservable であるかどうかをプログラム上で判定できると便利なことがあります。Knockoutは `ko.isComputed` というユーリティティ関数を提供しています。例えば ComputedObservable の値を除外してサーバに送信したい場合などでは、次のようにします。

```javascript
for (var prop in myObject) {
  if (myObject.hasOwnProperty(prop) && !ko.isComputed(myObject[prop])) {
      result[prop] = myObject[prop];
  }
}
```

ほかにも、Knockoutは Observable および ComputedObservable のために次のような関数を用意しています。

* `ko.isObservable` - Observable , ObservableArray または何らかのComputedObservableであれば true を返します。

* `ko.isWriteableObservable` - Observable、 ObservableArray、および書き込み可能な ComputedObservable であればtrueを返します (ko.isWriteableObservable のエイリアスです)。

### ComputedObservable があなたのUIのみで使用されている場合 {#when-the-computed-observable-is-only-used-in-your-ui}

もし、あなたが 合成されたフルネームをUIの中で使用したいだけの場合、以下のように宣言できます:

```javascript
function AppViewModel() {
	   // ... leave firstName and lastName unchanged ...

	   this.fullName = function() {
	       return this.firstName() + " " + this.lastName();
	   };
}
```

これで、UI要素内のあなたのバインディングは、メソッド呼び出しになります。例えば:

```html
The name is <span data-bind="text: fullName()"></span>
```

Knockout は 式が依存している Observable を検出した場合、内部的に ComputedObservable を生成し、後で関連する要素が除去されると、自動的にそれを破棄します。
