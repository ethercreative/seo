/* globals Craft, $ */
/**
 * SEO Redirects
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */
import { c } from "../helpers";

const REDIRECT_TYPES = {
	"301": "301 (Permanent)",
	"302": "302 (Temporary)",
};

export default class Redirects {
	
	constructor (namespace, csrf) {
		this.ns = namespace;
		this.csrf = csrf;
		
		const tables = document.querySelectorAll("tbody[data-redirects]");
		const tableForms = document.querySelectorAll("form[data-redirects]");
		this.newForm = document.getElementById(namespace + "-redirectsNew");
		this.bulkForm = document.getElementById(namespace + "-redirectsBulk");
		
		this.cancelCurrentEdit = () => {};
		this.editRow = null;

		this.newForm.addEventListener("submit", this.onSubmitNew);
		this.bulkForm.addEventListener("submit", this.onSubmitBulk);

		this.tables = {};
		for (let i = 0, l = tables.length; i < l; ++i) {
			const siteId = tables[i].dataset.redirects;
			this.tables[siteId] = tables[i];
		}

		this.tableForms = {};
		for (let i = 0, l = tableForms.length; i < l; ++i) {
			const siteId = tableForms[i].dataset.redirects;
			this.tableForms[siteId] = tableForms[i];
			this.tableForms[siteId].addEventListener(
				"submit",
				this.onUpdate.bind(this, siteId)
			);
		}
		
		this.initTables();
	}
	
	initTables () {
		Object.keys(this.tables).forEach(key => {
			if (this.tables[key].__sorter)
				this.tables[key].__sorter.destroy();

			this.tables[key].__sorter = new Craft.DataTableSorter(this.tables[key].parentNode, {
				onSortChange: () => this.onSort(this.tables[key]),
			});

			[].slice.call(this.tables[key].getElementsByTagName("tr")).forEach(row => {
				const links = row.getElementsByTagName("a");

				links[0].addEventListener("click", e => {
					this.onEditClick(key, e, row);
				});

				links[2].addEventListener("click", e => {
					this.onDeleteClick(key, e, row);
				});
			});
		});
	}
	
	// Events
	// =========================================================================
	
	onSubmitNew = e => {
		e.preventDefault();
		const form = e.target
			, spinner = form.getElementsByClassName("spinner")[0];
		
		const uri    = form.elements[this.namespaceField("uri")]
			, to     = form.elements[this.namespaceField("to")]
			, type   = form.elements[this.namespaceField("type")]
			, siteId = form.elements[this.namespaceField("siteId")];

		const order = this.tables[siteId.value].children.length;

		// Validate
		if (!Redirects._validate(uri, to, spinner))
			return;
		
		this.post("POST", {
			action: 'seo/redirects/save',
			order,
			uri: uri.value,
			to: to.value,
			type: type.value,
			siteId: siteId.value,
		}, ({ id }) => {
			this.tables[siteId.value].appendChild(this.rowStatic(
				id, order, uri.value, to.value, type.value, siteId.value
			));
			
			Craft.cp.displayNotice(
				"SEO: Redirect added successfully!"
			);
			spinner.classList.add("hidden");
			
			uri.value = '';
			to.value = '';
			uri.focus();
			this.initTables();
		}, error => {
			Craft.cp.displayError("SEO: " + error);
			spinner.classList.add("hidden");
		});
	};
	
	onSubmitBulk = e => {
		e.preventDefault();
		const form = e.target
			, spinner = form.getElementsByClassName("spinner")[0];
		
		const redirects = form.elements[this.namespaceField("redirects")]
			, separator = form.elements[this.namespaceField("separator")]
			, type      = form.elements[this.namespaceField("type")]
			, siteId    = form.elements[this.namespaceField("siteId")];
		
		// Validate
		let valid = true;
		if (redirects.value.trim() === "") {
			redirects.classList.add("error");
			valid = false;
		} else redirects.classList.remove("error");
		
		if (separator.value === "") {
			separator.classList.add("error");
			valid = false;
		} else separator.classList.remove("error");
		
		if (!valid) return;
		
		// Submit
		spinner.classList.remove("hidden");
		
		this.post("PUT", {
			redirects: redirects.value,
			separator: separator.value,
			type: type.value,
			siteId: siteId.value,
		}, ({ redirects: newRedirects }) => {
			newRedirects.forEach(({ id, order, uri, to, type, siteId }) => {
				this.tables[siteId || 'null'].appendChild(this.rowStatic(
					id, order, uri, to, type, siteId
				));
			});
			
			Craft.cp.displayNotice('SEO: Redirects added successfully!');
			spinner.classList.add("hidden");
			
			redirects.value = '';
			this.initTables();
		}, error => {
			Craft.cp.displayError('SEO: ' + error);
			spinner.classList.add("hidden");
		});
	};
	
