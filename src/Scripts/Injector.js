/**
 *	gitterdoc.com | Software for developers
 *
 *	@author Adian Preuß
 *  @license MIT
 *  @copyright 2018
 *	https://github.com/gitterdoc/critical
 */
const Critical = (new function Critical() {
	'use static';
	
	let animationFrame = null;
	
	this.__constructor = function __constructor() {
		animationFrame = requestAnimationFrame || mozRequestAnimationFrame || webkitRequestAnimationFrame || msRequestAnimationFrame;
	};
	
	this.__async = function __async(file) {
		try {
			console.warn('[Critical]', 'loading', file);
			
			var style		= document.createElement('link');
			style.rel		= 'stylesheet';
			style.href		= file;
			style.type		= 'text/css';
			style.onload	= function OnLoad() {
				console.warn('[Critical]', 'loaded & appended', file);
				
				setTimeout(function OnWaiting() {
					[].map.call(document.querySelectorAll('style[data-file="' + file + '"]'), function ForEach(style) {
						style.parentNode.removeChild(style);
						console.warn('[Critical]', 'removed inline style', file);
					});
				}, 1);
			};
			
			document.documentElement.firstChild.appendChild(style);
		} catch(e) {
			console.warn('[Critical]', 'error', e);
		}
	};
	
	this.load = function load(file) {
		if(!animationFrame) {
			animationFrame(function OnAsyncRequest() {
				this.__async(file);
			}.bind(this));
		} else {
			setTimeout(function OnAsyncRequest() {
				this.__async(file);
			}.bind(this), 0);
		}		
	};
	
	this.__constructor();
}());