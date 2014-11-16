# Observable を拡張する

Knockout の observable は 値の読み込み/書き出しと、値が変更された際の通知をサポートするために必要な、基本的な機能を提供します。しかし場合によっては、あなたは observable に対して、さらに付加的な機能を追加したい場合があるでしょう。これは例えば、observable に付加的なプロパティを追加したり、observable の前に書き込み可能な computed observable を配置して、書き込みに割り込んだりする場合です。Knockout 拡張はこの種の observable の増強を簡単かつ柔軟に行うための方法を提供します。

### 拡張を作成する方法 {#how-to-create-an-extender}

拡張を作成するには `ko.extenders` オブジェクトに関数を追加します。この関数は最初の引数に対象の observable 自身を、第二引数にはその他のオプションを取ります。そして、その observable を返すか、observable を何かの用途に使用する computed observable の類を返すことができます。

以下のシンプルな `logChange` エクステンダーは、observable に対して `subscribe` を行い、設定可能なメッセージと共に、console を使用してなんらかの変更内容を出力します。

```javascript
ko.extenders.logChange = function(target, option) {
    target.subscribe(function(newValue) {
       console.log(option + ": " + newValue);
    });
    return target;
};
```

あなたは observable の `extend` 関数を呼び出し、 `logChange` プロパティを含むオブジェクトを渡すことで、この拡張を使用できます。

```javascript
this.firstName = ko.observable("Bob").extend({logChange: "first name"});
```

もし、`firstName` observable の値が `Ted` に変更された場合、 console は `first name: Ted` を表示します。

### 実行例 1: 入力を数値のみに強制する {#live-example-1-forcing-input-to-be-numeric}

この例では、observable への書き込みを強制的に数値に変換し、小数点の丸め精度の設定が可能な拡張を作成します。このケースでは、拡張は実際の observable への入力に割り込む、書き込み可能なcomputed observable を返します。

<div class="liveExample" id="numericFields">

<p><input data-bind="value: myNumberOne"> (整数に丸められます)</p>
<p><input data-bind="value: myNumberTwo"> (小数点以下2桁に丸められます)</p>

<script type="text/javascript">

// Temporarily redirect ko.applyBindings to scope it to this live example
var realKoApplyBindings = ko.applyBindings;
ko.applyBindings = function() {
	if (arguments.length === 1)
		return ko.applyBindings(arguments[0], document.getElementById('numericFields'));
	return realKoApplyBindings.apply(ko, arguments);
}

/*<![CDATA[*/
ko.extenders.numeric = function(target, precision) {
    //create a writable computed observable to intercept writes to our observable
    var result = ko.pureComputed({
        read: target,  //always return the original observables value
        write: function(newValue) {
            var current = target(),
                roundingMultiplier = Math.pow(10, precision),
                newValueAsNum = isNaN(newValue) ? 0 : parseFloat(+newValue),
                valueToWrite = Math.round(newValueAsNum * roundingMultiplier) / roundingMultiplier;

            //only write if it changed
            if (valueToWrite !== current) {
                target(valueToWrite);
            } else {
                //if the rounded value is the same, but a different value was written, force a notification for the current field
                if (newValue !== current) {
                    target.notifySubscribers(valueToWrite);
                }
            }
        }
    }).extend({ notify: 'always' });

    //initialize with current value to make sure it is rounded appropriately
    result(target());

    //return the new computed observable
    return result;
};

function AppViewModel(one, two) {
    this.myNumberOne = ko.observable(one).extend({ numeric: 0 });
    this.myNumberTwo = ko.observable(two).extend({ numeric: 2 });
}

ko.applyBindings(new AppViewModel(221.2234, 123.4525));
/*]]>*/

ko.applyBindings = realKoApplyBindings;

</script>
</div>

** ソースコード: ビュー **

```html
<p><input data-bind="value: myNumberOne" /> (整数に丸められます)</p>
<p><input data-bind="value: myNumberTwo" /> (小数点以下2桁に丸められます)</p>
```

** ソースコード: ビューモデル **