	// TODO: Remove save / update boilerplate
	
	onUpdate = (siteId, e) => {
		e.preventDefault();
		const form = e.target
			, spinner = form.getElementsByClassName("spinner")[0];
		
		const id    = form.elements[this.namespaceField("id")]
			, order = form.elements[this.namespaceField("order")]
			, uri   = form.elements[this.namespaceField("uri")]
			, to    = form.elements[this.namespaceField("to")]
			, type  = form.elements[this.namespaceField("type")];
		
		// Validate
		if (!Redirects._validate(uri, to, spinner))
			return;
		
		this.post("POST", {
			action: 'seo/redirects/save',
			id: id.value,
			order: order.value,
			uri: uri.value,
			to: to.value,
			type: type.value,
		}, () => {
			const row = this.editRow;

			this.cancelCurrentEdit();
			
			this.tables[siteId].insertBefore(
				this.rowStatic(
					id.value, order.value, uri.value, to.value, type.value, siteId.value,
					row.dataset.added
				),
				row
			);
			
			this.tables[siteId].removeChild(row);
		}, error => {
			Craft.cp.displayError('SEO: ' + error);
			spinner.classList.add("hidden");
		});
	};
	
	onEditClick = (siteId, e, row) => {
		e.preventDefault();
		const { id, order, uri, to, type, added } = e.target.dataset;
		this.cancelCurrentEdit();
		
		const editRows = this.rowEdit(id, order, uri, to, type);

		this.editRow = row;
		this.editRow.setAttribute('data-added', added);

		this.cancelCurrentEdit = () => {
			this.tables[siteId].insertBefore(row, editRows[0]);
			this.tables[siteId].removeChild(editRows[0]);
			this.tables[siteId].removeChild(editRows[1]);
			
			this.cancelCurrentEdit = () => {};
			this.editRow = null;
		};
		
		this.tables[siteId].insertBefore(
			editRows[0],
			row
		);
		
		this.tables[siteId].insertBefore(
			editRows[1],
			row
		);
		
		editRows[0].getElementsByTagName("input")[1].focus();
		
		this.tables[siteId].removeChild(row);
	};
	
	onDeleteClick = (siteId, e, row) => {
		e.preventDefault();
		
		if (!confirm("Delete this redirect?"))
			return;
		
		this.post("DELETE", {
			id: row.dataset.id
		}, () => {
			Craft.cp.displayNotice('SEO: Redirect deleted');
			this.tables[siteId].removeChild(row);
		}, error => {
			Craft.cp.displayNotice('SEO: ' + error);
		});
	};

	onSort = table => {
		const rows = table.querySelectorAll('tr');
		const post = [];

		for (let i = 0, l = rows.length; i < l; i++) {
			const row = rows[i];
			row.dataset.order = i;
			row.querySelector('a').dataset.order = i;
			post.push({ id: row.dataset.id, order: row.dataset.order });
		}

		this.post('POST', {
			action: 'seo/redirects/sort',
			order: post,
		}, () => {
			Craft.cp.displayNotice('SEO: Redirect order saved');
		}, error => {
			Craft.cp.displayNotice('SEO: ' + error);
		});
	};
	
	// Helpers
	// =========================================================================
	
	namespaceField (handle) {
		return `${this.ns}[${handle}]`;
	}
	
