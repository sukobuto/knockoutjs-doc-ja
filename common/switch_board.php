<div id="switch_board">
<div id="toggle_aside">
  <div>
		<span class="label"></span><br>
		side menu
	</div>
</div>
</div>

<script type="text/javascript">
$(function() {
	var target = $('#content_wrap');
	var toggleAside = $('#toggle_aside');
	var label = toggleAside.find('.label');
	var label_htmls = {
		open: '<i class="icon-chevron-right"></i> OPEN',
		close: '<i class="icon-chevron-left"></i> CLOSE'
	};
	var closed = false;
	toggleAside.click(function() {
		if (closed) {	// open
			target.removeClass('aside_closed');
			label.html(label_htmls.close);
			closed = false;
		} else {		// close
			target.addClass('aside_closed');
			label.html(label_htmls.open);
			closed = true;
		}
	});
	label.html(label_htmls.close);
});
</script>
