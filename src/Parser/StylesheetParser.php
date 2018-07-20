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
	use Sabberworm\CSS\Parser as CSSParser;
	
	class StylesheetParser {
		private $url		= null;
		private $client		= null;
		private $content	= null;
		private $parser		= null;
		
		public function __construct($url) {
			$this->url		= $url;
			$this->client	= new GuzzleClient;
		}
		
		public function parse() {
			try {
				$response = $this->client->get($this->url, [
					'allow_redirects' => false
				]);
			} catch(\Exception $e) {
				/* Do Nothing */
			}
			
			if($response->getStatusCode() == 200) {
				$this->content	= $response->getBody()->getContents();
			}
			
			$this->run();
		}
		
		private function run() {
			if(empty($this->content)) {
				return;
			}
			
			$this->parser		= new CSSParser($this->content);
			$this->parser		= $this->parser->parse();
		}
		
		private function extractSelectors($elements) {
			$rules = [];
			
			foreach($elements AS $tag => $element) {
				// Adding Rule: <Element>
				if(!in_array($tag, $rules)) {
					$rules[] = $tag;
				}
				
				// ID's
				foreach($element['ids'] AS $id) {
					// Adding Rule: #<id>
					if(!in_array(sprintf('#%s', $id), $rules)) {
						$rules[] = '#' . $id;
					}
					
					// Adding Rule: <Element>#<id>
					if(!in_array(sprintf('%s#%s', $tag, $id), $rules)) {
						$rules[] = sprintf('%s#%s', $tag, $id);
					}
				}
				
				// Classes
				foreach($element['classes'] AS $class) {
					// Adding Rule: .<class>
					if(!in_array(sprintf('.%s', $class), $rules)) {
						$rules[] = '.' . $class;
					}
					
					// Adding Rule: <Element>.<id>
					if(!in_array(sprintf('%s.%s', $tag, $class), $rules)) {
						$rules[] = sprintf('%s.%s', $tag, $class);
					}
				}
			}
			
			return $rules;
		}
		
		public function extract($elements) {
			$extracted_rules	= [];
			$use_selectors		= $this->extractSelectors($elements);
			$ignore_selectors	= [
				':root',
				'a:',
				'svg',
				'::',
				'[hidden]'
			];
			
			foreach($this->parser->getAllRuleSets() AS $rule) {
				if($rule instanceof \Sabberworm\CSS\RuleSet\AtRuleSet) {
					continue;
				}
				
				if($rule instanceof \Sabberworm\CSS\RuleSet\DeclarationBlock) {
					$continue = false;
					
					foreach($rule->getSelector() AS $selector) {
						if(in_array($selector->getSelector(), $ignore_selectors)) {
							$continue = true;
							break;
						}
					}
					
					if($continue) {
						continue;
					}
					
					foreach($rule->getSelector() AS $selector) {
						if($this->compareSelector($selector->getSelector(), array_keys($elements))) {
							$content = $rule->__toString();
							
							if(!in_array($content, $extracted_rules)) {
								$extracted_rules[] = $rule->__toString();
							}
						}
						
						if($this->compareSelector($selector->getSelector(), $use_selectors)) {
							$content = $rule->__toString();
							
							if(!in_array($content, $extracted_rules)) {
								$extracted_rules[] = $rule->__toString();
							}
						}
					}
				}
			}
			
			return implode('', $extracted_rules);
		}
		
		private function compareSelector($selector, $list) {
			if(in_array($selector, $list)) {
				return true;
			}
			
			$single = explode(' ', $selector);
			
			if(count(array_diff($single, $list)) > 0) {
				return true;
			}
			
			return false;
		}
	}
?>