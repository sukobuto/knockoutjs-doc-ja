/**
 * SyntaxHighlighter Brush for TypeScript
 * Modified from shBrushJScript.js
 *
 * @version
 * 1.0.0 (Oct 03 2012)
 * 
 * @copyright
 * Copyright (C) 2012 Ian Obermiller.
 *
 * @license
 * Dual licensed under the MIT and GPL licenses.
 */
;(function()
{
  // CommonJS
  typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

  function Brush()
  {
    var keywords =
      // Javascript keywords
      'break case catch continue ' +
      'default delete do else false  ' +
      'for function if in instanceof ' +
      'new null return super switch ' +
      'this throw true try typeof var while with ' +

      // TypeScript keywords
      'any bool class constructor declare export ' +
      'extends get implements interface module ' +
      'number private public set static string void ';

    var r = SyntaxHighlighter.regexLib;
    
    var keywordsRegex = new RegExp(this.getKeywords(keywords), 'gm');

    this.regexList = [
      { regex: r.multiLineDoubleQuotedString, css: 'string' },       // double quoted strings
      { regex: r.multiLineSingleQuotedString, css: 'string' },       // single quoted strings
      { regex: r.singleLineCComments,         css: 'comments' },     // one line comments
      { regex: r.multiLineCComments,          css: 'comments' },     // multiline comments
      { regex: /\s*#.*/gm,                    css: 'preprocessor' }, // preprocessor tags like #region and #endregion
      { regex: keywordsRegex,                 css: 'keyword' }       // keywords
      ];
  
    this.forHtmlScript(r.scriptScriptTags);
  };

  Brush.prototype = new SyntaxHighlighter.Highlighter();
  Brush.aliases = ['ts', 'typescript'];

  SyntaxHighlighter.brushes.TypeScript = Brush;

  // CommonJS
  typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
