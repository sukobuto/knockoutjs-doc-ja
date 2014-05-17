<script type="text/javascript" src="resources/linq.min.js"></script>
<style>
	.tweet {
		margin-bottom: 2px;
	}
	.username {
		border: solid 1px #ff6633;
		background-color: #752d15;
	}
	.tweet_text {
		border: solid 1px #8ed82c;
		background-color: #365210;
	}
	.tweet_lang {
		border: solid 1px #2f63db;
		background-color: #132651;
	}
</style>

<article>
	
	<h1>TypeScript で Knockout を使ってみる</h1>
	
	<p>
		TypeScript という言語を使って、Knockout のサンプルコードを書いてみました。
	</p>
	<p>
		TypeScript は CoffeeScript などと同様に JavaScript へコンパイルするタイプの言語で、C# に近いオブジェクト指向が特徴です。
		ここでは、Knockout, jQuery, linq.js を組み合わせて使うシンプルな例を示します。<br />
		(linq.js を用いたコレクション操作については、
		<a href="http://neue.cc/2012/09/16_381.html" target="_blank">neue.cc 先生のライブコーディング</a>
		を<del>パク</del>参考にさせて頂きました。)
	</p>
	
	<h2>デモ</h2>
	<div class="demo" id="demo_1">
		<h1>Twitter キーワード検索</h1>

		検索キーワード: ("test"と入力すると比較的多くの言語のツイートがヒットします)
		<form data-bind="submit: search">
			<input type="text" data-bind="value: searchKeyword, valueUpdate: 'afterkeydown'" placeholder="検索キーワード"/>
			<button type="submit">SEARCH</button>
		</form>

		ヒットしたツイート:
		<div data-bind="foreach: searchResult">
			<div class="tweet">
				<span class="username" data-bind="text: '@' + from_user"> </span>
				<span class="tweet_text" data-bind="text: text"> </span>
			</div>
		</div>

		使用言語:
		<div data-bind="foreach: tweetLanguages">
			<span class="tweet_lang" data-bind="text: $data"> </span>
		</div>
	</div>
	<script type="text/javascript">
		// コンパイル済みJSコード
		var TwitterClient = (function () {
			function TwitterClient() { }
			TwitterClient.search = function search(keyword, callback) {
				$.getJSON("http://search.twitter.com/search.json?callback=?", {
					q: keyword
				}, callback);
			};
			return TwitterClient;
		})();
		var AppViewModel = (function () {
			function AppViewModel() {
				var _this = this;
				this.searchResult = ko.observableArray([]);
				this.searchKeyword = "";
				this.tweetLanguages = ko.computed(function () {
					return Enumerable.from(_this.searchResult()).groupBy(function (t) {
						return t.iso_language_code;
					}).select(function (g) {
						return g.key() + " (" + g.count() + ")";
					}).toArray();
				});
			}
			AppViewModel.prototype.search = function () {
				var _this = this;
				TwitterClient.search(this.searchKeyword, function (json) {
					_this.searchResult(json.results);
				});
			};
			return AppViewModel;
		})();
		$(function () {
			ko.applyBindings(new AppViewModel(), document.getElementById('demo_1'));
		});
	</script>
	
	<h2>コード: View</h2>
	<pre class="brush: html;">&lt;h1&gt;Twitter キーワード検索&lt;/h1&gt;

検索キーワード: (&quot;test&quot;と入力すると比較的多くの言語のツイートがヒットします)
&lt;form data-bind=&quot;submit: search&quot;&gt;
	&lt;input type=&quot;text&quot; data-bind=&quot;value: searchKeyword, valueUpdate: 'afterkeydown'&quot; placeholder=&quot;検索キーワード&quot;/&gt;
	&lt;button type=&quot;submit&quot;&gt;SEARCH&lt;/button&gt;
&lt;/form&gt;

ヒットしたツイート:
&lt;div data-bind=&quot;foreach: searchResult&quot;&gt;
	&lt;div class=&quot;tweet&quot;&gt;
		&lt;span class=&quot;username&quot; data-bind=&quot;text: '@' + from_user&quot;&gt; &lt;/span&gt;
		&lt;span class=&quot;tweet_text&quot; data-bind=&quot;text: text&quot;&gt; &lt;/span&gt;
	&lt;/div&gt;
&lt;/div&gt;

使用言語:
&lt;div data-bind=&quot;foreach: tweetLanguages&quot;&gt;
	&lt;span class=&quot;tweet_lang&quot; data-bind=&quot;text: $data&quot;&gt; &lt;/span&gt;
&lt;/div&gt;</pre>
	
	<h2>コード: ViewModel (TypeScript)</h2>
	<pre class="brush: ts;">// 外部参照
/// &lt;reference path=&quot;Scripts/jquery.d.ts&quot; /&gt;
/// &lt;reference path=&quot;Scripts/knockout.d.ts&quot; /&gt;
/// &lt;reference path=&quot;Scripts/linq.js.d.ts&quot; /&gt;

