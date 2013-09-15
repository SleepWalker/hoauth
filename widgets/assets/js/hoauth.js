$(function() {
	$('.hoauthWidget a').click(function() {
		var signinWin;
		var screenX     = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
			screenY     = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
			outerWidth  = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
			outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
			width       = 480,
			height      = 680,
			left        = parseInt(screenX + ((outerWidth - width) / 2), 10),
			top         = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
			features    = (
			'width=' + width +
			',height=' + height +
			',left=' + left +
			',top=' + top
			);
 
		signinWin=window.open(this.href,'Login',features);

		if (window.focus) {signinWin.focus()}

		return false;
	});
});