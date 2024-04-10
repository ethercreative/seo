import uid from '../util/uid.js';

export default class Tab extends HTMLElement {

	static get observedAttributes() {
		return ['selected'];
	}

	connectedCallback () {
		this.setAttribute('role', 'tab');

		if (!this.id)
			this.id = `seo-tab-${uid()}`;

		this.setAttribute('aria-selected', 'false');
		this.setAttribute('tabindex', '-1');
		this.#upgradeProperty('selected');
	}

	attributeChangedCallback () {
		const value = this.hasAttribute('selected');
		this.setAttribute('aria-selected', String(value));
		this.setAttribute('tabindex', value ? '0' : '-1');
	}

	// Getters / Setters
	// =========================================================================

	set selected (value) {
		value = Boolean(value);
		if (value) this.setAttribute('selected', '');
		else this.removeAttribute('selected');
	}

	get selected () {
		return this.hasAttribute('selected');
	}

	// Actions
	// =========================================================================

	#upgradeProperty (name) {
		if (!this.hasOwnProperty(name)) return;

		const value = this[name];
		delete this[name];
		this[name] = value;
	}

}
