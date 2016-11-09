var SeoSettings = function (namespace, run) {
	var self = this;
	this.namespace = namespace;

	switch (run) {
		case 'sitemap':
			new SeoSettings.EditableTable(this.namespace + '-customUrls', this.namespace + '-addCustomUrl');
			break;
		case 'redirects':
			var table = document.getElementById(this.namespace + '-redirects'),
				field = document.getElementById(this.namespace + '-redirects-field');
			new SeoSettings.EditableTable(this.namespace + '-redirects', this.namespace + '-addRedirect', function () {
				self.redirectsForm(table, field);
			});
			this.redirectsForm(table, field);
			break;
		case 'settings':
			this.sitemapName();
			this.populateFields();
			break;
	}
};

// SITEMAP
SeoSettings.prototype.sitemapName = function () {
	var example = document.getElementById(this.namespace + '-sitemapNameExample');
	document.getElementById(this.namespace + '-sitemapName').addEventListener('input', function () {
		example.textContent = this.value + '.xml';
	});
};

// REDIRECTS
SeoSettings.prototype.redirectsForm = function (table, field) {
	function parseRedirectForm() {
		var o = [];
		[].slice.call(table.querySelectorAll('tbody tr:not(.hidden)')).forEach(function (el) {
			o.push({
				'id': +el.getAttribute('data-id'),
				'uri': el.querySelector('[data-name="redirects-uri"]').value.trim(),
				'to': el.querySelector('[data-name="redirects-to"]').value.trim(),
				'type': el.querySelector('[data-name="redirects-type"]').value
			});
		});

		field.value = JSON.stringify(o).replace(/\\n/g, "\\n")
									    .replace(/\\'/g, "\\'")
									    .replace(/\\"/g, '\\"')
									    .replace(/\\&/g, "\\&")
								        .replace(/\\r/g, "\\r")
									    .replace(/\\t/g, "\\t")
									    .replace(/\\b/g, "\\b")
									    .replace(/\\f/g, "\\f");
	}

	parseRedirectForm();

	[].slice.call(document.querySelectorAll('[data-name]')).forEach(function (el) {
		el.addEventListener('input', parseRedirectForm);
	});
};

// SETTINGS
SeoSettings.prototype.populateFields = function () {
	document.getElementById('settings-populateAllEntriesBtn').addEventListener('click', function (e) {
		e.preventDefault();

		// Entries
		var entriesToProcess = [].slice.call(document.getElementById('settings-populateEntries')
			.querySelectorAll('input[type=checkbox]:checked')).reduce(function(a, el) {
			if (el.value === "*") return a;

			var s = el.value.split('-')[1].split('|');

			a.push({
				section: +s[0],
				type: +s[1],
				fields: s[2].split(':').map(function (n) { return +n; })
			});

			return a;
		}, []);

		// TODO: Categories
		// TODO: Globals
		// TODO: Products
		// TODO: Custom Element Types

		console.log(entriesToProcess);
	});
};

// HELPERS
SeoSettings.EditableTable = function (tableId, addButtonId, rowCb) {
	var self = this;
	this.rowCb = (typeof rowCb === "function" ? rowCb : function () {});
	this.table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
	this.row = this.table.firstElementChild.cloneNode(true);
	this.table.firstElementChild.remove();

	this.row.classList.remove('hidden');

	[].slice.call(this.table.getElementsByClassName('delete')).forEach(function (el) {
		el.addEventListener('click', function () {
			el.parentNode.parentNode.remove();
			self.rowCb();
		});
	});

	document.getElementById(addButtonId).addEventListener('click', function () {
		self.addRow();
	});
};

SeoSettings.EditableTable.prototype.addRow = function () {
	var self = this;

	var newRow = this.row.cloneNode(true);

	newRow.innerHTML = newRow.innerHTML.replace(/\{i}/g, this.table.childNodes.length - 2);

	newRow.getElementsByClassName('delete')[0].addEventListener('click', function () {
		newRow.remove();
		self.rowCb();
	});

	this.table.appendChild(newRow);

	this.rowCb();
};