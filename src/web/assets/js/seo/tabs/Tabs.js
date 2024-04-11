const TEMPLATE = document.createElement('template');
TEMPLATE.innerHTML = `
	<style>
		:host {
			display: block;
			border-radius: var(--small-border-radius);
			box-shadow: inset 0 0 0 1px rgba(51,64,77,.1);
		}
		.tabs {
			display: flex;
			flex-wrap: wrap;
			
			background: var(--gray-050);
			box-shadow: inset 0 0 0 1px rgba(205,216,228,.5);
			border-radius: var(--small-border-radius) var(--small-border-radius) 0 0;
			
			overflow: hidden;
		}
		::slotted(seo-tab) {
			position: relative;
			padding: var(--s) var(--m);
			cursor: pointer;
			border-radius: var(--small-border-radius) var(--small-border-radius) 0 0;
		}
		::slotted(seo-tab[selected]) {
			background: #fff;
			box-shadow: 
				inset 0 2px 0 var(--custom-text-color,var(--gray-500))
			  , inset 0 1px 0 1px rgba(51,64,77,.1)
			  , 0 2px 12px var(--custom-sel-tab-shadow-color,var(--gray-200))
			  !important;
		}
		::slotted(seo-tab[selected]:focus-visible)::after {
			content: '';
			position: absolute;
			z-index: 2;
			top: 3px;
			left: 3px;
			right: 3px;
			bottom: 3px;
			display: block;
			box-shadow: var(--focus-ring), 0 0 0 3px #fff;
			pointer-events: none;
		}
		::slotted(seo-tab:hover:not([selected])) {
			background: var(--gray-100);
		}
		::slotted(seo-panel) {
			display: block;
			padding: var(--l) var(--m);
		}
	</style>
	<nav class="tabs">
		<slot name="tab"></slot>
	</nav>
	<slot name="panel"></slot>
`;

// TODO: Use id (if available) to persist selected tab in localStorage

export default class Tabs extends HTMLElement {

	constructor () {
		super();

		this.attachShadow({ mode: 'open' });
		this.shadowRoot.append(TEMPLATE.content.cloneNode(true));

		const tabSlot = this.shadowRoot.querySelector('slot[name=tab]')
			, panelSlot = this.shadowRoot.querySelector('slot[name=panel]');

		tabSlot.addEventListener('slotchange', this.#onSlotChange);
		panelSlot.addEventListener('slotchange', this.#onSlotChange);
	}

	connectedCallback () {
		this.addEventListener('keydown', this.#onKeyDown);
		this.addEventListener('click', this.#onClick);

		if (!this.hasAttribute('role'))
			this.setAttribute('role', 'tablist');
	}

	disconnectedCallback () {
		this.removeEventListener('keydown', this.#onKeyDown);
		this.removeEventListener('click', this.#onClick);
	}

	// Handlers
	// =========================================================================

	#onSlotChange = () => {
		this.#linkPanels();
	};

	#onKeyDown = e => {
		if (e.target.getAttribute('role') !== 'tab')
			return;

		if (e.target.parentElement !== this)
			return;

		if (e.altKey)
			return;

		let nextTab;

		switch (e.key) {
			case 'ArrowLeft':
			case 'ArrowUp':
				nextTab = this.#getPrevTab();
				break;
			case 'ArrowRight':
			case 'ArrowDown':
				nextTab = this.#getNextTab();
				break;
			case 'Home':
				nextTab = this.#getFirstTab();
				break;
			case 'End':
				nextTab = this.#getLastTab();
				break;
			default:
				return;
		}

		e.preventDefault();

		this.#selectTab(nextTab);
	};

	#onClick = e => {
		if (e.target.getAttribute('role') !== 'tab')
			return;

		if (e.target.parentElement !== this)
			return;

		this.#selectTab(e.target);
	};

	// Actions
	// =========================================================================

	reset () {
		const tabs = this.#getAllTabs()
			, panels = this.#getAllPanels();

		tabs.forEach(tab => tab.selected = false);
		panels.forEach(panel => panel.hidden = true);
	}

	#selectTab (tab) {
		this.reset();

		const panel = this.#getPanelForTab(tab);

		if (!panel)
			throw new Error(`Missing panel with ID ${tab.getAttribute('aria-controls')}`);

		tab.selected = true;
		panel.hidden = false;
		tab.focus();
	}

	#linkPanels () {
		const tabs = this.#getAllTabs();

		tabs.forEach(tab => {
			const panel = tab.nextElementSibling;

			if (panel.tagName.toLowerCase() !== 'seo-panel')
				throw new Error(`Tab ${tab.id} is not a sibling of <seo-panel />`);

			tab.setAttribute('aria-controls', panel.id);
			panel.setAttribute('aria-labelledby', tab.id);
		});

		this.#selectTab(tabs.find(tab => tab.selected) || tabs[0]);
	}

	// Helpers
	// =========================================================================

	#getAllTabs () {
		return Array.from(this.querySelectorAll(':scope > seo-tab'));
	}

	#getAllPanels () {
		return Array.from(this.querySelectorAll(':scope > seo-panel'));
	}

	#getPanelForTab (tab) {
		const panelId = tab.getAttribute('aria-controls');

		return this.querySelector(`#${panelId}`);
	}

	#getNextTab () {
		const tabs = this.#getAllTabs();
		const index = tabs.findIndex(tab => tab.selected) + 1;

		return tabs[index % tabs.length];
	}

	#getPrevTab () {
		const tabs = this.#getAllTabs();
		const index = tabs.findIndex(tab => tab.selected) - 1;

		return tabs[(index + tabs.length) % tabs.length];
	}

	#getFirstTab () {
		return this.#getAllTabs()[0];
	}

	#getLastTab () {
		const tabs = this.#getAllTabs();

		return tabs[tabs.length - 1];
	}

}
