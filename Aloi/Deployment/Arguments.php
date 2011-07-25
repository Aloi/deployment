<?php
/* Copyright 2010 aloi-project
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA
 */

/**
 *
 * @author Cameron Manderson <cameronmanderson@gmail.com> (Aloi Contributor)
 */
class Aloi_Deployment_Arguments {
	protected $arguments = array();

	public function __construct($arguments) {
		$this->arguments = self::parseArgs($arguments);
	}

	public function getArgument($index) {
		if(!empty($this->arguments[$index])) {
			return $this->arguments[$index];
		}
	}
	public function setArgument($index, $value) {
		$this->arguments[$index] = $value;
	}
	
	public function getNumberedArguments($offset = null, $limit = null) {
		$numberedElements = array();
		foreach($this->arguments as $key => $value) if(is_numeric($key)) $numberedElements[$key] = $value;
		return array_slice($numberedElements, $offset ? $offset : 0, $limit ? $limit : count($this->arguments));
	}

	public static function parseArgs($argv){
		array_shift($argv);
		$out = array();
		foreach ($argv as $arg){
			if (substr($arg,0,2) == '--'){
				$eqPos = strpos($arg,'=');
				if ($eqPos === false){
					$key = substr($arg,2);
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				} else {
					$key = substr($arg,2,$eqPos-2);
					$out[$key] = substr($arg,$eqPos+1);
				}
			} else if (substr($arg,0,1) == '-'){
				if (substr($arg,2,1) == '='){
					$key = substr($arg,1,1);
					$out[$key] = substr($arg,3);
				} else {
					$chars = str_split(substr($arg,1));
					foreach ($chars as $char){
						$key = $char;
						$out[$key] = isset($out[$key]) ? $out[$key] : true;
					}
				}
			} else {
				$out[] = $arg;
			}
		}
		return $out;
	}
}