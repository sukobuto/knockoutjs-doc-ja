<article>
	
	<h1>"ifnot" バインディング</h1>
	
	<h3 id="purpose">用途</h3>
	<p>
		<code>ifnot</code> バインディングは、式の結果が反転されることを除いて
		<a href="if-binding"><code>if</code> バインディング</a>
		と全く同じものです。詳細は
		<a href="if-binding"><code>if</code> バインディング</a>
		をご覧ください。
	</p>
	
	<h3 id="note_ifnot_is_the_same_as_a_negated_if">(注) "ifnot" は "if" の逆と等価</h3>
	
	<p>次のマークアップは</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;ifnot: someProperty&quot;&gt;...&lt;/div&gt;</pre>
	
	<p>...次のマークアップと全く同じです。</p>
	<pre class="brush: html;">&lt;div data-bind=&quot;if: !someProperty()&quot;&gt;...&lt;/div&gt;</pre>
	
	<p>
		...ですが上記のように <code>someProperty</code> が Observable だとすれば、
		現在の値を取得するために関数として呼び出す必要があります。
	</p>
	<p>
		否定を用いた <code>if</code> の代わりに <code>ifnot</code> を使うただひとつの理由は、
		単に好みの問題といえます。多くの開発者は、<code>ifnot</code> を使うほうが整然としているように感じます。
	</p>
	
	<div class="tail_mini_text">原文は<a href="http://knockoutjs.com/documentation/ifnot-binding.html">こちら</a></div>
	
</article>