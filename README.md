knockoutjs-doc-ja
=================

# KnockoutJS の非公式日本語ドキュメントです。

http://kojs.sukobuto.com

Markdown に対応しました。
docs/articles/*.md が記事ファイルです。
PULL REQ 送っていただければ反映いたします！

# フォーマット

一応、本家と同様に以下のスタイルを一貫させていますが、
特に気にせず書いていただければこちらで整えます。

## ページタイトル... H1

```
# タイトル
```

## 節見出し... H3

節見出しには、ハッシュリンク用の ID を付加します。
Markdown を拡張した記法で、`{#some_id}` のように記述できます。

```
### 見出し {#heading_id}
### 使い方 {#how_to_use}
```

## コード

### HTML

	```html
	<p>hello world!</p>
	```

### JavaScript

	```javascript
	alert('hello world!');
	```

