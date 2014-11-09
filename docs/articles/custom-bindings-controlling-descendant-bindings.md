# 配下のバインディングを制御する

注: これは高度な技術であり、通常では再利用可能なバインディングのライブラリを作成する場合にのみ使用されます。普通、Knockoutでアプリケーションを構築する際には必要になりません。

デフォルトでは、バインディングはそれが適用されたDOM要素のみに影響します。しかし、もし全ての配下の要素にも影響を与えたい場合、どうしたらよいでしょうか？これは可能です。あなたのバインディングは、配下には全くバインドしないようKnockoutに伝えることもできますし、別の方法によって、何であれ好きな対象にバインドできます。

これを行うには、単にあなたのバインディングの**init**関数から、**return { controlsDescendantBindings: true }** を返します。

## 例: 配下のバインディングが適用されるかどうかを制御する

非常に簡単な例として、こちらは**allowBindings**という名前のカスタムバインディングで、値が**true**である場合のみ、配下のバインディングの適用を許可します。値が**false**の場合、**allowBindings**は、Knockoutに対して配下のバインディングの責任を持ち、対象の要素が普段のようにバインディングされない事を伝えます。

```javascript
ko.bindingHandlers.allowBindings = {
    init: function(elem, valueAccessor) {
        // Let bindings proceed as normal *only if* my value is false
        var shouldAllowBindings = ko.unwrap(valueAccessor());
        return { controlsDescendantBindings: !shouldAllowBindings };
    }
};
```

これが適用されるのを確認するために、こちらがサンプルの使用方法です。

```html
<div data-bind="allowBindings: true">
    <!-- This will display Replacement, because bindings are applied -->
    <div data-bind="text: 'Replacement'">Original</div>
</div>

<div data-bind="allowBindings: false">
    <!-- This will display Original, because bindings are not applied -->
    <div data-bind="text: 'Replacement'">Original</div>
</div>
```

## 例: 配下のバインディングに対する追加の値の供給

通常、**controlsDescendantBindings**を使用するバインディングでは、なんらかの修飾された[バインディングコンテキスト](./binding-context)に対しても配下のバインディングを適用するため、**ko.applyBindingsToDescendants(someBindingContext, element)**を呼び出します。例えば、あなたはバインディングコンテキストにいくつか追加のプロパティを付与する**withProperties**という名前のバインディングを作成可能で、付与したプロパティは全ての配下のバインディングで使用できるようになります。

```javascript
ko.bindingHandlers.withProperties = {
    init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
        // Make a modified binding context, with a extra properties, and apply it to descendant elements
        var innerBindingContext = bindingContext.extend(valueAccessor);
        ko.applyBindingsToDescendants(innerBindingContext, element);

        // Also tell KO *not* to bind the descendants itself, otherwise they will be bound twice
        return { controlsDescendantBindings: true };
    }
};
```

ご覧のとおり、バインディングコンテキストは追加のプロパティと共にクローンを生成する、**extend**関数を持ちます。**extend**関数は、コピーするプロパティを持つオブジェクト、またはそのようなオブジェクトを返す関数のいずれかを受け入れます。この関数の構文としては、バインディング値の将来的な変更が常にバインディングコンテキスト内で更新されることが望ましいです。このプロセスは元のバインディングコンテキストには影響しないので、兄弟レベルの要素に影響を与える恐れがありません - それは配下にのみ影響します。

こちらが上記のカスタムバインディングの使用例です。

```html
<div data-bind="withProperties: { emotion: 'happy' }">
    Today I feel <span data-bind="text: emotion"></span>. <!-- Displays: happy -->
</div>
<div data-bind="withProperties: { emotion: 'whimsical' }">
    Today I feel <span data-bind="text: emotion"></span>. <!-- Displays: whimsical -->
</div>
```

## 例: バインディングコンテキストの階層にレベルを追加する

[with](./with-binding)や[foreach](./foreach-binding)バインディングと同様に、バインディングコンテキストの階層に追加のレベルを作成します。これはその配下の要素が**$parent**、**$parents**、**$root**、または**$parentContext**の使用によって外側のレベルにあるデータにアクセスできることを意味します。

もし、あなたがこれをカスタムバインディングで行いたい場合、**bindingContext.extend()**の代わりに、**bindingContext.createChildContext(someData)**を使用してください。この関数は、ビューモデルが**someData**であり、**$parentContext**が**bindingContext**である新しいバインディングコンテキストを返します。もし望むのであれば、その後に**ko.utils.extend**を使用して、追加のプロパティを持つ子コンテキストを拡張することもできます。例えば、

```javascript
ko.bindingHandlers.withProperties = {
    init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
        // Make a modified binding context, with a extra properties, and apply it to descendant elements
        var childBindingContext = bindingContext.createChildContext(
            bindingContext.$rawData,
            null, // Optionally, pass a string here as an alias for the data item in descendant contexts
            function(context) {
                ko.utils.extend(context, valueAccessor());
            });
        ko.applyBindingsToDescendants(childBindingContext, element);

        // Also tell KO *not* to bind the descendants itself, otherwise they will be bound twice
        return { controlsDescendantBindings: true };
    }
};
```

この更新された**withProperties**バインディングはネストされた対象に使用することができ、ネストされた各レベルは親のレベルに**$parentContext**経由でアクセスできます:

```html
<div data-bind="withProperties: { displayMode: 'twoColumn' }">
    The outer display mode is <span data-bind="text: displayMode"></span>.
    <div data-bind="withProperties: { displayMode: 'doubleWidth' }">
        The inner display mode is <span data-bind="text: displayMode"></span>, but I haven't forgotten
        that the outer display mode is <span data-bind="text: $parentContext.displayMode"></span>.
    </div>
</div>
```

バインディングコンテキストを変更し、配下のバインディングを制御することは、あなた自身のカスタムバインディングの機構を作成するための、強力かつ高度なツールとなります。
