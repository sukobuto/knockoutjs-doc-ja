# "selectedOptions" バインディング

### 用途 {#purpose}

`selectedOptions` バインディングは、複数選択リスト内でどの要素が現在選択されているかを制御します。これは `<select>` 要素および `options` バインディングと組み合わせて使用されることを意図しています。

ユーザが複数選択リストの項目を選択、または選択解除した際に、このバインディングはあなたのビューモデル上の配列に対して、対応する値を追加または削除します。同様に、これがあなたのビューモデル上の observable array であると仮定すると、あなたがこの配列に（例えば `push` または `splice` 経由で）項目を追加または削除するたびに、UI の対応する項目が選択または選択解除されます。これは2ウェイバインディングです。

注: 単一選択のドロップダウンリストにおいて、どの要素が選択されているかを制御するには、代わりに [value バインディング](value-binding)を使用できます。

### 例 {#example}

```html
<p>
    Choose some countries you'd like to visit:
    <select data-bind="options: availableCountries, selectedOptions: chosenCountries" size="5" multiple="true"></select>
</p>

<script type="text/javascript">
    var viewModel = {
        availableCountries : ko.observableArray(['France', 'Germany', 'Spain']),
        chosenCountries : ko.observableArray(['Germany']) // 初期状態では, Germany のみが選択されています。
    };

    // ... そして ...
    viewModel.chosenCountries.push('France'); // 今、 France も選択されています。
</script>
```

### パラメータ {#parameters}

* メインパラメータ
  これは配列（または observable array ）であるべきです。 KO は要素の selected オプションを、配列の内容と一致するように設定します。以前の選択状態は上書きされます。

  もし、パラメータが observable array である場合、（例えば、`push`、`pop`、または[その他の observable array メソッド](observableArrays)によって）配列が変化するたびに、バインディングは要素の選択状態を更新します。パラメータが observable でない場合は、要素の選択状態は一度だけ設定され、それは再び更新されません。

  パラメータが observable array であるか否かに関わらず、KO は、ユーザーが複数選択リスト内の項目を選択または選択解除した事を検出して、一致するようにその配列を更新します。これは、あなたがどのオプションが選択されているかを読み出すための方法です。

* 追加パラメータ

  * なし

### 注: ユーザーに任意のJavaScriptオブジェクトから選択させる {#note-letting-the-user-select-from-arbitrary-javascript-objects}

上記のコード例では、ユーザは文字列値の配列から選択を行うことができます。あなたは、文字列を提供することに限定されていません - あなたが望む場合、 `option` の配列は任意の JavaScript オブジェクトを含むことができます。任意のオブジェクトが、どのようにリストに表示されるべきかを制御する方法の詳細については、[options バインディング](options-binding)を参照してください。

このシナリオでは、あなたが `selectedOptions` を使用して読み出しと書き込みできる値はオブジェクト自体で、そのテキスト表現ではありません。これはほとんどの場合、とてもクリーンでよりエレガントなコードにつながります。あなたのビューモデルは、ユーザが任意のオブジェクトの配列から選択することを想像することができ、これらのオブジェクトが画面上の表示に対してどのようにマッピングされるかを気にしません。

### 依存 {#dependencies}

Knockout コアライブラリ以外、なし。
