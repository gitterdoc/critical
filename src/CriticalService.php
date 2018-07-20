<?php
	/**
	 *	gitterdoc.com | Software for developers
	 *
	 *	@author Adian Preuß
	 *  @license MIT
	 *  @copyright 2018
	 *	https://github.com/gitterdoc/critical
	 */
	namespace gitterdoc\Critical;
	
	interface CriticalService {
		public function generate(string $stylesheet);
		public function inject(string $file);
		public function criticalBoot();
	}
?>