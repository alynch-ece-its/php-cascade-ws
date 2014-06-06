<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class File extends Linkable
{
	const DEBUG = false;
	const TYPE  = T::FILE;
	
	public function getData()
	{
		return $this->getProperty()->data;
	}
	
	public function getLastPublishedBy()
	{
		return $this->getProperty()->lastPublishedBy;
	}
	
	public function getLastPublishedDate()
	{
		return $this->getProperty()->lastPublishedDate;
	}
	
	public function getMaintainAbsoluteLinks()
	{
		return $this->getProperty()->maintainAbsoluteLinks;
	}
	
	public function getRewriteLinks()
	{
		return $this->getProperty()->rewriteLinks;
	}
	
	public function getShouldBeIndexed()
	{
		return $this->getProperty()->shouldBeIndexed;
	}
	
	public function getShouldBePublished()
	{
		return $this->getProperty()->shouldBePublished;
	}
	
	public function getText()
	{
		return $this->getProperty()->text;
	}
	
	public function publish()
	{
		if( $this->getProperty()->shouldBePublished )
		{
			$service = $this->getService();
			$service->publish( 
				$service->createId( $this->getType(), $this->getId() ) );
		}
		return $this;
	}
	
	public function setData( $data )
	{
		$this->getProperty()->data = $data;
		return $this;
	}
	
	public function setMaintainAbsoluteLinks( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $required must be a boolean" );
		
		$this->getProperty()->maintainAbsoluteLinks = $bool;
		
		return $this;
	}
	
	public function setRewriteLinks( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $required must be a boolean" );
		
		$this->getProperty()->rewriteLinks = $bool;
		
		return $this;
	}
	
	public function setShouldBeIndexed( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $required must be a boolean" );
			
		$this->getProperty()->shouldBeIndexed = $bool;
		return $this;
	}
	
	public function setShouldBePublished( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $required must be a boolean" );
			
		$this->getProperty()->shouldBePublished = $bool;
		return $this;
	}
	
	public function setText( $text )
	{
		$this->getProperty()->text = $text;
		$this->getProperty()->data = $text;
		return $this;
	}
}
?>