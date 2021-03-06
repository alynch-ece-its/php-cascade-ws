<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class XsltFormat extends Format
{
	const DEBUG = false;
	const TYPE  = T::XSLTFORMAT;
	
	public function displayXml()
	{
		$xml_string = htmlentities( $this->getProperty()->xml ); // &
		$xml_string = XMLUtility::replaceBrackets( $xml_string );
		
		echo S_H2 . "XML" . E_H2 .
		     S_PRE . $xml_string . E_PRE . HR;
		
		return $this;
	}

	public function getXml()
	{
		return $this->getProperty()->xml;
	}
	
	public function setXml( $xml )
	{
		$this->getProperty()->xml = $xml;
		
		return $this;
	}
}
?>