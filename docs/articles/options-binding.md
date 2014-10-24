# "option" バインディング

<!--
鈴木翻訳中
-->

### 用途 {#purpose}

`options` バインディングは `<select>` によるドロップダウンリスト、または `<select size='6'>` のような複数選択リストの選択肢を制御します。
このバインディングは `<select>` エレメントのみで使用できます。

紐付ける値は配列または ObservableArray です。`<select>` エレメントは配列の各アイテムから一つを表示します。

※ 複数選択リストの場合、選択状態をセットまたは取得するのに [`selectedOptions` バインディング](selectedOptions-binding) を使います。
択一のドロップダウンリストでは、他のフォーム部品同様に選択されたアイテムを [`value` バインディング](value-binding) で管理できます。

### 例1: ドロップダウンリスト {#example-1-drop-down-list}

```html
<p>
    行き先:
    <select data-bind="options: availableCountries"></select>
</p>
 
<script type="text/javascript">
    var viewModel = {
        // 選択肢の初期値を設定
        availableCountries: ko.observableArray(['フランス', 'ドイツ', 'スペイン'])
    };
 
    // ... その後 ...
    viewModel.availableCountries.push('中国'); // 選択肢を追加
</script>
```

### 例2: 複数選択リスト {#example-2-multi-select-list}

```html
<p>
    言ってみたい国はどこですか:
    <select data-bind="options: availableCountries" size="5" multiple="true"></select>
</p>
 
<script type="text/javascript">
    var viewModel = {
        availableCountries: ko.observableArray(['フランス', 'ドイツ', 'スペイン'])
    };
</script>
```


### 例3: 単なる文字列ではなく任意の JavaScript オブジェクトを表示 {#example-3-drop-down-list-representing-arbitrary-javascript-objects-not-just-strings}
```html
<p>
    あなたの国:
    <select data-bind="options: availableCountries,
                       optionsText: 'countryName',
                       value: selectedCountry,
                       optionsCaption: '-選択してください-'"></select>
</p>
 
<div data-bind="visible: selectedCountry"> <!-- どれかを選択したときに表示される -->
    選択した国およびその人口
    <span data-bind="text: selectedCountry() ? selectedCountry().countryPopulation : '不明'"></span>.
</div>
 
<script type="text/javascript">
    // 2つのプロパティを持ったオブジェクトのコンストラクタ
    var Country = function(name, population) {
        this.countryName = name;
        this.countryPopulation = population;
    };
 
    var viewModel = {
        availableCountries : ko.observableArray([
            new Country("イギリス", 65000000),
            new Country("アメリカ", 320000000),
            new Country("スウェーデン", 29000000)
        ]),
        selectedCountry : ko.observable() // デフォルトでは何も選択されていない状態
    };
</script>
```

### 例4: 表示される選択肢のテキストとして、任意の JavaScript オブジェクトを処理した値を用いる  {#example-4-drop-down-list-representing-arbitrary-javascript-objects-with-displayed-text-computed-as-a-function-of-the-represented-item}

```html
<!-- 下記の select 以外は例3と同じ: -->
<select data-bind="options: availableCountries,
                   optionsText: function(item) {
                       return item.countryName + ' (pop: ' + item.countryPopulation + ')'
                   },
                   value: selectedCountry,
                   optionsCaption: '-選択してください-'"></select>
```

例3と例4の違いは `optionsText` に指定した値のみです。

# Parameters {#parameters}

- Main parameter

	You should supply an array (or observable array). For each item, KO will add an <option> to the associated <select> node. Any previous options will be removed.
    
    If your parameter’s value is an array of strings, you don’t need to give any other parameters. The <select> element will display an option for each string value. However, if you want to let the user choose from an array of arbitrary JavaScript objects (not merely strings), then see the optionsText and optionsValue parameters below.
    
    If this parameter is an observable value, the binding will update the element’s available options whenever the value changes. If the parameter isn’t observable, it will only set the element’s available options once and will not update them again later.
    
