/* KARKASVOROT: mobile menu. No dependencies. */
(function () {
	var burger = document.querySelector('.kv-header__burger');
	var nav = document.getElementById('kv-nav');
	if (!burger || !nav) return;
	function set(open) {
		burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		document.body.classList.toggle('kv-menu-open', open);
	}
	burger.addEventListener('click', function () {
		set(burger.getAttribute('aria-expanded') !== 'true');
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && burger.getAttribute('aria-expanded') === 'true') {
			set(false);
			burger.focus();
		}
	});
	nav.addEventListener('click', function (e) {
		if (e.target.closest('a')) set(false);
	});
})();

/* KARKASVOROT: cookie consent, Yandex.Metrika after "Принять" only, goals, order-form focus. */
(function () {
	var id = document.body.getAttribute('data-metrika') || '';
	var banner = document.querySelector('.kv-cookie');
	function load() {
		if (!/^\d+$/.test(id) || window.ym) return;
		(function (m, e, t, r, i, k, a) {
			m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments); };
			m[i].l = 1 * new Date();
			k = e.createElement(t); a = e.getElementsByTagName(t)[0];
			k.async = 1; k.src = r; a.parentNode.insertBefore(k, a);
		})(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
		window.ym(id, 'init', { clickmap: true, trackLinks: true, accurateTrackBounce: true });
	}
	function goal(name) {
		if (choice === 'yes' && window.ym && id) window.ym(id, 'reachGoal', name);
	}
	var choice = null;
	try { choice = localStorage.getItem('kv_consent'); } catch (e) {}
	if (choice === 'yes') load();
	else if (choice !== 'no' && banner) banner.hidden = false;
	if (banner) {
		banner.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-consent]');
			if (!btn) return;
			var v = btn.getAttribute('data-consent');
			try { localStorage.setItem('kv_consent', v); } catch (err) {}
			choice = v;
			banner.hidden = true;
			if (v === 'yes') load();
			else if (window.ym) { window.ym(id, 'destruct'); location.reload(); } // consent withdrawn: drop the loaded Metrika
		});
	}
	document.addEventListener('click', function (e) {
		if (banner && e.target.closest('[data-consent-open]')) {
			banner.hidden = false;
			banner.querySelector('[data-consent]').focus();
		}
		var a = e.target.closest('[data-goal]');
		if (a) goal(a.getAttribute('data-goal'));
	});
	var req = document.querySelector('#zayavka [name="kv_req"]');
	if (req && window.crypto && crypto.getRandomValues) {
		// Cached HTML carries one kv_req for everyone: give each page view its own.
		req.value = Array.prototype.map.call(crypto.getRandomValues(new Uint8Array(16)), function (b) { return (b + 256).toString(16).slice(1); }).join('');
	}
	var done = document.querySelector('[data-goal-auto]');
	if (done) {
		goal(done.getAttribute('data-goal-auto'));
		try { // reload must not count the goal twice
			var u = new URL(location.href);
			u.searchParams.delete('zayavka');
			history.replaceState(null, '', u.toString());
		} catch (e) {}
	}
	var msg = done || document.querySelector('.kv-form__summary');
	if (msg && location.hash === '#zayavka') msg.focus();
})();

/* Product disclosure: native keyboard activation, outside click and Escape. */
(function () {
 var dropdown = document.querySelector('.kv-products__dropdown');
 if (!dropdown) return;
 document.addEventListener('click', function (e) {
  if (!dropdown.contains(e.target) || e.target.closest('.kv-products__list a')) dropdown.open = false;
 });
 document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape' && dropdown.open) {
   dropdown.open = false;
   if (!window.matchMedia('(max-width:56rem)').matches) dropdown.querySelector('summary').focus();
  }
 });
})();

/* Size calculator: type tabs + sliders redraw the SVG scheme; the button fills the theme order form.
   kvCalcGeom() also produced the static no-JS drawing in template-parts/calc.php. */
