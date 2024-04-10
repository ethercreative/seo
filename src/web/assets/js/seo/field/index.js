class SeoField {

	#field;
	#opts;
	#form;

	constructor (id, opts) {
		this.#field = document.querySelector('#' + id);
		this.#opts = opts;

		if (!this.#field)
			throw new Error(`Failed to find SEO field ${id}`);

		this.#form = this.#field.closest('form');

		if (!this.#form)
			throw new Error(`Failed to find form for SEO field ${id}`);

		new Craft.FormObserver($(this.#form), () => {
			this.#onFormChange();
		});
	}

	#onFormChange = () => {
		this.#field.dispatchEvent(
			new CustomEvent('seo-change', {
				detail: {
					form: this.#form,
					opts: this.#opts,
				},
			})
		);
	};

}

window.SeoField = SeoField;
