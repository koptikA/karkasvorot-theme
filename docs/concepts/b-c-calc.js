(function () {
	var $ = function (id) { return document.getElementById(id); };
	var w = $('w'), h = $('h'), type = 'slide';
	var names = { slide: 'Каркас откатных ворот', swing: 'Каркас распашных ворот', wicket: 'Калитка' };
	var f = function (v) { return (+v).toFixed(1).replace('.', ','); };
	function draw() {
		var wk = type === 'wicket', A = +w.value, B = +h.value;
		var total = type === 'slide' ? A * 1.45 : A;
		var s = Math.min(wk ? 150 : 120, 600 / total), W = A * s, H = B * s * 0.85, x = 70, y = 250 - H, fr, br;
		if (type === 'slide') {
			var t = W * 0.45;
			fr = 'M' + x + ' ' + y + 'H' + (x + W) + 'V250H' + x + 'Z M' + (x + W) + ' ' + y + 'L' + (x + W + t) + ' ' + (y + H * 0.4) + 'V250H' + (x + W);
			br = 'M' + x + ' 250L' + (x + W / 2) + ' ' + y + 'L' + (x + W) + ' 250M' + (x + W / 2) + ' ' + y + 'V250';
		} else if (type === 'swing') {
			fr = 'M' + x + ' ' + y + 'H' + (x + W / 2 - 3) + 'V250H' + x + 'Z M' + (x + W / 2 + 3) + ' ' + y + 'H' + (x + W) + 'V250H' + (x + W / 2 + 3) + 'Z';
			br = 'M' + x + ' ' + (y + H / 2) + 'H' + (x + W / 2 - 3) + 'M' + (x + W / 2 + 3) + ' ' + (y + H / 2) + 'H' + (x + W);
		} else {
			fr = 'M' + x + ' ' + y + 'H' + (x + W) + 'V250H' + x + 'Z';
			br = 'M' + x + ' ' + (y + H / 2) + 'H' + (x + W);
		}
		$('fr').setAttribute('d', fr);
		$('br').setAttribute('d', br);
		$('posts').innerHTML = '<rect class="post" x="' + (x - 16) + '" y="' + (y - 14) + '" width="14" height="' + (264 - y) + '"/><rect class="post" x="' + (x + W + 2) + '" y="' + (y - 14) + '" width="14" height="' + (264 - y) + '"/>';
		$('dmA').setAttribute('d', 'M' + x + ' 262V280M' + (x + W) + ' 262V280M' + x + ' 271H' + (x + W));
		$('tA').setAttribute('x', x + W / 2); $('tA').setAttribute('y', 296); $('tA').textContent = 'A = ' + f(A) + ' м';
		$('dmB').setAttribute('d', 'M' + (x - 34) + ' ' + y + 'H' + (x - 24) + 'M' + (x - 34) + ' 250H' + (x - 24) + 'M' + (x - 29) + ' ' + y + 'V250');
		$('tB').setAttribute('x', x - 46); $('tB').setAttribute('y', y + H / 2);
		$('tB').setAttribute('transform', 'rotate(-90 ' + (x - 46) + ' ' + (y + H / 2) + ')');
		$('tB').textContent = 'B = ' + f(B) + ' м';
		$('wv').textContent = f(A) + ' м'; $('hv').textContent = f(B) + ' м'; $('hw').textContent = f(A);
		$('go').textContent = (wk ? 'Рассчитать калитку ' : 'Рассчитать ворота ') + f(A) + ' × ' + f(B) + ' м';
	}
	w.addEventListener('input', draw);
	h.addEventListener('input', draw);
	document.querySelectorAll('.tabs button').forEach(function (b) {
		b.addEventListener('click', function () {
			document.querySelectorAll('.tabs button').forEach(function (x) { x.setAttribute('aria-pressed', x === b); });
			type = b.dataset.t;
			w.max = type === 'wicket' ? 1.5 : 7;
			if (type === 'wicket' && +w.value > 1.5) { w.value = 1; }
			draw();
		});
	});
	$('go').addEventListener('click', function () {
		$('prod').value = names[type];
		$('msg').value = names[type] + ': ширина проёма ' + f(w.value) + ' м, высота ' + f(h.value) + ' м. Город: ';
	});
	draw();
})();
