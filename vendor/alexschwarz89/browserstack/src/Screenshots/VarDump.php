<?php


namespace Alexschwarz89\Browserstack\Screenshots;

/**
 * gsVarDump: a pure PHP class for var_dump() alternatives with editable beautified css and unlimited levels deep
 * @author Ganjar Santoso (2014)
 * @link http://www.twitter.com/ganjarsantoso
 * @link https://github.com/ganjarsantoso/gsVarDump
 * @license The BSD 2-Clause License http://opensource.org/licenses/BSD-2-Clause
 */

class VarDump
{
	/**
	 * @var string $dumped Temporary store dump result
	 */
	private $dumped = '';
	
	/**
	 * @const string WHITE_SPACE : set the global white spaces
	 */
	const WHITE_SPACE = '&nbsp;';
	/**
	 * @const string WHITE_SPACE : set the global white spaces
	 */
	const NEW_LINE = '<br>';
	/**
	 * @const string SEPARATOR : set the global separator
	 */
	const SEPARATOR = '=>';
	/**
	 * @const string LIMIT : symbol when reaching limit level deep (if set)
	 */
	const LIMIT = '...';
	/**
	 * @const boolean IN_LINE_CSS_STYLE
	 * set false to use external css file (themes mode activated)
	 * set true to force the css in line with span/div tags using built in internal default css style
	 *   in this mode you dont need any external css (themes mode deactivated)
	 */
	const IN_LINE_CSS_STYLE = false;
	
	/**
     * Load all the initial class variable
     */
	public function __construct($skin=null)
	{
		// check if the skin is used 
		if (!empty($skin)) {
			// set the skin
			$this->useSkin($skin);
		}
	}
	
	/**
	 * Finalize the dump output by putting gs-var-dump css-class into div
	 */
	public function vardump($vardump, $limit=0, $htmlcode=true)
	{
		$this->dumped = '';
		return $this->_useHtmlCode($this->_dump($vardump, $limit, $htmlcode), "gs-var-dump", $htmlcode, "", "div");
	}
		
