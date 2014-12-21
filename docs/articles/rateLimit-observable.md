# 変更通知を遅延させる

注: この レート制限(rate-limit) APIは Knockout 3.1.0で追加されました。以前のバージョンでは、[throttle 拡張](throttle-extender) が同様の機能を提供します。

通常では、[observable](observables) が変更されるとただちに通知を行うため、その observable に依存している computed observable やバインディングは同期して更新されます。しかし、`rateLimit` 拡張は指定された期間の間、変更通知を抑制して遅らせることが可能です。 レート制限された observable は依存元に対して非同期に更新されます。

`rateLimit` 拡張は [observable array](observableArrays) と[computed observable](computedObservables) を含む、任意の種類の observable に適用できます。レート制限の主なユースケースは以下の通りです:

* 一定の遅延の後に応答して作成を行う
* 単一の更新に複数の変更を組み合わせる

### rateLimit 拡張を適用する {#applying-the-rateLimit-extender}

`rateLimit` は2つのパラメータ形式をサポートしています。

```javascript
// ショートハンド: ミリ秒単位でタイムアウトのみを指定します
someObservableOrComputed.extend({ rateLimit: 500 });

// ロングハンド: timeout と method の両方、または片方を指定します
someObservableOrComputed.extend({ rateLimit: { timeout: 500, method: "notifyWhenChangesStop" } });
```

method オプションは、いつ通知が発火するかを制御し、次の値を指定可能です。

1. `notifyAtFixedRate` - ** 他が指定されていない場合は、こちらがデフォルト値です。** 通知は observable に対する最初の変更（初期化時、またはひとつ前の通知のどちらかを基準とします）から、指定された期間の後に発生します。

2. `notifyWhenChangesStop` - 通知は observable に指定した期間の間、変更がなかった場合に発生します。observable の変更のたびにタイマーはリセットされるため、もし observable がタイムアウト期間よりも高い頻度で継続的に変更される場合は、通知が発生しません。

### 例 1: 基本 {#example-1-the-basics}

次のコードについて observable を考慮してください。

```javascript
var name = ko.observable('Bert');

var upperCaseName = ko.computed(function() {
    return name().toUpperCase();
});
```

通常、あなたが以下のように `name` を変更する場合:

```javascript
name('The New Bert');
```

...こちらでは、 `upperCaseName` はコードの次の行が実行される前に、すぐに再計算されます。しかし、あなたが次のように `rateLimit` を使用して `name` を定義した場合:

```javascript
var name = ko.observable('Bert').extend({ rateLimit: 500 });
```

...こちらでは、 `upperCaseName` は `name` が変更されてもその値がすぐに再計算されず、`name` はその新しい値を `upperCaseName` に通知して値の再計算を行う前に、500ミリ秒(0.5秒) 待機します。`name` が500ミリ秒の間に何回変更されたとしても、 `upperCaseName` は最新の値で一度だけ更新されます。

### 例 2: ユーザがタイピングを止めた時に何かを行う {#example-2-doing-something-when-the-user-stops-typing}

この実行例では、キーを押したときにすぐに反応する`instantaneousValue` observable を扱います。
次にこれを `delayedValue` computed observable の内側にラップして、`notifyWhenChangesStop` レート制限メソッドの使用により、最低でも400ミリ秒間変更が止まった後だけ通知を行うよう設定します。

こちらを試してみてください:

<div class="liveExample">

<p>スタッフ名を入力してください: <input data-bind="value: instantaneousValue,
    valueUpdate: [&quot;input&quot;, &quot;afterkeydown&quot;]"></p>
<p>現在の遅延した値: <b data-bind="text: delayedValue"></b></p>

<div data-bind="visible: loggedValues().length > 0" style="display: none;">
    <h3>Stuff you have typed:</h3>
    <ul data-bind="foreach: loggedValues"></ul>
</div>

<script type="text/javascript">

/*<![CDATA[*/
function AppViewModel() {
    this.instantaneousValue = ko.observable();
    this.delayedValue = ko.pureComputed(this.instantaneousValue)
        .extend({ rateLimit: { method: "notifyWhenChangesStop", timeout: 400 } });

    // Keep a log of the throttled values
    this.loggedValues = ko.observableArray([]);
    this.delayedValue.subscribe(function (val) {
        if (val !== '')
            this.loggedValues.push(val);
    }, this);
}

ko.applyBindings(new AppViewModel());
/*]]>*/

</script>
</div>

** ソースコード: ビュー **

```html
<p>スタッフ名を入力してください: <input data-bind='value: instantaneousValue,
    valueUpdate: ["input", "afterkeydown"]' /></p>
<p>現在の遅延した値: <b data-bind='text: delayedValue'> </b></p>

<div data-bind="visible: loggedValues().length > 0">
    <h3>あなたの入力したスタッフ名:</h3>
    <ul data-bind="foreach: loggedValues">
        <li data-bind="text: $data"></li>
    </ul>
</div>
```

** ソースコード: ビューモデル **

```javascript
function AppViewModel() {
    this.instantaneousValue = ko.observable();
    this.delayedValue = ko.pureComputed(this.instantaneousValue)
        .extend({ rateLimit: { method: "notifyWhenChangesStop", timeout: 400 } });

    // 制限された結果の値をログに保持します
    this.loggedValues = ko.observableArray([]);
    this.delayedValue.subscribe(function (val) {
        if (val !== '')
            this.loggedValues.push(val);
    }, this);
}

ko.applyBindings(new AppViewModel());
```

