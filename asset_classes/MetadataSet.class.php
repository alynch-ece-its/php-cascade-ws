<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class MetadataSet extends ContainedAsset
{
	const DEBUG    = false;
	const TYPE     = T::METADATASET;
	const HIDDEN   = T::HIDDEN;
	const INLINE   = T::INLINE;
	const VISIBLE  = T::VISIBLE;
	
	const AUTHOR      = "author";
	const DESCRIPTION = "description";
	const DISPLAYNAME = "display-name";
	const KEYWORDS    = "keywords";
	const SUMMARY     = "summary";
	const TEASER      = "teaser";
	const TITLE       = "title";
	
	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		
		if( $this->getProperty()->dynamicMetadataFieldDefinitions->dynamicMetadataFieldDefinition != NULL )
		{
			$this->processDynamicMetadataFieldDefinition();
		}
	}
	
	/**
	 * Appends a value/item to the end of a field.
	 */
	public function appendValue( $name, $value )
	{
		$value = trim( $value );
		
		if( $value == '' )
			throw new EmptyValueException( "The value cannot be empty." );
			
		$def = $this->getDynamicMetadataFieldDefinition( $name );
		$def->appendValue( $value );
		return $this;
	}
	
	public function copy( $par_id, $new_name )
	{
		$service         = $this->getService();
		$self_identifier = $service->createId( MetadataSet::TYPE, $this->getId() );
		
		$service->copy( $self_identifier, $par_id, $new_name, false );
		
		if( $service->isSuccessful() )
		{
			// get the parent
			$parent_id = $par_id->id;
			$parent    = $service->retrieve(
				$service->createId( T::METADATASETCONTAINER, $parent_id ), 
				P::METADATASETCONTAINER);
				
			// look for the new child
			foreach( $parent->children->child as $child )
			{
				$child_path = $child->path->path;
				$child_path_array = explode( '/', $child_path );
				
				if( in_array( $new_name, $child_path_array ) )
				{
					$child_found = $child;
					break;
				}
			}
			// get the digital id
			$child_id = $child_found->id;
			// return new object
			return new MetadataSet( $service, $service->createId( MetadataSet::TYPE, $child_id ) );
		}
		else
		{
			throw new Exception( "Failed to copy the asset." );
		}
	}
	
	public function edit()
	{
		$asset = new stdClass();
		
		$metadata_set = $this->getProperty();
		$metadata_set->dynamicMetadataFieldDefinitions->
		    dynamicMetadataFieldDefinition = array();
		    
		foreach( $this->dynamic_metadata_field_definitions as $definition )
		{
			$metadata_set->dynamicMetadataFieldDefinitions->
		    	dynamicMetadataFieldDefinition[] = $definition->toStdClass();
		}
		
		$asset->{ $p = $this->getPropertyName() } = $metadata_set;
		// edit asset
		$service = $this->getService();
		$service->edit( $asset );
		
		if( !$service->isSuccessful() )
		{
			throw new EditingFailureException( 
				"Failed to edit the asset. " . $service->getMessage() );
		}
		return $this->reloadProperty();
	}
	
	public function getAuthorFieldRequired()
	{
		return $this->getProperty()->authorFieldRequired;
	}
	
	public function getAuthorFieldVisibility()
	{
		return $this->getProperty()->authorFieldVisibility;
	}
	
	public function getDescriptionFieldRequired()
	{
		return $this->getProperty()->descriptionFieldRequired;
	}
	
	public function getDescriptionFieldVisibility()
	{
		return $this->getProperty()->descriptionFieldVisibility;
	}
	
	public function getDisplayNameFieldRequired()
	{
		return $this->getProperty()->displayNameFieldRequired;
	}
	
	public function getDisplayNameFieldVisibility()
	{
		return $this->getProperty()->displayNameFieldVisibility;
	}
	
	public function getDynamicMetadataFieldDefinition( $name )
	{
		if( !$this->hasDynamicMetadataFieldDefinition( $name ) )
			throw new NoSuchMetadataFieldDefinitionException( 
				"The definition $name does not exist" );
		
		foreach( $this->dynamic_metadata_field_definitions as $definition )
		{
			if( $definition->getName() == $name )
				return $definition;
		}
	}
	
	public function getDynamicMetadataFieldDefinitionNames()
	{
		return $this->field_names;
	}
	
	public function getDynamicMetadataFieldPossibleValueStrings( $name )
	{
		if( !$this->hasDynamicMetadataFieldDefinition( $name ) )
			throw new NoSuchMetadataFieldDefinitionException( 
				"The definition $name does not exist" );
				
		foreach( $this->dynamic_metadata_field_definitions as $definition )
		{
			if( $definition->getName() == $name )
				return $definition->getPossibleValueStrings();
		}
	}

	public function getEndDateFieldRequired()
	{
		return $this->getProperty()->endDateFieldRequired;
	}
	
	public function getEndDateFieldVisibility()
	{
		return $this->getProperty()->endDateFieldVisibility;
	}
	
	public function getKeywordsFieldRequired()
	{
		return $this->getProperty()->keywordsFieldRequired;
	}
	
	public function getKeywordsFieldVisibility()
	{
		return $this->getProperty()->keywordsFieldVisibility;
	}
	
	// used by WordPressConnector
	public function getNonHiddenWiredFieldNames()
	{
		$fields = array();
		
		if( $this->getProperty()->authorFieldVisibility != self::HIDDEN )
			$fields[] = self::AUTHOR;
		if( $this->getProperty()->descriptionFieldVisibility != self::HIDDEN )
			$fields[] = self::DESCRIPTION;
		if( $this->getProperty()->displayNameFieldVisibility != self::HIDDEN )
			$fields[] = self::DISPLAYNAME;
		if( $this->getProperty()->keywordsFieldVisibility != self::HIDDEN )
			$fields[] = self::KEYWORDS;
		if( $this->getProperty()->summaryFieldVisibility != self::HIDDEN )
			$fields[] = self::SUMMARY;
		if( $this->getProperty()->teaserFieldVisibility != self::HIDDEN )
			$fields[] = self::TEASER;
		if( $this->getProperty()->titleFieldVisibility != self::HIDDEN )
			$fields[] = self::TITLE;
			
		return $fields;
	}
	
	public function getParentContainerId()
	{
		return $this->getProperty()->parentContainerId;
	}
	
	public function getParentContainerPath()
	{
		return $this->getProperty()->parentContainerPath;
	}
	
	public function getReviewDateFieldRequired()
	{
		return $this->getProperty()->reviewDateFieldRequired;
	}
	
	public function getReviewDateFieldVisibility()
	{
		return $this->getProperty()->reviewDateFieldVisibility;
	}
	
	public function getStartDateFieldRequired()
	{
		return $this->getProperty()->startDateFieldRequired;
	}
	
	public function getStartDateFieldVisibility()
	{
		return $this->getProperty()->startDateFieldVisibility;
	}
	
	public function getSummaryFieldRequired()
	{
		return $this->getProperty()->summaryFieldRequired;
	}
	
	public function getSummaryFieldVisibility()
	{
		return $this->getProperty()->summaryFieldVisibility;
	}
	
	public function getTeaserFieldRequired()
	{
		return $this->getProperty()->teaserFieldRequired;
	}
	
	public function getTeaserFieldVisibility()
	{
		return $this->getProperty()->teaserFieldVisibility;
	}
	
	public function getTitleFieldRequired()
	{
		return $this->getProperty()->titleFieldRequired;
	}
	
	public function getTitleFieldVisibility()
	{
		return $this->getProperty()->titleFieldVisibility;
	}
	
	public function hasDynamicMetadataFieldDefinition( $name )
	{
		return in_array( $name, $this->field_names );
	}
	
	public function removeDynamicMetadataFieldDefinition( $name )
	{
		if( !in_array( $name, $this->field_names ) )
		{
			throw new NoSuchFieldException( "The field $name does not exist." );
		}
		
		$count = count( $this->dynamic_metadata_field_definitions );
		
		for( $i = 0; $i < $count; $i++ )
		{
			if( $this->dynamic_metadata_field_definitions[ $i ]->getName() == $name )
			{
				$before       = array_slice( $this->dynamic_metadata_field_definitions, 0, $i );
				$names_before = array_slice( $this->field_names, 0, $i );
				$after        = array();
				$names_after  = array();
				
				if( $count - $i > 1 )
				{
					$after       = array_slice( $this->dynamic_metadata_field_definitions, $i + 1 );
					$names_after = array_slice( $this->field_names, $i + 1 );
				}
				$this->dynamic_metadata_field_definitions = array_merge( $before, $after );
				$this->field_names = array_merge( $names_before, $names_after );
				break;
			}
		}
	
		return $this;
	}
	
	public function removeValue( $name, $value )
	{
		$value = trim( $value );
		
		if( $value == '' )
			throw new EmptyValueException( "The value cannot be empty." );
			
		$def = $this->getDynamicMetadataFieldDefinition( $name );
		$def->removeValue( $value );
		return $this;
	}
	
	public function setAuthorFieldRequired( $author_field_required=false )
	{
		if( !BooleanValues::isBoolean( $author_field_required ) )
			throw new UnacceptableValueException( "The value $author_field_required must be a boolean" );
			
		$this->getProperty()->authorFieldRequired = $author_field_required;
		return $this;
	}
	
	public function setAuthorFieldVisibility( $author_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $author_field_visibility ) )
			throw new UnacceptableValueException( "The value $author_field_visibility is not acceptable" );

		$this->getProperty()->authorFieldVisibility = $author_field_visibility;
		return $this;
	}
	
	public function setDescriptionFieldRequired( $description_field_required=false )
	{
		if( !BooleanValues::isBoolean( $description_field_required ) )
			throw new UnacceptableValueException( "The value $description_field_required must be a boolean" );
			
		$this->getProperty()->descriptionFieldRequired = $description_field_required;
		return $this;
	}
	
	public function setDescriptionFieldVisibility( $description_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $description_field_visibility=self::HIDDEN ) )
			throw new UnacceptableValueException( "The value $description_field_visibility is not acceptable" );
		
		$this->getProperty()->descriptionFieldVisibility = $description_field_visibility;
		return $this;
	}
	
	public function setDisplayNameFieldRequired( $display_name_field_required=false )
	{
		if( !BooleanValues::isBoolean( $display_name_field_required ) )
			throw new UnacceptableValueException( "The value $display_name_field_required must be a boolean" );
			
		$this->getProperty()->displayNameFieldRequired = $display_name_field_required;
		return $this;
	}
	
	public function setDisplayNameFieldVisibility( $display_name_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $display_name_field_visibility=self::HIDDEN ) )
			throw new UnacceptableValueException( "The value $display_name_field_visibility is not acceptable" );
		
		$this->getProperty()->displayNameFieldVisibility = $display_name_field_visibility;
		return $this;
	}
	
	public function setEndDateFieldRequired( $end_date_field_required=false )
	{
		if( !BooleanValues::isBoolean( $end_date_field_required ) )
			throw new UnacceptableValueException( "The value $end_date_field_required must be a boolean" );
			
		$this->getProperty()->endDateFieldRequired = $end_date_field_required;
		return $this;
	}
	
	public function setEndDateFieldVisibility( $end_date_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $end_date_field_visibility ) )
			throw new UnacceptableValueException( "The value $end_date_field_visibility is not acceptable" );
		
		$this->getProperty()->endDateFieldVisibility = $end_date_field_visibility;
		return $this;
	}
	
	public function setKeywordsFieldRequired( $keywords_field_required=false )
	{
		if( !BooleanValues::isBoolean( $keywords_field_required ) )
			throw new UnacceptableValueException( "The value $keywords_field_required must be a boolean" );
			
		$this->getProperty()->keywordsFieldRequired = $keywords_field_required;
		return $this;
	}
	
	public function setKeywordsFieldVisibility( $keywords_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $keywords_field_visibility ) )
			throw new UnacceptableValueException( "The value $keywords_field_visibility is not acceptable" );
		
		$this->getProperty()->keywordsFieldVisibility = $keywords_field_visibility;
		return $this;
	}
	
	public function setLabel( $name, $label )
	{
		$label = trim( $label );
		
		if( $label == '' )
			throw new EmptyValueException( "The label cannot be empty." );
	
		if( $this->hasDynamicMetadataFieldDefinition( $name ) )
		{
			$d = $this->getDynamicMetadataFieldDefinition( $name );
			$d->setLabel( $label );
			
			return $this;
		}
		else
		{
			throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
		}
	}
	
	public function setRequired( $name, $required )
	{
		if( !BooleanValues::isBoolean( $required ) )
			throw new UnacceptableValueException( "The value $required must be a boolean" );
			
		if( $this->hasDynamicMetadataFieldDefinition( $name ) )
		{
			$d = $this->getDynamicMetadataFieldDefinition( $name );
			$d->setRequired( $required );
			
			return $this;
		}
		else
		{
			throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
		}
	}
	
	public function setReviewDateFieldRequired( $review_date_field_required=false )
	{
		if( !BooleanValues::isBoolean( $review_date_field_required ) )
			throw new UnacceptableValueException( "The value $review_date_field_required must be a boolean" );
			
		$this->getProperty()->reviewDateFieldRequired = $review_date_field_required;
		return $this;
	}
	
	public function setReviewDateFieldVisibility( $review_date_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $review_date_field_visibility ) )
			throw new UnacceptableValueException( "The value $review_date_field_visibility is not acceptable" );
		
		$this->getProperty()->reviewDateFieldVisibility = $review_date_field_visibility;
		return $this;
	}
	
	public function setSelectedByDefault( $name, $value )
	{
		$value = trim( $value );
		
		if( $value == '' )
			throw new EmptyValueException( "The value cannot be empty." );
	
		if( $this->hasDynamicMetadataFieldDefinition( $name ) )
		{
			$d = $this->getDynamicMetadataFieldDefinition( $name );
			
			if( $d->hasPossibleValue( $value ) )
			{
				$d->setSelectedByDefault( $value );
			}
		}
		else
		{
			throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
		}
			
		return $this;
	}
	
	public function setStartDateFieldRequired( $start_date_field_required=false )
	{
		if( !BooleanValues::isBoolean( $start_date_field_required ) )
			throw new UnacceptableValueException( "The value $start_date_field_required must be a boolean" );
			
		$this->getProperty()->startDateFieldRequired = $start_date_field_required;
		return $this;
	}
	
	public function setStartDateFieldVisibility( $start_date_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $start_date_field_visibility ) )
			throw new UnacceptableValueException( "The value $start_date_field_visibility is not acceptable" );
		
		$this->getProperty()->startDateFieldVisibility = $start_date_field_visibility;
		return $this;
	}
	
	public function setSummaryFieldRequired( $summary_field_required=false )
	{
		if( !BooleanValues::isBoolean( $summary_field_required ) )
			throw new UnacceptableValueException( "The value $summary_field_required must be a boolean" );
			
		$this->getProperty()->summaryFieldRequired = $summary_field_required;
		return $this;
	}
	
	public function setSummaryFieldVisibility( $summary_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $summary_field_visibility ) )
			throw new UnacceptableValueException( "The value $summary_field_visibility is not acceptable" );
		
		$this->getProperty()->summaryFieldVisibility = $summary_field_visibility;
		return $this;
	}
	
	public function setTeaserFieldRequired( $teaser_field_required=false )
	{
		if( !BooleanValues::isBoolean( $teaser_field_required ) )
			throw new UnacceptableValueException( "The value $teaser_field_required must be a boolean" );
			
		$this->getProperty()->teaserFieldRequired = $teaser_field_required;
		return $this;
	}
	
	public function setTeaserFieldVisibility( $teaser_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $teaser_field_visibility ) )
			throw new UnacceptableValueException( "The value $teaser_field_visibility is not acceptable" );
		
		$this->getProperty()->teaserFieldVisibility = $teaser_field_visibility;
		return $this;
	}
	
	public function setTitleFieldRequired( $title_field_required=false )
	{
		if( !BooleanValues::isBoolean( $title_field_required ) )
			throw new UnacceptableValueException( "The value $title_field_required must be a boolean" );
			
		$this->getProperty()->titleFieldRequired = $title_field_required;
		return $this;
	}
	
	public function setTitleFieldVisibility( $title_field_visibility=self::HIDDEN )
	{
		if( !VisibilityValues::isVisibility( $title_field_visibility ) )
			throw new UnacceptableValueException( "The value $title_field_visibility is not acceptable" );
		
		$this->getProperty()->titleFieldVisibility = $title_field_visibility;
		return $this;
	}
	
	public function setVisibility( $name, $visibility )
	{
		if( !VisibilityValues::isVisibility( $visibility ) )
			throw new UnacceptableValueException( "The value $visibility is not acceptable" );

		if( $this->hasDynamicMetadataFieldDefinition( $name ) )
		{
			$d = $this->getDynamicMetadataFieldDefinition( $name );
			
			if( $visibility == self::VISIBLE || $visibility == self::INLINE || $visibility == self::HIDDEN )
			{
				$d->setVisibility( $visibility );
				return $this;
			}
			else
			{
				throw new NoSuchVisibilityException( "The definition $name does not exist" );
			}
		}
		else
		{
			throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
		}
	}
	
	public function swapDynamicMetadataFieldDefinitions( $def1, $def2 )
	{
		if( $def1 == '' || $def2 == '' )
			throw new EmptyValueException( "The value cannot be empty." );
			
		if( !in_array( $def1, $this->field_names ) )
			throw new NoSuchFieldException( "The definition $def1 does not exist" );
		
		if( !in_array( $def1, $this->field_names ) )
			throw new NoSuchFieldException( "The definition $def2 does not exist" );
			
		$first_def_pos  = -1;
		$second_def_pos = -1;
			
		$count = count( $this->dynamic_metadata_field_definitions );
	
		for( $i = 0; $i < $count; $i++ )
		{
			if( $this->dynamic_metadata_field_definitions[ $i ]->getName() == $def1 )
			{
				$first_def_pos = $i;
			}
			
			if( $this->dynamic_metadata_field_definitions[ $i ]->getName() == $def2 )
			{
				$second_def_pos = $i;
			}
		}
		
		$temp = $this->dynamic_metadata_field_definitions[ $first_def_pos ];
		$this->dynamic_metadata_field_definitions[ $first_def_pos ] = 
		    $this->dynamic_metadata_field_definitions[ $second_def_pos ];
		$this->dynamic_metadata_field_definitions[ $second_def_pos ] = $temp;
		
		return $this;
	}
	
	public function swapFields( $def1, $def2 )
	{
		return $this->swapDynamicMetadataFieldDefinitions( $def1, $def2 );
	}
	
	public function swapValues( $name, $value1, $value2 )
	{
		$def = $this->getDynamicMetadataFieldDefinition( $name );
		$def->swapValues( $value1, $value2 );
		return $this;
	}
	
	public function unsetSelectedByDefault( $name, $value )
	{
		$value = trim( $value );
		
		if( $value == '' )
			throw new EmptyValueException( "The value cannot be empty." );
	
		if( $this->hasDynamicMetadataFieldDefinition( $name ) )
		{
			$d = $this->getDynamicMetadataFieldDefinition( $name );
			
			if( $d->hasPossibleValue( $value ) )
			{
				$d->unsetSelectedByDefault( $value );
			}
		}
		else
		{
			throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
		}
			
		return $this;
	}
	
	private function processDynamicMetadataFieldDefinition()
	{
		$this->dynamic_metadata_field_definitions = array();
		$this->field_names                        = array();

		$definitions = 
		    $this->getProperty()->dynamicMetadataFieldDefinitions->
		    dynamicMetadataFieldDefinition;
		    
		if( !is_array( $definitions ) )
		{
			$definitions = array( $definitions );
		}
		
		$count = count( $definitions );
		
		for( $i = 0; $i < $count; $i++ )
		{
			$this->dynamic_metadata_field_definitions[] = 
				new DynamicMetadataFieldDefinition( $definitions[ $i ] );
			$this->field_names[] = $definitions[ $i ]->name;
		}
	}

	private $dynamic_metadata_field_definitions;
	private $field_names;
}
?>
