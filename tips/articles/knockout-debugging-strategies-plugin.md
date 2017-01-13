# Knockout.js のトラブルシューティング戦略

> この記事は、Ryan Niemeyer による2013/06/05のブログ記事、["Knockout.js Troubleshooting Strategies"](http://www.knockmeout.net/2013/06/knockout-debugging-strategies-plugin.html) の翻訳です。

この投稿には、私が過去数年間に渡り、 [Knockout](http://knockoutjs.com/) アプリケーションをデバッグするために使用してきた多くのティップス、トリック、および戦略が含まれています。ご自由にコメント欄であなた自身のティップスのいくつかを共有したり、あなたが議論したい他の分野を提案してください。

### バインディングの問題

Knockout において最も多くのエラーは、典型的には間違っていたり、無効なバインディングの周辺で遭遇します。ここでは、問題のコンテキストについて診断し理解するために、いくつかの異なる方法があります。

#### 古典的な "pre" タグ

主なデバッグ作業の1つは、特定の要素に対してバインドされているのが、どのようなデータかを究明することです。私が伝統的にこれを達成している汚くも手早い方法は、問題のある特定のデータコンテキストを出力する "pre" タグを作成することです。

Knockout には `ko.toJSON` という名前のヘルパー関数が含まれており、最初に全ての observable / computed をプレーンな値に変換した、クリーンな JavaScript オブジェクトを作成した後、[JSON.stringify](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify) を使用して JSON 文字列に変換します。あなたは以下のように、 "pre" タグにこれを適用できます。

```html
<pre data-bind="text: ko.toJSON($data, null, 2)"></pre>
```

KO 2.1 では、第二と第三引数はそのまま `JSON.stringify` に渡され、第三引数は整形された出力を生成するためのインデントを制御します。 KO2.1 未満のバージョンでも、 `JSON.stringify(ko.toJS($data), null, 2)` を使用して同様の事が可能です。あなたはこのテクニックを、特定のプロパティを見たり、`$data`、`$parent`、または `$root` のような特殊な[コンテキスト変数](http://knockoutjs.com/documentation/binding-context.html)に対して使用できます。これによって、以下のような出力が得られます。

```javascript
{
  "title": "Some items",
  "items": [
    {
      "description": "one",
      "price": 0
    },
    {
      "description": "two",
      "price": 0
    },
    {
      "description": "three",
      "price": 0
    }
  ],
  "titleUpper": "SOME ITEMS"
}
```

#### 面倒？それならば単に console.log しましょう

"pre" タグは少し大変に見えますか？簡単でより直接的な解決策は、あなたのバインディング文字列で、単に `console.log` 出力を行うことです。バインディング文字列は JavaScript オブジェクトに渡され、その中に記述されているコードは実行されます。加えて、あなたのバインディング文字列には、存在しないバインディングを記述することもできます。これは、あるバインディングは他のバインディングのオプションとして使用することができるため(`optionsText` や `optionsValue` について考えてみてください)、 KO は未定義のバインディングハンドラを発見しても例外をスローしないからです。これは、あなたが単に以下のようにできることを意味します:

```html
<input data-bind="blah: console.log($data), value: description" />
```

上記のブロックは `description` プロパティの記述ミスに遭遇する前に、あなたのデータをコンソールに出力します。また、あなたがコンソールに表示したい出力方法に合わせて、 `ko.toJS` または `ko.toJSON` 関数にあなたのデータをラップすることもできます。

アップデート: KO 3.0 では、実際のハンドラが存在しないバインディングは、もはや実行されません。あなたは以下で説明するカスタムバインディングハンドラを使用するか、または組み込みの `uniqueName` バインディングのような、何か無害なものを使用する必要があります。

#### 拡張機能/ブックマークレット

同様に、この種の調査を行うため、コミュニティによって作成されたいくつかの偉大なソリューションがあります:

* [Knockout Context Debugger Chrome extension](https://chrome.google.com/webstore/detail/knockoutjs-context-debugg/oddcpmchholgcjgjdnfjmildmlielhof?hl=en) - Chrome の開発ツールにペインが追加され、特定の要素に関連付けられたコンテキストを表示することができます。更新: 最新バージョンでは、同じくいくつかの素晴らしいトレース機能が追加されます。[Tim Stuyckens](https://twitter.com/timstuyckens) によって作成されました。

* [Knockout Glimpse plugin](https://github.com/aaronpowell/glimpse-knockout) - [Glimpse](http://getglimpse.com/) は、ASP.NET のための強力なデバッグツールです。[Aaron Powell](http://www.aaron-powell.com/) このツールのために、素晴らしい Knockout プラグインを作成しました。

* [Bowtie](http://bowtie-ko.com) - Max Pollack によるブックマークレットで、バインディングに関連付けられたコンテクストを検査し、ウォッチする値を追加できます。注意 - これは対象のページに jQuery が含まれていることを前提とします。

* [Knockout-debug](https://github.com/jmeas/knockout-view) - James Smith によるブックマークレットで、あなたのビューモデルを素敵に表示します。

#### パフォーマンステスト - どのくらいの頻度でバインディングが発火するのか？

探索 / 調査においてしばしば興味深いのは、何の値に対して、どれくらいの頻度でバインディングが実行されているかという事です。私は、この目的のためにカスタムバインディングを使用する事を好みます。

```javascript
 ko.bindingHandlers.logger = {
        update: function(element, valueAccessor, allBindings) {
            //store a counter with this element
            var count = ko.utils.domData.get(element, "_ko_logger") || 0,
                data = ko.toJS(valueAccessor() || allBindings());

            ko.utils.domData.set(element, "_ko_logger", ++count);

            if (window.console && console.log) {
                console.log(count, element, data);
            }
        }
    };
```

これは対象となる要素のバインディングが何回実行されたかについて、回数を表示します。あなたは記録したい値を渡すか、または要素上の全てのバインディングに提供された値が記録されます。あなたの必要に応じて、同様にタイムスタンプを含めることができます。あなたのUIで不必要な作業がどれほど多く発生しているかを発見して、あなたは目を丸くすることが何度もあるでしょう。それは observableArray に[何度も](http://www.knockmeout.net/2012/04/knockoutjs-performance-gotcha.html)値がプッシュされているのかもしれませんし、または [スロットリング](http://knockoutjs.com/documentation/throttle-extender.html) (訳注: KO 3.0 以降はレート制限API)が適切であるようなケースかもしれません。例えば、あなたは以下のように、これを要素上に配置することができます。

```html
 <input data-bind="logger: description, value: description, enable: isEditable" />
```

そして、以下のような出力が行われるでしょう。

```
 1 <input.../> "Robert"
 2 <input.../> "Bob"
 3 <input.../> "Bobby"
 4 <input.../> "Rob"
```

注意点として、KO 3.0 (ほぼベータ版) において、要素上の各バインディングは独立して発火するので、現在[全てのバインディングが一斉に発火する](http://www.knockmeout.net/2012/06/knockoutjs-performance-gotcha-3-all-bindings.html)のとは逆であることを述べておきます。 3.0 がリリースされた時は、この事を念頭に置いた上で上記のようなバインディングを使用する必要があり、おそらくあなたは記録したい特定の依存性のみを渡すことになるでしょう。

#### 未定義のプロパティ

バインディングの問題について、もう一つの一般的な原因は未定義のプロパティです。以下はプロパティが欠落しており、それが後になっても追加されない場合のシナリオを処理するための簡単なティップです。これは明確なデバッグ·ティップではありませんが、あなたがあまり大騒ぎせず、潜在的なバインディングの問題を回避するのに役立ちます。

例えば、これはエラーの原因になります（`myMissingProperty` が欠落している場合）。

```html
 <span data-bind="text: myMissingProperty"></span>
```

しかし、これは `undefined` でも適切にバインドされます:

```html
 <span data-bind="text: $data.myMissingProperty"></span>
```

JavaScriptにおいて、未定義の変数の値を取得しようとするとエラーになりますが、実在する別のオブジェクトの、未定義のプロパティにアクセスすることは問題ありません。そのため、 `myMissingProperty` と `$data.myMissingProperty` は等価ですが、親のオブジェクト(`$data`) に対し未定義のプロパティを参照することで、"variable is not defined" エラーを避けることができます。

#### カスタムバインディングプロバイダを使用して例外を捕捉する

バインディングのエラーは Knockout アプリケーションでは避けられません。最初のセクションに記載されている技術の多くは、あなたのバインディング周りのコンテキストを理解するのに役立ちます。さらに、バインディングの問題を追跡し記録するのに役立つ別のオプションも存在します。[カスタムバインディングプロバイダ](http://www.knockmeout.net/2011/09/ko-13-preview-part-2-custom-binding.html)は、これらの例外を捕捉するための良い拡張ポイントを提供します。

例えば、単純な "wrapper" バインディングは以下のようになります。

```javascript
 (function() {
      var existing = ko.bindingProvider.instance;

        ko.bindingProvider.instance = {
            nodeHasBindings: existing.nodeHasBindings,
            getBindings: function(node, bindingContext) {
                var bindings;
                try {
                   bindings = existing.getBindings(node, bindingContext);
                }
                catch (ex) {
                   if (window.console and console.log) {
                       console.log("binding error", ex.message, node, bindingContext);
                   }
                }

                return bindings;
            }
        };

    })();
```

これにより、バインディングの例外を捕捉して、エラーメッセージ、要素、およびバインディングコンテキスト（含まれている`$data`、`$root` 等）と共に記録できるようになるでしょう。バインディングプロバイダは、バインディングがヒットしている期間全体について、記録および診断を行うための有用なツールとなります。

### ビューモデルの問題

バインディングの問題は Knockout におけるエラーの最も一般的な原因になると思われますが、ビューモデルの側にも同様にエラー/バグは存在します。以下はあなたのビューモデルコードを検査し、制御するいくつかの方法です。

#### ロギング用の手動での購読(subscribe)

ビューモデルで物事がどのように実行されるかを理解するための基本的なテクニックは、observable や computed に手動で購読を行い、値を記録することです。これらの購読は、それ自身の依存関係を作成しないので、自由に個々の値またはオブジェクト全体を記録することができます。あなたは observable、computed、および observableArray に対してこれを行うことができます。

```javascript
this.firstName = ko.observable();
    this.firstName.triggeredCount = 0;
    this.firstName.subscribe(function(newValue) {
        if (window.console && console.log) {
            console.log(++this.triggeredCount, "firstName triggered with new value", newValue);
        }
    });
```

あなたは関係のあるどのような情報が含まれるようにも、メッセージをフォーマットできます。また、observable / computed を追跡するような拡張を作成することも容易です。

```javascript
ko.subscribable.fn.logIt = function(name) {
        this.triggeredCount = 0;
        this.subscribe(function(newValue) {
            if (window.console && console.log) {
                console.log(++this.triggeredCount, name + " triggered with new value", newValue);
            }
        }, this);

        return this;
    };
```

この拡張は `this` を返すので、observable/computed の作成中に、それをチェーンすることができます。

```javascript
this.firstName = ko.observable(first).logIt(this.username + " firstName");
```

このケースでは、私は `firstName` observable を持つオブジェクトの集合を扱っており、それぞれがユニークな値を出力することを確認するために `username` を含めています。出力は次のようになります：

```
1 "bob1234 firstName triggered with new value" "Robert"
2 "bob1234 firstName triggered with new value" "Bob"
3 "bob1234 firstName triggered with new value" "Bobby"
4 "bob1234 firstName triggered with new value" "Rob"
```

#### "this" の値

`this` の値の問題は Knockout を始めたときに遭遇する最も一般的な課題の一つです。イベント(`click` / `event` バインディン4グ)のバインディングを行い、他のコンテキスト(`$parent` や `$root` 等)を離れて関数を使用する際、Knockout は これらのハンドラにおいて現在のデータを `this` の値として使用するため、通常あなたはコンテキストに気を配る必要があります。

適切なコンテキストを扱っていることを確認するには、いくつかの方法があります:

1 - 新しい関数を作成する際に　`.bind` を使用すると、常にあなたのビューモデル内の特定のコンテキストにバインドされます。もしブラウザがネイティブに[bind](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Function/bind)をサポートしていない場合、Knockout は shim(代替品)を作成します。

```javascript
this.removeItem = function(item) {
      this.items.remove(item);
  }.bind(this);
```

もし、対象の関数がプロトタイプ上に存在する場合、あなたは各インスタンスに対してバインドを作成する（理想的ではありません）必要があるかもしれません。

```javascript
this.removeItem = this.removeItem.bind(this);
```

2 - ビューモデル内に、現在のインスタンスに対応する変数(`var self = this;`)を作成して、常にあなたのハンドラ内で `this` ではなく `self` を使用することができます。このテクニックは便利ですが、 プロトタイプオブジェクト上の関数に使用することができず、またmix-in によって関数を追加するのに向いていないため、これらの関数では `this` の値を通してインスタンスにアクセスする必要があります(#1 のようにバインドされない限り)。

```javascript
var self = this;

  this.removeItem = function(item) {
     self.items.remove(item);
  };
```

3 - マークアップ内でそれを目にすることがそれほど気にならないようであれば、バインディング文字列内で `.bind` を使用することもできます。これは次のようになります。

```html
 <button data-bind="click: $parent.removeItem.bind($parent)">Remove Item</button>
```

同様に、`event` バインディングをラップしたシンプルなカスタムバインディングを作成して、以下のようにハンドラとコンテキストを持たせることもできます。

```javascript
data-bind="clickWithContext: { action: $parent.removeItem, context: $parent }"
```

4 - 現在、私が個人的には主に使用している方法は、自身の[デリゲートイベントプラグイン](https://github.com/rniemeyer/knockout-delegatedEvents)を使用することです。このプラグインはイベント委譲が可能で、副産物としてその関数を所有するオブジェクトから、自動的にメソッドを切り離して呼び出すことができます。また、あなたのオブジェクトのプロトタイプ上に存在する関数を呼び出す事も便利/実用的になります。このプラグインにおいて、上のサンプルは以下のようにシンプルになります。

```html
<button data-click="removeItem">Remove Item</button>
```

私はこのプラグインが、ビューモデルの中にある、コンテキストバインディングに対する懸念の大半を取り除くことができることを発見しました。

### コンソールの使用

私が Knockout アプリケーションをデバッグ / 検査するお気に入りの方法の一つは、ブラウザのデバッグコンソールを使用することです。もしあなたのビューモデルが適切に記述されている場合、通常はアプリケーション全体をコンソールから制御できます。最近、私はデバッグコンソールから数百ものレコードを含むデータ変換を実行して、適切なデータを読み込み、各レコードをループして更新の必要な observable を操作した後、レコードをデータベースに書き戻しました。これは常に最良のアイデアではないかもしれませんが、それは私が私のシナリオにおいて変換を達成するために、最も実用的かつ効率的な方法でした。

#### あなたのデータへのアクセス

最初のステップは、コンソールからあなたのデータへの参照を取得することです。あなたのアプリケーションが(おそらく特定の名前空間の下で)グローバルに公開されている場合は、すでに準備ができています。しかし、もしビューモデルをグローバルに利用せずに `ko.applyBindings` を呼び出している場合でも、簡単にデータを取得することができます。Knockout は `ko.dataFor` と `ko.contextFor` ヘルパーメソッドを含んでおり、バインド中の要素で使用可能なデータ/コンテキストを伝えることができます。例えば、あなたのビューモデル全体を取得するためには、以下のようなコンソール入力で始めることができます。

```javascript
var data = ko.dataFor(document.body);
```

今、あなたはビューモデル全体にアクセスすることができます(もし、あなたが特定のルート要素を指定せず、単に `ko.applyBindings` を呼び出した場合)。このビューモデルにアクセスすることで、メソッドを呼び出し、observable の値を設定し、 特定のオブジェクトをロギング / 検査できます。

#### 少しスマートにロギングを行う

Knockout のデバッグを行っており、いくつかの `observable` や `computed` に直接 `console.log` を実行した場合、以下のようなあまり有用でない出力を発見します。

```javascript
function d(){if(0<arguments.length){if(!d.equalityComparer||!d.equalityComparer(c,arguments[0]))d.H(),c=arguments[0],d.G();return this}b.r.Wa(d);return c}
```

上記の出力を改善する1つの方法は、各種の型に対して `toString` 関数を追加することです。私は型の名前（`observable` や `computed` など）と現在の値を表示することを好みます。この方法は、対象が確かに KO オブジェクトであり、どの型であるか、最新の値が何であるかをすばやく理解するのに役立ちます。これは次のようになります。

```javascript
 ko.observable.fn.toString = function() {
        return "observable: " + ko.toJSON(this(), null, 2);
    };

    ko.computed.fn.toString = function() {
        return "computed: " + ko.toJSON(this(), null, 2);
    };
```

Chromeのコンソール出力は、以下のようになります。

```
observable: "Bob"
computed: "Bob Smith"
observable: "Jones"
computed: "Bob Jones"
```

全てのブラウザが `toString` 関数を尊重しているわけではないため、この際は確実にChrome が役立つことに注意してください。また、observableArray は取り扱いが少し困難です。Chrome は observableArray（関数）が `length` と `splice` メソッドの両方を持っていることを認識して、それが配列であることを前提としています。このケースでは、observableArrays から `splice` 関数を削除しない限り、`toString` 関数の追加はあまり効果的ではありません。

### ファイナルノート

* ** Knockout コアのデバッグ ** - Knockoutアプリケーションで例外が発生したとき、私はあなたが最初の選択肢として、Knockoutコアライブラリのコードをデバッグしようとすることをお勧めしません。最初にあなたのアプリケーションのコードについて、問題となっている原因を隔離した後でシンプルに再現させることが、ほとんど常により効果的であり、効率的です。私の経験では、Knockout コアに足を踏み入れることが良い選択になりうる主な状況は、複数のブラウザ間で一貫性のない振る舞いを発見した時です。Knockout コアのステップ実行やフォローに挑戦することは可能であり、もしあなたが自分自身でコアライブラリのデバッグを行う時は、バインディングハンドラ自身の内部が良いスタート地点になります。

* ** ロギングに注意してください ** - あなたのコードにロギングや計測の機能を追加する場合、このログが computed やバインディングの依存関係に影響するかもしれないことに注意してください。例えば、あなたが `computed` 内で `ko.toJSON($root)` のロギングをした時、構造内の全ての observable に対して依存を持つことになるでしょう。加えて、あなたが大規模なビューモデルを扱っている場合、頻繁に巨大なオブジェクトグラフをJSON に変換することによる、潜在的なパフォーマンスへの影響に注意してください。このようなシナリオでは、スロットリング (訳注: KO 3.0 以降はレート制限API) が役立つことがあります。

* ** console.log のフォーマット ** - この記事内のスニペットにおける `console.log` の呼び出しは単なるサンプルです。私はいくつかのブラウザが `console.log` に対する複数の引数をサポートしておらず、またいくつかはオブジェクト階層を扱うことができないことを理解しています。あなたが様々なブラウザでこのようなロギングを行う必要がある場合、あなたが適切な出力を行っていることを確認してください。通常、私が開発中にロギングを行う場合は、一時的に Chrome を使用しています。
