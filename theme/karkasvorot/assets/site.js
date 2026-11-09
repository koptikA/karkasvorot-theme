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