```javascript
ko.extenders.numeric = function(target, precision) {
    // observable への書込みに割り込む、書込み可能な computed observable を作成します
    var result = ko.pureComputed({
        read: target,  //常に元の observable を返します
        write: function(newValue) {
            var current = target(),
                roundingMultiplier = Math.pow(10, precision),
                newValueAsNum = isNaN(newValue) ? 0 : parseFloat(+newValue),
                valueToWrite = Math.round(newValueAsNum * roundingMultiplier) / roundingMultiplier;

            // 値が変更された場合のみ書込みを行います
            if (valueToWrite !== current) {
                target(valueToWrite);
            } else {
                // もし 丸められた値が同じであり、元の入力値自体は異なっている場合、現在の入力フィールドに対して強制的に通知します
                if (newValue !== current) {
                    target.notifySubscribers(valueToWrite);
                }
            }
        }
    }).extend({ notify: 'always' });

    // 現在の値が適切に丸められていることを確認して初期化します
    result(target());

    // 新しい computed observable を返します
    return result;
};

function AppViewModel(one, two) {
    this.myNumberOne = ko.observable(one).extend({ numeric: 0 });
    this.myNumberTwo = ko.observable(two).extend({ numeric: 2 });
}

ko.applyBindings(new AppViewModel(221.2234, 123.4525));
```

UIから拒否された値を自動的に消去するためには、computed observable に対して `.extend({notify: 'always'})` を使用する必要があることに注意してください。これを行わない場合、ユーザーが無効な `newValue` を入力して、 丸められた結果として変更されていない `valueToWrite` を受け取る可能性があります。そして、モデルの値が変更されない限り、UI のテキストボックスを更新するための通知は発生しません。`{ notify: 'always' }` を使用すると、 もし computed なプロパティの値が変更なしの値であっても、テキストボックスの更新が(拒否された値を消去して)行われます。


### 実行例 2: observable にバリデーションを追加する {#live-example-2-adding-validation-to-an-observable}

この例では、observable を必要に応じてマークできるようにする拡張を作成します。新しいオブジェクトを返す代わりに、この拡張は単に、既存の observable に対して付加的な sub-observable を追加します。
 observable は関数なので、元々それ自身のプロパティを持つことができます。しかしながら、ビューモデルがJSONに変換された場合、 sub-observable は捨てられてしまうため、私たちはシンプルに、実際の observable の値が残るようにしましょう。これは、UIのみに関連した追加機能を付加するための良い方法で、サーバに送信しなおす必要はありません。

** ソースコード: ビュー **

```html
<p data-bind="css: { error: firstName.hasError }">
    <input data-bind='value: firstName, valueUpdate: "afterkeydown"' />
    <span data-bind='visible: firstName.hasError, text: firstName.validationMessage'> </span>
</p>
<p data-bind="css: { error: lastName.hasError }">
    <input data-bind='value: lastName, valueUpdate: "afterkeydown"' />
    <span data-bind='visible: lastName.hasError, text: lastName.validationMessage'> </span>
</p>
```

** ソースコード: ビューモデル **

```javascript
ko.extenders.required = function(target, overrideMessage) {
    // observable にいくつかの sub-observable を追加します
    target.hasError = ko.observable();
    target.validationMessage = ko.observable();

    // バリデーションを行うための関数を定義します
    function validate(newValue) {
       target.hasError(newValue ? false : true);
       target.validationMessage(newValue ? "" : overrideMessage || "This field is required");
    }

    初期のバリデーション
    validate(target());

    // 値が変更されるたびにバリデーションが実行されます
    target.subscribe(validate);

    //元の observable を返します
    return target;
};

function AppViewModel(first, last) {
    this.firstName = ko.observable(first).extend({ required: "Please enter a first name" });
    this.lastName = ko.observable(last).extend({ required: "" });
}

ko.applyBindings(new AppViewModel("Bob","Smith"));
```

### 複数の拡張を適用する {#applying-multiple-extenders}

複数の拡張は、observable の `.extend` メソッドに対する一度の呼び出しによって適用することができます。

```javascript
this.firstName = ko.observable(first).extend({ required: "Please enter a first name", logChange: "first name" });
```

この場合、`require` と `logChange` 両方の拡張が、observable に対して実行されることになります。
