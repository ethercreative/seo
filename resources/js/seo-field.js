var SeoField = function (namespace, readabilityFields) {
	var self = this;

	this.namespace = namespace;
	this.readabilityFields = readabilityFields;

	// Snippet
	// TODO: Make keyword (or available parts of) bold in snippet title / desc
	this.title();
	this.slug();
	this.desc();

	// Keyword
	this.keyword = document.getElementById(namespace + '-keyword');
	this.keyword.addEventListener('change', function () {
		self.keywordListener();
	});

	// Score
	this.score = document.getElementById(namespace + "-score");
	this.toggle();

	// Calculate
	this.calculateScore();
};

// SNIPPET
SeoField.prototype.title = function () {
	var title = this.titleField = document.getElementById(this.namespace + '-title'),
		t = document.getElementById('title'),
		tInput, titleInput, self = this;

	tInput = function () {
		title.value = this.value + ' ' + initial;
	};

	titleInput = function () {
		this.classList.remove('clean');
		t.removeEventListener('input', tInput, false);
		this.removeEventListener('input', titleInput, false);
	};

	if (t && title.classList.contains('clean')) {
		var initial = title.value;
		t.addEventListener('input', tInput, false);
	}

	title.addEventListener('input', titleInput, false);

	title.addEventListener('change', function () { self.calculateScore(); });
};

SeoField.prototype.slug = function () {
	var slug = this.slugField = document.getElementById(this.namespace + '-slug'),
		s = document.getElementById('slug'),
		r = document.getElementById(this.namespace + '-ref'),
		self = this;

	if (s && slug) {
		slug.textContent = s.value;
		r.textContent = r.textContent.replace(s.value, '');

		// On a loop because crafts slug generation doesn't trigger any events
		setInterval(function () {
			if (slug.textContent !== s.value) {
				slug.textContent = s.value;
				self.calculateScore();
			}
		}, 1000);
	}
};

SeoField.prototype.desc = function () {
	var desc = this.descField = document.getElementById(this.namespace + '-description'),
		self = this;

	function adjustHeight () {
		desc.oninput();
	}

	// Set Initial Height
	setTimeout(function () {
		adjustHeight();
	}, 1);

	Craft.livePreview.on('enter', adjustHeight);
	Craft.livePreview.on('exit', adjustHeight);
	window.addEventListener('resize', adjustHeight);

	// Disable line breaks
	desc.addEventListener('keydown', function (e) {
		if (e.keyCode === 13) e.preventDefault();
	});

	// Cleanse line breaks and check length
	desc.addEventListener('input', function () {
		this.value = this.value.replace(/(\r\n|\n|\r)/gm," ");
		if (this.value.length > 160) this.classList.add('invalid');
		else this.classList.remove('invalid');
	});

	desc.addEventListener('change', function () { self.calculateScore(); });
};

// KEYWORD
SeoField.prototype.keywordListener = function () {
	this.calculateScore();
};

// SCORE
SeoField.prototype.toggle = function () {
	var self = this,
		isOpen = false;

	this.score.getElementsByClassName('toggle-score')[0].addEventListener('click', function () {
		self.score.classList.toggle('open');
		isOpen = !isOpen;

		if (isOpen) {
			self.score.getElementsByClassName('details')[0].style.height = self.score.getElementsByClassName('details-inner')[0].clientHeight + 'px';
		} else {
			self.score.getElementsByClassName('details')[0].style.height = '';
		}

		//self.moveItems(isOpen);
	});
};
// FIXME
SeoField.prototype.moveItems = function (isOpen) {
	var items = [].slice.call(this.score.getElementsByClassName('item')),
		details = this.score.querySelectorAll('.details li');

	[].slice.call(details).forEach(function (detail) {
		var i = detail.getAttribute('data-i');

		if (isOpen) {
			items[i].style.left = detail.offsetLeft + 'px';
			items[i].style.top = detail.offsetTop + 'px';
		} else {
			items[i].style.left = (i * (100 / items.length)) + '%';
			items[i].style.top = '';
		}
	});
};

