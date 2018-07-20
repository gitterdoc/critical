<?php
	/**
	 *	gitterdoc.com | Software for developers
	 *
	 *	@author Adian Preuß
	 *  @license MIT
	 *  @copyright 2018
	 *	https://github.com/gitterdoc/critical
	 */
	namespace gitterdoc\Critical\Commands;

	use Route;
	use Illuminate\Console\Command;
	use gitterdoc\Critical\Parser\DocumentParser;
	
	class Refresh extends Commands {
		protected $name				= 'critical:refresh';
		protected $description		= '*UNDER DEVELOPMENT* Refresh\'s the generated stylesheets for each Route/Page.';
		protected $routes			= [];
		protected $elements			= [];
		
		public function handle() {
			parent::handle();
			$this->loadingRoutes();
			$this->startParsing();
		}
		
		protected function loadingRoutes() {
			$this->info('Refreshing critical Stylesheets...');
			$routes		= Route::getRoutes();
			$statistic	= [
				'methods'	=> [],
				'parameter'	=> 0,
				'solid'		=> 0,
				'count'		=> 0,
				'used'		=> 0
			];
			
			foreach($routes->get() as $index => $route) {
				foreach($route->methods AS $method) {
					if(!isset($statistic['methods'][$method])) {
						$statistic['methods'][$method] = 0;
					}
					
					++$statistic['methods'][$method];
				}
				
				if(in_array('GET', $route->methods) && ! str_contains($route->uri, [ '{', '}' ])) {
					$this->routes[] = $route->uri;
					++$statistic['used'];
				}
				
				if(str_contains($route->uri, [ '{', '}' ])) {
					++$statistic['parameter'];
				} else {
					++$statistic['solid'];
				}
				
				++$statistic['count'];
			}
			
			$this->comment(sprintf(' > Found %s usable routes', $statistic['used']));
			$this->comment(sprintf(' > ignoring %s parameterized routes', $statistic['parameter']));
		}
		
		protected function startParsing() {
			$this->writeBreak();
			$this->info('Starting to parse given routes...');
			
			foreach($this->routes AS $route) {
				$document = new DocumentParser(url($route));
				$document->start();
				$hide = [ '[LOG]', '[NL]', '[SL]', '[INFO]' ];
				
				do {
					$output = $document->loop();

					if(!empty($output)) {
						if(!str_contains(strtoupper($output), [ '[SL]'])) {
							$this->writeBreak();
						}
						
						if(trim($output) && str_contains(strtoupper($output), [ '[LOG]'])) {
							$this->single(str_replace($hide, '', $output));
						} else if(trim($output) && str_contains(strtoupper($output), [ '[SL]'])) {
							$this->single(str_replace($hide, '', $output));
							$this->writeBreak();
						} else if(trim($output) && str_contains(strtoupper($output), [ '[WARN]'])) {
							$this->single(str_replace($hide, '', $output), 'yellow');
						} else if(trim($output) && str_contains(strtoupper($output), [ '[INFO]'])) {
							$this->single(str_replace($hide, '', $output), 'cyan');
						} else if(trim($output) && str_contains(strtoupper($output), [ '[ERROR]'])) {
							$this->single(str_replace($hide, '', $output), 'red');
						} else {
							$this->single(str_replace($hide, '', $output), 'green');
						}
						
						if(str_contains(strtoupper($output), [ '[NL]'])) {
							$this->writeBreak();
						}
					}
					
					if($document->isFinished()) {
						$this->elements[$route] = $document->getElements();
						break;
					}
				} while($document->isRunning());
			}
			
			$this->writeBreak();
			$this->info('Parsing is finished!');
			$this->comment(sprintf(' > %d documents are parsed', count($this->elements)));
			
			foreach($this->elements AS $name => $document) {
				$classes	= 0;
				$ids		= 0;
				
				foreach($document AS $elements) {
					$classes	+= count($elements['classes']);
					$ids		+= count($elements['ids']);
				}
				
				$this->comment(sprintf('   - [%s] has %d elements with %d classes and %d id\'s', $name, count($document), $classes, $ids));
			}
		}
	}
?>