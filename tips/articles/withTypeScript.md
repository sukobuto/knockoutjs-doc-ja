# TypeScript + Knockout ES5 でさらにシンプルに

> 以前の Twitter を使った DEMO が動かなくなっていたことと、TypeScript が正式リリースされたこともあり
本記事はリニューアルいたしました。
過去のサンプルをご覧になりたい方は [こちら](http://kojs.sukobuto.com/tips/withTypeScript-old) をご参照ください。

[TypeScript](http://www.typescriptlang.org/) という言語のサンプルをご用意しました。

TypeScript は CoffeeScript などと同様に JavaScript へコンパイルするタイプの言語
(altJS と総称される) で、C# に近いオブジェクト指向表現が特徴です。
ここでは TypeScript と [Knockout ES5](knockout-es5) の力を借りて [グリッドエディタ](gridEditor) を書きなおしてみます。

TypeScript での開発の始め方については [TypeScript クイックガイド](http://phyzkit.net/typescript/#chapter2) がわかりやすいです。

#### ViewModel

```javascript
/// <reference path="../d.ts/knockout/knockout.d.ts" />
/// <reference path="../d.ts/knockout.es5/knockout.es5.d.ts" />
	
class ItemViewModel {
	constructor(public name: string, public price: number) {
		ko.track(this); // プロパティ (name, number) を監視できるようにする
	}
}

class GiftSetViewModel {
	constructor(public gifts: ItemViewModel[]) {
		ko.track(this); // プロパティ (gifts) を監視できるようにする
		
		// メンバ関数内の this を自身のインスタンスに固定する
		this.addGift = this.addGift.bind(this);
		this.removeGift = this.removeGift.bind(this);
		this.save = this.save.bind(this);
	}
	
	addGift(): void {
		this.gifts.push({
			name: "",
			price: 0
		});
	}
	
	removeGift(gift: ItemViewModel): void {
		this.gifts.remove(gift);
	}
	
	save(): void {
		alert('次のようにサーバに送信できます:' + ko.toJSON(this.gifts));
	}
}

ko.applyBindings(new GiftSetViewModel([
	new ItemViewModel('高帽子', 39.95),
	new ItemViewModel('長いクローク', 120.00)
]));
```

#### View

```html
<form data-bind="submit: save">
	<p>欲しいものリスト: <span data-bind="text: gifts.length"> </span> 点</p>
	<table data-bind="visible: gifts.length > 0">
		<thead>
		<tr>
			<th>名前</th>
			<th>価格</th>
			<th></th>
		</tr>
		</thead>
		<tbody data-bind="foreach: gifts">
		<tr>
			<td><input type="text" data-bind="value: name" /></td>
			<td><input type="text" data-bind="value: price" /></td>
			<td><a href="#" data-bind="click: $root.removeGift">削除</a></td>
		</tr>
		</tbody>
	</table>

	<button data-bind="click: addGift">追加</button>
	<button type="submit" data-bind="enable: gifts.length > 0">登録</button>
</form>
```

### TypeScript で Knockout を使う際の問題点と対処法

`GiftSetViewModel` の `constructor` で、なにやら謎の処理が行われていますね。

#### JavaScript の問題児 `this`

TypeScript という言語は、JavaScript の言語仕様を置き換えるものではなく
あくまで JavaScript のスーパーセット≒表現拡張です。
つまり JavaScript でクセモノの `this` は TypeScript でも同様にクセモノなのです。

`GiftSetViewModel` のメンバ関数である `addGift, removeGift, save` は、
次のように JavaScript にコンパイルされます。

```javascript
GiftSetViewModel.prototype.addGift = function () {
	this.gifts.push({
		name: "",
		price: 0
	});
};

GiftSetViewModel.prototype.removeGift = function (gift) {
	this.gifts.remove(gift);
};

GiftSetViewModel.prototype.save = function () {
	alert('次のようにサーバに送信できます:' + ko.toJSON(this.gifts));
};
```

それぞれの関数内で参照される `this` は、関数を次のように呼び出すことにより
簡単に置き換えることができます。

```javascript
var giftSet = new GiftSetViewModel([]);
giftSet.addGift.call(undefined); // this を undefined で置き換えて実行
// 内部で this.gifts を参照しようとしてエラーとなる
```

さらに悪いことに、`click, event` などのイベント系バインディングでは、
Knockout は必ず `this` を置き換えつつ、ハンドラ関数を呼び出します。

#### `Function.bind` による `this` の束縛

そこでこの3行によって `this` を書き換えられないように固定していたのです。

```javascript
// メンバ関数内の this を自身のインスタンスに固定する
this.addGift = this.addGift.bind(this);
this.removeGift = this.removeGift.bind(this);
this.save = this.save.bind(this);
```

`this.memberFunc = this.memberFunc.bind(this)` とすることで
`this` を束縛した新たな関数を自身のプロパティとして保持します。
インスタンスごとに関数オブジェクトが生成されてしまう、というパフォーマンス上のデメリットはありますが、
`this` が容易く書き換わる JavaScript の世界ですから諦めどころといえるでしょう。

#### まとめて束縛しよう

次のような関数を定義することで、メンバ関数の数だけ `bind` せずに一括で `this` を束縛できます。
関数名がなにやら不穏ではありますが、これでコンストラクタがすこしスッキリしますね。

```javascript
function bindMySelf(viewModel: any, functionNames: string[]): void {
	functionNames.forEach((functionName) => {
		viewModel[functionName] = viewModel[functionName].bind(viewModel);
	})
}

class GiftSetViewModel {
	constructor(public gifts: ItemViewModel[]) {
		ko.track(this);
		// メンバ関数を View 公開できるようにする
		bindMySelf(this, ['addGift', 'removeGift', 'save']);
	}
	
	addGift(): void {
		this.gifts.push({
			name: "",
			price: 0
		});
	}
	
	removeGift(gift: ItemViewModel): void {
		this.gifts.remove(gift);
	}
	
	save(): void {
		alert('次のようにサーバに送信できます:' + ko.toJSON(this.gifts));
	}
}
```