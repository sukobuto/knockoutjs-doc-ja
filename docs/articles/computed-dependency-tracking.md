
# 依存トラッキングの仕組み

入門者の方にとってこのセクションは必須ではありません。ここでは、Knockoutが依存をトラッキング(把握)する仕組みおよび、UI上の正しい部品が更新される仕組みを解説します。

これは非常にシンプルで素敵です。トラッキングアルゴリズムは以下のようになっています。

1. ComputedObservable が宣言されると、 Knockout はその初期値を取得するため、直ちに評価関数を呼び出します。

2. 評価関数を実行している間、KO はその評価関数内で参照している全ての Observable  (他の ComputedObservable を含む)に対してサブスクリプション(購読)を設定します。
このサブスクリプションのコールバックでは評価関数を再び呼び出し、ステップ１からの処理を繰り返します。(古いサブスクリプションは破棄し、適用されないようにします。)

3. Knockout は ComputedObservable の全ての購読者に対し、新しい値を通知します。

即ち、 Knockout は評価関数を最初に実行した時の依存だけを検出しているのではありません。毎回検出しているのです。これは、動的な依存を実現できるということを意味します。例えばA,B,Cの３つの Observable があり、AはBまたはCのどちらに依存するかを決定するといった仕様の ComputedObservable では、Aが変更された場合と、BもしくはCのうち現在選択されている方が変更された場合にのみ再評価されます。あなたが依存を定義する必要はありません。依存はコードの実行時に自動推論されるのです。

他の巧妙なトリックとしては、宣言型バインディングは、単純に ComputedObservable として実装されています。ですので、もしバインディングが Observable の値を読み取ると、バインディングはその Observable に依存することになり、 Observable が変更されるとバインディングが再評価されるようになります。

PureComputedObservable は、少し異なる動作をします。詳細は、[PureComputedObservable のドキュメント](./computed-pure) を参照してください。

### Peekで依存をコントロールする {#controlling-dependencies-using-peek}

Knockoutでは通常、あなたが望んでいるかどうかに関わらず、全ての依存がトラッキングされます。しかし時には、どの Observable が ComputedObservable を更新させるのかをコントロールする必要があります。とりわけ、Ajaxリクエストを発行するといったアクションを含む ComputedObservable などでは、依存対象をコントロールする必要が出てきます。 `peek` 関数は、依存を生成することなくプロパティにアクセスすることを可能にします。

次の例をもとに説明します。２つの Observable プロパティからAjaxリクエストを発行し、 `currentPageData` という Observable プロパティを更新する ComputedObservable があるとします。この ComputedObservable は `pageIndex` が変更された時に更新されますが、 `selectedItem` の変更については無視します。 `peek` を使ってアクセスしているからです。このケースでは、 `selectedItem` の現在の値はあくまでも「新しいデータセットが読み込まれたとき」にトラッキングされる値として使用しています。

```javascript
ko.computed(function() {
    var params = {
        page: this.pageIndex(),
        selected: this.selectedItem.peek()
    };
    $.getJSON('/Some/Json/Service', params, this.currentPageData);
}, this);
```

注: もし、単に ComputedObservable が高頻度で更新されるのを抑止したいのであれば、[rateLimit拡張](./rateLimit-observable) を使用してください。

### ComputedObservable の依存関係を無視する {#ignoring-dependencies-within-a-computed}

`ko.ignoreDependencies` 関数は、ComputedObservable を含むコードを実行し、そのComputeObservable の依存関係に関与したくない場合のシナリオに使用できます。この関数はまた、カスタムバインディング内でObservable にアクセスするコードを呼び出し、しかしその Observable の変更によるバインディングの再トリガを望まない場合にも有用です。

```javascript
ko.ignoreDependencies( callback, callbackTarget, callbackArgs );
```

例:

```javascript
ko.bindingHandlers.myBinding = {
    update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var options = ko.unwrap(valueAccessor());
        var value = ko.unwrap(options.value);
        var afterUpdateHandler = options.afterUpdate;

        // the developer supplied a function to call when this binding updates, but
        // we don't really want to track any dependencies that would re-trigger this binding
        if (typeof afterUpdateHandler === "function") {
            ko.ignoreDependencies(afterUpdateHandler, viewModel, [value, color]);
        }

        $(element).somePlugin("value", value);
    }
}
```

### 注: なぜ循環依存は意味を成さないのか {#note-why-circular-dependencies-arent-meaningful}

ComputedObservable はいくつかの Observable の入力を単一の Observable 出力に対応付けるための機能です。そのため、依存関係の連鎖中に循環を含むことは無意味です。循環は再帰とは異なります。エクセルの2つのセルが関数により互いに参照しあっているようなものです。無限の評価ループを引き起こしてしまいます。

Knockout は依存グラフに循環があったとき、どうするでしょうか。次のルールにより、強制的に無限ループを回避します: * Knockout は既に評価済みの ComputedObservable を再び評価しません。 * このルールにより、循環を含むコードでは思いがけない影響を受けます。これは２つのシチュエーションに該当します。1つは、2つの ComputedObservable が互いに依存している場合です(片方もしくは両方の ComputedObservable に `deferEvaluation` が使われているのであれば可能です)。2つ目は、 ある ComputedObservable が依存している別の Observable を、その ComputedObservbable がみずから変更する場合です(直接依存していようが、依存チェーンを介して依存していようが同じです)。これらのパターンのうち一つでも当てはまり、循環依存を完全に回避したいのであれば、前述の `peek` 関数を使用して下さい。