	/**
	 * Process the dump variable to match php var_dump()
	 */
	private function _dump($vardump, $limit=0, $htmlcode=true, $level=1, $onlevel=1)
	{
		if ($htmlcode) {
			$white_space = self::WHITE_SPACE;
			$new_line = self::NEW_LINE;
		} else {
			$white_space = ' ';
			$new_line = PHP_EOL;
		}
		// get the temporary dump result
		$result = $this->dumped;
		// get data type and make it to UPPERCASE
		$datatype = strtoupper(gettype($vardump));
		
		switch($datatype) {
			// in case data type is BOOLEAN
			case 'BOOLEAN'		: 
				// set bool status
				if ($vardump) $booltext = 'true'; else $booltext = 'false';
				$result = 
					$this->_useHtmlCode(gettype($vardump), "type", $htmlcode, $white_space) . 
					$this->_useHtmlCode($booltext, "value boolean", $htmlcode, $new_line);
				break;
				
			// in case data type is INTEGER
			case 'INTEGER'		: 
				$result = 
					$this->_useHtmlCode("int", "type", $htmlcode, $white_space) . 
					$this->_useHtmlCode($vardump, "value integer", $htmlcode, $new_line);
				break;
				
			// in case data type is DOUBLE or FLOAT
			case 'DOUBLE'		:
				// because the output value of gettype(float) is always double then set it to float when it's float
				// in case in the future float and double are different in php
				if (is_float($vardump)) $dtype = 'float'; else $dtype = gettype($vardump);
				$result = 
					$this->_useHtmlCode($dtype, "type", $htmlcode, $white_space) . 
					$this->_useHtmlCode($vardump, "value double", $htmlcode, $new_line);
				break;
				
			// in case data type is STRING
			case 'STRING'		:
				$result = 
					$this->_useHtmlCode(gettype($vardump), "type", $htmlcode, $white_space) . 
					$this->_useHtmlCode("'".htmlspecialchars($vardump)."'", "value string", $htmlcode, $white_space) .
					$this->_useHtmlCode("(length=".mb_strlen($vardump).")", "size", $htmlcode, $new_line);
				break;
				
			// in case data type is ARRAY
			case 'ARRAY'		:
				// get array size
				$size = sizeof($vardump);
				$result = 
					$this->_useHtmlCode(gettype($vardump), "array", $htmlcode, $white_space) . 
					$this->_useHtmlCode("(size={$size})", "size", $htmlcode, $new_line);
					
				// if array is empty
				if ($size===0) {
					if ($htmlcode) $result .= str_repeat($white_space, $level*2);
					$result .= $this->_useHtmlCode("empty", "empty", $htmlcode, $new_line);
				}
				
				// loop all the array datas
				foreach ($vardump as $key => $val) {
					// in case the array key is integer, omit the quote
					if (is_int($key)) $keyx = $key; else $keyx = "'{$key}'";
					// create white space to make nice look
					if ($htmlcode) $result .= str_repeat($white_space, $level*2);
					// if limit exceeded
					if ($limit>0) {
						if ($onlevel>$limit) {
							// write limit symbol
							$result .= self::LIMIT.$new_line;
							break;
						}
					}
					
					// set the result
					$result .= 
						$this->_useHtmlCode($keyx, "value arraykey", $htmlcode, $white_space) . 
						$this->_useHtmlCode(self::SEPARATOR, "separator", $htmlcode);
						
					// create white space to make nice look
					if (strtoupper(gettype($val))=='ARRAY' || strtoupper(gettype($val))=='OBJECT') {
						if ($htmlcode) {
							$result .= $new_line;
							if ($htmlcode) $result .= str_repeat($white_space, ++$level*2);
						}
						// loop the function for multilevel array
						$result .= $this->_dump($val, $limit, $htmlcode, ++$level, ++$onlevel);
						$level--;
					} else {
						$result .= $white_space;
						// loop the function for multilevel array
						$result .= $this->_dump($val, $limit, $htmlcode, ++$level, ++$onlevel);
					}
					$onlevel--;
					$level--;
				}
				/*
				// use this if you want to separated in level only
				foreach ($vardump as $key => $val) {
					if (is_int($key)) $keyx = $key; else $keyx = "'{$key}'";
					if ($htmlcode) $result .= str_repeat(WHITE_SPACE, $level);
					$result .= 
						$this->_useHtmlCode($keyx, "value arraykey", $htmlcode, $white_space) . 
						$this->_useHtmlCode(self::SEPARATOR, "separator", $htmlcode, $white_space);
					$result .= $this->_dump($val, $limit, $htmlcode, ++$level, ++$onlevel);
					$level--;
				}
				*/
				break;
				
			// in case data type is OBJECT
			case 'OBJECT'		:
				// get size of object
				$sizeobj = count((array)$vardump);
				$result = 
					$this->_useHtmlCode(gettype($vardump), "object", $htmlcode) . "(" .
					$this->_useHtmlCode(get_class($vardump), "objname", $htmlcode) . ")[" .
					$this->_useHtmlCode($sizeobj, "size class", $htmlcode) . "]";
				$result .= $new_line;				

				// get class name
				$classname = get_class($vardump);
				// loop all the object datas
				foreach ((array)$vardump as $obj => $val) {
					// get visibility of object variable
					if ($classname!=$obj) {
						if ($obj[1]=='*') {
							$objname = substr($obj,2);
							$visibility = 'protected';
						} elseif (strpos($obj, $classname)!==false) {
							$objname = substr($obj, mb_strlen($classname)+1);
							$visibility = 'private';
						} else {
							$objname = $obj;
							$visibility = 'public';
						}
					}
					
					// in case object name is integer, omit the quotes
					if (!is_int($objname)) $objname = "'{$objname}'";
					// create white space to make nice look
					if ($htmlcode) $result .= str_repeat($white_space, $level*2);
					// if limit exceeded
					if ($limit>0) {
						if ($onlevel>$limit) {
							$result .= self::LIMIT.$new_line;
							break;
						}
					}
					
					// set the result
					$result .= 
						$this->_useHtmlCode($visibility, "visibility", $htmlcode, $white_space) . 
						$this->_useHtmlCode($objname, "value arraykey", $htmlcode, $white_space) . 
						$this->_useHtmlCode(self::SEPARATOR, "separator", $htmlcode);
						
					// create white space to make nice look
					if (strtoupper(gettype($val))=='ARRAY' || strtoupper(gettype($val))=='OBJECT') {
						$result .= $new_line;
						if ($htmlcode) $result .= str_repeat($white_space, ++$level*2);
						// loop the function for multilevel object
						$result .= $this->_dump($val, $limit, $htmlcode, ++$level, ++$onlevel);
						$level--;
					} else {
						$result .= $white_space;
						// loop the function for multilevel object
						$result .= $this->_dump($val, $limit, $htmlcode, ++$level, ++$onlevel);
					}
					$onlevel--;
					$level--;
				}
				break;
				
			// in case data is NULL
			case 'NULL'			:
				$result = 
					$this->_useHtmlCode(strtolower(gettype($vardump)), "null", $htmlcode, $new_line);
				break;
				
			// in case data is RESOURCE
			case 'RESOURCE'		:
				$result = 
					$this->_useHtmlCode(gettype($vardump), "resource", $htmlcode) . "(" .
					$this->_useHtmlCode(intval($vardump).", ".get_resource_type($vardump), "size", $htmlcode) . ")";
				if ($htmlcode) $result .= $new_line;
				break;
				
			// in case of unknown data type
			default	:
				$result = 
					$this->_useHtmlCode("resource", "unknown", $htmlcode) . "(" .
					$this->_useHtmlCode(intval($vardump).", ".get_resource_type($vardump), "size", $htmlcode) . ")";
				break;
		}
		// move result to temporary
		$this->dumped = $result;
		// return result
		return $result;
	}
	
