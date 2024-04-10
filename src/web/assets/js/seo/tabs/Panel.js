import uid from '../util/uid.js';

export default class Panel extends HTMLElement {

	connectedCallback () {
		this.setAttribute('role', 'tabpanel');

		if (!this.id)
			this.id = `seo-panel-${uid()}`;
	}

}
