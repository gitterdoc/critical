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

	use Input;
	use Artisan;
	use Route;
	use Storage;
	use gitterdoc\Critical\Commands\Refresh;
	use gitterdoc\Critical\Parser\DocumentParser;
	use gitterdoc\Critical\Parser\StylesheetParser;
	
	trait CriticalStylesheet {		
		private $injected = false;
	
		public function criticalBoot() {
			Artisan::bootstrap();
			
			foreach([
				new Refresh
			] AS $command) {
				Artisan::registerCommand($command);
			}
			
			$this->publishes([
				__DIR__ . '/Configuration/main.php' => config_path('critical.php')
			], 'config');
		}
		
		public function criticalRegister() {
			$this->app->instance('gitterdoc\Critical\CriticalStylesheet', $this);
			$this->mergeConfigFrom(__DIR__ . '/Configuration/main.php', 'critical');
		}
		
		public function generate(string $stylesheet) {
			$state	= request('critical', true);
			$config	= config('critical');
			
			if($state === 'false' || $state === '0' || $state === false) {
				return '<!-- [Critical] Temporary disabled -->';
			}
			
			if(!$config['enabled']) {
				return;
			}
			
			if($config['onlyprint']) {
				return file_get_contents($stylesheet);
			}
			
			if($config['caching.enabled']) {
				if(!file_exists(storage_path('critical'))) {
					mkdir(storage_path('critical'), 0777);
				}
				
				$url	= MD5(url(Route::getCurrentRoute()->uri));
				$file	= MD5($stylesheet);
				
				// Check Time
				if(file_exists(storage_path('critical/' . $url . '.' . $file . '.cache'))) {
					if((time() - filemtime(storage_path('critical/' . $url . '.' . $file . '.cache'))) >= $config['caching.time']){
						@unlink(storage_path('critical/' . $url . '.' . $file . '.cache'));
					} else {
						return file_get_contents(storage_path('critical/' . $url . '.' . $file . '.cache'));
					}
				}
				
				$style	= $this->parsedStylesheet($stylesheet);
				$result	= $style->extract($this->parseElements());
				file_put_contents(storage_path('critical/' . $url . '.' . $file . '.cache'), $result);
				return $result;
			}

			$style	= $this->parsedStylesheet($stylesheet);
			$result	= $style->extract($this->parseElements());
			return $result;
		}
		
		private function parsedStylesheet($stylesheet) {
			$stylesheet = new StylesheetParser($stylesheet);
			$stylesheet->parse();
			return $stylesheet;
		}
		
		private function parseElements() {
			$document = new DocumentParser(url(Route::getCurrentRoute()->uri));
			$document->start();
			
			do {
				$document->loop();

				if($document->isFinished()) {
					break;
				}
			} while($document->isRunning());
			
			return $document->getElements();
		}
		
		public function inject(string $file) {
			if(!config('critical.enabled') || config('critical.onlyprint')) {
				return;
			}
			
			$script = '';
			
			if(!$this->injected) {
				$this->injected	 = true;
				$script			.= file_get_contents(__DIR__ . '/Scripts/Injector.js');
			}
			
			$script .= sprintf('Critical.load(\'%s\');', $file);
			
			return $script;
		}
	}
