/* global Garnish, $ */

/**
 * SEO Settings
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2018
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     3.5.0
 */

class SeoFieldSettings {

	// Properties
	// =========================================================================

	namespace = '';

	tokenRegex = /\[title]\[(\d)].*/g;
	tokenList = null;
	tokenTemplate = null;

	tokenSort = null;

	constructor (namespace) {
		this.namespace = namespace;
		if (namespace.substr(-1) !== "-")
			this.namespace += "-";

		this.initSeoTitle();
	}

	// Init / Setup
	// =========================================================================

	initSeoTitle () {
		this.tokenList = this.getElementById("seoMetaTitle");
		this.tokenTemplate = this.getElementById("seoMetaToken").content;

		this.initSorting();

		const existingTokens =
			this.tokenList.querySelectorAll("li:not([data-static])");
		let i = existingTokens.length;
		if (i > 0) while (i--)
			this.setupToken(existingTokens[i]);

		this.tokenSort.addItems($(existingTokens));

		window.addEventListener("focus", this.onFocusChange, true);
		window.addEventListener("blur", this.onFocusChange, true);

		this.getElementById("seoMetaAdd").addEventListener(
			"click",
			this.onAddClick
		);
	}

	initSorting () {
		this.tokenSort = new Garnish.DragSort({
			container: this.tokenList,
			filter: null,
			ignoreHandleSelector: 'input[data-template]',
			axis: null,
			collapseDraggees: true,
			magnetStrength: 4,
			helperLagBase: 1.5,
			onSortChange: this.onSortChange,
		});
	}

	setupToken (token) {
		const tmpl = token.querySelector("[data-template]");
		SeoFieldSettings.onTemplateInput({ target: tmpl });
		tmpl.addEventListener(
			"input",
			SeoFieldSettings.onTemplateInput
		);

		token.querySelector("[data-lock]").addEventListener(
			"click",
			SeoFieldSettings.onLockClick
		);

		token.querySelector("[data-delete]").addEventListener(
			"click",
			this.onDeleteClick
		);
	}

	// Events
	// =========================================================================

	onFocusChange = () => {
		if (this.tokenList.contains(document.activeElement))
			this.tokenList.parentNode.classList.add("focus");
		else
			this.tokenList.parentNode.classList.remove("focus");
	};

	onAddClick = e => {
		e.preventDefault();
		const li = document.importNode(this.tokenTemplate, true);
		const indexReplace = li.querySelectorAll("[name*='__LOOP_INDEX__']")
			, index = this.tokenList.children.length;

		let i = indexReplace.length,
			t = null;
		while (i--) {
			const el = indexReplace[i];
			const name = el.getAttribute("name");
			el.setAttribute(
				"name",
				name.replace("__LOOP_INDEX__", index)
			);

			if (~name.indexOf("key"))
				el.value = Math.random().toString(36).substr(2, 3);

			if (~name.indexOf("template"))
				t = el;
		}

		this.setupToken(li);
		this.tokenList.appendChild(li);
		this.tokenSort.addItems($(li));
		t.focus();
	};

	static onTemplateInput (e) {
		const value = e.target.value;
		e.target.style.width = `calc(${value.length}ch + 10px)`;
	}

	static onLockClick (e) {
		e.preventDefault();
		const target = e.target;
		const input = target.previousElementSibling;

		if (input.value === "1") {
			input.value = "0";
			target.setAttribute("title", "Lock Token");
			target.parentNode.classList.remove("locked");
		} else {
			input.value = "1";
			target.setAttribute("title", "Unlock Token");
			target.parentNode.classList.add("locked");
		}
	}

	onDeleteClick = e => {
		e.preventDefault();
		this.tokenList.removeChild(e.target.parentNode);
		this.reIndexTokens();
	};

	onSortChange = () => {
		this.reIndexTokens();
	};

	// Helpers
	// =========================================================================

	reIndexTokens () {
		const tokens = this.tokenList.children;
		let i = tokens.length;
		if (i > 0) while (i--) {
			const token = tokens[i]
				, rpl = SeoFieldSettings.replaceKey.bind(this, i);
			const inputs = token.getElementsByTagName("input");
			let x = inputs.length;
			while (x--) {
				inputs[x].setAttribute(
					"name",
					inputs[x].getAttribute("name").replace(this.tokenRegex, rpl)
				);
			}
		}
	}

	static replaceKey (i, a, b) {
		return a.replace(b, i);
	}

	getElementById (id) {
		return document.getElementById(this.namespace + id);
	}

}

window.SeoFieldSettings = SeoFieldSettings;