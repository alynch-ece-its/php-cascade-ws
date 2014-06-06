<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class XmlBlock extends Block
{
	const DEBUG = false;
	const TYPE  = T::XMLBLOCK;
	
	public function getXML()
	{
		return $this->getProperty()->xml;
	}
	
	public function setXML( $xml )
	{
		if( trim( $xml ) == '' )
		{
			throw new EmptyValueException( "The xml cannot be empty." );
		}
		
		$this->getProperty()->xml = $xml;
		return $this;
	}
}
?>