- Additional parameters

	- `optionsCaption`
	
		Sometimes, you might not want to select any particular option by default. But a single-select drop-down list usually starts with some item selected, so how can you avoid preselecting something? The usual solution is to prefix the list of options with a special dummy option that just reads “Select an item” or “Please choose an option” or similar, and have that one selected by default.
        
        This easy to do: just add an additional parameter with name `optionsCaption`, with its value being a string to display. For example:
        
        `<select data-bind='options: myOptions, optionsCaption: "Select an item...", value: myChosenValue'></select>`
        
        KO will prefix the list of items with one that displays the text “Select an item…” and has the value `undefined`. So, if `myChosenValue` holds the value `undefined` (which observables do by default), then the dummy option will be selected. If the `optionsCaption` parameter is an observable, then the text of the initial item will update as the observable’s value changes.
        
	- `optionsText`
		
		Similar to `optionsText`, you can also pass an additional parameter called `optionsValue` to specify which of the objects’ properties should be used to set the `value` attribute on the `<option>` elements that KO generates. You can also specify a JavaScript function to determine this value. This function will receive the selected item as its only argument and should return a string to use for the <option> element’s value attribute.
        
        Typically you’d only want to use `optionsValue` as a way of ensuring that KO can correctly retain selection when you update the set of available options. For example, if you’re repeatedly getting a list of “car” objects via Ajax calls and want to ensure that the selected car is preserved, you might need to set `optionsValue` to `"carId"` or whatever unique identifier each “car” object has, otherwise KO won’t necessarily know which of the previous “car” objects corresponds to which of the new ones.
	
	- `optionsIncludeDestroyed`
		
		Sometimes you may want to mark an array entry as deleted, but without actually losing record of its existence. This is known as a non-destructive delete. For details of how to do this, see [the destroy function on `observableArray`](observableArrays#destroy-and-destroyall).
        
        By default, the options binding will skip over (i.e., hide) any array entries that are marked as destroyed. If you want to show destroyed entries, then specify this additional parameter like:
        
        `<select data-bind='options: myOptions, optionsIncludeDestroyed: true'></select>`
        
	- `optionsAfterRender`
		
		If you need to run some further custom logic on the generated `option` elements, you can use the `optionsAfterRender` callback. See Note 2 below.
	
	- `selectedOptions`
	
		For a multi-select list, you can read and write the selection state using `selectedOptions`. Technically this is a separate binding, so it has [its own documentation](electedOptions-binding).
		
	- `valueAllowUnset`
		
		If you want Knockout to allow your model property to take values that have no corresponding entry in your `<select>` element (and display this by making the `<select>` element blank), then see [documentation for `valueAllowUnset`](value-binding#using-valueallowunset-with-select-elements).
	
### Note 1: Selection is preserved when setting/changing options {#note-1-selection-is-preserved-when-settingchanging-options}

When the `options` binding changes the set of options in your `<select>` element, KO will leave the user’s selection unchanged where possible. So, for a single-select drop-down list, the previously selected option value will still be selected, and for a multi-select list, all the previously selected option values will still be selected (unless, of course, you’ve removed one or more of those options).

That’s because the `options` binding tries to be independent of the `value` binding (which controls selection for a single-select list) and the `selectedOptions` binding (which controls selection for a multi-select list).

### Note 2: Post-processing the generated options {#note-2-post-processing-the-generated-options}

If you need to run some further custom logic on the generated `option` elements, you can use the `optionsAfterRender` callback. The callback function is invoked each time an `option` element is inserted into the list, with the following parameters:

1. The inserted `option` element
1. The data item against which it is bound, or `undefined` for the caption element

Here’s an example that uses optionsAfterRender to add a disable binding to each option.

```html
<select size=3 data-bind="
    options: myItems,
    optionsText: 'name',
    optionsValue: 'id',
    optionsAfterRender: setOptionDisable">
</select>
 
<script type="text/javascript">
    var vm = {
        myItems: [
            { name: 'Item 1', id: 1, disable: ko.observable(false)},
            { name: 'Item 3', id: 3, disable: ko.observable(true)},
            { name: 'Item 4', id: 4, disable: ko.observable(false)}
        ],
        setOptionDisable: function(option, item) {
            ko.applyBindingsToNode(option, {disable: item.disable}, item);
        }
    };
    ko.applyBindings(vm);
</script>
```

### Dependencies {#dependencies}

None, other than the core Knockout library.