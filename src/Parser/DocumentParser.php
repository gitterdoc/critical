<?php
	/**
	 *	gitterdoc.com | Software for developers
	 *
	 *	@author Adian Preuß
	 *  @license MIT
	 *  @copyright 2018
	 *	https://github.com/gitterdoc/critical
	 */
	namespace gitterdoc\Critical\Parser;
	
	use GuzzleHttp\Client as GuzzleClient;
	use Masterminds\HTML5;
	
	class DocumentParser {
		private $url		= null;
		private $running	= false;
		private $step		= null;
		private $client		= null;
		private $content	= null;
		private $timing		= [];
		private $elements	= [];
		private $broken		= false;
		private $finished	= false;
		
		public function __construct($url) {
			$this->url		= $url;
			$this->step		= -1;
			$this->client	= new GuzzleClient;
		}
		
		public function isRunning() {
			return $this->running;
		}
		
		public function start() {
			$this->timing['running']	= microtime(true);
			$this->running				= true;
		}
		
		public function stop() {
			$this->running = false;
			// $this->timing['running']
		}
		
		public function loop() {
			++$this->step;
			
			switch($this->step) {
				case 0:
					return sprintf('[INFO] > %s', $this->url);
				break;
				case 1:
					$this->timing['download'] = microtime(true);
					return  '   [LOG] Downloading...';
				break;
				case 2:
					try {
						$response = $this->client->get(sprintf('%s?critical=false', $this->url), [
							'allow_redirects' => false
						]);
					} catch(\Exception $e) {
						if($e->getResponse()->getStatusCode() != 200) {
							$this->content	= null;
							$this->broken	= true;
							return sprintf('    [ERROR] URL is broken with response %d. Continue...', $e->getResponse()->getStatusCode());
						} else {
							$this->content	= null;
							$this->broken	= true;
							return sprintf('    [ERROR] DocumentParser: %s', $e->getMessage());
						}
					}
					
					if($response->getStatusCode() == 200) {
						$this->content = $response->getBody();
						return sprintf('[SL] Finished in %f seconds!', (microtime(true) - $this->timing['download']));
					} else {
						$this->content	= null;
						$this->broken	= true;
						return sprintf('    [ERROR] URL is broken with response %d. Continue...', $response->getStatusCode());
					}
				break;
				case 3:
					if($this->broken) {
						return null;
					}
					
					$this->timing['parsing'] = microtime(true);
					
					return  '   [SL][LOG] Parsing...';
				break;
				case 4:
					if($this->broken) {
						return null;
					}
					
					$document	= new HTML5();
					$dom		= $document->loadHTML($this->content);
					
					foreach($dom->getElementsByTagName('*') AS $element){
						if(!isset($this->elements[$element->nodeName])) {
							$this->elements[$element->nodeName] = [
								'ids'		=> [],
								'classes'	=> []
							];
						}
						
						if($element->attributes->length > 0) {
							foreach($element->attributes AS $attribute) {
								switch($attribute->name) {
									case 'class':
										$classes = explode(' ', $attribute->nodeValue);
										
										foreach($classes AS $class) {
											if(!in_array($class, $this->elements[$element->nodeName]['classes'])) {
												$this->elements[$element->nodeName]['classes'][] = $class;
											}
										}
									break;
									case 'id':
										if(!in_array($attribute->nodeValue, $this->elements[$element->nodeName]['ids'])) {
											$this->elements[$element->nodeName]['ids'][] = $attribute->nodeValue;
										}
									break;
								}
							}
						}
					}
					
					$this->finished = true;
					return sprintf('[SL]     Finished in %f seconds, found %d Elements!', (microtime(true) - $this->timing['parsing']), count($this->elements));
				break;
				default:
					$this->running = false;
					
					if(empty($this->content)) {
						return sprintf('    [WARN] DocumentParser stopped at step %s[NL]', $this->step);
					}
				break;
			}
		}
		
		public function isFinished() {
			return $this->finished;
		}
		
		public function getElements() {
			return $this->elements;
		}
	}
?>