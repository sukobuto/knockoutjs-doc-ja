# 破棄処理をカスタマイズ

典型的なKnockoutアプリケーションでは、例えば[template](./template-binding)バインディングや制御フローバインディング([if](./if-binding), [ifnot](./ifnot-binding), [with](./with-binding), そして[foreach](./foreach-binding))の使用により、DOM要素は動的に追加および削除されます。カスタムバインディングを作成する時、しばしばそのカスタムバインディングと関連付けられたDOM要素がKnockoutによって削除される際に、クリーンアップのロジックを追加することが望ましいことがあります。


## DOM要素の破棄に対するコールバックの登録

ノードが削除された時に実行される関数を登録するため、**ko.utils.domNodeDisposal.addDisposeCallback(node, callback)**を呼び出すことができます。例として、あなたがウィジェットのインスタンス化を行うカスタムバインディングを作成するとしましょう。バインディングを持つDOM要素が取り除かれた時は、ウィジェットの**destroy**メソッドを呼び出したいとします。

```javascript
ko.bindingHandlers.myWidget = {
    init: function(element, valueAccessor) {
        var options = ko.unwrap(valueAccessor()),
            $el = $(element);

        $el.myWidget(options);

        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            // これはDOM要素がKnockoutによって削除されるか、
            // コードの他の部分でko.removeNode(element)を呼び出した際に実行されます
            $el.myWidget("destroy");
        });
    }
};
```

## 外部データのクリーンアップに対するオーバーライド

DOM要素を削除する時、Knockoutは要素に関連付けられているすべてのデータをクリーンアップするためのロジックを実行します。
このロジックでは、もしjQueryがあなたのページでロードされている場合、KnockoutはjQueryの**cleanData**メソッドを呼び出します。
高度なシナリオでは、あなたのアプリケーションからデータが削除される処理について中止したり、カスタマイズしたい事があるかもしれません。
Knockoutは**ko.utils.domNodeDisposal.cleanExternalData(node)**関数を公開しており、これはカスタムロジックに対応するために上書きできます。
例えば、**cleanData**の呼び出しを中止するには、標準の**cleanExternalData**実装を置き換えるために空の関数を使用できます。

```javascript
ko.utils.domNodeDisposal.cleanExternalData = function () {
    // 何もしません。DOM要素と関連付けられたいかなるjQueryデータも、
    // DOMから要素が削除された際にクリーンアップされません。
};
```
