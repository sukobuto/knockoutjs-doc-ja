# 仮想エレメントにバインドできるようにする

注: これは高度な技術であり、通常では再利用可能なバインディングのライブラリを作成する場合にのみ使用されます。普通、Knockoutでアプリケーションを構築する際には必要になりません。

Knockoutの制御フローバインディング（例えば、[if](./if-binding)と[foreach](./foreach-binding)）は通常のDOM要素だけでなく、特別なコメントベースの構文で定義された"仮想の"DOM要素にも適用できます。例えば:

```html
<ul>
    <li class="heading">My heading</li>
    <!-- ko foreach: items -->
        <li data-bind="text: $data"></li>
    <!-- /ko -->
</ul>
```

カスタム·バインディングは仮想エレメントと共に動作することもできますが、これを有効にするには**ko.virtualElements.allowedBindings** APIを使用して、明示的にKnockoutに対してバインディングが仮想エレメントを理解することを伝える必要があります。

## 例

手始めに、こちらがDOMノードの並び順をランダムに並び替えるカスタムバインディングです。

```javascript
ko.bindingHandlers.randomOrder = {
    init: function(elem, valueAccessor) {
        // Pull out each of the child elements into an array
        var childElems = [];
        while(elem.firstChild)
            childElems.push(elem.removeChild(elem.firstChild));

        // Put them back in a random order
        while(childElems.length) {
            var randomIndex = Math.floor(Math.random() * childElems.length),
                chosenChild = childElems.splice(randomIndex, 1);
            elem.appendChild(chosenChild[0]);
        }
    }
};
```

これは、通常のDOM要素に対しては問題なく動作します。以下の要素はランダムな順番にシャッフルされます。

```html
<div data-bind="randomOrder: true">
    <div>First</div>
    <div>Second</div>
    <div>Third</div>
</div>
```

しかし、これは仮想エレメントに対しては動作しません。もし、以下を試した場合:

```html
<!-- ko randomOrder: true -->
    <div>First</div>
    <div>Second</div>
    <div>Third</div>
<!-- /ko -->
```

...すると、 **The binding 'randomOrder' cannot be used with virtual elements. **というエラーになります。これを修正しましょう。仮想エレメントに対して**randomOrder**を使用可能にするには、それを許可するようKnockoutに伝えることから始めます。次の行を追加します。

```javascript
ko.virtualElements.allowedBindings.randomOrder = true;
```

今、エラーは発生しなくなりました。しかしながら、それはまだ正常に動作しません、なぜなら私たちの**randomOrder**バインディングは通常のDOM API呼び出し(**firstChild**, **appendChild**, 等)を使用してコーディングされているため、仮想エレメントを理解しないからです。これが、KOが仮想エレメントへの対応について明示的なオプトインを必要とする理由です: あなたのカスタムバインディングは仮想エレメントAPIを使用してコーディングされていない限り、正常に動作しません！

**randomOrder**のコードを更新して、今回はKOの仮想エレメントAPIを使用しましょう:

```javascript
ko.bindingHandlers.randomOrder = {
    init: function(elem, valueAccessor) {
        // Build an array of child elements
        var child = ko.virtualElements.firstChild(elem),
            childElems = [];
        while (child) {
            childElems.push(child);
            child = ko.virtualElements.nextSibling(child);
        }

        // Remove them all, then put them back in a random order
        ko.virtualElements.emptyNode(elem);
        while(childElems.length) {
            var randomIndex = Math.floor(Math.random() * childElems.length),
                chosenChild = childElems.splice(randomIndex, 1);
            ko.virtualElements.prepend(elem, chosenChild[0]);
        }
    }
};
```

注意点として、**domElement.firstChild**のようなAPIを使用する代わりに、今私たちは**ko.virtualElements.firstChild(domOrVirtualElement)**を使用しています。**randomOrder**バインディングは仮想エレメントに対して正しく動作します。例えば、**&lt;!-- ko randomOrder: true --&gt;...&lt;!-- /ko --&gt;**のように。

また、全ての**ko.virtualElements** APIは通常のDOM要素と下位互換性があるため、**randomOrder**はまだ、通常のDOM要素でも動作します。

## 仮想エレメントのAPI

Knockoutは、仮想エレメントを操作するために以下の機能を提供します。

* **ko.virtualElements.allowedBindings**

バインディングが仮想エレメントで使用できるかどうかを決定するキーを持つオブジェクトです。**ko.virtualElements.allowedBindings.mySuperBinding = true** を設定すると、**mySuperBinding** を仮想エレメントで使用することが許可されます。

* **ko.virtualElements.emptyNode(containerElem)**

現実または仮想のDOM要素である**containerElem**から全ての子ノードを削除します(メモリリークを避けるため、関連する全てのデータをクリーニングします)。

* **ko.virtualElements.firstChild(containerElem)**

現実または仮想のDOM要素である**containerElem**の最初の子要素を返すか、子要素が存在しない場合は**null**を返します。

* **ko.virtualElements.insertAfter(containerElem, nodeToInsert, insertAfter)**

現実または仮想のDOM要素である**containerElem**の子要素として**insertAfter**の直後の位置に**nodeToInsert**を挿入します(**insertAfter**は**containerElem**の子要素である必要があります)。

* **ko.virtualElements.nextSibling(node)**

現実または仮想の親DOM要素内にある**node**の次の兄弟ノードを返すか、次の兄弟ノードが存在しない場合は**null**を返します。

* **ko.virtualElements.prepend(containerElem, nodeToPrepend)**

現実または仮想のDOM要素である**containerElem**に対して、最初の子要素として**nodeToPrepend**を挿入します。

* **ko.virtualElements.setDomNodeChildren(containerElem, arrayOfNodes)**

現実または仮想のDOM要素である**containerElem**から全ての子要素を削除し(このプロセスで、メモリリークを避けるため、関連する全てのデータをクリーニングします)、新しい子要素として**arrayOfNodes**の全てのノードを挿入します。

注意点として、これは通常のDOM APIのフルセットに対する完全な代替品であることを意図していません。
Knockoutは、制御フローバインディングを実装する際に必要とされる変換の類を実行可能にするため、仮想エレメントのAPI について最小限のセットのみを提供します。
