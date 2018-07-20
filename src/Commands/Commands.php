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

	use Illuminate\Console\Command;
	use Symfony\Component\Console\Formatter\OutputFormatterStyle;

	abstract class Commands extends Command {
		protected function getVersion() {
			if(file_exists('composer.json')) {
				$content	= file_get_contents('composer.json');
				$composer	= null;
				
				try {
					$composer = json_decode($content);
					
					if(empty($composer)) {
						throw new \Exception('composer.json is empty.');
					}
					
					if(empty($composer->require)) {
						throw new \Exception('No required packages available.');
					}

					if(empty($composer->require->{'gitterdoc/critical'})) {
						throw new \Exception('Package version not available.');
					}
					
					return $composer->require->{'gitterdoc/critical'};
				} catch(\Exception $e) {
					/* Do Nothing */
				}
			}
			
			return 'Unknown';
		}

		public function header($string, $character = '#', $space = 5, $color = 'magenta', $verbosity = null) {
			if(!$this->output->getFormatter()->hasStyle($color)) {
				$this->output->getFormatter()->setStyle($color, new OutputFormatterStyle($color));
			}

			$this->line(str_repeat($character, strlen($string) + ($space * 2 + (strlen($character) * 2))), $color, $verbosity);
			$this->line(sprintf('%1$s%3$s%2$s%3$s%1$s', $character, $string, str_repeat(' ', $space)), $color, $verbosity);
			$this->line(str_repeat($character, strlen($string) + ($space * 2 + (strlen($character) * 2))), $color, $verbosity);

			$this->output->newLine();
		}
		
		public function writeBreak() {
			$this->getOutput()->newLine();
		}
		
		public function single($string, $color = 'white', $verbosity = null) {
			if(!$this->output->getFormatter()->hasStyle($color)) {
				$this->output->getFormatter()->setStyle($color, new OutputFormatterStyle($color));
			}

			$this->getOutput()->write($color ? "<$color>$string</$color>" : $string, false, $this->parseVerbosity($verbosity));
		}
		
		public function handle() {
			$this->header(sprintf('gitterdoc Critical | Version: %2$s | laravel v%1$s', app()::VERSION, $this->getVersion()));
		}
	}
?>