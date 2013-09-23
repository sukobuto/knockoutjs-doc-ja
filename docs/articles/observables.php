<article>
	<h1>Observable (ViewModelをつくる)</h1>
	<p>Knockout は次の３つの思想に基づいています。</p>
	<ol>
		<li>Observable と依存関係トラッキング</li>
		<li>宣言型バインディング</li>
		<li>UIテンプレート</li>
	</ol>
	<p>ここでは上記３つのうち最初の１つを紹介しますが、その前に、MVVM パターンと View Model のコンセプトについて考えてみましょう。</p>
	
	<h1>MVVM と View Model</h1>
	<p>
		Model-View-ViewModel (MVVM) はユーザインターフェイスを構築するための設計パターンであり、概念モデルです。
		MVVM では、プログラムを次の３つに分割して設計することで、機能的なUIのコードをシンプルに保ちます。
	</p>
	<ul>
		<li>
			<p>
				<strong>Model</strong>:&nbsp; いかなるUIにも依存しない、ビジネスドメインのデータと操作を表すオブジェクトです。<br>
				Knockout を使う場合、サーバに保管されたデータを取得・変更するために、サーバサイドコードをAjaxで呼び出すことになるでしょう。
			</p>
		</li>
		<li>
			<p>
				<strong>ViewModel</strong>:&nbsp; UIで必要とされるデータと操作を表現する、純粋なオブジェクトです。
				例えば、アイテムを追加・削除できるリストを実装するとき、ViewModel では データ「アイテムのリスト」　操作「追加」「削除」　を公開します。
			</p>
			<p>
				ViewModel自体 は UI ではないことに注意してください。ボタンや表示スタイルに関するいかなる情報も含みません。
				さらに ViewModel は永続データモデルではありません。ユーザが画面を操作する上で、保存されていない（あくまでメモリ上の）データを保持します。
				Knockout を使えば、ViewModel はいかなるHTMLに関する情報を含まない、純粋な JavaScript オブジェクトとなります。
				このように ViewModel を抽象的に保つことで、簡潔さを損なうこと無く改良を加えていくことができます。
			</p>
		</li>
		<li>
			<p>
				<strong>View</strong>:&nbsp; ViewModel の状態に応じてインタラクティブに変化する UI です。
				ViewModel が提供する情報を表示し、ViewModel にコマンドを送ります。(ユーザがボタンをクリックした時など)
				そして ViewModel に変化があれば更新します。
			</p>
			<p>
				Knockout を使う場合、バインディングで ViewModel とリンクしたシンプルなHTMLドキュメントが View となります。
				あるいは、テンプレートを使って ViewModel のデータから HTML を生成することができます。
			</p>
		</li>
	</ul>
	
	<p>Knockout で ViewModel を作るには、次のように JavaScript オブジェクトを定義するだけです。</p>
	<pre class="brush: js">var myViewModel = {
	personName: 'ボブ',
	personAge: 123
};</pre>
	
	<p>
		その後、宣言型バインディングを使用して、この ViewModel の View を作成します。次の例は「personName」を表示する場合です。
	</p>
	<pre class="brush: html">お名前： &lt;span data-bind="text: personName"&gt;&lt;/span&gt;</pre>
	
	<h2>Knockout を作動させよう</h2>
	<p>
		<code>data-bind</code> 属性は HTML 標準ではありませんが、問題ありません。(HTML5に厳密に準拠しており、HTML4においては解釈されない属性は無視されます。）
		しかしブラウザは <code>data-bind</code> が何を意味するか知らないため、バインディングを有効にするために Knockout を作動させる必要があります。
	</p>
	
	<p>Knockout を作動させるには、<code>&lt;script&gt;</code> タグ内に次のように記述します。
	<pre class="brush: js">ko.applyBindings(myViewModel);</pre>
	
	<p>
		上記のスクリプトは、HTMLの最下部に配置してください。もしくは、DOMのreadyイベントハンドラでラップする (jQueryの $(function(){　～ })などのように )ことで、&lt;head&gt;タグやその他どこにでも配置することができます。
	</p>
	<p>
		これで完了です！記述されたHTMLに従って、Viewが次のように展開されます。
	</p>
	<pre class="brush: html">お名前： &lt;span&gt;Bob&lt;/span&gt;</pre>
	
	<p>ko.applyBindings は次の引数（最大２つ）を受け取ります。</p>
	<ul>
		<li>
			<p>
				１つ目の引数は、View に対してバインドすべき ViewModel です。
			</p>
		</li><li>
			<p>
				２つ目の引数はオプションです。ViewModel をバインドする対象のDOM要素を指定することができます。<br>
				例： <code>ko.applyBindings(myViewModel, document.getElementById('someElementId'))</code><br>
				これにより、ID「someElementId」が付与された要素と、その配下の要素に対してのみバインドを適用することができます。
				１つのページに対して、部分ごとに異なる ViewModel をバインドさせるといった使い方ができます。
			</p>
		</li>
	</ul>
	<p title="ﾄﾞﾔｧ">実にシンプルですね。</p>
	
	<h1>Observable</h1>
	<p>
		基本的な ViewModel のつくりかたと、どのようにして ViewModel のプロパティを画面に表示するかをご理解頂けたかと思います。
		しかし、Knockout の最大のメリットのひとつは「 ViewModel が変更されると自動的にUIが更新される」 ということです。
		Knockout はいかにして ViewModel の変更を知ることができるのでしょうか。
		その答えは、ViewModel のプロパティを Observable (=オブザーバブル=監視可能) として定義することです。
		Observable は特殊な JavaScript オブジェクトで、プロパティのサブスクライバー (=購読者) に対して変更を知らせ、
		かつ自動的に依存関係を検知できる仕組みがあります。
	</p>
	
	<p>先ほどの ViewModel を次のように書き換えてみましょう。</p>
	<pre class="brush: js">var myViewModel = {
	personName: ko.observable('ボブ'),
	personAge: ko.observable(123)
};</pre>
	
	<p>
		View を変更する必要はありません。<code>data-bind</code> は先程記述した内容のままで動作します。
		変わったところは、変更を検知できるようになったことです。プロパティが変更されれば、View が自動的に更新されます。
	</p>
	
	<h2>Observable を読み書きする</h2>
	<p>
		JavaScript には getter/setter 構文がありますが、残念ながら全てのブラウザで実装されているわけではありません(IEｹﾞﾌﾝｹﾞﾌﾝ...)。
		そこで互換性を確保するため、<code>ko.observable</code> オブジェクトの実態は <em>function</em> です。
	</p>
	
	<ul>
		<li>
			<p>
				<strong>Observable の現在の値を取得</strong> するには、引数なしで observable をコールします。
				今回の例では、<code>myViewModel.personName()</code> は <code>'ボブ'</code>を、
				<code>myViewModel.personAge()</code> は <code>123</code> を返却します。
			</p>
		</li>
		<li>
			<p>
				<strong>Observable に新しい値をセット</strong> するには、新しい値を引数に observable をコールします。
				例えば、<code>myViewModel.personName('メアリー')</code> とすると名前が <code>'メアリー'</code>に変わります。
			</p>
		</li>
		<li>
			<p>
				<strong>複数の Observable プロパティに新しい値をセット</strong> する場合、メソッドチェーンが便利です。
				例えば、<code>myViewModel.personName('メアリー').personAge(50)</code>
				 とすると名前は <code>'メアリー'</code> に、年齢は <code>50</code> に変わります。
			</p>
		</li>
	</ul>
	
	<p>
		Observable の核心は監視できることにあります。言い換えると、変更通知を受け取るコードが別にあるということです。
		実際それは Knockout の組み込みバインディングの多くが内部で行なっていることです。
		<code>data-bind="text: personName"</code> と書くと、<code>text</code> バインディングは
		<code>personName</code> の変更通知を受け取るように登録されます。
	</p>
	<p>
		<code>myViewModel.personName('メアリー')</code> を呼び出して名前を <code>'メアリー'</code> に変更すると、
		<code>text</code> バインディングは関連付けられた DOM 要素のテキストを自動的に更新します。
		以上が、 ViewModel の変更が View に伝播する仕組みです。
	</p>
	
	<h2 id="explicitly_subscribing_to_observables">Observable の変更通知を明示的に購読する</h2>
	<p><em>通常の用途では、手動で購読することはありません。入門者の方はこのセクションを読み飛ばして下さい。</em></p>
	
	<p>
		Observable の変更通知を購読する方法を示します。
		Observable の subscribe 関数をコールします。
	</p>
	<pre class="brush: js">myViewModel.personName.subscribe(function(newValue) {
	alert("この人の新しい名前は " + newValue + "だそうです。");
});</pre>
	
	<p>
		<code>subscribe</code> 関数は Knockout ライブラリの中で非常に多く使用されています。<br>
		また、購読を止める必要がある場合は、次のように <code>dispose</code> 関数を呼び出します。
	</p>
	<pre class="brush: js">var subscription = myViewModel.personName.subscribe(function(newValue) {
	// なにかする
});
// ...その後
subscription.dispose(); // もう通知は不要です</pre>
	
	<p>
		ほとんどの場面において、このように購読する必要はありません。
		なぜなら、組み込みバインディング及びテンプレートシステムが購読の管理をしてくれるからです。
	</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/observables.html">こちら</a></div>
</article>