	post (
		method,
		fields = {},
		onSuccess = () => {},
		onError = () => {},
	) {
		const jsonData = {};

		jsonData[this.csrf.name] = this.csrf.token;
		
		Object.keys(fields).forEach(key => {
			if (fields.hasOwnProperty(key))
				jsonData[key] = fields[key];
		});
		
		$.ajax({
			type: method,
			url: window.location.href,
			dataType: 'json',
			data: jsonData,
		}).done((data, status) => {
			if (status === "success") {
				if (data.hasOwnProperty("success")) {
					onSuccess(data);
				} else if (data.hasOwnProperty("error")) {
					if (typeof data.error === typeof "") {
						onError(data.error);
					} else {
						onError(data.error[Object.keys(data.error)[0]][0]);
					}
				}
			} else {
				onError(data);
			}
		}).fail(() => {
			onError("An unknown error has occurred");
		});
	}
	
	rowStatic (id = -1, order = -1, uri = "", to = "", type = 301, siteId = null, dateCreated = null) {
		const added = dateCreated || 'Now';

		const row = c("tr", { "tabindex": 0, "data-id": id }, [
			// URI
			c("td", { "class": "redirects--title-col" }, [
				c("div", { "class": "element small" }, [
					c("div", { "class": "label" }, [
						c("span", { "class": "title" }, [
							c("a", {
								"href": "#",
								"title": "Edit Redirect",
								"data-id": id,
								"data-order": order,
								"data-uri": uri,
								"data-to": to,
								"data-type": type,
								"data-added": dateCreated,
								"click": e => this.onEditClick(siteId, e, row),
							}, uri)
						])
					])
				])
			]),

			// To
			c("td", {}, to),
			
			// Type
			c("td", {}, REDIRECT_TYPES[type]),

			// Added
			c("td", {}, added),
			
			// Reorder
			c("td", { "class": "thin action" }, [
				c("a", {
					"class": "move icon",
					"title": "Reorder",
				}),
			]),

			// Delete
			c("td", { "class": "thin action" }, [
				c("a", {
					"class": "delete icon",
					"title": "Delete",
					"click": e => this.onDeleteClick(siteId, e, row),
				}),
			]),
		]);
		
		return row;
	}
	
	rowEdit (id, order, uri, to, type) {
		return [
			c("tr", { "class": "redirects--edit-row" }, [
				// URI
				c("td", {}, [
					c("input", {
						"value": id,
						"type": "hidden",
						"name": this.namespaceField("id")
					}),
					c("input", {
						"value": order,
						"type": "hidden",
						"name": this.namespaceField("order")
					}),
					c("input", {
						"value": uri,
						"type": "text",
						"class": "text fullwidth",
						"name": this.namespaceField("uri")
					}),
				]),
				
				// To
				c("td", {}, [
					c("input", {
						"value": to,
						"type": "text",
						"class": "text fullwidth",
						"name": this.namespaceField("to")
					}),
				]),
				
				// Type
				c("td", { "colspan": 2 }, [
					c("div", { "class": "select" }, [
						c("select", {
							"name": this.namespaceField("type")
						}, Object.keys(REDIRECT_TYPES).map(value => {
							const opts = { value };
							if (type === value) opts["selected"] = "selected";
							
							return c(
								"option",
								opts,
								REDIRECT_TYPES[value]
							);
						}))
					])
				]),
				
				// Spinner
				c("td", {}, [
					c("div", { "class": "spinner hidden" })
				])
			]),
			
			c("tr", { "class": "redirects--edit-controls" }, [
				c("td", { "colspan": 5 }, [
					c("input", {
						"class": "btn submit",
						"type": "submit",
						"value": "Update",
					}),
					c("input", {
						"class": "btn",
						"type": "button",
						"value": "Cancel",
						"click": () => { this.cancelCurrentEdit(); },
					}),
				])
			])
		];
	}

	static _validate (uri, to, spinner) {
		let valid = true;
		if (uri.value.trim() === "") {
			uri.classList.add("error");
			valid = false;
		} else uri.classList.remove("error");

		if (to.value.trim() === "") {
			to.classList.add("error");
			valid = false;
		} else to.classList.remove("error");

		if (!valid)
			return false;

		// Submit
		spinner.classList.remove("hidden");

		return true;
	}
	
}
