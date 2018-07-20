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
	
	use gitterdoc\critical\CriticalStylesheet;
	use gitterdoc\critical\CriticalService;
	use Illuminate\Support\ServiceProvider;
	
	class Install extends ServiceProvider implements CriticalService {
		use CriticalStylesheet;
		
		public function boot() {
			$this->criticalBoot();
		}
		
		public function register() {
			$this->criticalRegister();
		}
	}
?>