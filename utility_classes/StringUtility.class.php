<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/22/2014 Fixed some bugs.
 */
class StringUtility
{
	public static function endsWith($haystack, $needle)
	{
    	return $needle === "" || substr( $haystack, -strlen( $needle ) ) === $needle;
	}
	
	public static function getExplodedStringArray( $delimiter, $string )
	{
		$temp   = array();
		$tokens = explode( $delimiter, $string );
		
		if( count( $tokens ) > 0 )
		{
			foreach( $tokens as $token )
			{
				if( trim( $token, " \n\t" ) != "" )
				{
					$temp[] = trim( $token, " \n\t" );
				}
			}
		}
		return $temp;
	}
	
	public static function startsWith( $haystack, $needle )
	{
    	return $needle === "" || strpos( $haystack, $needle ) === 0;
	}
}
?>