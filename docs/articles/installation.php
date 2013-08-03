<article>
	<h1>導入方法</h1>
	<p>
		Knockout は独立した JavaScript ライブラリであり、いかなるライブラリにも依存しません。
		次の手順でプロジェクトに導入できます。
	</p>
	<ol>
		<li>
			<p>
				最新版の Knockout を <a href="http://knockoutjs.com/downloads/index.html">本家ダウンロードページ</a>からダウンロードします。
				通常の開発および製品での使用であれば、基本的にミニファイ版 (<code>knockout-x.y.z.js</code>) を選択して下さい。
			</p>
			<p>
				<em>
					デバッグ用途でのみ、ミニファイされていないバージョン (<code>knockout-x.y.z.debug.js</code>) を選択して下さい。
					ミニファイ版と機能は同じですが、変数名・コメントなど可読性を保っており、また内部APIも隠蔽されていません。
				</em>
			</p>
		</li>
		<li>
			<p>
				jQuery などと同じように、HTMLページのどこかに、<code>&lt;script&gt;</code>タグで参照してください。
			</p>
		</li>
	</ol>
	<p>例:</p>
	<pre class="brush: html">&lt;script type="text/javascript" src="knockout-2.2.0.js"&gt;&lt;/script&gt;</pre>
	
	<p>
		これで使用する準備が出来ました。上記の src属性は、Knockout を設置した場所に合わせて正しく指定して下さい。
		続いて、入門者の方は <a href="http://learn.knockoutjs.com/">インタラクティブ・チュートリアル</a> で基本を学ぶか、
		<a href="http://knockoutjs.com/examples/">Live Examples</a>を読む、
		もしくは本ドキュメントの「<a href="/docs/observables">Observable (ViewModelをつくる)</a>」から掘り下げて行かれることをお勧めします。
	</p>
	
	<h3 id="content_delivery_networks_cdns">Content Delivery Networks (CDN)</h3>
	<p>
		ダウンロードスピード向上のために、代わりにサードパーティの CDN で提供される <code>knockout.js</code>
		を参照することができます。
	</p>
	<ul>
		<li>
			<strong>Microsoft Ajax CDN</strong> (<a href="http://www.asp.net/ajaxlibrary/CDN.ashx">about</a>)
			<ul>
				<li><a href="http://ajax.aspnetcdn.com/ajax/knockout/knockout-2.2.0.js">Knockout version 2.2.0</a></li>
			</ul>
		</li>
		<li>
			<strong>CDNJS</strong> (<a href="http://cdnjs.com/">about</a>)
			<ul>
				<li><a href="http://cdnjs.cloudflare.com/ajax/libs/knockout/2.2.0/knockout-min.js">Knockout version 2.2.0</a></li>
			</ul>
		</li>
	</ul>
	<blockquote>
		<p>
			リンクアドレスをコピーし、<code>src</code> 属性に指定して下さい。
		</p>
	</blockquote>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/installation.html">こちら</a></div>
</article>