SeoField.prototype.calculateScore = function () {
	if (this.keyword.value) {
		var score = {};

		// TODO: Call one of these functions judgeDredd. Very important.
		score.titleLength = this.judgeTitleLength();
		score.titleKeyword = this.judgeTitleKeyword();
		score.slug = this.judgeSlug();
		score.desc = this.judgeDesc();

		if (this.readabilityFields.length > 0) {
			SeoField.getFieldsHTML(this.readabilityFields, function (res) {
				console.log(res);
				// Word count (300 minimum)
				// Keyword in first paragraph (?)
				// Keyword in image alt text
				// Flesch Reading Ease
				// Outbound links
				// Keyword in headings (h2)?
				// Keyword density max 2.5% of text, include total count
			});
		}

		this.score.classList.remove('disabled');
		var list = this.score.getElementsByClassName('details-inner')[0];
		list.innerHTML = '';
		for (var key in score) {
			if (score.hasOwnProperty(key) && score[key]) {
				list.innerHTML += '<li>' + score[key].reason + '</li>';
			}
		}
	} else {
		this.score.classList.remove('open');
		this.score.classList.add('disabled');
	}
};

// CALCULATOR
SeoField.prototype.judgeTitleLength = function () {
	var v = this.titleField.value,
		ret;

	ret = {
		score : (v.length < 40 || v.length > 60) ? SeoField.Levels.BAD : SeoField.Levels.GOOD,
		reason: (v.length < 40) ? SeoField.Reasons.titleLengthFailMin : (v.length > 60) ? SeoField.Reasons.titleLengthFailMax : SeoField.Reasons.titleLengthSuccess
	};
	ret.reason = ret.reason.replace('{l}', v.length);

	return ret;
};

SeoField.prototype.judgeTitleKeyword = function () {
	var ret;

	if (this.titleField.value.indexOf(this.keyword.value) > -1) {
		var w = this.titleField.value.split(' '),
			inFirstHalf = false;

		for (var i = 0; i < w.length/2; i++) {
			if (w[i] == this.keyword.value) {
				inFirstHalf = true;
				break;
			}
		}

		if (inFirstHalf) {
			ret = {
				score : SeoField.Levels.GOOD,
				reason: SeoField.Reasons.titleKeywordSuccess
			};
		} else {
			ret = {
				score : SeoField.Levels.OK,
				reason: SeoField.Reasons.titleKeywordPosFail
			};
		}
	} else {
		ret = {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.titleKeywordFail
		};
	}

	return ret;
};

SeoField.prototype.judgeSlug = function () {
	if (!this.slugField) return;

	if (this.slugField.value.indexOf(this.keyword.value) > -1) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.slugSuccess
		};
	} else {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.slugFail
		};
	}
};

// TODO: Check if keyword in first half / number of times it appears?
SeoField.prototype.judgeDesc = function () {
	if (this.descField.value.indexOf(this.keyword.value) > -1) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.descSuccess
		};
	} else {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.descFail
		};
	}
};

// HELPERS
/**
 * Naked Text
 *
 * @return {string}
 */
SeoField.stripHTML = function (html) {
	var tmp = document.createElement("DIV");
	tmp.innerHTML = html;
	// TODO: Link count
	console.log(tmp.querySelectorAll('a'));
	return tmp.textContent || tmp.innerText || "";
};

/**
 * Get Parsed Fields HTML
 *
 * @param {object} fields
 * @param {function} cb
 */
SeoField.getFieldsHTML = function (fields, cb) {
	var data = {};

	for (var i = 0; i < fields.length; i++) {
		/* jshint ignore:start */
		[].slice.call(document.forms[0].elements).forEach(function (el) {
			data[el.getAttribute('name')] = el.value;
		});
		/* jshint ignore:end */
	}

	Craft.postActionRequest('seo/parser?fields=' + fields.join(','), data, function(response) {
		cb(response);
	});
};

// CONSTS / ENUMS
SeoField.Levels = {
	NONE: 0,
	GOOD: 1,
	OK: 2,
	BAD: 3
};

SeoField.Reasons = {
	titleLengthFailMin: 'The title contains {l} characters which is less than the recommended minimum of 40 characters.',
	titleLengthFailMax: 'The title contains {l} characters which is greater than the recommended maximum of 60 characters.',
	titleLengthSuccess: 'The title is between the recommended minimum and maximum length.',

	titleKeywordFail: 'The title does not contain the keyword. Try adding it near the beginning of the title.',
	titleKeywordSuccess: 'The title contains the keyword near the beginning.',
	titleKeywordPosFail: 'The title contains the keyword, but not near the beginning. Try to move it closer to the start of the title.',

	slugFail: 'The URL does not contain the keyword. Try adding it to the slug.',
	slugSuccess: 'The URL contains the keyword.',

	descFail: 'The description does not contain the keyword. Try adding it near the beginning of the description.',
	descSuccess: 'The description contains the keyword.',
};