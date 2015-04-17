
# 書き込み可能なComputedObservable

入門者の方はこのセクションを読み飛ばしていただいて問題ありません。書き込み可能な ComputedObservable はかなり発展的な技法で、必要とされる場面はそう多くありません。

通常、 ComputedObservable は他の Observable の値を元に計算された値を持っており、従って読み取り専用です。意外に思われるかもしれませんが、 ComputedObservable を書き込み可能にすることができます。その方法は、書き込まれた値に応じて処理をするコールバック関数を設定するだけです。

全ての読み取りと書き込みに独自ロジックを割り込ませることによって、書き込み可能な ComputedObservable はあたかも単なる Observable のように扱うことが可能です。単なる Observable と同じように、メソッドチェーンを使用して、モデルオブジェクト上の複数のObservableプロパティや ComputedObservable のプロパティを変更することができます。例えば、

`myViewModel.fullName('Joe Smith').age(50)`

書き込み可能な ComputedObservable は、幅広い用途に使用できるパワフルな機能です。

### 例1:ユーザ入力を分解する {#example-1-decomposing-user-input}

クラシックな "ファーストネーム+ラストネーム=フルネーム" の例に立ち戻って、あなたは後ろから前の内容に戻すことができます: `fullName` を書き込み可能な ComputedObservable にすることで、ユーザは直接フルネームを編集することができ、彼らが入力した値は解析され、元の `firstName` と `lastName` の Observable に戻ってマッピングされます。この例では、 `write` コールバックは入力されたテキストを "firstName" と "lastName" の構成要素に分割して、それらの値を元の Observable に書き込みます。

# ここに実行例が入ります

#### ソースコード: ビュー

```html
<div>First name: <span data-bind="text: firstName"></span></div>
<div>Last name: <span data-bind="text: lastName"></span></div>
<div class="heading">Hello, <input data-bind="textInput: fullName"/></div>
```

#### ソースコード: ビューモデル

```
function MyViewModel() {
    this.firstName = ko.observable('Planet');
    this.lastName = ko.observable('Earth');

    this.fullName = ko.pureComputed({
        read: function () {
            return this.firstName() + " " + this.lastName();
        },
        write: function (value) {
            var lastSpacePos = value.lastIndexOf(" ");
            if (lastSpacePos > 0) { // Ignore values with no space character
                this.firstName(value.substring(0, lastSpacePos)); // Update "firstName"
                this.lastName(value.substring(lastSpacePos + 1)); // Update "lastName"
            }
        },
        owner: this
    });
}

ko.applyBindings(new MyViewModel());
```

これは [HelloWorld](../tips/helloWorld) の例の全く逆で、ファーストネームとラストネームは編集できませんが、結合されたフルネームは編集できます。

上記の ViewModel コードでは、単一の引数によって ComputedObservable を初期化する方法を紹介しました。使用可能なすべてのオプションを知りたい方は [ComputedObservableリファレンス](./computed-reference) をご覧ください。

###例2:全項目の選択/選択解除 {#example-2-selectingdeselecting-all-items}

選択可能な項目のリストをユーザに提示する際には、しばしば全項目の選択または選択解除するための方法を含んでいることが有用です。これは、全ての項目が選択されているかどうかを表すブール値によって、かなり直感的に表現することができます。 `true` に設定すると全ての項目が選択され、 `false` に設定すると全項目の選択が解除されます。

# ここに実行例が入ります

#### ソースコード: ビュー

```html
<div class="heading">
    <input type="checkbox" data-bind="checked: selectedAllProduce" title="Select all/none"/> Produce
</div>
<div data-bind="foreach: produce">
    <label>
        <input type="checkbox" data-bind="checkedValue: $data, checked: $parent.selectedProduce"/>
        <span data-bind="text: $data"></span>
    </label>
</div>
```

#### ソースコード: ビューモデル

```javascript
function MyViewModel() {
    this.produce = [ 'Apple', 'Banana', 'Celery', 'Corn', 'Orange', 'Spinach' ];
    this.selectedProduce = ko.observableArray([ 'Corn', 'Orange' ]);
    this.selectedAllProduce = ko.pureComputed({
        read: function () {
            // Comparing length is quick and is accurate if only items from the
            // main array are added to the selected array.
            return this.selectedProduce().length === this.produce.length;
        },
        write: function (value) {
            this.selectedProduce(value ? this.produce.slice(0) : []);
        },
        owner: this
    });
}
ko.applyBindings(new MyViewModel());
```

