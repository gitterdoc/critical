<?php
	/**
	 *	gitterdoc.com | Software for developers
	 *
	 *	@author Adian Preuß
	 *  @license MIT
	 *  @copyright 2018
	 *	https://github.com/gitterdoc/critical
	 */
	
	function critical($file, $in_header = true) {
		$html = '';

		if(!config('critical.enabled', false) || !app()->has('gitterdoc\Critical\CriticalStylesheet')) {
			if(!$in_header) {
				return '';
			}
			
			return sprintf('<link rel="stylesheet" type="text/css" href="%s" />', asset($file));
		}
		
		if(config('critical.noscript', false) && $in_header) {
			$html .= '<noscript>';
			$html .= sprintf('<link rel="stylesheet" type="text/css" href="%s" />', asset($file));
			$html .= '</noscript>';
		}
		
		$generator = app()->make('gitterdoc\Critical\CriticalStylesheet');
			
		if($in_header) {
			$html .= '<style data-file="' . asset($file) . '">';
			$html .= $generator->generate(asset($file));
			$html .= '</style>';
			return $html;
		}
		
		$html .= '<script>';
		$html .= str_replace([ "\r", "\n", "\t" ], '', $generator->inject(asset($file)));
		$html .= '</script>';
		return $html;
	}
?>