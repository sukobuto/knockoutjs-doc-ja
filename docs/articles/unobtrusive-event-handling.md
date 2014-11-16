# "めだたない"イベントハンドラ

ほとんどの場合、data-bind 属性は、ビューモデルをバインドする、クリーンで簡潔な方法を提供します。しかしイベント処理では、通常では引数を渡すためのテクニックとして匿名関数が推奨されており、しばしばdata-bind属性が冗長になってしまう領域の一つです。例えば:

```html
<a href="#" data-bind="click: function() { viewModel.items.remove($data); }">
    remove
</a>
```

別の方法として、Knockout はDOM要素に関連付けられたデータを識別するための、2つのヘルパー関数を提供しています。

* `ko.dataFor(element)` - element に対してバインドされるデータを返します。
* `ko.contextFor(element)` - elementに適用される[バインディング·コンテキスト](binding-context)全体を返します。

これらのヘルパー関数は、jQueryの `bind` や `click` のように、目立たない形で適用されているイベントハンドラ内において使用することができます。上記の関数は、例えば `remove` クラスが設定されている各リンクに対して適用できます。

```javascript
$(".remove").click(function () {
    viewModel.items.remove(ko.dataFor(this));
});
```

さらにこのテクニックは、イベント委譲をサポートするために使用することができます。 jQuery の `live / delegate / on` 関数は、これを実現するための簡単な方法です：

```javascript
$(".container").on("click", ".remove", function() {
    viewModel.items.remove(ko.dataFor(this));
});
```

今、単一のイベントハンドラがより上位の階層で設定され、 `remove` クラスを持つ全てのリンクに対するクリックを処理します。この方法のメリットは、ドキュメントへ動的に追加された、追加分のリンクを自動的に処理することです（おそらく、結果の `item` は observableArray に追加されるでしょう）。

### 実行例: ネストされた子要素 {#live-example-nested-children}

この例では、複数の親子階層上にある "add" と "remove" リンクに対し、それぞれのリンクの種類ごとに単一のハンドラを目立たない形で適用しています。

<div class="liveExample">

<ul id="people" data-bind="template: { name: &quot;personTmpl&quot;, foreach: people }">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Bob</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Jan</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>

    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Don</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Ted</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>

    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Ben</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Joe</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Ali</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>

    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Ken</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>
</ul>
    </li>
</ul>
    </li>

    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Doug</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>
</ul>
    </li>
</ul>
    </li>

    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Ann</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Eve</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>

    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind="text: name">Hal</span>
        <a class="add" href="#"> add child </a>
        <ul data-bind="template: { name: &quot;personTmpl&quot;, foreach: children }"></ul>
    </li>
</ul>
    </li>
</ul>

<script id="personTmpl" type="text/html">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind='text: name'></span>
        <a class="add" href="#"> add child </a>
        <ul data-bind='template: { name: "personTmpl", foreach: children }'></ul>
    </li>
</script>

<script type="text/javascript">

/*<![CDATA[*/
var Person = function(name, children) {
    this.name = ko.observable(name);
    this.children = ko.observableArray(children || []);
};

var PeopleModel = function() {
    this.people = ko.observableArray([
        new Person("Bob", [
            new Person("Jan"),
            new Person("Don", [
                new Person("Ted"),
                new Person("Ben", [
                    new Person("Joe", [
                        new Person("Ali"),
                        new Person("Ken")
                    ])
                ]),
                new Person("Doug")
            ])
        ]),
        new Person("Ann", [
            new Person("Eve"),
            new Person("Hal")
        ])
    ]);

    this.addChild = function(name, parentArray) {
        parentArray.push(new Person(name));
    };
};

ko.applyBindings(new PeopleModel());

//attach event handlers
$("#people").on("click", ".remove", function() {
    //retrieve the context
    var context = ko.contextFor(this),
        parentArray = context.$parent.people || context.$parent.children;

    //remove the data (context.$data) from the appropriate array on its parent (context.$parent)
    parentArray.remove(context.$data);

    return false;
});

$("#people").on("click", ".add", function() {
    //retrieve the context
    var context = ko.contextFor(this),
        childName = context.$data.name() + " child",
        parentArray = context.$data.people || context.$data.children;

    //add a child to the appropriate parent, calling a method off of the main view model (context.$root)
    context.$root.addChild(childName, parentArray);

    return false;
});

/*]]>*/

</script>
</div>

** ソースコード: ビュー **
```html
<ul id="people" data-bind='template: { name: "personTmpl", foreach: people }'>
</ul>

<script id="personTmpl" type="text/html">
    <li>
        <a class="remove" href="#"> x </a>
        <span data-bind='text: name'></span>
        <a class="add" href="#"> add child </a>
        <ul data-bind='template: { name: "personTmpl", foreach: children }'></ul>
    </li>
</script>
```

** ソースコード: ビューモデル **
```javascript
var Person = function(name, children) {
    this.name = ko.observable(name);
    this.children = ko.observableArray(children || []);
};

var PeopleModel = function() {
    this.people = ko.observableArray([
        new Person("Bob", [
            new Person("Jan"),
            new Person("Don", [
                new Person("Ted"),
                new Person("Ben", [
                    new Person("Joe", [
                        new Person("Ali"),
                        new Person("Ken")
                    ])
                ]),
                new Person("Doug")
            ])
        ]),
        new Person("Ann", [
            new Person("Eve"),
            new Person("Hal")
        ])
    ]);

    this.addChild = function(name, parentArray) {
        parentArray.push(new Person(name));
    };
};

ko.applyBindings(new PeopleModel());

//attach event handlers
$("#people").on("click", ".remove", function() {
    //retrieve the context
    var context = ko.contextFor(this),
        parentArray = context.$parent.people || context.$parent.children;

    //remove the data (context.$data) from the appropriate array on its parent (context.$parent)
    parentArray.remove(context.$data);

    return false;
});

$("#people").on("click", ".add", function() {
    //retrieve the context
    var context = ko.contextFor(this),
        childName = context.$data.name() + " child",
        parentArray = context.$data.people || context.$data.children;

    //add a child to the appropriate parent, calling a method off of the main view model (context.$root)
    context.$root.addChild(childName, parentArray);

    return false;
});
```

どれほどリンクがネストされていても問題なく、ハンドラは常に適切なデータを識別して実行することができます。このテクニックを使用すると、私たちはそれぞれの独立したリンクにハンドラを適用するオーバーヘッドを避けることができ、マークアップをクリーンで簡潔に保つことができます。