// ツイッタークライアント(モデル)定義
class TwitterClient {
    static search(keyword: string, callback?: (json: any) =&gt; any) {
        $.getJSON(&quot;http://search.twitter.com/search.json?callback=?&quot;, { q: keyword }, callback);
    }
}

// メイン ViewModel 定義
class AppViewModel {

    searchKeyword: string = &quot;&quot;; // 検索キーワード
    searchResult: KnockoutObservableArray = ko.observableArray([]);	// 検索結果配列
    tweetLanguages: KnockoutComputed; // 検索結果のツイートの使用言語ごとの集計

    constructor() {
		// 検索結果から使用言語ごとの集計を得る LINQ クエリを ko.computed で使用
        this.tweetLanguages = ko.computed(() =&gt;
            Enumerable.from(this.searchResult())
                .groupBy(t =&gt; t.iso_language_code) // 言語でグループ化
                .select(g =&gt; g.key() + &quot; (&quot; + g.count() + &quot;)&quot;) // 文字列として射影
                .toArray() // 配列に変換
        );
    }

    search() { // 検索アクション
        TwitterClient.search(this.searchKeyword, (json) =&gt; {
            this.searchResult(json.results);
        });
    }
}

$(() =&gt; ko.applyBindings(new AppViewModel()));</pre>
	
	<h2>著者雑記</h2>
	
	<p>
		TypeScript は JavaScript の上に、純粋なオブジェクト指向, 型推論, ラムダ式などのシンタックスシュガーを構築する
		とてもパワフルな言語です。しかし、このサンプルを書いた時に１つだけ疑問に思った点があります。
	</p>
	<p>
		それは、<code>this</code> が使えないコンテキストについてです。TypeScript において、コンパイラレベルで <code>this</code> が使えないコンテキストがあります。
	</p>
	<h4>TypeScript「this」が使えるコンテキスト</h4>
	<ul>
		<li>グローバルコンテキスト, 通常の関数定義および <code>function</code> 式</li>
		<li>クラスのコンストラクタ, メンバ関数およびメンバアクセサ</li>
	</ul>
	<p>
		上記２つ<strong>以外</strong>のコンテキストでは、<code>this</code> を使おうとするとコンパイルエラーになります。
		たとえば、このサンプルの <code>AppViewModel.tweetLanguages</code> を、定義と同時に
		<code>ko.computed</code> で初期化しようとすると、内部で参照している <code>this.searchResult()</code>
		にてコンパイルエラーが発生してしまいます。
		つまり、しかたなくコンストラクタの内部で初期化しているのです。
	</p>
	<p>
		この挙動は、JavaScript のオブジェクトリテラルについて理解されている方は
		「そりゃそうだろう」と思われるかもしれません。
		そこで、次のコードを見て下さい。上記の TypeScript コードは、次のようにコンパイルされます。
	</p>
	
	<h4>コード:ViewModel (コンパイル結果の JavaScript)</h4>
	<pre class="brush: js">
		var TwitterClient = (function () {
			function TwitterClient() { }
			TwitterClient.search = function search(keyword, callback) {
				$.getJSON("http://search.twitter.com/search.json?callback=?", {
					q: keyword
				}, callback);
			};
			return TwitterClient;
		})();
		var AppViewModel = (function () {
			function AppViewModel() { // プロパティもコンストラクタで生成してるじゃん！
				var _this = this;
				this.searchResult = ko.observableArray([]);
				this.searchKeyword = "";
				this.tweetLanguages = ko.computed(function () {
					return Enumerable.from(_this.searchResult()).groupBy(function (t) {
						return t.iso_language_code;
					}).select(function (g) {
						return g.key() + " (" + g.count() + ")";
					}).toArray();
				});
			}
			AppViewModel.prototype.search = function () {
				var _this = this;
				TwitterClient.search(this.searchKeyword, function (json) {
					_this.searchResult(json.results);
				});
			};
			return AppViewModel;
		})();
		$(function () {
			ko.applyBindings(new AppViewModel());
		});</pre>
	
	<p>
		コメントにも書いたとおり、すべてのプロパティはコンストラクタの内部で宣言・初期化されます。
		つまり、コンストラクタの先頭で宣言している <code>var _this = this;</code> が使えるスコープにあるわけです。
		それならば、プロパティ宣言の初期化処理で <code>this</code> が使えたっていいのではないか、と思うのです。
	</p>
	<p>
		TypeScript はまだ正式リリースされていないので、どうにか <code>this</code> を使える子にしてあげて欲しいです。
	</p>
	<p>
		追記: TypeScript 0.9.5 にて確認したところ上記のように改善されていました！
	</p>
	
	<div class="tail_mini_text">
		この記事は、翻訳者が独自に作成したものです。
	</div>
	
</article>