### 例 3: 複数の Ajax リクエストを避ける {#example-3-avoiding-multiple-ajax-requests}

以下のモデルは、ページングされたグリッドとしてレンダリング可能なデータを表します。

```javascript
function GridViewModel() {
    this.pageSize = ko.observable(20);
    this.pageIndex = ko.observable(1);
    this.currentPageData = ko.observableArray();

    // pageIndex または pageSize が更新されるたびに /Some/Json/Service を取得して、
    // その結果を currentPageData の更新に使用します
    ko.computed(function() {
        var params = { page: this.pageIndex(), size: this.pageSize() };
        $.getJSON('/Some/Json/Service', params, this.currentPageData);
    }, this);
}
```

computed observable は `pageIndex` と `pageSize` を評価するため、それら両方から独立しています。そして、このコードは `GridViewModel` が初期化された時と、 `pageIndex` または `pageSize` プロパティが変更されるたびに、jQuery の [$.getJSON 関数](http://api.jquery.com/jQuery.getJSON/)を使用して `currentPageData` を再読み込みします。

これは非常にシンプルかつエレガントです（さらに、 observable であるクエリパラメータを追加して、それらが変更されるたびに自動的に更新を行うことも簡単です）が、潜在的に効率性の問題があります。 仮に、 `GridViewModel` に `pageIndex` と `pageSize` の両方を変更する、次の関数を追加したとしましょう。

```javascript
this.setPageSize = function(newPageSize) {
    // ページサイズを変更するたび、常にページ番号を1にリセットします
    this.pageSize(newPageSize);
    this.pageIndex(1);
}
```

問題は、これが二つの Ajax リクエストを発生させるということです: 最初の一つは、あなたが `pageSize` を更新した時に開始され、二つ目はあなたが `pageIndex` を更新した直後に開始されます。これは、帯域幅とサーバリソースの無駄であり、そして予期しない競合状態の元になります。

computed observable に適用された時、`rateLimit` 拡張は computed 関数の過剰な評価を避けることもできます。短いレート制限タイムアウト（例えば、0ミリ秒）を使用すると、依存対象への同期的な変更がどのような順序であっても、あなたの computed observable が一度だけ再評価を行うよう実行されることを保証できます。例えば:

```javascript
ko.computed(function() {
    // この評価ロジックは、前のものと完全に同じです
    var params = { page: this.pageIndex(), size: this.pageSize() };
    $.getJSON('/Some/Json/Service', params, this.currentPageData);
}, this).extend({ rateLimit: 0 });
```

今、あなたは `pageIndex` と `pageSize` を好きなだけ何度でも変更可能で、そしてあなたが JavaScript ランタイムに対してスレッドを開放した後、Ajax 呼び出しは一度だけ発生します。

### computed observable のための特別な考察 {#special-consideration-for-computed-observables}

computed observable は、その値が変更された時ではなく、computed observable の依存対象の一つが変更された時にレート制限タイマーが実行されます。computed observable は、変更通知が発生すべきタイムアウト期限の後に、その値が実際に必要になるか、または computed observable の値が直接アクセスされるまでは再評価されません。もし、あなたが computed によって評価済みの最新の値にアクセスする必要がある場合は、`peek` メソッドでそれを行うことができます。

### レート制限された observable に、常に通知を行うよう強制する {#forcing-rate-limited-observables-to-always-notify-subscribers}

observable の値がプリミティブ（数値、文字列、ブール値、またはnull）である時、observable の依存性は、デフォルトではそれが実際に以前とは異なる値に設定された場合にのみ通知されます。そのため、プリミティブ値を持つレート制限された observable はタイムアウト期間の終了時に、その値が以前と異なっている場合にのみ通知を行います。言い換えると、もしプリミティブ値を持つレート制限された observable が、タイムアウト期限の前に新しい値に変更され、そして元の値に変更しなおされたとしても、通知は発生しません。もし値が同じであっても、常に更新が通知されることを保証したい場合は、`rateLimit` に加えて、 `notify` 拡張を使用してください。

```javascript
myViewModel.fullName = ko.computed(function() {
    return myViewModel.firstName() + " " + myViewModel.lastName();
}).extend({ notify: 'always', rateLimit: 500 });
```

### throttle extender との比較 {#comparison-with-the-throttle-extender}

もし、あなたが非推奨の　`throttle` 拡張を使用しているコードを移行したい場合は、`rateLimit` 拡張が、`throttle` 拡張とは異なっている次の点に注意してください。

`rateLimit` を使用する時:

1. observable への書き込みは遅延しません。observable の値はすぐに更新されます。書き込み可能な computed observable の場合、これは write 関数が常に即座に実行されることを意味しています。

2. `valueHasMutated` を手動で呼び出した時を含め、全ての `change` 通知は遅延します。これは、未変更値の通知を強制させるために、レート制限された observable に対して `valueHasMutated` の使用はできないということを意味します。

3. デフォルトのレート制限メソッドは、`throttle` のアルゴリズムとは異なっています。`throttle` の振る舞いと一致させるには、`notifyWhenChangesStop` メソッドを使用します。

4. レート制限された computed observable の評価は、レート制限されていません。もし値を読み出した場合、それは即座に再評価されます。
