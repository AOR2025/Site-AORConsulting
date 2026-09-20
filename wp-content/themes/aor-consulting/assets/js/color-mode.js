/* Apply the preference in the head so returning visitors see the right colors immediately. */
(() => {
	'use strict';
	const root = document.documentElement;
	const storageKey = 'aor-color-mode';
	const system = window.matchMedia('(prefers-color-scheme: dark)');
	const validPreference = (value) => value === 'light' || value === 'dark' ? value : null;
	let preference = null;
	try {
		preference = validPreference(window.localStorage.getItem(storageKey));
	} catch {
		// Private browsing or a storage restriction must not prevent switching themes.
	}

	const apply = () => {
		const mode = preference || (system.matches ? 'dark' : 'light');
		root.dataset.aorTheme = mode;
		document.querySelectorAll('[data-aor-theme-toggle]').forEach((button) => {
			button.setAttribute('aria-pressed', String(mode === 'dark'));
			button.title = mode === 'dark' ? 'Passer au thème clair' : 'Passer au thème sombre';
		});
	};
	apply();

	const bindToggle = () => {
		document.querySelectorAll('[data-aor-theme-toggle]').forEach((button) => {
			button.hidden = false;
			button.addEventListener('click', () => {
				preference = root.dataset.aorTheme === 'dark' ? 'light' : 'dark';
				try {
					window.localStorage.setItem(storageKey, preference);
				} catch {
					// The choice still applies to this page when persistence is unavailable.
				}
				apply();
			});
		});
		apply();
	};
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bindToggle, { once: true });
	} else {
		bindToggle();
	}

	system.addEventListener('change', () => {
		if (!preference) apply();
	});
	window.addEventListener('storage', (event) => {
		if (event.key === storageKey || event.key === null) {
			preference = validPreference(event.newValue);
			apply();
		}
	});
})();
