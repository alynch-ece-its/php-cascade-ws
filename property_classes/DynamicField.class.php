<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class DynamicField extends Property
{
	public function __construct( stdClass $f=NULL )
	{
		if( $f != NULL )
		{
			$this->name = $f->name;
			
			if( $f->fieldValues->fieldValue != NULL )
			{
				// can be an object, one value or NULL
				// can be an array
				$this->processFieldValues( $f->fieldValues->fieldValue );
			}
			else
			{
				$this->field_values = new FieldValue( new stdClass() );
			}
		}
	}
	
	public function getFieldValue()
	{
		return $this->field_values;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setValue( $values )
	{
		if( !is_array( $values ) )
		{
			$values = array( $values );
		}

		$this->field_values->setValues( $values );
	
		return $this;
	}
	
	public function toStdClass()
	{
		if( !isset( $this->name ) )
			return NULL;
			
		$obj = new stdClass();
		$obj->name = $this->name;
		
		if( $this->field_values != NULL )
		{
			$field_values = $this->field_values->toStdClass();
		}
		else
		{
			$field_values = new stdClass();
		}
		
		$obj->fieldValues = $field_values;
		
		return $obj;
	}
	
	private function processFieldValues( $values )
	{
		if( is_array( $values ) )
		{
			$obj = new stdClass();
			$obj->array = $values;
		}
		else
		{
			$obj = $values;
		}
		
		$this->field_values = new FieldValue( $obj );
	}
	
	private $name;
	private $field_values;
}
?>