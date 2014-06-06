<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class XMLUTility
{
	public static function replaceBrackets( $string )
	{
		$string = str_replace( '<', '&lt;', $string );
		$string = str_replace( '>', '&gt;', $string );
		
		return $string;
	}
}
?>
