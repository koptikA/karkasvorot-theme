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
