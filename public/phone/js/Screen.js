(function(doc, win) {
	var viewport = document.querySelector("meta[name=viewport]");
	//下面是根据设备像素设置viewport
	if (window.devicePixelRatio == 1) {
		viewport.setAttribute('content', 'width=device-width,initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no');
	}
	if (window.devicePixelRatio == 2) {
		viewport.setAttribute('content', 'width=device-width,initial-scale=0.5, maximum-scale=0.5, minimum-scale=0.5, user-scalable=no');
	}
	if (window.devicePixelRatio == 3) {
		viewport.setAttribute('content', 'width=device-width,initial-scale=0.3333333333333333, maximum-scale=0.3333333333333333, minimum-scale=0.3333333333333333, user-scalable=no');
	}
	var docEl = doc.documentElement,
		isIOS = navigator.userAgent.match(/iphone|ipod|ipad/gi),
		dpr = isIOS ? Math.min(win.devicePixelRatio, 3) : 1,
		dpr = window.top === window.self ? dpr : 1, //?iframe???,????
		dpr = 1, // ????IFRAME,???1
		scale = 1 / dpr,
		resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize';
	docEl.dataset.dpr = win.devicePixelRatio;
	if (navigator.userAgent.match(/iphone/gi) && screen.width == 375 && win.devicePixelRatio == 2) {
		docEl.classList.add('iphone6')
	}
	if (navigator.userAgent.match(/iphone/gi) && screen.width == 414 && win.devicePixelRatio == 3) {
		docEl.classList.add('iphone6p')
	}
	var metaEl = doc.createElement('meta');
	metaEl.name = 'viewport';
	metaEl.content = 'initial-scale=' + scale + ',maximum-scale=' + scale + ', minimum-scale=' + scale;
	docEl.firstElementChild.appendChild(metaEl);
	var recalc = function() {
		var width = docEl.clientWidth;
		if (width / dpr > 750) {
			width = 750 * dpr;
		}
		docEl.style.fontSize = 100 * (width / 750) + 'px';
	};
	recalc()
	if (!doc.addEventListener) return;
	// win.addEventListener(resizeEvt, recalc, false);
})(document, window);