###例3:コンバーター {#example-3-a-value-converter}

ときには、保存されているものと異なる書式でデータを表示したいことがあると思います。例えば、ある価格情報が float 値で保存されているけれど、ユーザには固定少数点数で通貨記号を含めた編集を許可したいといった場合です。書き込み可能 ComputedObservable を使えば、フォーマットされた価格を表現し、背後にある float 値に対して受け付けた入力をマッピングすることができます。

# ここに実行例が入ります

#### ソースコード: ビュー

```html
<div>Enter bid price: <input data-bind="textInput: formattedPrice"/></div>
<div>(Raw value: <span data-bind="text: price"></span>)</div>
```

#### ソースコード: ビューモデル

```javascript
function MyViewModel() {
    this.price = ko.observable(25.99);

    this.formattedPrice = ko.pureComputed({
        read: function () {
            return '$' + this.price().toFixed(2);
        },
        write: function (value) {
            // Strip out unwanted characters, parse as float, then write the
            // raw data back to the underlying "price" observable
            value = parseFloat(value.replace(/[^\.\d]/g, ""));
            this.price(isNaN(value) ? 0 : value); // Write to underlying storage
        },
        owner: this
    });
}

ko.applyBindings(new MyViewModel());
```

これで、ユーザが新しい価格を入力する度に、どのような書式で入力したかに関わらず、通貨記号と小数点以下2桁の形式に更新されます。これはユーザにとって優れたエクスペリエンスを提供します。なぜなら、ユーザは、ソフトウェアがそれぞれどのような書式で入力すればよいかを知らなくても使うことができるからです。小数点以下2桁以上が入力できないことは、それを入力してみれば(自動的に削除されることから)わかります。同じく `write` コールバックにてマイナス記号その他を取り除いているため、マイナスの値が入力できないこともわかります。

###例4:フィルタリングと検証 {#example-4-filtering-and-validating-user-input}

例１で、空白を含まない値は無視しています。つまり基準に合致しない場合に、受け付けた入力を背後にある Observable に書き込まないという、いわばフィルタのようなことができます。

この手順をさらに進めてみましょう。 `isValid` フラグを設けることで、最後に入力された値が条件を満たさないときだけ、エラーメッセージを表示するようにできます。バリデーションを実現するより簡単な方法がありますが(後述)、まずは次の ViewModel でメカニズムを説明致します。

# ここに実行例が入ります

#### ソースコード: ビュー

```html
<div>Enter a numeric value: <input data-bind="textInput: attemptedValue"/></div>
<div class="error" data-bind="visible: !lastInputWasValid()">That's not a number!</div>
<div>(Accepted value: <span data-bind="text: acceptedNumericValue"></span>)</div>
```

#### ソースコード: ビューモデル

```javascript
function MyViewModel() {
    this.acceptedNumericValue = ko.observable(123);
    this.lastInputWasValid = ko.observable(true);

    this.attemptedValue = ko.pureComputed({
        read: this.acceptedNumericValue,
        write: function (value) {
            if (isNaN(value))
                this.lastInputWasValid(false);
            else {
                this.lastInputWasValid(true);
                this.acceptedNumericValue(value); // Write to underlying storage
            }
        },
        owner: this
    });
}

ko.applyBindings(new MyViewModel());
```

ここで `acceptedNumericValue` は数値のみを格納しており、数値以外の値の入力は `acceptedNumericValue` は更新せずにエラーメッセージを表示させるきっかけとなります。

注:上記の例のように、入力が数値であるかを検証するような微々たる用途では、このテクニックは大げさすぎます。 `<input>` 要素に対して jQueryValidation の `number` クラスを使うほうがはるかに単純になります。 [grideditor](../tips/gridEditor) デモで紹介しているように、 Knockout と jQueryValidation はとても相性が良いです。それは別として、前述の ViewModel の例では「どんなフィードバックを出現させるかをコントロールする独自ロジック」を使ったフィルタリングとバリデーションの汎用的なメカニズムを説明させていただきました。 jQueryValidation が解決するものよりもより複雑なシナリオに対応することができます。
