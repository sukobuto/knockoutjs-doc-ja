# JSON データの読み込みと保存

Knockout は洗練されたクライアントサイドのインタラクティビティを実装することができますが、ほとんど全ての Web アプリケーションは、サーバとデータを交換するか、または少なくともローカルストレージのためにデータをシリアライズする必要があります。データの交換や保存に最も便利な方法は [JSON 形式](http://json.org/) です - 今日では Ajax アプリケーションの大半が使用しているフォーマットです。

### データの読み込みと保存 {#loading-or-saving-data}

Knockout はデータを読み込みまたは保存するために、なんらかの特定の技術を使用することを強制していません。あなたが選択したサーバサイドの技術に適合する、どのような機構でも使用可能です。最も一般的に使用される機構は、[getJSON](http://api.jquery.com/jQuery.getJSON/)、[post](http://api.jquery.com/jQuery.post/)、[ajax](http://api.jquery.com/jQuery.ajax/) などの jQuery の Ajax ヘルパーメソッドです。あなたは、サーバからデータを取得することができます:

```javascript
$.getJSON("/some/url", function(data) {
    // data をあなたのビューモデルに使用して、
    // Knockout は UI を自動的に更新します。
})
```

...またはサーバにデータを送信することができます。

```javascript
var data = /* JSON形式のあなたのデータ - 以下を参照してください */;
$.post("/some/url", data, function(returnedData) {
    // このコールバックはpostが成功した際に実行されます
})
```

また、もしあなたが jQuery を使用したくない場合は、JSON データを読み込みまたは保存するために、他のどのような機構でも使用することができます。そのため、全ての Knockout  アプリケーションは、以下のようにあなたの支援を必要とします。

* 保存の際は、 あなたのビューモデルのデータを上記のような技術を使用することで送信可能な、シンプルな JSON フォーマットにしてください。
* 読み込みの際は、上記のような技術によって取得したデータを利用して、あなたのビューモデルを更新してください。

### ビューモデルデータのデータをプレーンな JSON に変換する {#converting-view-model-data-to-plain-json}

あなたのビューモデルは JavaScript のオブジェクトであり、ある意味、 [JSON.stringify](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify) （モダンブラウザではネイティブ関数です）または [json2.js](https://github.com/douglascrockford/JSON-js/blob/master/json2.js) ライブラリのようなどの標準の JSON シリアライザでも、それを JSON 文字列にシリアライズできます。しかしながら、あなたのビューモデルはおそらく observable、computed observable、 observable array などを含んでおり、それらは JavaScript 関数として実装されるので、あなた自身の追加的な作業なしでは常にきれいにシリアライズされるわけではありません。

observable の類を含むビューモデルデータのシリアライズを容易にするために、Knockout は二つのヘルパー関数を用意しています。

* `ko.toJS` - この関数はあなたのビューモデルのオブジェクトグラフに対して、各 observable を現在の値に置換した後で複製するため、Knockout に関連したアーティファクトが存在せず、あなたのデータのみを含んだプレーンなコピーを取得できます。
* `ko.toJSON` - この関数は、あなたのビューモデルデータを表す JSON 文字列を作成します。これは最初に、単純にあなたのビューモデルに対して `ko.toJS` を呼び出し、その結果に対してブラウザネイティブの JSON シリアライザを使用します。注: JSON シリアライザを持たない古いブラウザ(例えば、IE7 またはそれ以前)でこれを動作させるには、[json2.js](https://github.com/douglascrockford/JSON-js/blob/master/json2.js) ライブラリも参照する必要があります。
例えば、ビューモデルを以下のように定義します。

```javascript
var viewModel = {
    firstName : ko.observable("Bert"),
    lastName : ko.observable("Smith"),
    pets : ko.observableArray(["Cat", "Dog", "Fish"]),
    type : "Customer"
};
viewModel.hasALotOfPets = ko.computed(function() {
    return this.pets().length > 2
}, viewModel)
```

これは observables、 computed observables、 observable arrays、プレーンな値の組み合わせを含んでいます。あなたは以下のように、`ko.toJSON` を使用してサーバへの送信に適切な JSON 文字列に変換することができます。

```javascript
var jsonData = ko.toJSON(viewModel);

// Result: jsonData は以下の値と同等の文字列になります
// '{"firstName":"Bert","lastName":"Smith","pets":["Cat","Dog","Fish"],"type":"Customer","hasALotOfPets":true}'
```

または、もしあなたがシリアライズする前のプレーンなJavaScript オブジェクトグラフを必要とするなら、以下のように `ko.toJS` を使用します。

```javascript
var plainJs = ko.toJS(viewModel);

// Result: plainJS は observable を一切含まない、プレーンなJavaScriptオブジェクトです。これは単にデータです。
// このオブジェクトは以下と同等です:
//   {
//      firstName: "Bert",
//      lastName: "Smith",
//      pets: ["Cat","Dog","Fish"],
//      type: "Customer",
//      hasALotOfPets: true
//   }
```

`ko.toJSON` は [JSON.stringify](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify) と同じ引数を受け入れることに注意してください。例えば、Knockout アプリケーションをデバッグする時に、あなたのビューモデルデータの "live" な表現を持っていると便利です。この目的のため、きれいに整形された表示を生成するには、`ko.toJSON` に spaces 引数を渡して、以下のようにあなたのビューモデルに対してバインドできます。

```html
<pre data-bind="text: ko.toJSON($root, null, 2)"></pre>
```

### JSON を使用してビューモデルデータを更新する {#updating-view-model-data-using-json}

もし、あなたがサーバから何かのデータを読み込んで、ビューモデルの更新のためにそれを使用したいのであれあｂ、最も簡単な方法はそれを自分自身で行うことです。例えば、

```javascript
// Load and parse the JSON
var someJSON = /* Omitted: fetch it from the server however you want */;
var parsed = JSON.parse(someJSON);

// Update view model properties
viewModel.firstName(parsed.firstName);
viewModel.pets(parsed.pets);
```

多くのシナリオでは、この直接的なアプローチは最も簡単で柔軟性のある解決策です。もちろん、あなたのビューモデルのプロパティが更新されると、 Knockout はそれに対応する UI 表示の更新を行います。

しかし、多くの開発者は、すべてのプロパティを更新するために手動で一行もコードを記述することなく、入力されたデータを用いてそれらのビューモデルを更新するための、より慣例的なアプローチの使用を好みます。あなたのビューモデルが多くのプロパティ、または深くネストされたデータ構造を持っている場合、これは手動でマッピングを行うために、あなたが記述する必要のあるコードの量を大幅に減らすことができるので有益です。このテクニックの詳細については、[knockout.mapping プラグイン](plugins-mapping) を参照してください。
