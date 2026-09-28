/**
 * اسکریپت‌های قالب بازار
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		/* سایه هدر هنگام اسکرول */
		var header = document.getElementById('bz-header');
		var toTop = document.getElementById('bz-to-top');
		window.addEventListener('scroll', function () {
			var y = window.scrollY;
			if (header) { header.classList.toggle('is-stuck', y > 8); }
			if (toTop) { toTop.classList.toggle('is-visible', y > 480); }
		}, { passive: true });

		/* بازگشت به بالا */
		if (toTop) {
			toTop.addEventListener('click', function () {
				window.scrollTo({ top: 0, behavior: 'smooth' });
			});
		}

		/* منوی موبایل */
		var burger = document.getElementById('bz-burger');
		var drawer = document.getElementById('bz-drawer');
		var closeBtn = document.getElementById('bz-drawer-close');

		function openDrawer() {
			if (!drawer) { return; }
			drawer.classList.add('is-open');
			document.body.style.overflow = 'hidden';
			if (burger) { burger.setAttribute('aria-expanded', 'true'); }
		}
		function closeDrawer() {
			if (!drawer) { return; }
			drawer.classList.remove('is-open');
			document.body.style.overflow = '';
			if (burger) { burger.setAttribute('aria-expanded', 'false'); }
		}
		if (burger) { burger.addEventListener('click', openDrawer); }
		if (closeBtn) { closeBtn.addEventListener('click', closeDrawer); }
		if (drawer) {
			drawer.addEventListener('click', function (e) {
				if (e.target === drawer) { closeDrawer(); }
			});
		}
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { closeDrawer(); }
		});

		/* دکمه‌های + و − برای تعداد محصول */
		document.querySelectorAll('div.quantity').forEach(function (qty) {
			if (qty.querySelector('.bz-qty-btn')) { return; }
			var input = qty.querySelector('input.qty');
			if (!input) { return; }

			var minus = document.createElement('button');
			minus.type = 'button';
			minus.className = 'bz-qty-btn';
			minus.setAttribute('aria-label', 'کاهش');
			minus.textContent = '−';

			var plus = document.createElement('button');
			plus.type = 'button';
			plus.className = 'bz-qty-btn';
			plus.setAttribute('aria-label', 'افزایش');
			plus.textContent = '+';

			function step(dir) {
				var val = parseFloat(input.value) || 0;
				var stepVal = parseFloat(input.step) || 1;
				var min = input.min ? parseFloat(input.min) : 0;
				var max = input.max ? parseFloat(input.max) : Infinity;
				val += dir * stepVal;
				val = Math.max(min, Math.min(max, val));
				input.value = val;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
			minus.addEventListener('click', function () { step(-1); });
			plus.addEventListener('click', function () { step(1); });

			qty.classList.add('bz-qty');
			qty.insertBefore(minus, input);
			qty.appendChild(plus);
		});

		/* به‌روزرسانی شمارنده سبد خرید پس از افزودن محصول (ووکامرس آجاکس) */
		if (document.body.classList.contains('woocommerce') || true) {
			jQuery && jQuery(document.body).on('added_to_cart removed_from_cart', function () {
				/* فرگمنت‌ها خودکار جایگزین می‌شوند؛ فقط پالس بصری */
				document.querySelectorAll('.bz-cart-count').forEach(function (el) {
					el.animate && el.animate([
						{ transform: 'scale(1)' },
						{ transform: 'scale(1.4)' },
						{ transform: 'scale(1)' }
					], { duration: 320 });
				});
			});
		}
	});
})();
