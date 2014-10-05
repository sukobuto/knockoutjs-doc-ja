# "template" バインディング

## 目的

**template**バインディングは、関連づけられたDOM要素に対し、テンプレートの描画結果を取り込みます。テンプレートは、洗練されたUIの構造 - それは繰り返しや、ネストされているブロックかもしれません - を、あなたのビューモデルデータ上の関数として構築するためのシンプルで便利な方法です。

テンプレートを使用するには、主に2つの方法があります。

* ネイティブのテンプレート化は**foreach**, **if**, **with**,そして他のフロー制御のバインディングを支えているメカニズムです。内部的には、これらフロー制御のバインディングは、対象のDOM要素に含まれるHTMLマークアップをキャプチャし、任意のデータ項目に対して描画するためのテンプレートとして使用します。この機能はKnockoutに組み込まれており、他の外部ライブラリを必要としません。

* 文字列ベースのテンプレート化はKnockoutをサードパーティのテンプレートエンジンと接続するための方法です。Knockoutはあなたのモデルの値を外部のテンプレートエンジンに渡し、結果のマークアップされた文字列をあなたのドキュメントに注入します。jQuery.tmpとUnderscoreテンプレートエンジンで使用する例を後で解説します。


## パラメータ

* メインパラメータ

  * ショートハンドシンタックス: もし、単に文字列値を指定すると、KOは描画するテンプレートのIDとしてそれを解釈します。テンプレートに供給されるデータは、現在のモデルオブジェクトになります。

  * より多くを制御するには、以下のプロパティのいくつかを組み合わせたJavaScriptオブジェクトを渡します。

    * **name** - 描画対象のテンプレートに含まれる、要素のIDです。プログラムによって動的にこの値を変更する方法は、[Note 5](#Note5)を参照してください。

    * **data** - テンプレートを描画するためのデータを供給するオブジェクトです。このパラメータが省略された場合、KOは**foreach**パラメータを参照するか、または現在のモデルオブジェクトを使用してフォールバックします。

    * **if** - このパラメータが使用されると、指定した式がtrue（またはtrueのような値）と評価された場合のみ、テンプレートが描画されます。これは、テンプレートを設定する前にバインドされた時のnull Observableを防ぐために有用です。

    * **foreach** - KOがテンプレートを"foreach"モードで描画するよう指示します - 詳しくは[Note 2](#Note2)を参照してください。

    * **as** - foreachと組み合わせて使用された場合、描画される各アイテムに対してエイリアスを定義します - 詳しくは[Note 3](#Note3)を参照してください。

    * **afterRender**, **afterAdd**, または**beforeRemove** - 描画されたDOM要素に対して呼び出されるコールバック関数です - [Note 4](#Note4)を参照してください。


### <a name="Note1"></a> Note 1: 名前付きテンプレートの描画

通常、フロー制御のバインディング（**foreach**, **with**, **if**, 等々）を使用する際、テンプレートに名前を設定する必要はありません。それらはDOM要素内のマークアップに基づいて暗黙的、匿名的に定義されます。しかし、もし必要であれば、テンプレートを個別の要素に分割し、それらを名前によって参照することができます。

```javascript
<h2>Participants</h2>
Here are the participants:
<div data-bind="template: { name: 'person-template', data: buyer }"></div>
<div data-bind="template: { name: 'person-template', data: seller }"></div>

<script type="text/html" id="person-template">
    <h3 data-bind="text: name"></h3>
    <p>Credits: <span data-bind="text: credits"></span></p>
</script>

<script type="text/javascript">
     function MyViewModel() {
         this.buyer = { name: 'Franklin', credits: 250 };
         this.seller = { name: 'Mario', credits: 5800 };
     }
     ko.applyBindings(new MyViewModel());
</script>
```

この例では、**person-template**のマークアップは2回使用されています: 一回は**buyer**、そして二回目は**seller**に対して。注意すべき点として、テンプレートのマークアップは`<script type="text/html">`で囲まれています — ダミーの**type**属性は、マークアップがJavaScriptとして実行されないようにするため必要であり、Knockoutはそれがテンプレートとして使用されている場合を除き、そのマークアップにバインディングを適用することはありません。

名前付きテンプレートの使用が必要になることはそれほど頻繁にはありませんが、必要性が生じた場合には、マークアップの重複を最小限に抑えることができます。


### <a name="Note2"></a> Note 2: "foreach" オプションを名前付きテンプレートで使用する

もし、**foreach**バインディングと同じ事のために名前付きテンプレートを使用したい場合でも、それを自然な方法で行うことができます。

```javascript
<h2>Participants</h2>
Here are the participants:
<div data-bind="template: { name: 'person-template', foreach: people }"></div>

<script type="text/html" id="person-template">
    <h3 data-bind="text: name"></h3>
    <p>Credits: <span data-bind="text: credits"></span></p>
</script>

 function MyViewModel() {
     this.people = [
         { name: 'Franklin', credits: 250 },
         { name: 'Mario', credits: 5800 }
     ]
 }
 ko.applyBindings(new MyViewModel());
```

これは**foreach**を使用したエレメントの内部に匿名テンプレートを直接埋め込んだ場合と同等の結果をもたらします。すなわち:

```javascript
<div data-bind="foreach: people">
    <h3 data-bind="text: name"></h3>
    <p>Credits: <span data-bind="text: credits"></span></p>
</div>
```


### <a name="Note3"></a> Note 3: “foreach” のアイテムにエイリアスを与える“as” の使用

**foreach**のテンプレートをネストした場合、階層の上位レベルに存在するアイテムを参照できると何かと便利です。これを行う1つの方法は、バインディング内で**$parent**または他の[バインディングコンテキスト](./binding-context)変数を参照することです。

しかしながら、よりシンプルでエレガントな方法は、反復内の変数として名前を定義し、使用することです。例えば:

```javascript
<ul data-bind="template: { name: 'employeeTemplate',
                                  foreach: employees,
                                  as: 'employee' }"></ul>
```

文字列値の**'employee'**が**as**によって関連付けられていることに注目してください。この**foreach**ループの内部ではどこでも、描画の際に子テンプレートのバインディングから、employeeオブジェクトにアクセスするために**employee**を参照することが可能になりました。

これは主に複数階層のネストされた**foreach**ブロックを使用している場合に有用です。階層内の上位のレベルで定義されたどのような名前のアイテムでも参照を可能にする、明確な方法を提供するからです。以下は、**month**を描画する際、どのように**season**を参照できるかを示す、完全な例です。

```javascript
<ul data-bind="template: { name: 'seasonTemplate', foreach: seasons, as: 'season' }"></ul>

<script type="text/html" id="seasonTemplate">
    <li>
        <strong data-bind="text: name"></strong>
        <ul data-bind="template: { name: 'monthTemplate', foreach: months, as: 'month' }"></ul>
    </li>
</script>

<script type="text/html" id="monthTemplate">
    <li>
        <span data-bind="text: month"></span>
        is in
        <span data-bind="text: season.name"></span>
    </li>
</script>

<script>
    var viewModel = {
        seasons: ko.observableArray([
            { name: 'Spring', months: [ 'March', 'April', 'May' ] },
            { name: 'Summer', months: [ 'June', 'July', 'August' ] },
            { name: 'Autumn', months: [ 'September', 'October', 'November' ] },
            { name: 'Winter', months: [ 'December', 'January', 'February' ] }
        ])
    };
    ko.applyBindings(viewModel);
</script>
```

Tip: 文字列のリテラル値を渡すことを忘れないでください（例えば、`as: season` ではなく `as: 'season'`）。なぜなら、すでに存在する変数の値を参照していない、新たな変数に名前を与えているからです。


### <a name="Note4"></a> Note 4: “afterRender”, “afterAdd”, そして “beforeRemove” の使用

時には、テンプレートから生成されたDOM要素に対し、カスタムのポストプロセッシング処理を実行したいことがあるかもしれません。
例えばjQuery UIのようなJavaScriptウィジェットライブラリを使用している場合、テンプレートの出力を中断してjQuery UIコマンドを実行し、描画された要素の一部をDatePickerやSlider、または他の何かに変換したいことがあるでしょう。

一般的に、DOM要素にそのようなポストプロセッシングを実行するための最善の方法は[カスタムバインディング](./custom-bindings)を作成することですが、しかしあなたが本当に、テンプレートから出力された生のDOM要素に単にアクセスしたい場合、**afterRender**を使用することができます。

関数のリファレンスを渡すと(関数のリテラル、またはビューモデル上の関数名のどちらか)、Knockoutはテンプレートを描画または再描画した後、直ちにそれを呼び出します。
もし**foreach**を使用している場合、Knockoutは**afterRender**コールバックを、各アイテムがObservable配列に追加された際に呼び出します。例えば:

```javascript
<div data-bind='template: { name: "personTemplate",
                            data: myData,
                            afterRender: myPostProcessingLogic }'> </div>
```

...そして、ビューモデル（つまり、**myData**を含むオブジェクト）上に対応する関数を定義します：

```javascript
viewModel.myPostProcessingLogic = function(elements) {
    // "elements" is an array of DOM nodes just rendered by the template
    // You can add custom post-processing logic here
}
```

もし、あなたがforeachを使用していて、厳密に要素が追加された、あるいは削除された場合のみ通知が必要な場合は、代わりに**afterAdd**と**beforeRemove**を使用することができます。詳細については、[foreachバインディング](./foreach-binding)の説明を参照してください。


### <a name="Note5"></a> Note 5: 使用するテンプレートの動的な指定

複数の名前付きテンプレートが存在している場合、**name**オプションに対してObservableを渡すことができます。Observableの値が更新されると、要素の内容は適切なテンプレートを使用して再描画されます。他の方法として、使用するテンプレートを決定するためのコールバック関数を渡すこともできます。もし、**foreach**テンプレートモードを使用している場合、Knockoutはアイテムの値を1つ目の引数として渡し、配列内の各アイテムに対して関数を評価します。それ以外の場合、関数は**data**オプションの値か、または現在のモデルオブジェクト全体にフォールバックしたものを受け取ります。

例えば、

```javascript
<ul data-bind='template: { name: displayMode,
                           foreach: employees }'> </ul>

<script>
    var viewModel = {
        employees: ko.observableArray([
            { name: "Kari", active: ko.observable(true) },
            { name: "Brynn", active: ko.observable(false) },
            { name: "Nora", active: ko.observable(false) }
        ]),
        displayMode: function(employee) {
            // Initially "Kari" uses the "active" template, while the others use "inactive"
            return employee.active() ? "active" : "inactive";
        }
    };

    // ... then later ...
    viewModel.employees()[1].active(true); // Now "Brynn" is also rendered using the "active" template.
</script>
```

関数がObservableな値を参照している場合、バインディングはそれらの値が変更されたタイミングで更新されます。これにより、適切なテンプレートを使用して再描画されることになります。

また、関数に2つ目の引数が設定されている場合、それは[バインディングコンテキスト](./binding-context)全体を受け取ります。そのため、動的にテンプレートが選択された際にも、**$parent**やその他の[バインディングコンテキスト](./binding-context)値にアクセスすることが可能です。
例えば、上記のコードスニペットを次のように修正できます。

```javascript
displayMode: function(employee, bindingContext) {
    // Now return a template name string based on properties of employee or bindingContext
}
```

### <a name="Note6"></a> Note 6: jQuery.tmpl の使用と外部の文字列ベーステンプレートエンジン

ほとんどの場合、Knockoutネイティブのテンプレート化と**foreach**, **if**, **with**や他のフロー制御バインディングは、任意の洗練されたUIを構築するために必要となる全てでしょう。しかし、[Underscoreテンプレートエンジン](http://documentcloud.github.io/underscore/#template)や[jQuery.tmpl](https://github.com/BorisMoore/jquery-tmpl)のような外部のテンプレートライブラリと統合したい場合には、Knockoutはそれを行うための方法を提供しています。

デフォルトでは、Knockoutには[jQuery.tmpl](https://github.com/BorisMoore/jquery-tmpl)のサポートが付属しています。それを使用するには、以下のライブラリをこの順序のまま読み込む必要があります。

```html
<!-- First jQuery -->     <script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<!-- Then jQuery.tmpl --> <script src="jquery.tmpl.js"></script>
<!-- Then Knockout -->    <script src="knockout-x.y.z.js"></script>
```

そして、あなたのテンプレートでjQuery.tmpl構文を使用することができます。例えば、

```javascript
<h1>People</h1>
<div data-bind="template: 'peopleList'"></div>

<script type="text/html" id="peopleList">
    {{each people}}
        <p>
            <b>${name}</b> is ${age} years old
        </p>
    {{/each}}
</script>

<script type="text/javascript">
    var viewModel = {
        people: ko.observableArray([
            { name: 'Rod', age: 123 },
            { name: 'Jane', age: 125 },
        ])
    }
    ko.applyBindings(viewModel);
</script>
```

`{{each ...}}`と`${ ... }`がjQuery.tmplの構文であるため、これは動作します。しかも、テンプレートをネストするのは簡単なことです:data-bind属性をテンプレートの内部から使用することができるため、単に`data-bind="template: ..."`をテンプレート内に置くことでネストされたテンプレートを描画することが可能です。

ただし、2011年12月の時点で、jQuery.tmplがもはやアクティブに開発されていないことに注意して下さい。jQuery.tmplやその他の文字列ベースのテンプレートエンジンの替わりに、私たちは、KnockoutネイティブのDOMベーステンプレートを使用することをお勧めします（つまり、**foreach**, **if**, **with**, 等々のバインディング）。


### <a name="Note7"></a> Note 7: Underscore.js テンプレートエンジンの使用

[Underscore.jsのテンプレートエンジン](http://documentcloud.github.io/underscore/#template)は、デフォルトではERBスタイルの区切り文字を使用しています（`<%=...%>`）。以下は、前述の例にあるテンプレートが、Underscoreの場合どのように見えるかです：

```javascript
<script type="text/html" id="peopleList">
    <% _.each(people(), function(person) { %>
        <li>
            <b><%= person.name %></b> is <%= person.age %> years old
        </li>
    <% }) %>
</script>
```

[こちらはKnockoutとUnderscoreテンプレートを統合する簡単な実装です。](http://jsfiddle.net/rniemeyer/NW5Vn/)統合のためのコードは16行ほどの長さですが、Knockoutの**data-bind**属性（そしてネストされたテンプレート）とKnockoutバインディングコンテキスト変数（**$parent**, **$root**, 等々）をサポートするのに十分です。

もし、あなたが`<%= ... %>`デリミタのファンではない場合、あなたが選択した任意の区切り文字を使用するように、Underscoreテンプレートエンジンを設定することができます。


## 依存性

* **ネイティブのテンプレート化**はKnockout自信を除く、いかなるライブラリも必要としません。

* **文字列ベースのテンプレート化**は、jQuery.tmpl や Undrescore テンプレートエンジンのような、あなたが参照している適切なテンプレートエンジン一つのみに依存します。