function kvCalcGeom(type, A, B) {
	var r = function (v) { return Math.round(v * 10) / 10; };
	var s = Math.min(type === 'wicket' ? 150 : 120, 600 / (type === 'slide' ? A * 1.45 : A));
	var x = 70, W = r(A * s), y = r(250 - B * s * 0.85), H = r(250 - y), m = r(y + H / 2), e = r(x + W), fr, br;
	if (type === 'slide') {
		fr = 'M' + x + ' ' + y + 'H' + e + 'V250H' + x + 'ZM' + e + ' ' + y + 'L' + r(x + W * 1.45) + ' ' + r(y + H * 0.4) + 'V250H' + e;
		br = 'M' + x + ' 250L' + r(x + W / 2) + ' ' + y + 'L' + e + ' 250M' + r(x + W / 2) + ' ' + y + 'V250';
	} else if (type === 'swing') {
		fr = 'M' + x + ' ' + y + 'H' + r(x + W / 2 - 3) + 'V250H' + x + 'ZM' + r(x + W / 2 + 3) + ' ' + y + 'H' + e + 'V250H' + r(x + W / 2 + 3) + 'Z';
		br = 'M' + x + ' ' + m + 'H' + r(x + W / 2 - 3) + 'M' + r(x + W / 2 + 3) + ' ' + m + 'H' + e;
	} else {
		fr = 'M' + x + ' ' + y + 'H' + e + 'V250H' + x + 'Z';
		br = 'M' + x + ' ' + m + 'H' + e;
	}
	return { fr: fr, br: br, py: r(y - 14), ph: r(264 - y), p2: r(x + W + 2), dA: 'M70 262V280M' + e + ' 262V280M70 271H' + e,
		tA: r(x + W / 2), dB: 'M36 ' + y + 'H46M36 250H46M41 ' + y + 'V250', tB: m };
}
(function () {
	var root = document.querySelector('.kv-calc');
	if (!root) return;
	var q = function (s) { return root.querySelector(s); }, tabs = root.querySelectorAll('[data-type]');
	var w = q('#kv-calc-w'), h = q('#kv-calc-h'), go = q('.kv-calc__go'), tab = q('[aria-pressed="true"]'), last = '';
	var f = function (v) { return (+v).toFixed(1).replace('.', ','); };
	function set(sel, attrs) { for (var k in attrs) q(sel).setAttribute(k, attrs[k]); }
	function draw() {
		var wk = tab.getAttribute('data-type') === 'wicket', g = kvCalcGeom(tab.getAttribute('data-type'), +w.value, +h.value);
		set('.kv-calc__fr', { d: g.fr }); set('.kv-calc__br', { d: g.br });
		set('.kv-calc__p1', { y: g.py, height: g.ph }); set('.kv-calc__p2', { x: g.p2, y: g.py, height: g.ph });
		set('.kv-calc__dA', { d: g.dA }); set('.kv-calc__tA', { x: g.tA }); set('.kv-calc__dB', { d: g.dB });
		set('.kv-calc__tB', { y: g.tB, transform: 'rotate(-90 28 ' + g.tB + ')' });
		q('.kv-calc__tA').textContent = 'A = ' + f(w.value) + ' м';
		q('.kv-calc__tB').textContent = 'B = ' + f(h.value) + ' м';
		[w, h].forEach(function (el) {
			el.setAttribute('aria-valuetext', f(el.value) + ' м');
			q('output[for="' + el.id + '"]').textContent = f(el.value) + ' м';
		});
		q('.kv-calc__w').textContent = f(w.value);
		q('.kv-calc__what').textContent = wk ? 'Калитка' : 'Ворота';
		go.textContent = (wk ? 'Рассчитать калитку ' : 'Рассчитать ворота ') + f(w.value) + ' × ' + f(h.value) + ' м';
	}
	w.addEventListener('input', draw);
	h.addEventListener('input', draw);
	[].forEach.call(tabs, function (b) {
		b.addEventListener('click', function () {
			tab = b;
			[].forEach.call(tabs, function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
			w.max = b.getAttribute('data-max');
			if (+w.value > +w.max) w.value = 1;
			draw();
		});
	});
	go.addEventListener('click', function () {
		var product = document.getElementById('kv-f-product'), comment = document.getElementById('kv-f-comment');
		var name = tab.getAttribute('data-product'), line = name + ': ширина проёма ' + f(w.value) + ' м, высота ' + f(h.value) + ' м. Город: ';
		if (product) product.value = name;
		if (comment) { // replace our previous line, keep what the visitor typed
			var v = comment.value, rest = last && v.indexOf(last) === 0 ? v.slice(last.length) : (v ? '\n' + v : '');
			comment.value = line + rest;
			last = line;
		}
	});
	draw();
})();

/* Orbit gallery: page scroll swings each work out on an arc as the next swings in.
   Off without JS and with prefers-reduced-motion: the works stay a plain grid. ponytail: no snap. */
(function () {
	var sec = document.querySelector('.kv-orbit');
	if (!sec || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	var items = [].slice.call(sec.querySelectorAll('.kv-orbit__item'));
	var titles = sec.querySelectorAll('.kv-orbit__title');
	var count = sec.querySelector('.kv-orbit__count b'), cap = sec.querySelector('.kv-orbit__cap');
	var n = items.length, STEP = 26, cur = 0, active = -1, ticking = false;
	var tints = ['#15110f', '#0f1316', '#141210', '#101315', '#12140f', '#16100f', '#0f1214'];
	if (n < 2) return;
	sec.classList.add('is-orbit');
	function progress() {
		var span = sec.offsetHeight - window.innerHeight;
		return Math.min(1, Math.max(0, -sec.getBoundingClientRect().top / span)) * (n - 1);
	}
	function place(p) {
		var mobile = window.matchMedia('(max-width:56rem)').matches, R = mobile ? window.innerWidth * 1.15 : Math.min(window.innerHeight * 1.05, 900);
		items.forEach(function (el, i) {
			var d = i - p, ad = Math.abs(d), a, x, y;
			if (mobile) { a = (-90 + d * STEP) * Math.PI / 180; x = Math.cos(a) * R; y = Math.sin(a) * R + R; }
			else { a = (180 - d * STEP) * Math.PI / 180; x = Math.cos(a) * R + R; y = Math.sin(a) * R; } // next rises from below
			el.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px) rotate(' + (d * STEP * 0.6).toFixed(2) + 'deg) scale(' + Math.max(0.45, 1 - ad * 0.28).toFixed(3) + ')';
			el.style.opacity = ad > 2.2 ? 0 : 1 - ad * 0.35;
			el.style.zIndex = 10 - Math.round(ad);
			el.style.filter = 'brightness(' + (1 - Math.min(ad, 1) * 0.45).toFixed(2) + ')';
		});
		var k = Math.round(p);
		if (k !== active) {
			active = k;
			[].forEach.call(titles, function (t, i) { t.classList.toggle('is-on', i === k); });
			count.textContent = String(k + 1).padStart(2, '0');
			cap.textContent = items[k].getAttribute('data-title');
			sec.style.setProperty('--tint', tints[k % tints.length]);
		}
	}
	function tick() {
		var target = progress();
		cur += (target - cur) * 0.12;
		if (Math.abs(target - cur) < 0.002) cur = target;
		place(cur);
		ticking = cur !== target;
		if (ticking) requestAnimationFrame(tick);
	}
	function kick() { if (!ticking) { ticking = true; requestAnimationFrame(tick); } }
	window.addEventListener('scroll', kick, { passive: true });
	window.addEventListener('resize', kick);
	items.forEach(function (el, i) { // keyboard focus brings that work to the centre
		el.addEventListener('focus', function () {
			window.scrollTo(0, sec.getBoundingClientRect().top + window.pageYOffset + (sec.offsetHeight - window.innerHeight) * i / (n - 1));
		});
	});
	cur = progress();
	place(cur);
})();
