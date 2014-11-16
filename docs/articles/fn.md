# "fn" で Observable に機能を追加

時折、あなたはKnockoutコアの変数型に新しい機能性を付加することで、コードをスリム化する機会を発見することがあるでしょう。あなたは以下のいずれかの種類として、カスタム関数を定義することができます:

![Knockoutクラスの継承関係](http://knockoutjs.com/documentation/images/fn/type-hierarchy.png)

継承により、あなたが `ko.subscribable` に関数を追加すると、それは他の全てにおいて使用可能になります。あなたが `ko.observable` に関数を追加した場合は、`ko.observableArray` に継承されますが、 `ko.computed` には継承されません。

カスタム関数を追加するには、それを以下の拡張ポイントの1つに設定します。

* `ko.subscribable.fn`
* `ko.observable.fn`
* `ko.observableArray.fn`
* `ko.computed.fn`

これにより、カスタム関数は、その時点以降に作成された同じ種類の全ての値で利用できるようになります。

** 注: ** 本当に広範囲のシナリオで適用可能なカスタム関数でのみ、この拡張ポイントを使用するのが最善です。もし、一度だけカスタム関数を使用をする予定の場合は、これらの名前空間にその関数を追加する必要はありません。

### 例: observable array のフィルタされたビュー {#example-a-filtered-view-of-an-observable-array}

こちらは `filterByProperty` 関数を定義して、その後に作成された全ての `ko.observableArray` インスタンスで使用できるようにする方法です。

```javascript
ko.observableArray.fn.filterByProperty = function(propName, matchValue) {
    return ko.pureComputed(function() {
        var allItems = this(), matchingItems = [];
        for (var i = 0; i < allItems.length; i++) {
            var current = allItems[i];
            if (ko.unwrap(current[propName]) === matchValue)
                matchingItems.push(current);
        }
        return matchingItems;
    }, this);
}
```

この関数は元の配列を変更せずに残したまま、配列のフィルタされたビューを提供するための、新しく計算された値を返します。フィルタされた配列は computed observable であるため、元の配列が変更されるたびに再計算されます。

以下の動作例では、あなたがこれをどのように使用できるかを示しています:

<div class="liveExample">

<h3>All tasks (<span data-bind="text: tasks().length">3</span>)</h3>
<ul data-bind="foreach: tasks">
    <li>
        <label>
            <input type="checkbox" data-bind="checked: done">
            <span data-bind="text: title">Find new desktop background</span>
        </label>
    </li>

    <li>
        <label>
            <input type="checkbox" data-bind="checked: done">
            <span data-bind="text: title">Put shiny stickers on laptop</span>
        </label>
    </li>

    <li>
        <label>
            <input type="checkbox" data-bind="checked: done">
            <span data-bind="text: title">Request more reggae music in the office</span>
        </label>
    </li>
</ul>

<h3>Done tasks (<span data-bind="text: doneTasks().length">2</span>)</h3>
<ul data-bind="foreach: doneTasks">
    <li data-bind="text: title">Find new desktop background</li>

    <li data-bind="text: title">Request more reggae music in the office</li>
</ul>

<script type="text/javascript">

/*<![CDATA[*/
function Task(title, done) {
    this.title = ko.observable(title);
    this.done = ko.observable(done);
}

function AppViewModel() {
    this.tasks = ko.observableArray([
        new Task('Find new desktop background', true),
        new Task('Put shiny stickers on laptop', false),
        new Task('Request more reggae music in the office', true)
    ]);

    // Here's where we use the custom function
    this.doneTasks = this.tasks.filterByProperty("done", true);
}

ko.applyBindings(new AppViewModel());
/*]]>*/

</script>
</div>

** ソースコード: ビュー **
```html
<h3>All tasks (<span data-bind="text: tasks().length"> </span>)</h3>
<ul data-bind="foreach: tasks">
    <li>
        <label>
            <input type="checkbox" data-bind="checked: done" />
            <span data-bind="text: title"> </span>
        </label>
    </li>
</ul>

<h3>Done tasks (<span data-bind="text: doneTasks().length"> </span>)</h3>
<ul data-bind="foreach: doneTasks">
    <li data-bind="text: title"></li>
</ul>
```

** ソースコード: ビューモデル **
```javascript
function Task(title, done) {
    this.title = ko.observable(title);
    this.done = ko.observable(done);
}

function AppViewModel() {
    this.tasks = ko.observableArray([
        new Task('Find new desktop background', true),
        new Task('Put shiny stickers on laptop', false),
        new Task('Request more reggae music in the office', true)
    ]);

    // Here's where we use the custom function
    this.doneTasks = this.tasks.filterByProperty("done", true);
}

ko.applyBindings(new AppViewModel());
```

#### これは必須ではありません {#its-not-mandatory}

もし、あなたが多くの observable array をフィルタリングする傾向がある場合、全てのobservable arrayに対してグローバルに `filterByProperty` を追加すると、コードが整理されるかもしれません。しかし、時々しかフィルタリングの必要性がない場合、 `ko.observableArray.fn` への追加を選択する代わりに、以下のように手動で `doneTask` を構築するだけで済みます。

```javascript
this.doneTasks = ko.pureComputed(function() {
    var all = this.tasks(), done = [];
    for (var i = 0; i < all.length; i++)
        if (all[i].done())
            done.push(all[i]);
    return done;
}, this);
```
