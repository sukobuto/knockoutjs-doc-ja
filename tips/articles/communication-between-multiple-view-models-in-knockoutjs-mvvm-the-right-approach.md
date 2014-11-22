# KnockoutJS (MVVM) において複数のビューモデルの間で連携する、正しい方法

> この記事は、Rahul Patil による2014/08/25のブログ記事、["Communication between multiple View Models in KnockoutJS (MVVM), the right approach!"](http://www.wrapcode.com/knockoutjs/communication-between-multiple-view-models-in-knockoutjs-mvvm-the-right-approach/) の翻訳です。


### 肥大したビューモデル

KnockoutJS は、JavaScript コードを汚くせずに、レスポンシブでデータリッチなユーザインターフェースを作成する助けとなる美しいフレームワークです。KnockoutJS を学び始めるとき、初学者は単一のビューモデルを作成し、それをグローバルまたは特定のDOM要素にバインディングして、それで遊ぶ習慣をつける傾向があります。

より深く使い始めると、単一のビューモデルはエンタープライズアプリケーションを開発するのに十分ではないと気づきます。それはアプリケーションのモジュール性を保守する助けになりません。アプリケーションが大きくなるにつれ、ビューモデルの複雑さは増します。コードを保守するのが難しく、テストを行うことはさらに難しくなり、あなたは文字通りあきらめてしまうことでしょう。

### 複数のビューモデルの利点

なぜ複数のビューモデルを使用すべきなのでしょうか？答えはシンプルで、モジュール化して保守できるからです。複数のビューモデルはモジュールとして振る舞います。基本的に、大きな問題を小さな複数のモジュールに分割することで、再利用可能で容易に拡張可能になります。最も細かいレベルにコンポーネントを分離することで、アプリケーションを維持する能力を保持することができます。あなたのアプリケーションに複数のビューモデルを導入するのは良い事です。

しかしながら、このアプローチについては StackOverflow の KnockoutJS セクションにおいてメジャーであり最も議論されている問題があります (一般的に、MVVMパターンではよくある疑問です)。 複数のビューモデル間での連携方法です。驚くべきことに、この問題は人々が頻繁に直面するにも関わらず、 KO のドキュメントにおいてはまったく記述がなく、議論もされていません。私は、少なくとも Stack Overflow において KnockoutJS ユーザー / 開発者 が同じ問題を抱えてしまうことに対して手助けをしたいと思います。

そう、私たちは以下のコードのような、単一のビューポート内で異なるDOM要素にバインドされている、複数のビューモデル間で連携するための独創的で容易な解決策を持っていません。

```javascript
var viewModel1 = function(){
    var self = this;
    self.firstName = ko.observable();
    self.lastName = ko.observable();
    self.fullName = ko.computed(function(){
        return self.firstName + " " + self.lastName;
    });
};

var viewModel2 = function(){
    var self = this;
    self.premium = ko.observable();
};

ko.applyBindings(new viewModel1(), document.getElementById("container1"));
ko.applyBindings(new viewModel2(), document.getElementById("container2"));
```

複数のビューモデル間の連携を保守する正しい方法は二つ存在します。

  1. マスタービューモデルを保守する
  2. pub-sub(出版-購読型モデル) を導入する

# マスタービューモデルを保守する

複数のビューモデル間での連携を達成する最初の方法は、マスタービューモデルを導入することです。

```javascript
// ビューモデル 1 の定義
var viewModel1 = function(){
 this.firstName = ko.observable("Wrapcode");
 this.messageForVM2 = ko.observable("Hello from first view model");
 this.message = ko.observable("Hello this is vm1")
};

// ビューモデル 2 の定義
var viewModel2 = function(vm1){
 this.firstName = ko.observable(vm1.firstName());
 this.message = ko.observable(vm1.messageForVM2());
};

// 両方のビューモデルのインスタンスを保持するマスタービューモデル
var masterVM = (function(){
 this.viewModel1 = new viewModel1(),
 this.firstName = "Rahul",
 this.viewModel2 = new viewModel2(this.viewModel1);
})();

ko.applyBindings(masterVM)
```

JSFiddle のライブアクションはこちらです : http://jsfiddle.net/rahulrulez/paxnd6uz/1/

<iframe src="http://jsfiddle.net/rahulrulez/paxnd6uz/1/embedded/" width="100%" height="300" frameborder="0" allowfullscreen="allowfullscreen"></iframe>

このアプローチでは、 マスタービューモデル内に存在するサブビューモデルのインスタンス間で、情報をやり取りするのは容易な事です。しかし、変更の反映について未だ問題があります。複数のビューモデル間でデータを交換する方法は発見しましたが、連携は未だ受動的であり、もし上記の fiddle で入力ボックスの内容を変更した場合、 それはもう片方のビューモデルには反映されません。ここで、 次のアプローチである 出版/購読パターンが重要な役割を担います。

# Knockout でのPub-Sub と Postbox

マスタービューモデルアプローチを採用した場合、連携のために、あなたは一つのビューモデルをもう片方に対して参照したり渡したりする必要があります。この連携方法は非常に受動的であり、マスタービューモデルアプローチでは変更を観察したり追跡することはできません。この制限を克服するため、KnockoutJS はネイティブの PubSub 関数を持っています - `ko.subscribable` この関数は全く詳細にはドキュメント化されていません。これが、PubSub を統合してアプリケーションを拡張するための方法です。

# ko.subscribable オブジェクトの構築

```
var shouter = new ko.subscribable();
```

sbuscribable は継承されているので、ビューモデルのスコープに関わらず使用可能です。しかし、あなたが同じ事をするために複数のインスタンスを作成しなくてよいように、グローバルに生成する方が良いでしょう。

# トピックによって変更を通知する

さて、Knockout の subscriber (購読者) 用の関数を使用して、 shouter (subscribable) に変更を通知しましょう。
上記の例で(JSFiddle を参照してください)、私たちは viewModel1 の this.messageForVM2 の値を publish (公開)することで、2つめのビューモデルからアクセスできるようにしたいと考えています。我々はthis.messageForVM2 の subscribe 関数内で購読者に通知を行うことで、これを達成することができます。
subscribe については[こちら]()を参照してください。

```javascript
this.messageForVM2.subscribe(function(newValue) {
 shouter.notifySubscribers(newValue, "messageToPublish");
 });
```

ko.subscribable.notifySubscribers は2つのプロパティを取ることを覚えてください。

  1. あなたが対象に通知する値 - 値はどの形式にもできます。Number, string, object, function ... 文字通りなんでも。

  2. トピック名 - トピック名はユニークな文字列で、複数の購読を識別します。

# トピックの購読

公開された値や更新をキャッチできるようにするには、特定のトピックに対して購読を行う必要があります。変更があるたびに、購読を行っている関数が呼び出されます。私たちは、この関数内に必要なロジックを記述することができます。subscribable の拡張された subscribe メソッドは、3つのパラメータを予期しています。

  1. コールバック - 購読しているトピックの値が変更された時、あなたが実行されることを期待する関数。
  2. コールバックターゲット - もし、あなたが コールバックターゲット を提供すると、コールバックは指定したターゲットとバインドされ、ターゲットスコープの内容がコールバック関数内からアクセスできるようになります。
  3. イベント / トピック - トピック名 / 識別子。

```
shouter.subscribe(function(newValue) {
     this.message(newValue);
 }, this, "messageToPublish");
```

fiddle の実行結果です。

<iframe src="http://jsfiddle.net/rahulrulez/paxnd6uz/4/embedded/" width="100%" height="300" frameborder="0" allowfullscreen="allowfullscreen"></iframe>

メッセージボックスに何かを入力してみてください。observable が変更を追跡する限り、 subscribable はメッセージを公開します。今、連携は有効であり、なおかつ分離されています。私たちは両方のビューモデル内で、いかなる種類の参照も持っていません。

あなたは KO ネイティブの PubSub によって多くのことができます。さらに、あなたは間違いなく Ryan Niemeyer の、 Postbox と呼ばれるとても有用な拡張を試してみるべきです。これはシンプルな pub/sub の有用性を次のレベルに拡張しており、複数のビューモデルに対する、複数の同期的な observable / component や、初期化時のメッセージの公開、購読のクリーンアップや解除のような、様々なカスタマイズを行っています。

Knockout-Postbox の実例です :

<iframe src="http://jsfiddle.net/rniemeyer/mg3hj/embedded/" width="100%" height="300" frameborder="0" allowfullscreen="allowfullscreen"></iframe>

もし、あなたがKnockout アプリケーションで連携を失うことなくモジュールに分割することを計画している場合、この記事が少し有利なスタートを切るための助けとなることを願っています。

Peace,
Rahul Patil
