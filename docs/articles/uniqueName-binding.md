# "uniqueName" バインディング

### 用途 {#purpose}

`uniqueName` バインディングは、関連する DOM 要素が空でない `name` 属性を持つことを保証します。もし、対象の DOM 要素が `name` 属性を持っていない場合、このバインディングはそれを一つ追加して、なんらかのユニークな文字列値を設定します。

あなたがこのバインディングを使用する必要性はあまりないでしょう。これは、いくつかのレアケースでのみ有用です。例えば:

* KO を使用している際は name は無関係であっても、他の技術が、特定の要素が name を持っているという仮定に依存しているかもしれません。例えば、[jQuery Validation](http://jqueryvalidation.org/) は、今のところ name を持っている要素のみを検証します。これを Knockout UI と一緒に使用する場合、しばしば jQuery Validation の混乱を避けるために `uniqueName` バインディングの適用が必要なことがあります。[KO で jQuery Validation を使用する例](../examples/gridEditor)を参照してください。

* IE 6 では、ラジオボタンが `name` 属性を持っていない場合、チェックすることができません。通常、ラジオボタン要素はそれを相互に排他的なグループに分けるために name 属性を持っているので、ほとんどの場合これは無関係です。しかしながら、この条件に該当しており、あなたにとって不要であるため `name` 属性を追加しなかった場合は、KOは、それらをチェックできることを保証するため、内部的にこれらの要素に対して `uniqueName` を使用します。

### 例 {#example}

```html
<input data-bind="value: someModelProperty, uniqueName: true" />
```

### パラメータ {#parameters}

* メインパラメータ

  `true` (または true と評価される値) を渡すと、前述の例のように `uniqueName` バインディングが有効になります。
* 追加パラメータ

  * なし

### 依存 {#dependencies}

Knockout コアライブラリ以外、なし。
