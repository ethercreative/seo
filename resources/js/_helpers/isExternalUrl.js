/**
 * External URL checker
 * From http://stackoverflow.com/a/9744104/550109
 *
 * @param {string} url
 */
export default function isExternalUrl (url) {
	const domain = url => {
		let res = /https?:\/\/((?:[\w\d-]+\.)+[\w\d]{2,})/i.exec(url);
		return res !== null ? res[1] : false;
	};
	
	return domain(location.href) === domain(url);
}