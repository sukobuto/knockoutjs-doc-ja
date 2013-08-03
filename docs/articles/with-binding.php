<script type="text/javascript">
window.twitterApi = (function () {
    var throttleFunction = function (fn, throttleMilliseconds) {
        var invocationTimeout;

        return function () {
            var args = arguments;
            if (invocationTimeout)
                clearTimeout(invocationTimeout);

            invocationTimeout = setTimeout(function () {
                invocationTimeout = null;
                fn.apply(window, args);
            }, throttleMilliseconds);
        };
    };

    var getTweetsForUsersThrottled = throttleFunction(function (userNames, callback) {
        if (userNames.length == 0)
            callback([]);
        else {
            var url = "http://search.twitter.com/search.json?callback=?&rpp=100&q=";
            for (var i = 0; i < userNames.length; i++)
                url += "from:" + userNames[i] + (i < userNames.length - 1 ? " OR " : "");
            $.ajax({
                url: url,
                dataType: "jsonp",
                success: function (data) { callback($.grep(data.results || [], function (tweet) { return !tweet.to_user_id; })); }
            });
        }
    }, 50);

    return {
        getTweetsForUser: function (userName, callback) {
            return this.getTweetsForUsers([userName], callback);
        },
        getTweetsForUsers: function (userNames, callback) {
            return getTweetsForUsersThrottled(userNames, callback);
        }
    };
})();
</script>

<article>
	
	<h1>"with" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	
	<p>
		<code>with</code> バインディングは新たな <a href="binding-context">バインディング・コンテキスト</a>
		を作成します。配下のエレメントは指定したオブジェクト内でバインドされます。
	</p>
	
	<p>
		もちろん、<code>with</code> バインディングをいくつでも入れ子にすることができますし、
		<a href="if-binding"><code>if</code></a> や
		<a href="foreach-binding"><code>foreach</code></a>
		のような別のフロー制御バインディングと同時に使うこともできます。
	</p>
	
	<h3 id="example_1">例1</h3>
	
	<p>
		バインディング・コンテキストを子オブジェクトに切り替える基礎的な例です。
		注目すべき点は <code>data-bind</code> 属性の中で、<code>latitude</code>
		や <code>longitude</code> にプレフィックス <code>coords.</code> を付ける必要がないことです。
		なぜならバインディング・コンテキストが <code>coords</code> に切り替わっているからです。
	</p>
	
	<pre class="brush: html;">&lt;h1 data-bind=&quot;text: city&quot;&gt; &lt;/h1&gt;
&lt;p data-bind=&quot;with: coords&quot;&gt;
	緯度: &lt;span data-bind=&quot;text: latitude&quot;&gt; &lt;/span&gt;,
	経度: &lt;span data-bind=&quot;text: longitude&quot;&gt; &lt;/span&gt;
&lt;/p&gt;
	
&lt;script type=&quot;text/javascript&quot;&gt;
	ko.applyBindings({
		city: &quot;ロンドン&quot;,
		coords: {
			latitude:  51.5001524,
			longitude: -0.1262362
		}
	});
&lt;/script&gt;</pre>
	
	<h3 id="example_2">例2</h3>
	
	<p>このデモから次の2つのことがわかります。</p>
	<ul>
		<li>
			<code>with</code> バインディングは、関連付けられた値が <code>null</code>/<code>undefined</code>
			なのか否かに基づいて、配下のエレメントを動的に 追加・削除 します。
		</li>
		<li>
			<a href="binding-context"><code>$parent</code> や <code>$root</code> などのコンテキスト変数</a>
			を使って、親のバインディング・コンテキストにあるプロパティ/関数にアクセスすることができます。
		</li>
	</ul>
	
	<p>試してみましょう:</p>
	
	<div class="demo" id="demo_1">
		<form data-bind="submit: getTweets">
			Twitter アカウント:
			<input type="text" data-bind="value: twitterName" />
			<button type="submit">ツイートを取得</button>
		</form>

		<div data-bind="with: resultData">
			<h3>最終取得日時<span data-bind="text: retrievalDate"> </span></h3>
			<ol data-bind="foreach: topTweets">
				<li data-bind="text: text"></li>
			</ol>

			<button data-bind="click: $parent.clearResults">ツイートをクリア</button>
		</div>
	</div>
	<script>
		function AppViewModel() {
			var self = this;
			self.twitterName = ko.observable('@StephenFry');
			self.resultData = ko.observable(); // 初期値なし

			self.getTweets = function() {
				twitterApi.getTweetsForUser(self.twitterName(), function(data) {
					self.resultData({
						retrievalDate: new Date(),
						topTweets: data.slice(0, 5)
					});
				});
			}

			self.clearResults = function() {
				self.resultData(undefined);
			}
		}

		ko.applyBindings(new AppViewModel(), document.getElementById('demo_1'));
	</script>
	
	<h4>ソースコード: View</h4>
	<pre class="brush: html;">&lt;form data-bind=&quot;submit: getTweets&quot;&gt;
	Twitter アカウント:
	&lt;input data-bind=&quot;value: twitterName&quot; /&gt;
	&lt;button type=&quot;submit&quot;&gt;ツイートを取得&lt;/button&gt;
