/**
 * Readability
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import EntryMarkup from "./EntryMarkup";

export default class Readability {
	
	constructor (namespace) {
		const cont = document.getElementById(`${namespace}MarkupContainer`);
		
		document.getElementById(`${namespace}SendMarkup`).addEventListener(
			"click",
			() => {
				EntryMarkup.update().then(content => {
					const xhr = new XMLHttpRequest();
					
					xhr.open("POST", "http://localhost:8080/readability/parse");
					xhr.setRequestHeader("Content-type", "application/json");
					xhr.onerror = () => {
						console.log(xhr.responseText);
					};
					xhr.onload = () => {
						const res = JSON.parse(xhr.responseText);
						cont.innerHTML = res.markup;
					};
					xhr.send(JSON.stringify({
						key: "HI",
						markup: content.outerHTML,
					}));
				});
			}
		);
	}
	
}