/* Orbit gallery: page scroll swings each work out on an orbit as the next swings in.
   No libraries: sticky section + one rAF loop. ponytail: no snap — add when users ask. */
(function () {
	var sec = document.querySelector('.orbit');
	if (!sec) { return; }
	var items = [].slice.call(sec.querySelectorAll('.orbit__item'));
	var titles = [].slice.call(sec.querySelectorAll('.orbit__title'));
	var count = sec.querySelector('.orbit__count b');
	var cap = sec.querySelector('.orbit__cap');
	var n = items.length, STEP = 26, cur = 0, target = 0, active = -1;
	var tints = ['#15110f', '#0f1316', '#141210', '#101315', '#12140f', '#16100f', '#0f1214'];
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function progress() {
		var r = sec.getBoundingClientRect(), span = sec.offsetHeight - window.innerHeight;
		return Math.min(1, Math.max(0, -r.top / span)) * (n - 1);
	}
	function place(p) {
		var mobile = window.innerWidth < 900;
		var R = mobile ? window.innerWidth * 1.15 : Math.min(window.innerHeight * 1.05, 900);
		items.forEach(function (el, i) {
			var d = i - p, ad = Math.abs(d), x, y, a;
			if (mobile) { a = (-90 + d * STEP) * Math.PI / 180; x = Math.cos(a) * R; y = Math.sin(a) * R + R; }
			else { a = (180 - d * STEP) * Math.PI / 180; x = Math.cos(a) * R + R; y = Math.sin(a) * R; } // vertical arc: next rises from below, previous leaves upward
			var s = Math.max(0.45, 1 - ad * 0.28);
			el.style.transform = 'translate(' + x + 'px,' + y + 'px) rotate(' + d * STEP * 0.6 + 'deg) scale(' + s + ')';
			el.style.opacity = ad > 2.2 ? 0 : 1 - ad * 0.35;
			el.style.zIndex = 10 - Math.round(ad);
			el.style.filter = 'brightness(' + (1 - Math.min(ad, 1) * 0.45) + ')';
		});
		var k = Math.round(p);
		if (k !== active) {
			active = k;
			titles.forEach(function (t, i) { t.classList.toggle('is-on', i === k); });
			count.textContent = String(k + 1).padStart(2, '0');
			cap.textContent = items[k].querySelector('img').alt;
			sec.style.setProperty('--tint', tints[k % tints.length]);
		}
	}
	function tick() {
		target = progress();
		cur += (target - cur) * (reduce ? 1 : 0.12);
		if (Math.abs(target - cur) < 0.001) { cur = target; }
		place(cur);
		requestAnimationFrame(tick);
	}
	tick();
})();
