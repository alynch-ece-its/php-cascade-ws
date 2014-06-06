<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Metadata extends Property
{
	public function __construct( stdClass $obj, $service=NULL, $metadata_set_id='' )
	{
		$this->author              = $obj->author;
		$this->display_name        = $obj->displayName;
		$this->end_date            = $obj->endDate;
		$this->keywords            = $obj->keywords;
		$this->meta_description    = $obj->metaDescription;
		$this->review_date         = $obj->reviewDate;
		$this->start_date          = $obj->startDate;
		$this->summary             = $obj->summary;
		$this->teaser              = $obj->teaser;
		$this->title               = $obj->title;
		$this->service             = $service;
		$this->metadata_set        = NULL;
		$this->metadata_set_id     = $metadata_set_id;
		
		if( $obj->dynamicFields != NULL ) // could be NULL
		{
			$this->processDynamicFields( $obj->dynamicFields->dynamicField );
		}
	}
	
	public function getAuthor()
	{
		return $this->author;
	}
	
	public function getDisplayName()
	{
		return $this->display_name;
	}
	
	public function getDynamicField( $name )
	{
		$name = trim( $name );
		
		if( $name == '' )
			throw new EmptyNameException( "The name cannot be empty." );
	
		foreach( $this->dynamic_fields as $field )
		{
			if( $field->getName() == $name )
				return $field;
		}
		
		throw new NoSuchFieldException( "The dynamic field $name does not exist" );
	}

	public function getDynamicFieldValues( $name )
	{
		$name = trim( $name );
		
		if( $name == '' )
			throw new EmptyNameException( "The name cannot be empty." );
	
		$field = $this->getDynamicField( $name );
		
		return $field->getFieldValue()->getValues();
	}
	
	public function getDynamicFieldPossibleValues( $name )
	{
		return $this->getMetadataSet()->getDynamicMetadataFieldPossibleValueStrings( $name );
	}
	
	public function getDynamicFieldNames()
	{
		return $this->dynamic_field_names;
	}
	
	public function getDynamicFields()
	{
		return $this->dynamic_fields;
	}
	
	public function getEndDate()
	{
		return $this->end_date;
	}
	
	public function getKeywords()
	{
		return $this->keywords;
	}
	
	public function getMetadataSet()
	{
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}

		return $this->metadata_set;
	}
	
	public function getMetaDescription()
	{
		return $this->meta_description;
	}
	
	public function getReviewDate()
	{
		return $this->review_date;
	}
	
	public function getStartDate()
	{
		return $this->start_date;
	}
	
	public function getSummary()
	{
		return $this->summary;
	}
	
	public function getTeaser()
	{
		return $this->teaser;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function hasDynamicField( $name )
	{
		if( $name == '' )
			throw new EmptyNameException( "The name cannot be empty." );
	
		return in_array( $name, $this->dynamic_field_names );
	}
	
	public function setAuthor( $author )
	{
		$author = trim( $author );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getAuthorFieldRequired() && $author == '' )
		{
			throw new RequiredFieldException( "The author field is required." );
		}

		$this->author = $author;
		return $this;
	}
	
	public function setDisplayName( $display_name )
	{
		$display_name = trim( $display_name );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getDisplayNameFieldRequired() && $display_name == '' )
		{
			throw new RequiredFieldException( "The displayName field is required." );
		}

		$this->display_name = $display_name;
		return $this;
	}
	
	public function setDynamicField( $field, $values )
	{
		return $this->setDynamicFieldValue( $field, $values );
	}
	
	public function setDynamicFieldValue( $field, $values ) // string values
	{
		if( !is_array( $values ) )
		{
			$values = array( $values );
		}
		
		$v_count = count( $values );
		
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = Asset::getAsset( 
				$this->service, MetadataSet::TYPE, $this->metadata_set_id );
		}
		
		$df_def     = $this->metadata_set->getDynamicMetadataFieldDefinition( $field );
		$field_type = $df_def->getFieldType();
		$required   = $df_def->getRequired();
		$df         = $this->getDynamicField( $field );
		
		// text can accept anything
		if( $field_type == T::TEXT && $v_count == 1 )
		{
			$value = $values[0];
			
			if( $value == NULL ) // turn NULL to empty string
				$value = '';
			
			if( $required && $value == '' )
			{
				throw new RequiredFieldException( "The $field_type requires non-empty value" );
			}
			
			$v = new stdClass();
			$v->value = $value;
			$df->setValue( array( $v) );
		}
		// radio and dropdown can accept only one value
		else if( ( $field_type == T::RADIO || $field_type == T::DROPDOWN ) &&
			$v_count == 1 )
		{
			$value = $values[0]; // read first value
			
			if( $value == '' ) // turn empty string to NULL
				$value = NULL;
			
			if( $required && $value == NULL ) // cannot be empty if required
				throw new RequiredFieldException( "The $field_type requires non-empty value" );
			
			$possible_values = $df_def->getPossibleValueStrings(); // read from metadataSet
			
			if( !in_array( $value, $possible_values ) && $value != NULL ) // undefined value
				throw new NoSuchValueException( "The value $value does not exist" );
			
			$v = new stdClass();
			
			if( $value != '' )
				$v->value = $value;
		
			$df->setValue( array( $v ) );
		}
		else if( ( $field_type == T::CHECKBOX || $field_type == T::MULTISELECT ) &&
			$v_count > 0 )
		{
			if( $required && ( in_array( NULL, $values) || in_array( '', $values ) ) )
			{
				throw new RequiredFieldException( "The $field_type requires non-empty value" );
			}
		
			$possible_values = $df_def->getPossibleValueStrings();
			
			foreach( $values as $value )
			{
				if( !in_array( $value, $possible_values ) && $value != NULL )
				{
					throw new NoSuchValueException( "The value $value does not exist" );
				}
			}
			
			$v_array = array();
			
			foreach( $values as $value )
			{
				$v = new stdClass();
				$v->value = $value;
				$v_array[] = $v;
			}
			
			$df->setValue( $v_array );
		}
		
		return $this;
	}
	
	public function setDynamicFieldValues( $field, $values )
	{
		return $this->setDynamicField( $field, $values );
	}
	
	public function setEndDate( $end_date )
	{
		$end_date = trim( $end_date );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getEndDateFieldRequired() && $end_date == '' )
		{
			throw new RequiredFieldException( "The endDate field is required." );
		}

		$this->end_date = $end_date;
		return $this;
	}
	
	public function setKeywords( $keywords )
	{
		$keywords = trim( $keywords );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getKeywordsFieldRequired() && $keywords == '' )
		{
			throw new RequiredFieldException( "The keywords field is required." );
		}

		$this->keywords = $keywords;
		return $this;
	}
	
	public function setMetaDescription( $meta_description )
	{
		$meta_description = trim( $meta_description );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getDescriptionFieldRequired() && $meta_description == '' )
		{
			throw new RequiredFieldException( "The metaDescription field is required." );
		}

		$this->meta_description = $meta_description;
		return $this;
	}
	
	public function setReviewDate( $review_date )
	{
		$review_date = trim( $review_date );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getReviewDateFieldRequired() && $review_date == '' )
		{
			throw new RequiredFieldException( "The reviewDate field is required." );
		}

		$this->review_date = $review_date;
		return $this;
	}
	
	public function setStartDate( $start_date )
	{
		$start_date = trim( $start_date );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getStartDateFieldRequired() && $start_date == '' )
		{
			throw new RequiredFieldException( "The startDate field is required." );
		}

		$this->start_date = $start_date;
		return $this;
	}
	
	public function setSummary( $summary )
	{
		$summary = trim( $summary );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getSummaryFieldRequired() && $summary == '' )
		{
			throw new RequiredFieldException( "The summary field is required." );
		}

		$this->summary = $summary;
		return $this;
	}
	
	public function setTeaser( $teaser )
	{
		$teaser = trim( $teaser );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getTeaserFieldRequired() && $teaser == '' )
		{
			throw new RequiredFieldException( "The teaser field is required." );
		}

		$this->teaser = $teaser;
		return $this;
	}
	
	public function setTitle( $title )
	{
		$title = trim( $title );
	
		if( $this->metadata_set == NULL )
		{
			$this->metadata_set = new MetadataSet( 
		    	$this->service, $this->service->createId( 
		        	MetadataSet::TYPE, $this->metadata_set_id ) );
		}
		        
		if( $this->metadata_set->getTitleFieldRequired() && $title == '' )
		{
			throw new RequiredFieldException( "The title field is required." );
		}

		$this->title = $title;
		return $this;
	}
	
	public function toStdClass()
	{
		$obj                  = new stdClass();
		$obj->author          = $this->author;
		$obj->displayName     = $this->display_name;
		$obj->endDate         = $this->end_date;
		$obj->keywords        = $this->keywords;
		$obj->metaDescription = $this->meta_description;
		$obj->reviewDate      = $this->review_date;
		$obj->startDate       = $this->start_date;
		$obj->summary         = $this->summary;
		$obj->teaser          = $this->teaser;
		$obj->title           = $this->title;

		$count = count( $this->dynamic_fields );
		
		if( $count == 0 )
		{
			$obj->dynamicFields = NULL;
		}
		else if( $count == 1 )
		{
			$obj->dynamicFields = $this->dynamic_fields[0]->toStdClass();
		}
		else
		{
			$obj->dynamicFields->dynamicField = array();
			
			for( $i = 0; $i < $count; $i++ )
			{
				$obj->dynamicFields->dynamicField[] = 
				    $this->dynamic_fields[$i]->toStdClass();
			}
		}
		
		return $obj;
	}
	
	private function processDynamicFields( $fields )
	{
		$this->dynamic_fields      = array();
		$this->dynamic_field_names = array();

		if( !is_array( $fields ) )
		{
			$fields = array( $fields );
		}
		
		foreach( $fields as $field )
		{
			$df = new DynamicField( $field );
			$this->dynamic_fields[] = $df;
			$this->dynamic_field_names[] = $field->name;
		}
	}

	private $author;
	private $display_name;
	private $end_date;
	private $keywords;
	private $meta_description;
	private $review_date;
	private $start_date;
	private $summary;
	private $teaser;
	private $title;
	private $dynamic_fields;
	private $dynamic_field_names;
	private $service;
	private $metadata_set_id;
}
?>
