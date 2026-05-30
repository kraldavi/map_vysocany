// ==UserScript==
// @name         Vysočany Map – send owners to Nette
// @namespace    https://github.com/mapa-vysocany
// @version      2.2
// @description  Button on every page; on cadastre site POSTs owners to /owners
// @match        *://*/*
// @grant        GM_xmlhttpRequest
// @connect      localhost
// @connect      127.0.0.1
// @run-at       document-end
// @inject-into  page
// ==/UserScript==

(function () {
	'use strict';

	const BTN_ID = 'mapa-vysocany-nette-btn';
	const API_URL = 'http://localhost:8000/owners';
	const API_KEY = 'dev-secret';

	function mountTarget() {
		return document.body || document.documentElement;
	}

	function createButton() {
		const button = document.createElement('button');
		button.id = BTN_ID;
		button.type = 'button';
		button.textContent = '💾 Nette';
		button.title = 'Send owners from cadastre to local Nette API (localhost:8000)';
		Object.assign(button.style, {
			position: 'fixed',
			bottom: '20px',
			right: '20px',
			zIndex: '2147483647',
			padding: '10px 14px',
			borderRadius: '8px',
			border: '2px solid #2980b9',
			background: '#fff',
			color: '#1a1a1a',
			fontSize: '14px',
			fontFamily: 'system-ui, sans-serif',
			cursor: 'pointer',
			boxShadow: '0 2px 12px rgba(0,0,0,.35)',
		});

		button.addEventListener('click', onClick);
		return button;
	}

	function ensureButton() {
		const target = mountTarget();
		if (!target) {
			return;
		}
		if (document.getElementById(BTN_ID)) {
			return;
		}
		target.appendChild(createButton());
	}

	function extractOwners() {
		const rows = document.querySelectorAll('table.vlastnici tbody tr:not(:first-child)');
		if (!rows.length) {
			return null;
		}
		return Array.from(rows).map((row) => ({
			name: row.querySelector('td')?.textContent.trim() || '',
			share: row.querySelector('td.right')?.textContent.trim() || '',
		}));
	}

	function cellText(nextTd) {
		if (!nextTd) {
			return '';
		}
		const link = nextTd.querySelector('a');
		return (link?.textContent ?? nextTd.textContent).trim();
	}

	function extractHouseNumberFromText(text) {
		const match = text.match(/č\.?\s*p\.?\s*(\d+)/i);
		return match ? match[1] : '';
	}

	function extractAddressFields() {
		let place = '';
		const houseByLabel = new Map();

		document.querySelectorAll('tr td.nazev').forEach((td) => {
			const label = td.textContent.trim();
			const text = cellText(td.nextElementSibling);

			if (label.includes('Budova s číslem popisným')) {
				houseByLabel.set('budova', text);
			} else if (label.includes('Adresní místa')) {
				houseByLabel.set('adresni', text);
			} else if (label.includes('Stavební objekt')) {
				houseByLabel.set('objekt', text);
			} else if (label.includes('Katastrální území')) {
				place = text.replace(/\s*\[\d+\]/, '').trim();
			}
		});

		const houseNumber =
			extractHouseNumberFromText(houseByLabel.get('budova') || '')
			|| extractHouseNumberFromText(houseByLabel.get('adresni') || '')
			|| extractHouseNumberFromText(houseByLabel.get('objekt') || '');

		return { houseNumber, place };
	}

	function onClick() {
		const owners = extractOwners();
		const { houseNumber, place } = extractAddressFields();

		console.log('[Vysočany Map] URL:', location.href);
		console.log('[Vysočany Map] House number:', houseNumber);
		console.log('[Vysočany Map] Place:', place);
		console.log('[Vysočany Map] Owners:', owners);

		if (!owners?.length) {
			alert(
				'No owners table on this page (table.vlastnici).\n\n'
					+ 'Open a building detail on the cadastre site (nahlizenidokn.cuzk.cz) and try again.',
			);
			return;
		}

		if (!houseNumber || !place) {
			alert('Could not read house number or cadastral area.');
			return;
		}

		const postData =
			`housenumber=${encodeURIComponent(houseNumber)}`
			+ `&owners=${encodeURIComponent(JSON.stringify(owners))}`
			+ `&place=${encodeURIComponent(place)}`;

		GM_xmlhttpRequest({
			method: 'POST',
			url: API_URL,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				Authorization: 'Bearer ' + API_KEY,
			},
			data: postData,
			onload(res) {
				console.log('[Vysočany Map] Backend:', res.status, res.responseText);
				if (res.status >= 200 && res.status < 300) {
					alert('Saved:\n' + res.responseText);
				} else {
					alert(`Backend error (${res.status}):\n${res.responseText}`);
				}
			},
			onerror(err) {
				console.error('[Vysočany Map] Error:', err);
				alert(
					'Could not connect to ' + API_URL
						+ '\n\nRun: docker compose up -d',
				);
			},
		});
	}

	ensureButton();
	window.addEventListener('load', ensureButton);
	new MutationObserver(ensureButton).observe(document.documentElement, {
		childList: true,
		subtree: false,
	});
})();
