class SeoTextarea extends HTMLTextAreaElement {

	connectedCallback () {
		this.addEventListener('input', e => {
			let { borderTopWidth, borderBottomWidth, paddingTop, paddingBottom } = window.getComputedStyle(e.target);
			borderTopWidth = +borderTopWidth.replace(/[^\d.]/g, '');
			borderBottomWidth = +borderBottomWidth.replace(/[^\d.]/g, '');
			paddingTop = +paddingTop.replace(/[^\d.]/g, '');
			paddingBottom = +paddingBottom.replace(/[^\d.]/g, '');
			e.target.style.height = '';
			e.target.style.height = (e.target.scrollHeight + borderTopWidth + borderBottomWidth - paddingTop - paddingBottom) + 'px';
		});
	}

}

customElements.define('seo-textarea', SeoTextarea, { extends: 'textarea' });
