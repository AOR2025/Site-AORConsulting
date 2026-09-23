/* Progressive enhancements: all content and the contact form work without JavaScript. */
(() => {
	'use strict';

	const openLinkedExpertise = () => {
		let id;
		try {
			id = decodeURIComponent(window.location.hash.slice(1));
		} catch {
			return;
		}
		const target = document.getElementById(id);
		if (target instanceof HTMLDetailsElement && target.classList.contains('aor-expertise')) {
			target.open = true;
		}
	};
	openLinkedExpertise();
	window.addEventListener('hashchange', openLinkedExpertise);
	document.querySelectorAll('a[href^="#"]').forEach((link) => {
		link.addEventListener('click', () => {
			const target = document.getElementById(link.hash.slice(1));
			if (target instanceof HTMLDetailsElement && target.classList.contains('aor-expertise')) {
				target.open = true;
			}
		});
	});

	document.querySelectorAll('[data-contact-subject]').forEach((link) => {
		link.addEventListener('click', () => {
			const subject = document.querySelector('.aor-contact-form [name="contact_subject"]');
			if (subject && [...subject.options].some((option) => option.value === link.dataset.contactSubject)) {
				subject.value = link.dataset.contactSubject;
			}
		});
	});

	document.querySelectorAll('.aor-training').forEach((training) => {
		const tablist = training.querySelector('[role="tablist"]');
		if (!tablist) return;
		const tabs = [...tablist.querySelectorAll('[role="tab"]')];
		const panels = tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));
		if (panels.some((panel) => !panel)) return;
		const selectTab = (index, focus = false) => {
			tabs.forEach((tab, i) => {
				const active = i === index;
				tab.setAttribute('aria-selected', String(active));
				tab.tabIndex = active ? 0 : -1;
				panels[i].hidden = !active;
			});
			if (focus) tabs[index].focus();
		};
		tabs.forEach((tab, index) => {
			panels[index].setAttribute('role', 'tabpanel');
			panels[index].setAttribute('aria-labelledby', tab.id);
			panels[index].tabIndex = 0;
			tab.addEventListener('click', () => selectTab(index));
			tab.addEventListener('keydown', (event) => {
				let next;
				if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
				if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
				if (event.key === 'Home') next = 0;
				if (event.key === 'End') next = tabs.length - 1;
				if (next !== undefined) {
					event.preventDefault();
					selectTab(next, true);
				}
			});
		});
		training.dataset.enhanced = 'true';
		tablist.hidden = false;
		selectTab(0);
	});

	if (!window.fetch || !window.FormData) return;
	document.querySelectorAll('.aor-contact-form').forEach((form) => {
		const button = form.querySelector('[type="submit"]');
		const feedback = form.querySelector('.aor-form-feedback');
		if (!button || !feedback) return;
		let submitting = false;
		form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (submitting || !form.reportValidity()) return;
			submitting = true;
			button.disabled = true;
			form.setAttribute('aria-busy', 'true');
			feedback.hidden = false;
			feedback.dataset.state = 'pending';
			feedback.textContent = 'Envoi de votre message…';
			const data = new FormData(form);
			data.set('aor_response', 'json');
			const controller = new AbortController();
			const timeout = window.setTimeout(() => controller.abort(), 20000);
			try {
					// The hidden WordPress field named "action" shadows form.action.
					const response = await fetch(form.getAttribute('action'), {
					method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal
				});
				const result = await response.json();
				if (!result || typeof result.success !== 'boolean' || typeof result.data?.message !== 'string') {
					throw new Error('Unexpected response');
				}
				const success = response.ok && result.success;
				feedback.dataset.state = success ? 'success' : 'error';
				// Server text is never interpreted as markup.
				feedback.textContent = result.data.message;
				if (success) form.reset();
			} catch {
				feedback.dataset.state = 'error';
				feedback.textContent = 'La confirmation d’envoi n’a pas pu être reçue. Votre saisie est conservée. Vérifiez votre connexion avant de réessayer.';
			} finally {
				window.clearTimeout(timeout);
				submitting = false;
				button.disabled = false;
				form.removeAttribute('aria-busy');
				feedback.focus({ preventScroll: true });
			}
		});
	});
})();
