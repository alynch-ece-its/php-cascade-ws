<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class DataDefinition extends ContainedAsset
{
	const DEBUG     = false;
	const TYPE      = T::DATADEFINITION;
	const DELIMITER = ';';

    /**
    * The constructor
    * @param $service the AssetOperationHandlerService object
    * @param $identifier the identifier object
    */
	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		$this->xml             = $this->getProperty()->xml;
		//$this->id              = $this->getProperty()->id;
		$this->attributes      = array();
		// process the xml
		$this->processSimpleXMLElement( new SimpleXMLElement( $this->xml ) );
		// fully qualified identifiers
		$this->identifiers = array_keys( $this->attributes );
	}
	
	public function display()
	{
		$xml_string = XMLUtility::replaceBrackets( $this->xml );
		
		echo S_H2 . "XML" . E_H2 .
		     S_PRE . $xml_string . E_PRE . HR;
		echo S_H2 . "Attributes" . E_H2 . S_PRE;
		var_dump( $this->attributes );
		echo E_PRE . HR;
		
		return $this;
	}
	
	public function displayAttributes()
	{
		echo S_H2 . "Attributes" . E_H2 . S_PRE;
		var_dump( $this->attributes );
		echo E_PRE . HR;
		
		return $this;
	}
	
	public function displayXml( $formatted=true )
	{
		if( $formatted )
		{
			$xml_string = XMLUtility::replaceBrackets( $this->xml );
			echo S_H2 . "XML" . E_H2 . S_PRE;
		}

		echo $xml_string;
		
		if( $formatted )
			 echo E_PRE . HR;
		
		return $this;
	}
	
	public function edit()
	{
		$asset = new stdClass();
		$asset->{ $p = $this->getPropertyName() } = $this->getProperty();
		// edit asset
		$service = $this->getService();
		$service->edit( $asset );
		
		if( !$service->isSuccessful() )
		{
			throw new EditingFailureException( 
				M::EDIT_ASSET_FAILURE . $service->getMessage() );
		}
		return $this->reloadProperty();
	}

	public function getField( $field_name )
	{
		if( !in_array( $field_name, $this->identifiers ) )
			throw new NoSuchFieldException( 
			    "The field name $field_name does not exist." );

		return $this->attributes[ $field_name ];
	}
	
	public function getIdentifiers()
	{
		return $this->identifiers;
	}
	
	public function getXml()
	{
		return $this->xml;
	}

	public function hasField( $field_name )
	{
		return $this->hasIdentifier( $field_name );
	}
	
	public function hasIdentifier( $field_name )
	{
		return ( in_array( $field_name, $this->identifiers ) );
	}
	
	public function isMultiple( $field_name )
	{
		if( !in_array( $field_name, $this->identifiers ) )
		{
			throw new NoSuchFieldException( 
			    "The field name $field_name does not exist." );
		}
		
		if( isset( $this->attributes[ $field_name ]->multiple ) ) // group
		{
			return true;
		}
		else if( isset( $this->attributes[ $field_name ][ 0 ]->multiple ) )
		{
			return true;
		}
		
		return false;
	}
	
	public function setXml( $xml )
	{
		$this->getProperty()->xml = $xml;
		$this->xml = $xml;
		$this->processSimpleXMLElement( new SimpleXMLElement( $this->xml ) );

		return $this;
	}

	private function processSimpleXMLElement( $xml_element, $group_names='' )
	{
		foreach( $xml_element->children() as $child )
		{
			$type       = trim( $child->attributes()->{ $a = 'type' } );
			$name       = $child->getName();
			$identifier = $child[ 'identifier' ]->__toString();
			$old_group  = $group_names;
			
			if( $name == 'group' )
			{
				// if a field/group belongs to a group,
				// add the group name to the identifier
				$group_names    .= $identifier;
				$group_names    .= self::DELIMITER;
				$attributes      = $child->attributes();
				$attribute_array = array();
				// add the name
				$attribute_array[ 'name' ] = $name;

				// create the attribute array
				foreach( $attributes as $key => $value )
				{
					$attribute_array[$key] = $value->__toString();
				}
				// store attributes
				$this->attributes[ trim( $group_names, self::DELIMITER ) ] = $attribute_array;
				// recursively process children
				$this->processSimpleXMLElement( $child, $group_names );
				
				// reset parent name for siblings
				$group_names = $old_group;
			}
			else
			{
				$value_string = '';
				
				// process checkbox, dropdown, radio, selector
				if( $name == 'text' && isset( $type ) && $type != 'datetime' && $type != 'calendar' )
				{
					$item_name = '';
				
					// if type is not defined, then normal, multi-line, wysiwyg
					switch( $type )
					{
						case 'checkbox':
						case 'dropdown':
							$item_name = $type;
							break;
						case 'radiobutton':
							$item_name = 'radio';
							break;
						case 'multi-selector':
							$item_name = 'selector';
							break;
					}
				
					$text = array();
				
					foreach( $child->{$p = "$item_name-item"} as $item )
					{
						$text[] = $item->attributes()->{ $a = 'value' };
					}
					
					$value_string = implode( self::DELIMITER, $text );
				}
			
				$attributes      = $child->attributes();
				$attribute_array = array();
				// add the name
				$attribute_array[ 'name' ] = $name;
				
				// attach items for checkbox, dropdown, radio, selector
				if( $value_string != '' )
				{
					$attribute_array[ 'items' ] = $value_string;
				}
				// create the attribute array
				foreach( $attributes as $key => $value )
				{
					$attribute_array[$key] = $value->__toString();
				}
				
				// add identifier/attribute array to $this->attributes
				// add the first item
				$this->attributes[ $group_names . $identifier ] = $attribute_array;
			}
		}
	}
	
	private $attributes;      // all attributes of each field
	//private $id;              // the id string
	private $identifiers;     // all identifiers of fields
	private $xml;             // the definition xml
}
?>