&lt;/form&gt;

&lt;div data-bind=&quot;with: resultData&quot;&gt;
	&lt;h3&gt;最終取得日時&lt;span data-bind=&quot;text: retrievalDate&quot;&gt; &lt;/span&gt;&lt;/h3&gt;
	&lt;ol data-bind=&quot;foreach: topTweets&quot;&gt;
		&lt;li data-bind=&quot;text: text&quot;&gt;&lt;/li&gt;
	&lt;/ol&gt;

	&lt;button data-bind=&quot;click: $parent.clearResults&quot;&gt;ツイートをクリア&lt;/button&gt;
&lt;/div&gt;</pre>
	
	<h4>ソースコード: ViewModel</h4>
	<pre class="brush: js;">function AppViewModel() {
	var self = this;
	self.twitterName = ko.observable('@StephenFry');
	self.resultData = ko.observable(); // 初期値なし

	self.getTweets = function() {
		twitterApi.getTweetsForUser(self.twitterName(), function(data) {
			self.resultData({
				retrievalDate: new Date(),
				topTweets: data.slice(0, 5)
			});
		});
	}

	self.clearResults = function() {
		self.resultData(undefined);
	}
}

ko.applyBindings(new AppViewModel());</pre>
	
	<h3 id="parameters">パラメタ</h3>
	
	<ul>
		<li>
			主パラメタ
			<p>
				配下のエレメントのバインディングのためのコンテキストとして使いたいオブジェクトです。
			</p>
			<p>
				<code>null</code> や <code>undifined</code> として評価された場合、
				配下のエレメントはバインドが適用されず、ドキュメントから削除されます。
			</p>
			<p>
				式が Observable を伴う場合、その値が変更されるたびに式が再評価されます。
				その際、配下のエレメントは一度クリアされ、マークアップの新たなコピーがドキュメントに追加され、
				式の返却値が新たなコンテキストとしてバインドされます。
			</p>
		</li>
		<li>
			追加パラメタ
			<p>なし</p>
		</li>
	</ul>
	
	<h3 id="note_using_with_without_a_container_element">(注) コンテナエレメントなしで "with" を使う</h3>
	<p>
		<a href="if-binding"><code>if</code></a> や <a href="foreach-binding"><code>foreach</code></a>
		などの他のフロー制御バインディングと同じく、<code>with</code> をコンテナエレメントなしで設置することができます。
		新たなコンテナエレメントを導入できない状況でコンテキストを切り替える場合に便利です。
		詳細は <a href="if-binding#note_using_if_without_a_container_element"><code>if</code></a>
		もしくは <a href="foreach-binding#note_4_using_foreach_without_a_container_element"><code>foreach</code></a>
		をご覧ください。
	</p>
	
	<p>例:</p>
	<pre class="brush: html;">&lt;ul&gt;
	&lt;li&gt;ヘッダアイテム&lt;/li&gt;
	&lt;!-- ko with: 出発便 --&gt;
	...
	&lt;!-- /ko --&gt;
	&lt;!-- ko with: 到着便 --&gt;
	...
	&lt;!-- /ko --&gt;
&lt;/ul&gt;</pre>
	
	<p>
		このコメント <code>&lt;!--ko--&gt;</code> と <code>&lt;!--/ko--&gt;</code> は、
		内部にマークアップを含む“バーチャルエレメント”の 開始 / 終了 のマーカーとしての役割をもっています。
		Knockout はこのバーチャルエレメント構文を理解し、本当のコンテナエレメントがあるかのようにバインドします。
	</p>
	
	<h3 id="dependencies">依存</h3>
	<p>Knockout コアライブラリ以外、なし。</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/with-binding.html">こちら</a></div>
	
</article>

