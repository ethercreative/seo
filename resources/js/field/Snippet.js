/* globals Craft, $ */

/**
 * Snippet
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import { debounce } from "../helpers";

export default class Snippet {
	
	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;

		this.mainForm = document.getElementById("main-form");

		this.titleField = document.getElementById(`${namespace}Title`);
		this.slugField  = document.getElementById(`${namespace}Slug`);
		this.descField  = document.getElementById(`${namespace}Description`);
		
		this.SEO.snippetFields = {
			title: this.titleField,
			slug:  this.slugField,
			desc:  this.descField,
		};
		
		this.title();
		this.slugField && SEO.options.hasPreview && this.slug();
		this.desc();
	}
	
	/**
	 * Sync up the main title input with the SEO one
	 * (if it's a new entry, or we don't have a title)
	 */
	title () {
		const editables = this.titleField.getElementsByClassName("seo--snippet-title-editable");

		this.titleObserver = new MutationObserver(this.onTitleEditableMutation);

		for (let i = 0, l = editables.length; i < l; ++i) {
			this.titleObserver.observe(editables[i], {
				childList: true,
				characterData: true,
				subtree: true,
			});
		}

		this.formObserver = new MutationObserver(debounce(this.onAnyChange, 500));
		this._observeMainForm();
	}
	
	/**
	 * Sync up the SEO slug with crafts
	 */
	slug () {
		const mainSlugField = document.getElementById("slug");
		
		// Skip if we don't have a slug field (i.e. the homepage)
		if (!mainSlugField) return;
		
		const onSlugChange = () => {
			this.slugField.textContent = mainSlugField.value;
		};
		
		mainSlugField.addEventListener("input", onSlugChange);
		
		// Slug generation has a debounce that we need to account for to keep
		// the slugs in sync
		const title = document.getElementById("title");
		title && title.addEventListener("input", debounce(onSlugChange, 500));
		
		// Sync straight away (see above in title() as to why)
		onSlugChange();
	}
	
	/**
	 * Adjust the height of the description TextArea to ensure it never scrolls,
	 * and handle descriptions that are longer than the recommended length.
	 */
	desc () {
		const adjustHeight = () => {
			setTimeout(() => {
				this.descField.style.height = "";
				this.descField.style.height = this.descField.scrollHeight + "px";
			}, 1);
		};
		
		// Prevent line breaks
		this.descField.addEventListener("keydown", e => {
			if (e.keyCode === 13) e.preventDefault();
		});
		
		// Cleanse line breaks & check length
		this.descField.addEventListener("input", () => {
			this.descField.value =
				this.descField.value.replace(/(\r\n|\r|\n)/gm, " ");
			
			if (this.descField.value.length > 313)
				this.descField.classList.add("invalid");
			else
				this.descField.classList.remove("invalid");
			
			adjustHeight();
		});
		
		// Adjust height TextArea size changes
		
		// On tab change
		if (document.getElementById("tabs")) {
			const tabs = document.querySelectorAll("#tabs a.tab");
			for (let i = 0; i < tabs.length; i++) {
				tabs[i].addEventListener("click", adjustHeight);
			}
		}
		
		// On open / close live preview
		if (Craft.livePreview) {
			Craft.livePreview.on("enter", adjustHeight);
			Craft.livePreview.on("exit", adjustHeight);
		}
		
		// On window resize
		window.addEventListener("resize", adjustHeight);
		
		// Set initial height (extra delay to fix FF bug)
		setTimeout(() => {
			adjustHeight();
		}, 15);
	}

	// Events
	// =========================================================================

	onTitleEditableMutation = mutations => {
		mutations.forEach(mutation => {
			let target = mutation.target;

			if (target.nodeName !== "#text") {
				const sel = Snippet._getSelection(target);
				target.innerHTML = target.textContent;
				Snippet._restoreSelection(target, sel);
			}

			while (target.nodeName === "#text")
				target = target.parentNode;

			target.nextElementSibling.value = target.textContent;

			this.titleObserver.takeRecords();
		});
	};

	onAnyChange = async () => {
		this.formObserver.disconnect();
		this.formObserver.takeRecords();

		const tokens = await this._renderTokens();
		const titleTokens = this.titleField.children;
		for (let i = 0, l = titleTokens.length; i < l; ++i) {
			const el = titleTokens[i];
			const key = el.dataset.key;

			if (!tokens.hasOwnProperty(key))
				continue;

			if (~el.className.indexOf("locked") || el.textContent.trim() === "")
				el.textContent = tokens[key];
		}

		this._observeMainForm();
	};

	// Helpers
	// =========================================================================

	static _getSelection (el) {
		if (window.getSelection && document.createRange) {
			let range = window.getSelection().getRangeAt(0);
			let preSelectionRange = range.cloneRange();
			preSelectionRange.selectNodeContents(el);
			preSelectionRange.setEnd(range.startContainer, range.startOffset);
			let start = preSelectionRange.toString().length;

			return {
				start: start,
				end: start + range.toString().length
			};
		}

		let selectedTextRange = document.selection.createRange();
		let preSelectionTextRange = document.body.createTextRange();
		preSelectionTextRange.moveToElementText(el);
		preSelectionTextRange.setEndPoint("EndToStart", selectedTextRange);
		let start = preSelectionTextRange.text.length;

		return {
			start: start,
			end: start + selectedTextRange.text.length
		};
	}

	static _restoreSelection (el, sel) {
		if (window.getSelection && document.createRange) {
			let charIndex = 0, range = document.createRange();
			range.setStart(el, 0);
			range.collapse(true);
			let nodeStack = [el], node, foundStart = false,
				stop = false;

			while (!stop && (node = nodeStack.pop())) {
				if (node.nodeType === 3) {
					let nextCharIndex = charIndex + node.length;
					if (!foundStart && sel.start >= charIndex && sel.start <= nextCharIndex) {
						range.setStart(node, sel.start - charIndex);
						foundStart = true;
					}
					if (foundStart && sel.end >= charIndex && sel.end <= nextCharIndex) {
						range.setEnd(node, sel.end - charIndex);
						stop = true;
					}
					charIndex = nextCharIndex;
				} else {
					let i = node.childNodes.length;
					while (i--) {
						nodeStack.push(node.childNodes[i]);
					}
				}
			}

			sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);

			return;
		}

		let textRange = document.body.createTextRange();
		textRange.moveToElementText(el);
		textRange.collapse(true);
		textRange.moveEnd("character", sel.end);
		textRange.moveStart("character", sel.start);
		textRange.select();
	}

	async _renderTokens () {
		return new Promise(resolve => {
			const fields = $(this.mainForm).serializeArray().reduce((a, b) => {
				a[b.name] = b.value;
				return a;
			}, {});

			if (fields.hasOwnProperty("action"))
				delete fields.action;

			Craft.postActionRequest('seo/seo/render-data', {
				...this.SEO.options.renderData,
				...fields,
			}, resolve);
		});
	}

	_observeMainForm () {
		this.formObserver.observe(this.mainForm, {
			childList: true,
			attributes: true,
			characterData: true,
			subtree: true,
		});
	}
	
}
