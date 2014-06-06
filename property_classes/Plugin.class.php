<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Plugin extends Property
{
	public function __construct( stdClass $p=NULL )
	{
		if( $p != NULL )
		{
			$this->name  = $p->name;
			
			if( $p->parameters->parameter != NULL )
			{
				$this->processParameters( $p->parameters->parameter );
			}
		}
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getParameter( $name )
	{
		foreach( $this->parameters as $parameter )
		{
			if( $parameter->getName() == $name )
			{
				return $parameter;
			}
		}
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
	
	public function hasParameter( $name )
	{
		foreach( $this->parameters as $parameter )
		{
			if( $parameter->getName() == $name )
			{
				return true;
			}
		}
		return false;
	}
	
	public function setParameterValue( $name, $value )
	{
		$parameter = $this->getParameter( $name );
		$parameter->setValue( $value );
		
		return $this;
	}
	
	public function toStdClass()
	{
		$obj       = new stdClass();
		$obj->name = $this->name;
		$count     = count( $this->parameters );
		
		if( $count == 0 )
		{
			$obj->parameters = new stdClass();
		}
		else if( $count == 1 )
		{
			$obj->parameters->parameter = $this->parameters[0];
		}
		else
		{
			$obj->parameters->parameter = array();
			
			foreach( $this->parameters as $parameter )
			{
				$obj->parameters->parameter[] = $parameter->toStdClass();
			}
		}
		
		return $obj;
	}
	
	private function processParameters( $parameters )
	{
		$this->parameters = array();

		if( !is_array( $parameters ) )
		{
			$parameters = array( $parameters );
		}
		foreach( $parameters as $parameter )
		{
			$this->parameters[] = new Parameter( $parameter );
		}
	}

	private $name;
	private $parameters;
}
?>