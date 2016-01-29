var SeoField = function (namespace) {
	this.namespace = namespace;

	// Snippet
	this.title();
	this.slug();
	this.desc();

	// Score
	this.score = document.getElementById(namespace + "-score");
	this.toggle();
};

// SNIPPET
SeoField.prototype.title = function () {
	var title = document.getElementById(this.namespace + '-title'),
		t = document.getElementById('title'),
		tInput, titleInput;

	tInput = function () {
		title.value = this.value + ' ' + initial;
	};

	titleInput = function () {
		this.classList.remove('clean');
		this.removeEventListener('input', titleInput, false);
		t.removeEventListener('input', tInput, false);
	};

	if (t && title.classList.contains('clean')) {
		var initial = title.value;
		t.addEventListener('input', tInput, false);
	}

	title.addEventListener('input', titleInput, false);
};

SeoField.prototype.slug = function () {
	var slug = document.getElementById(this.namespace + '-slug'),
		s = document.getElementById('slug'),
		r = document.getElementById(this.namespace + '-ref');

	if (s && slug) {
		slug.textContent = s.value;
		r.textContent = r.textContent.replace(s.value, '');

		// On a loop because crafts slug generation doesn't trigger any events
		setInterval(function () {
			slug.textContent = s.value;
		}, 1000);
	}
};

SeoField.prototype.desc = function () {
	var desc = document.getElementById(this.namespace + '-description');

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

		self.moveItems(isOpen);
	});
};

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