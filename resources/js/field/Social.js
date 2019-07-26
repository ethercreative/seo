/* globals Craft */

/**
 * Snippet
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

export default class Social {

	// Properties
	// =========================================================================

	socialPreviews = null;
	snippetObserver = null;

	previous = {
		title: '',
		desc: '',
		url: '',
	};

	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;

		this.socialPreviews = document.querySelectorAll('.seo--social-preview-content');

		this.initObservers();
		this.initImages();
		this.initDesc();
	}
	
	// Initializers
	// =========================================================================

	initObservers () {
		// TODO: This feels a bit overkill
		// Use custom events instead (triggered by the snippet)?

		this.snippetObserver = new MutationObserver(
			this.onSnippetChange
		);

		// Trigger once to fill this.previous
		this.onSnippetChange();

		Object.values(this.SEO.snippetFields).forEach(el => {
			if (el === null)
				return;

			if (~['TEXTAREA', 'INPUT'].indexOf(el.tagName.toUpperCase())) {
				el.addEventListener('change', this.onSnippetChange);
				return;
			}

			this.snippetObserver.observe(el, {
				childList: true,
				characterData: true,
				subtree: true,
			});
		});
	}
	
	initImages () {
		const imageInputs = document.querySelectorAll(
			`a[data-seo-social-image='${this.namespace}']`
		);
		
		let i = imageInputs.length;
		while (i--) {
			const imageInput = imageInputs[i];
			imageInput.addEventListener(
				"click",
				this.onImageInputClick.bind(this, imageInput)
			);
			imageInput.querySelector(".remove").addEventListener(
				"click",
				this.onImageRemoveClick.bind(this, imageInput)
			);
		}
	}
	
	initDesc () {
		const descInputs = document.querySelectorAll(
			`textarea[data-seo-social-desc='${this.namespace}']`
		);
		
		let i = descInputs.length;
		while (i--) {
			const desc = descInputs[i];
			desc.addEventListener("input", () => {
				// Replace line-breaks with spaces
				desc.value = desc.value.replace(/(\r\n|\r|\n)/gm, " ");
			});
			desc.addEventListener("keydown", e => {
				// Prevent line-breaks
				if (e.keyCode === 13) e.preventDefault();
			});
		}
	}
	
	// Events
	// =========================================================================
	
	onImageInputClick = (self, e) => {
		e.preventDefault();
		
		if (
			self.classList.contains("has-image")
			|| e.target.classList.contains("remove")
		) return;
		
		Craft.createElementSelectorModal(
			"craft\\elements\\Asset",
			{
				multiSelect: false,
				criteria: {
					kind: ['image'],
				},
				onSelect: elements => {
					const image = elements[0];
					self.classList.add("has-image");
					self.style.backgroundImage = `url(${image.url})`;
					self.firstElementChild.value = image.id;
				},
			}
		);
	};
	
	onImageRemoveClick = self => {
		if (!self.classList.contains("has-image")) return;
		
		self.classList.remove("has-image");
		self.style.backgroundImage = "";
		self.firstElementChild.value = "";
	};

	onSnippetChange = () => {
		const hasSlug = this.SEO.snippetFields.slug !== null;

		const title = this.SEO.snippetFields.title.getSafeValue()
			, desc  = this.SEO.snippetFields.desc.getSafeValue()
			, url   = hasSlug ? this.SEO.snippetFields.slug.getSafeValue() : '';

		for (let i = 0, l = this.socialPreviews.length; i < l; ++i) {
			const titleInput = this.socialPreviews[i].getElementsByTagName('input')[0]
				, descInput = this.socialPreviews[i].getElementsByTagName('textarea')[0]
				, urlInput = this.socialPreviews[i].getElementsByTagName('span')[0];

			if (titleInput.value.trim() === '' || titleInput.value.trim() === this.previous.title.trim())
				titleInput.value = title;

			if (descInput.value.trim() === '' || descInput.value.trim() === this.previous.desc.trim())
				descInput.value = desc;

			urlInput.textContent = url;
		}

		this.previous.title = title.replace(/[\r\n]/g, '');
		this.previous.desc = desc.replace(/[\r\n]/g, '');
		this.previous.url = url;
	};
	
}