	/**
	 * Generate html-styled output span/div
	 */
	private function _useHtmlCode($display_item, $css_class='', $use_html=true, $html_add='', $tags='span')
	{
		if ($use_html) {
			if (!empty($css_class)) $css_class = $this->cssStyle($css_class, self::IN_LINE_CSS_STYLE);
			$result = "<".$tags." ".$css_class.">".$display_item."</".$tags.">".$html_add;
		} else {
			$result = $display_item.$html_add;
		}
		return $result;
	}
	
	/**
	 * Generate css-link
	 */
	public function useSkin($skinPath)
	{
		if (self::IN_LINE_CSS_STYLE==false) {
			echo '<link rel="stylesheet" type="text/css" href="'.$skinPath.'">';
			/*
			// use this if you want to load css file without link, place it between html head tag
			$handle = fopen($skinPath, 'r');
			$read = fread($handle, filesize($skinPath));
			fclose($handle);
			echo '<style type="text/css">'.$read.'</style>';
			*/
		}
	}
	
	/**
	 * Generate css-themes
	 * in line css used if IN_LIE_CSS_STYLE set to true
	 * otherwise css external theme used
	 */
	private function cssStyle($css_class, $in_line_mode=false)
	{
		if ($in_line_mode) {
			$css = '';
			$css_class = strtolower(trim($css_class));
			$cssClasses = explode(' ', $css_class);
			foreach ($cssClasses as $cssClass) {
				$cssClass = trim($cssClass);
				switch($cssClass) {
					case 'gs-var-dump':
						$css .= "font-family:monospace, Consolas, Monaco, 'Courier New'; white-space:nowrap; font-size:small; padding:1em 0;";
						break;
					case 'type':
						$css .= 'font-size:smaller;';
						break;
					case 'array':
					case 'object':
					case 'limit':
					case 'resource':
					case 'unknown':
						$css .= 'font-weight:bold;';
						break;
					case 'null':
						$css .= 'color:#3465a4;';
						break;
					case 'boolean':
						$css .= 'color:#75507b;';
						break;
					case 'integer':
						$css .= 'color:#4e9a06;';
						break;
					case 'double':
						$css .= 'color:#f57900;';
						break;
					case 'string':
						$css .= 'color:#cc0000;';
						break;
					case 'empty':
						$css .= 'color:#888a85; font-style:italic;';
						break;
					case 'visibility':
					case 'size':
					case 'objname':
						$css .= 'font-style:italic;';
						break;
					case 'value':
					case 'arraykey':
					case 'separator':
					default: break;
				}
			}
			$css = 'style="'.$css.'"';
		} else {
			$css = 'class="'.$css_class.'"';
		}
		return $css;
	}
}

// Direct function call to gsVarDump
function vardump($vardump, $limit=0, $use_htmlcode=true, $skin=false)
{
	$dump = new gsVarDump($skin);
	echo $dump->vardump($vardump, $limit, $use_htmlcode);
}
