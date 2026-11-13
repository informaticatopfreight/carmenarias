/* Carmen Arias · interacción y movimiento */
(function () {
	'use strict';

	var doc = document.documentElement;
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------- Letra grande ---------- */
	var btnTexto = document.querySelector('[data-texto-grande]');
	if (btnTexto) {
		var sync = function () {
			var on = doc.classList.contains('texto-grande');
			btnTexto.setAttribute('aria-pressed', on ? 'true' : 'false');
			btnTexto.querySelector('span').textContent = on ? 'Letra normal' : 'Letra grande';
		};
		sync();
		btnTexto.addEventListener('click', function () {
			doc.classList.toggle('texto-grande');
			try { localStorage.setItem('ca-texto', doc.classList.contains('texto-grande') ? 'grande' : 'normal'); } catch (e) {}
			sync();
		});
	}

	/* ---------- Menú móvil ---------- */
	var cab = document.querySelector('.cabecera-sitio');
	var btnMenu = document.querySelector('.btn-menu');
	var setAlto = function () {
		if (cab) doc.style.setProperty('--alto-cabecera', Math.round(cab.getBoundingClientRect().bottom) + 'px');
	};
	if (btnMenu) {
		btnMenu.addEventListener('click', function () {
			setAlto();
			var abierto = btnMenu.getAttribute('aria-expanded') === 'true';
			btnMenu.setAttribute('aria-expanded', abierto ? 'false' : 'true');
			doc.classList.toggle('menu-abierto', !abierto);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && doc.classList.contains('menu-abierto')) {
				btnMenu.click();
				btnMenu.focus();
			}
		});
	}
	// Botones de submenú añadidos por PHP; el menú de reserva no los lleva, se crean aquí.
	document.querySelectorAll('.menu-principal .menu-item-has-children').forEach(function (li) {
		var b = li.querySelector(':scope > .submenu-btn');
		if (!b) {
			b = document.createElement('button');
			b.className = 'submenu-btn';
			b.setAttribute('aria-expanded', 'false');
			b.setAttribute('aria-label', 'Abrir submenú');
			b.innerHTML = '<svg class="icono" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
			li.insertBefore(b, li.querySelector(':scope > .sub-menu'));
		}
		b.addEventListener('click', function () {
			var on = li.classList.toggle('abierto');
			b.setAttribute('aria-expanded', on ? 'true' : 'false');
		});
	});

	/* ---------- Sombra de la cabecera al bajar ---------- */
	var lastY = -1;
	function onScrollCab() {
		var y = window.scrollY;
		if (cab && (y > 20) !== (lastY > 20)) cab.classList.toggle('con-sombra', y > 20);
		lastY = y;
	}

	/* ---------- Aparición al hacer scroll ---------- */
	var reveals = document.querySelectorAll('.reveal');
	if ('IntersectionObserver' in window && !reduce) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('visible');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
		reveals.forEach(function (el, i) {
			var r = el.getBoundingClientRect();
			if (r.top < window.innerHeight) {
				el.style.transitionDelay = Math.min(i, 6) * 60 + 'ms';
				el.classList.add('visible');
			} else {
				io.observe(el);
			}
		});
		// Escalonado dentro de rejillas y listas
		document.querySelectorAll('.rejilla, .lista-centros, .cifras, .linea').forEach(function (g) {
			Array.prototype.forEach.call(g.children, function (c, i) { c.style.transitionDelay = (i % 6) * 80 + 'ms'; });
		});
	} else {
		reveals.forEach(function (el) { el.classList.add('visible'); });
	}

	/* ---------- Contadores ---------- */
	if ('IntersectionObserver' in window && !reduce) {
		var co = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (!e.isIntersecting) return;
				co.unobserve(e.target);
				var el = e.target, n = +el.dataset.n, mas = el.hasAttribute('data-mas'), t0 = performance.now();
				(function tick(t) {
					var p = Math.min(1, (t - t0) / 1600), v = Math.round(n * (1 - Math.pow(1 - p, 3)));
					el.textContent = (mas ? '+' : '') + v.toLocaleString('es-ES');
					if (p < 1) requestAnimationFrame(tick);
				})(t0);
			});
		}, { threshold: 0.6 });
		document.querySelectorAll('[data-n]').forEach(function (el) { co.observe(el); });
	}

	/* ---------- Cita que se ilumina palabra a palabra ---------- */
	var cita = document.querySelector('[data-cita]');
	var palabras = [];
	if (cita) {
		cita.innerHTML = cita.textContent.trim().split(/\s+/).map(function (w) { return '<span class="w">' + w + '</span>'; }).join(' ');
		palabras = cita.querySelectorAll('.w');
		if (reduce) palabras.forEach(function (w) { w.classList.add('on'); });
	}

	/* ---------- Línea de la historia y parallax ---------- */
	var linea = document.querySelector('[data-linea]');
	var parallax = document.querySelector('[data-parallax]');

	var ticking = false;
	function onScroll() {
		onScrollCab();
		if (reduce) return;
		var h = window.innerHeight;
		if (cita && palabras.length) {
			var r = cita.getBoundingClientRect();
			var p = (h * 0.85 - r.top) / (r.height + h * 0.35);
			p = Math.max(0, Math.min(1, p));
			var k = Math.round(p * palabras.length);
			palabras.forEach(function (w, i) { w.classList.toggle('on', i < k); });
		}
		if (linea) {
			var t = linea.getBoundingClientRect();
			var lp = (h * 0.9 - t.top) / (t.height + h * 0.4);
			linea.style.setProperty('--p', Math.max(0.02, Math.min(1, lp)).toFixed(3));
		}
		if (parallax && window.scrollY < h * 1.5) {
			parallax.style.transform = 'translateY(' + Math.min(40, window.scrollY * 0.08) + 'px)';
		}
	}
	window.addEventListener('scroll', function () {
		if (!ticking) {
			ticking = true;
			requestAnimationFrame(function () { onScroll(); ticking = false; });
		}
	}, { passive: true });
	window.addEventListener('resize', setAlto);
	onScroll();

	/* ---------- Visor de imágenes ---------- */
	var visor = document.querySelector('.visor');
	if (visor) {
		var vImg = visor.querySelector('img');
		var grupo = [], idx = 0, origen = null;

		var srcGrande = function (img) {
			var a = img.closest('a');
			if (a && /\.(jpe?g|png|webp|gif)(\?.*)?$/i.test(a.getAttribute('href') || '')) return a.href;
			var best = img.currentSrc || img.src, bw = 0;
			(img.getAttribute('srcset') || '').split(',').forEach(function (s) {
				var p = s.trim().split(/\s+/), w = parseInt(p[1], 10);
				if (w > bw && w <= 2000) { bw = w; best = p[0]; }
			});
			return best;
		};
		var mostrar = function (i) {
			idx = (i + grupo.length) % grupo.length;
			vImg.src = srcGrande(grupo[idx]);
			vImg.alt = grupo[idx].alt || '';
			visor.querySelector('.visor-ant').hidden = grupo.length < 2;
			visor.querySelector('.visor-sig').hidden = grupo.length < 2;
		};
		var abrir = function (img) {
			var cont = img.closest('.wp-block-gallery, .gallery');
			grupo = cont ? Array.prototype.slice.call(cont.querySelectorAll('img')) : [img];
			origen = img;
			mostrar(grupo.indexOf(img));
			visor.hidden = false;
			doc.style.overflow = 'hidden';
			visor.querySelector('.visor-cerrar').focus();
		};
		var cerrar = function () {
			visor.hidden = true;
			vImg.removeAttribute('src');
			doc.style.overflow = '';
			if (origen) origen.focus && origen.focus();
		};

		var imgs = document.querySelectorAll('.entrada-contenido .wp-block-gallery img, .entrada-contenido .gallery img, .entrada-contenido .wp-block-image img');
		imgs.forEach(function (img) {
			if (img.closest('a') && !/\.(jpe?g|png|webp|gif)(\?.*)?$/i.test(img.closest('a').getAttribute('href') || '')) return;
			img.classList.add('ampliable');
			img.setAttribute('tabindex', '0');
			img.setAttribute('role', 'button');
			img.addEventListener('click', function (e) { e.preventDefault(); abrir(img); });
			img.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrir(img); } });
		});
		visor.querySelector('.visor-cerrar').addEventListener('click', cerrar);
		visor.querySelector('.visor-ant').addEventListener('click', function () { mostrar(idx - 1); });
		visor.querySelector('.visor-sig').addEventListener('click', function () { mostrar(idx + 1); });
		visor.addEventListener('click', function (e) { if (e.target === visor) cerrar(); });
		document.addEventListener('keydown', function (e) {
			if (visor.hidden) return;
			if (e.key === 'Escape') cerrar();
			if (e.key === 'ArrowLeft') mostrar(idx - 1);
			if (e.key === 'ArrowRight') mostrar(idx + 1);
		});
		// Deslizar con el dedo
		var x0 = null;
		visor.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
		visor.addEventListener('touchend', function (e) {
			if (x0 === null) return;
			var dx = e.changedTouches[0].clientX - x0;
			if (Math.abs(dx) > 50) mostrar(idx + (dx < 0 ? 1 : -1));
			x0 = null;
		});
	}
})();
