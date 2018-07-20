<?php
	/**
	 *	gitterdoc.com | Software for developers
	 *
	 *	@author Adian Preuß
	 *  @license MIT
	 *  @copyright 2018
	 *	https://github.com/gitterdoc/critical
	 */
	
	return [
		/**
		 * Is Critical enabled?
		*/
		'enabled' => env('CRITICAL_ENABLED', true),
		
		/**
		 * Describes the handling
		 * true = Only stylesheet will be printed, no more handling
		 * false = yeach site request will be parsed to find specific styles
		*/
		'onlyprint' => env('CRITICAL_ONLY_PRINT', true),
		
		/**
		 * Enable or disable the caching
		 * or set Max. lifetime of the cache
		*/
		'caching' =>  [
			'enabled'	=> env('CRITICAL_CACHE_ENABLED', true),
			'time'		=> env('CRITICAL_CACHE_TIME', 3600)
		],
		
		/**
		 * Adding original stylesheet, when the browser hav'nt javascript
		*/
		'noscript' => env('CRITICAL_NOSCRIPT', true)
	];