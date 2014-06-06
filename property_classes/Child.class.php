<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/12/2014 data in $c can be NULL, for audit
 */
class Child extends Property
{
	public function __construct( stdClass $c )
	{
/*
		if( $c == NULL || 
			!isset( $c->id ) ||
			//!isset( $c->path ) ||
			!isset( $c->type ) ||
			!isset( $c->recycled )
		)
		{
			throw new EmptyValueException( 
			    "The data in the stdClass object is not complete." . BR );
		}
*/
		if( $c != NULL )
		{
			$this->id       = $c->id;
			if( $c->path != NULL )
				$this->path = new Path( $c->path );
			else
				$this->path = NULL;
			$this->type     = $c->type;
			$this->recycled = $c->recycled;
		}
	}
	
	public function display()
	{
		echo "Type: " . $this->type . BR .
			"Path: "  . $this->path->getPath() . BR .
			"ID: "    . $this->id . BR . BR;
	}
	
	public function getAsset( AssetOperationHandlerService $service )
	{
		if( $service == NULL )
			throw new NullServiceException( "The service object cannot be NULL." );
			
		return Asset::getAsset( $service, $this->type, $this->id );
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getPath()
	{
		return $this->path;
	}
	
	public function getPathPath()
	{
		return $this->path->getPath();
	}
	
	public function getPathSiteId()
	{
		return $this->path->getSiteId();
	}
	
	public function getPathSiteName()
	{
		return $this->path->getSiteName();
	}
	
	public function getRecycled()
	{
		return $this->recycled;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function toLiString()
	{
		return S_LI . $this->type . " " . 
			$this->path->getPath() . " " . $this->id . E_LI;
	}
	
	public function toStdClass()
	{
		$obj           = new stdClass();
		$obj->id       = $this->id;
		$obj->path     = $this->path->toStdClass();
		$obj->type     = $this->type;
		$obj->recycled = $this->recycled;
		return $obj;
	}
	
	public function toXml( $indent )
	{
		return $indent . "<" . $this->type . " path=\"" .
			$this->path->getPath() . "\" id=\"" . $this->id . "\"/>\n";
	}
	
	private $id;
	private $path;
	private $type;
	private $recycled;
}
?>
