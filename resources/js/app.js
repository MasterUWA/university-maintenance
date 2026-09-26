import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const storedTheme = localStorage.getItem('campuscare-theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
	document.documentElement.classList.add('dark');
}

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
	button.addEventListener('click', () => {
		const isDark = document.documentElement.classList.toggle('dark');
		localStorage.setItem('campuscare-theme', isDark ? 'dark' : 'light');
		button.setAttribute('aria-pressed', String(isDark));
	});
});
