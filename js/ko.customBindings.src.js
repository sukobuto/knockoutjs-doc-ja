/**
 * depend on...
 * 	- jquery 1.7+
 * 	- jquery caret
 *
 * @author Kenta Suzuki
 * @copyright 2012 sukobuto.com
 */

(function($) {

	var dummyCallback = function(){};
	var uo = ko.utils.unwrapObservable;	// alias

	/**
	 * ハッシュオブジェクトおよびその各要素が ko.observable であればアンラップする
	 * @param mixed hash アンラップするハッシュオブジェクト（ハッシュオブジェクトでなくても可）
	 * @returns mixed アンラップ済みのハッシュオブジェクトまたは値
	 */
	function uho(hash) {
		hash = ko.utils.unwrapObservable(hash);
		if (typeof hash != "object") return hash;
		for (var name in hash)
			hash[name] = ko.utils.unwrapObservable(hash[name]);
		return hash;
	}

	/**
	 * selectorに合致する要素を検索する。
	 * まずstartingElementを起点として、一番近い親要素から順に検索する。
	 * 見つからなければ、DOM全体から再検索する。
	 *
	 * 直近の親要素をヒットさせたい場合は要素名またはクラス名、
	 * ある特定の要素をヒットさせたい場合はIDでの指定が有効。
	 *
	 * @param string selector 検索のためのCSSセレクタ
	 * @param jQuery startingElement 起点とするjQueryオブジェクト
	 * @returns ヒットした場合はjQueryオブジェクト, それ以外はfalse
	 */
	function searchElementsForSmartBinding(selector, startingElement) {
		var candidates = startingElement.closest(selector);
		if (candidates.length > 0) return candidates;
		candidates = $(selector);
		if (candidates.length > 0) return candidates;
		return false;
	};

	/**
	 * Custom binding [ enterKey ] (Static)
	 * Enterキー押下に対する振る舞いをバインドする
	 *
	 * binding-type: function/object
	 * binding-object: {
	 * 	command:		[default:function] Enterキー押下で呼び出されるコールバック関数を指定する。
	 * 	event:			Enterキー押下イベントの種別（'keydown', 'keyup', 'keypress'）。
	 * 					デフォルトは 'keypress'。
	 * 	acceptInput:	true=テキストボックスに対して改行の入力を許可する。
	 * 					デフォルトは textarea なら true, それ以外なら false。
	 * 	moveFocus:		true=Enterキー押下によるフォーカス遷移を実現する。
	 * 	submitForm:		true=Enterキー押下によるフォームの送信を許可する。
	 * 					デフォルトは false。
	 * 	withShift:		Shift + Enter 押下で呼び出されるコールバック関数を指定する。
	 * 	withCtrl:		Ctrl + Enter 押下で呼び出されるコールバック関数を指定する。
	 * 	withCtrlShift:	Ctrl + Shift + Enter 押下で呼び出されるコールバック関数を指定する。
	 * 	bubbling:		false=バブリングを停止する
	 * }
	 */
	ko.bindingHandlers['enterKey'] = {
		init : function(element, valueAccessor, allBindingsAccessor) {
			var value = uho(valueAccessor());
			var elm = $(element);
			var isTextarea = elm.is('textarea');
			var isTextbox = elm.is('input[type="text"]') || elm.is('input[type="password"]');
			var config = ko.utils.extend({
				event: 'keypress',
				acceptInput: elm.is('textarea') ? true : false,
				moveFocus: false,
				submitForm: false,
				command: dummyCallback,
				withShift: dummyCallback,
				withCtrl: dummyCallback,
				withCtrlShift: dummyCallback,
				bubbling: true
			}, (typeof value == "function" ? { command: value } : value));
			var text = allBindingsAccessor().value || false;
			if (text) text = ko.isObservable(text) ? text : false;
			$(element)[config.event](function(e) {
				if ((e.keyCode ? e.keyCode : e.which ? e.which : e.charCode) == 13) {
					if (config.bubbling === false) {
						if (e.stopPropagation) e.stopPropagation();
						else e.cancelBubble = true;
					}
					config.command(text);
					if (e.shiftKey && e.ctrlKey){
						config.withCtrlShift();
						e.preventDefault();
						return false;
					} else if (e.shiftKey) {
						config.withShift();
						e.preventDefault();
						return false;
					} else if (e.ctrlKey) {
						config.withCtrl();
						e.preventDefault();
						return false;
					}
					if (!config.moveFocus && text && config.acceptInput && isTextbox) {
						var str = elm.val();
						var caretPos = elm.caret().start;
						var left = str.substring(0, caretPos - 1);
						var right = str.substring(caretPos);
						elm.val(left + "\n" + right);
						caretPos = elm.val().length - right.length;
						elm.caret(caretPos, caretPos);
					}
					if (!config.moveFocus && text && !config.acceptInput && isTextarea) {
						var str = elm.val();
						var caretPos = elm.caret().start;
						var left = str.substring(0, caretPos - 2);
						if (left.substring(caretPos - 2) == "\r")
							left = left.substring(0, caretPos - 3);
						var right = str.substring(caretPos);
						elm.val(left + right);
						caretPos = elm.val().length - right.length;
						elm.caret(caretPos, caretPos);
					}
					if (config.moveFocus && element.form) {
						var i;
						var elements = element.form.elements;
						for (i = 0; i < elements.length; i++) {
							if (element == elements[i]) break;
						}
						i = (i + (e.shiftKey ? -1 : 1)) % elements.length;
						if (i < 0) i += elements.length;
						elements[i].focus();
					}
					if (!config.submitForm && isTextbox) {
						e.preventDefault();
						return false;
					} else if (config.submitForm && isTextarea) {
						element.form.submit();
					}
				}
			});
		}
	};

	/**
	 * Custom binding [ tabKey ] (Static)
	 * Tabキー押下に対する振る舞いをバインドする
	 *
	 * binding-type: function/object
	 * binding-object: {
	 * 	command:		[default:function] Tabキー押下で呼び出されるコールバック関数を指定する。
	 * 	acceptInput:	true=テキストボックスに対してTabの入力を許可するとともに
	 * 					Tabキー押下によるフォーカス遷移を抑止する。
	 * 					デフォルトは false。
	 * 	tabString:		Tabキー押下によりテキストボックスに入力される文字列を指定する。
	 * 					acceptInput が true のときのみ有効。
	 * 					デフォルトは "	"（タブ文字）。
	 * }
	 */
	ko.bindingHandlers['tabKey'] = {
		init: function(element, valueAccessor, allBindingsAccessor) {
			var value = uho(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				acceptInput: false,
				tabString: "	",
				command: dummyCallback
			}, (typeof value == "function" ? { command: value } : value));
			var text = allBindingsAccessor().value || false;
			if (text) text = ko.isObservable(text) ? text : false;
			$(element).keydown(function(e) {
				if ((e.keyCode ? e.keyCode : e.which ? e.which : e.charCode) == 9) {
					config.command(text);
					if (text && config.acceptInput) {
						var str = elm.val();
						var caretPos = elm.caret().start;
						var left = str.substring(0, caretPos);
						var right = str.substring(caretPos);
						elm.val(left + config.tabString + right);
						caretPos = elm.val().length - right.length;
						elm.caret(caretPos, caretPos);
						e.preventDefault();
						return false;
					}
				}
			});
		}
	};

	/**
	 * Custom binding [ escKey ] (Static)
	 * Escキー押下に対する振る舞いをバインドする
	 *
	 * binding-type: function/object
	 * binding-object: {
	 * 	command:		[default:function]Escキー押下で呼び出されるコールバック関数を指定する。
	 * }
	 */
	ko.bindingHandlers['escKey'] = {
		init: function(element, valueAccessor) {
			var value = uho(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				command: dummyCallback
			}, (typeof value == 'function' ? { command: value } : value));
			elm.keydown(function(e) {
				if ((e.keyCode ? e.keyCode : e.which ? e.which : e.charCode) == 27) {
					config.command();
				}
			});
		}
	};

	/**
	 * Custom binding [ fadeVisible ]
	 * 可視状態をフェード遷移としてバインドする
	 *
	 * binding-type: boolean(可視)/object
	 * binding-object: {
	 * 	visible:		[default:boolean] true=可視（フェードイン）, false=不可視（フェードアウト）
	 * 	speed:			フェードにかかる時間。'slow', 'normal', 'fast'
	 * 					またはミリ秒にて指定する。デフォルトは'normal'。
	 * 	complete:		フェード終了後に実行するコールバックを指定する。
	 * 	showDelay:		フェードイン開始までの待ち時間をミリ秒で指定する。
	 * 	hideDelay:		フェードアウト開始までの待ち時間をミリ秒で指定する。
	 * 	showOnHover:	マウスオーバーを判定するためのCSSセレクタを指定する。
	 * 					CSSセレクタにより選択された要素に対するマウスオーバーにより可視化する。
	 * 					要素はまず近い親要素から検索され、見つからなければDOM全体から検索される。
	 * 					デフォルトは false。（visible, hideOnHoverとの併用は不可）
	 * 	hideOnHover:	マウスオーバーを判定するためのCSSセレクタを指定する。
	 * 					CSSセレクタにより選択された要素に対するマウスオーバーにより非表示にする。
	 * 					要素はまず近い親要素から検索され、見つからなければDOM全体から検索される。
	 * 					デフォルトは false。（visible, showOnHoverとの併用は不可）
	 * }
	 */
	ko.bindingHandlers['fadeVisible'] = {
		init: function(element, valueAccessor) {
			var value = uo(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				visible: undefined,
				showOnHover: false,
				hideOnHover: false,
				speed: 'normal',
				complete: dummyCallback,
				showDelay: 0,
				hideDelay: 0,
				enableHover: true
			}, ( typeof value == 'boolean' ? {visible: value} : value ));
			if (uo(config.visible) !== undefined) {
				uo(config.visible) ? elm.show() : elm.hide();
				return;
			}
			var timer = false;	// showDelay, hideDelay 用のタイマー識別子
			var clearTimer = function() {
				if (timer) {
					clearTimeout(timer);
					timer = false;
				}
			};
			var show = function() {		// 表示するための振る舞いを定義
				elm.fadeIn(uo(config.speed), uo(config.complete));
			};
			var hide = function() {		// 非表示にするための振る舞いを定義
				elm.fadeOut(uo(config.speed), uo(config.complete));
			};
			var showExec = function() {
				clearTimer();
				var delay = uo(config.showDelay);
				if (delay) timer = setTimeout(show, delay);
				else show();
			};
			var hideExec = function() {
				clearTimer();
				if (elm.is(":animated")) elm.stop();
				var delay = uo(config.hideDelay);
				if (delay) timer = setTimieout(hide, delay);
				else hide();
			};
			var target;			// showOnHover, hideOnHover の判定対象となるjQueryオブジェクト
			if (config.showOnHover) {
				elm.hide();
				target = searchElementsForSmartBinding(config.showOnHover, elm);
				if (target) target.mouseenter(function() {
					if (uo(config.enableHover)) showExec();
				}).mouseleave(hideExec);
			} else if (config.hideOnHover) {
				target = searchElementsForSmartBinding(config.hideOnHover, elm);
				if (target) target.mouseenter(function() {
					if (uo(config.enableHover)) hideExec();
				}).mouseleave(showExec);
			}
		},
		update: function(element, valueAccessor) {
			var value = uho(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				visible: undefined,
				speed: 'normal',
				complete: dummyCallback,
				showDelay: 0,
				hideDelay: 0
			}, ( typeof value == 'boolean' ? {visible: value} : value ));
			if (config.visible === undefined) return;
			var func = config.visible
				? function() { elm.fadeIn(config.speed, config.complete); }
				: function() { elm.fadeOut(config.speed, config.complete); };
			var delay = value.visible
				? config.showDelay
				: config.hideDelay;
			var timer = $.data(element, 'ko.fadeVisible.update.timer');
			if (timer) clearTimeout(timer);
			if (delay > 0) $.data(element, 'ko.fadeVisible.update.timer', setTimeout(func, delay));
			else func();
	    }
	};

	/**
	 * Custom binding [ slideVisible ]
	 * 可視状態をスライドダウン・スライドダウンアニメーションでバインドする
	 *
	 * binding-type: boolean(可視)/object
	 * binding-object: {
	 * 	visible:		[default:boolean] true=可視（スライドダウン）, false=不可視（スライドアップ）
	 * 	speed:			スライドにかかる時間。'slow', 'normal', 'fast'
	 * 					またはミリ秒にて指定する。デフォルトは'normal'。
	 * 	complete:		スライド終了後に実行するコールバックを指定する。
	 * 	showDelay:		スライドダウン開始までの待ち時間をミリ秒で指定する。
	 * 	hideDelay:		スライドアップ開始までの待ち時間をミリ秒で指定する。
	 * 	showOnHover:	マウスオーバーを判定するためのCSSセレクタを指定する。
	 * 					CSSセレクタにより選択された要素に対するマウスオーバーにより可視化する。
	 * 					要素はまず近い親要素から検索され、見つからなければDOM全体から検索される。
	 * 					デフォルトは false。（visible, hideOnHoverとの併用は不可）
	 * 	hideOnHover:	マウスオーバーを判定するためのCSSセレクタを指定する。
	 * 					CSSセレクタにより選択された要素に対するマウスオーバーにより非表示にする。
	 * 					要素はまず近い親要素から検索され、見つからなければDOM全体から検索される。
	 * 					デフォルトは false。（visible, showOnHoverとの併用は不可）
	 * 	enableHover:	true= showOnHover/hideOnHover の挙動を有効化する。
	 * 					デフォルトは true。動的バインド可能。
	 * }
	 */
	ko.bindingHandlers['slideVisible'] = {
		init: function(element, valueAccessor) {
			var value = uo(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				showOnHover: false,
				hideOnHover: false,
				speed: 'normal',
				complete: dummyCallback,
				showDelay: 0,
				hideDelay: 0,
				enableHover: true
			}, ( typeof value == 'boolean' ? {visible: value} : value ));
			if (uo(config.visible) !== undefined) {
				uo(config.visible) ? elm.show() : elm.hide();
				return;
			}
			var timer = false;	// showDelay, hideDelay 用のタイマー識別子
			var clearTimer = function() {
				if (timer) {
					clearTimeout(timer);
					timer = false;
				}
			};
			var show = function() {		// 表示するための振る舞いを定義
				elm.slideDown(uo(config.speed), uo(config.complete));
			};
			var hide = function() {		// 非表示にするための振る舞いを定義
				elm.slideUp(uo(config.speed), uo(config.complete));
			};
			var showExec = function() {
				clearTimer();
				var delay = uo(config.showDelay);
				if (delay) timer = setTimeout(show, delay);
				else show();
			};
			var hideExec = function() {
				clearTimer();
				if (elm.is(":animated")) elm.stop();
				var delay = uo(config.hideDelay);
				if (delay) timer = setTimieout(hide, delay);
				else hide();
			};
			var target;			// showOnHover, hideOnHover の判定対象となるjQueryオブジェクト
			if (config.showOnHover) {
				elm.hide();
				target = searchElementsForSmartBinding(config.showOnHover, elm);
				if (target) target.mouseenter(function() {
					if (uo(config.enableHover)) showExec();
				}).mouseleave(hideExec);
			} else if (config.hideOnHover) {
				target = searchElementsForSmartBinding(config.hideOnHover, elm);
				if (target) target.mouseenter(function() {
					if (uo(config.enableHover)) hideExec();
				}).mouseleave(showExec);
			}
		},
		update: function(element, valueAccessor) {
			var value = uho(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				visible: undefined,
				speed: 'normal',
				complete: dummyCallback,
				showDelay: 0,
				hideDelay: 0
			},( typeof value == 'boolean' ? {visible: value} : value ));
			if (config.visible === undefined) return;
			var func = config.visible
				? function() { elm.slideDown(config.speed, config.complete); }
				: function() { elm.slideUp(config.speed, config.complete); };
			var delay = value.visible
				? config.showDelay
				: config.hideDelay;
			var timer = $.data(element, 'ko.slideVisible.update.timer');
			if (timer) clearTimeout(timer);
			if (delay > 0) $.data(element, 'ko.slideVisible.update.timer', setTimeout(func, delay));
			else func();
	    }
	};

	/**
	 * Custom binding [ slideState ]
	 * 要素の可視状態を、ポジション移動アニメーションでバインドする
	 *
	 * binding-type: object
	 * binding-object: {
	 * 	statuses:	ステータスIDをキーとする、位置情報や可視状態を定義したハッシュオブジェクト
	 * 		'status': {
	 * 			left:		left, top, right, bottom はそれぞれ必要なものを適宜指定する。
	 * 			top:
	 * 			right:
	 * 			bottom:
	 * 			visible:	アニメーション終了後の可視状態を指定する。
	 * 			speed:		アニメーションにかかる時間。'slow', 'normal', 'fast'
	 * 						またはミリ秒にて指定する。デフォルトは'normal'。
	 * 			delay:		アニメーション開始までの待ち時間をミリ秒で指定する。
	 * 			complete:	アニメーション終了後に実行するコールバックを指定する。
	 * 		}
	 * 	status:		現在のステータスID
	 * }
	 */
	ko.bindingHandlers['slideState'] = {
		init: function(element, valueAccessor) {
			var value = valueAccessor();
			if (!value.status || !value.statuses) return;
			var statuses = {};
			for (var id in value.statuses) {
				var st = value.statuses[id];
				statuses[id] = {};
				statuses[id].params = {};
				if (typeof st.left == 'number') {
					statuses[id].params.left = st.left + 'px';
					delete st.left;
				}
				if (typeof st.top == 'number') {
					statuses[id].params.top = st.top + 'px';
					delete st.top;
				}
				if (typeof st.right == 'number') {
					statuses[id].params.right = st.right + 'px';
					delete st.right;
				}
				if (typeof st.bottom == 'number') {
					statuses[id].params.bottom = st.bottom + 'px';
					delete st.bottom;
				}
				statuses[id].options = ko.utils.extend({
					visible: true,
					speed: 'normal',
					delay: 0,
					complete: dummyCallback
				}, st);
				if (st.visible === false) {
					statuses[id].options.complete = (function() {
						var complete = statuses[id].options.complete;
						var elm = $(element);
						return function() {
							elm.hide();
							complete();
						};
					})();
				}
			}
			$.data(element, 'ko.slideState.statuses', statuses);
			// initialize view
			var status = uo(valueAccessor().status);
			if (!status || !statuses[status]) return;
			var st = statuses[status];
			var elm = $(element);
			elm.css(st.params);
			st.options.visible ? elm.show() : elm.hide();
		},
		update: function(element, valueAccessor) {
			var statuses = $.data(element, 'ko.slideState.statuses');
			var status = uo(valueAccessor().status);
			if (!statuses || !status || !statuses[status]) return;
			var st = statuses[status];
			var elm = $(element);
			if (!st.options.visible && elm.is(':hidden')) return;
			var options = {
				duration: st.options.speed,
				complete: st.options.complete,
				queue: false
			};
			var doAnimate = function() {
				if (st.options.visible) elm.show();
				elm.animate(st.params, options);
			};
			if (st.options.delay > 0) setTimeout(doAnimate, st.options.delay);
			else doAnimate();
		}
	};

	/**
	 * Custom binding [ hoverOpacity ]
	 * マウスホバーによる不透明度の変化をバインドする
	 *
	 * binding-type: boolean/object
	 * binding-object: {
	 * 	over:		マウスが要素に乗った状態のOpacity値。
	 * 				デフォルトは 0.7。
	 * 	out:		マウスが要素に乗っていない状態のOpacity値。
	 * 				デフォルトは 1。
	 * }
	 */
	ko.bindingHandlers['hoverOpacity'] = {
		init: function(element, valueAccessor) {
			var value = valueAccessor();
			if (!value) return;
			var config = ko.utils.extend({
				over: 0.7,
				out: 1
			}, typeof value == "object" ? value : {} );
			var elm = $(element);
			elm.mouseover(function() {
				elm.css('opacity', uo(config.over));
			}).mouseout(function() {
				elm.css('opacity', uo(config.out));
			});
		}
	};

	/**
	 * Custom binding [ outerClick ]
	 * 要素の外側のクリックイベントに対してコマンドをバインドする
	 *
	 * binding-type: function/object
	 * binding-object: {
	 * 	command:	[default:function] 要素の外側がクリックされた際に呼び出されるコールバック関数を指定する。
	 * 	enable:		true = commandの実行を可能にする。動的バインド可能。
	 * 				デフォルトは true
	 * 	inners:		内側として扱う要素をCSSセレクタで指定する。
	 * 				要素はまず近い親要素から検索され、見つからなければDOM全体から検索される。
	 * 				デフォルトは false（指定なし）
	 *
	 * }
	 */
	ko.bindingHandlers['outerClick'] = {
		init: function(element, valueAccessor) {
			var value = uo(valueAccessor());
			var elm = $(element);
			var config = ko.utils.extend({
				command: dummyCallback,
				enable: true,
				inners: false
			}, (typeof value == 'function' ? {command: value} : value));
			var isInner = false;
			// Bind click event to suppress
			function onInnerClick(e){
				isInner = true;
			};
			elm.click(onInnerClick);
			if (config.inners) {
				var inners = searchElementsForSmartBinding(config.inners, elm);
				if (inners.length > 0) inners.each(function(inner) {
					$(inner).click(onInnerClick);
				});
			}
			// Bind click elsewhere
			$(document).click(function(e) {
				if (isInner) {
					isInner = false;
					return;
				}
				if (uo(config.enable)) config.command.call(element, ko.dataFor(element), e);
			});
		}
	};

})(jQuery);