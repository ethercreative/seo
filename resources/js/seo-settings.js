import Redirects from "./Redirects";

const SeoSettings = function (namespace, run, csrf) {
	this.namespace = namespace;
	this.csrf = {
		name: csrf[0],
		token: csrf[1],
	};

	switch (run) {
		case 'sitemap':
			new SeoSettings.EditableTable(this.namespace + '-customUrls', this.namespace + '-addCustomUrl');
			break;
		case 'redirects':
			this.redirectsForm();
			break;
		case 'settings':
			this.sitemapName();
			break;
	}
};

window.SeoSettings = SeoSettings;

// Sitemap
// =============================================================================
SeoSettings.prototype.sitemapName = function () {
	const example = document.getElementById(this.namespace + '-sitemapNameExample');
	document.getElementById(this.namespace + '-sitemapName').addEventListener('input', function () {
		example.textContent = this.value + '.xml';
	});
};

// Redirects
// =============================================================================
SeoSettings.prototype.redirectsForm = function () {
	new Redirects(this.namespace, this.csrf);
};

// Helpers
// =============================================================================
SeoSettings.EditableTable = function (tableId, addButtonId, rowCb) {
	this.rowCb = (typeof rowCb === "function" ? rowCb : function () {});
	this.table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
	this.row = this.table.firstElementChild.cloneNode(true);
	this.table.firstElementChild.remove();

	this.row.classList.remove('hidden');

	[].slice.call(this.table.getElementsByClassName('delete')).forEach((el) => {
		el.addEventListener('click', () => {
			el.parentNode.parentNode.remove();
			this.rowCb();
		});
	});

	document.getElementById(addButtonId).addEventListener('click', () => {
		this.addRow();
	});
};

SeoSettings.EditableTable.prototype.addRow = function () {
	const newRow = this.row.cloneNode(true);

	newRow.innerHTML = newRow.innerHTML.replace(/{i}/g, this.table.childNodes.length - 2);

	newRow.getElementsByClassName('delete')[0].addEventListener('click', () => {
		newRow.remove();
		this.rowCb();
	});

	this.table.appendChild(newRow);

	this.rowCb();
};