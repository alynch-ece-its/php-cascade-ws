<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class WorkflowDefinition extends ContainedAsset
{
	const DEBUG = false;
	const TYPE  = T::WORKFLOWDEFINITION;
	
	const NAMING_BEHAVIOR_AUTO       = T::AUTO_NAME;
	const NAMING_BEHAVIOR_DEFINITION = T::NAMEOFDEFINITION;
	const NAMING_BEHAVIOR_BLANK      = 'empty';
	
	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		$this->ordered_steps        = array();
		$this->non_ordered_steps    = array();
		$this->ordered_step_map     = array();
		$this->non_ordered_step_map = array();
		$this->triggers             = array();
		
		$simple_xml         = simplexml_load_string( $this->getProperty()->xml );
		$def_attr           = $simple_xml->attributes();
		$this->name         = $def_attr->name;
		$this->initial_step = $def_attr->{ "initial-step" };
		
		foreach( $simple_xml->triggers->trigger as $trigger )
		{
			$this->triggers[] = new TriggerDefinition( $trigger );
		}
		
		if( $simple_xml->steps )
		{
			foreach( $simple_xml->steps->step as $step )
			{
				$new_step = new StepDefinition( $step );
				$this->ordered_steps[] = $new_step;
				$this->ordered_step_map[ $new_step->getIdentifier() ] = $new_step;
			}
		}
		
		if( $simple_xml->{ "non-ordered-steps" } )
		{
			foreach( $simple_xml->{ "non-ordered-steps" }->step as $step )
			{
				$new_step = new StepDefinition( $step );
				$this->non_ordered_steps[] = $new_step;
				$this->non_ordered_step_map[ $new_step->getIdentifier() ] = $new_step;
			}
		}
	}
	
	public function addGroup( Group $g )
	{
		if( $g == NULL )
		{
			throw new NullAssetException( "The group cannot be NULL." );
		}
	
		$group_name   = $g->getName();
		$group_string = $this->getProperty()->applicableGroups;
		$group_array  = explode( ';', $group_string );
		
		if( !in_array( $group_name, $group_array ) )
		{
			$group_array[] = $group_name;
		}
		
		$group_string = implode( ';', $group_array );
		$this->getProperty()->applicableGroups = $group_string;
		return $this;
	}
	
	public function displayXml()
	{
		$xml_string = XMLUtility::replaceBrackets( 
			$this->getProperty()->xml );
		
		echo S_H2 . "XML" . E_H2 .
		     S_PRE . $xml_string . E_PRE . HR;
		
		return $this;
	}
	
	public function dump( $formatted=false )
	{
		parent::dump( $formatted );		
		$this->displayXml();
		return $this;
	}
	
	public function edit()
	{
		$asset                                    = new stdClass();
		$asset->{ $p = $this->getPropertyName() } = $this->getProperty();
		
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

	public function getApplicableGroups()
	{
		return $this->getProperty()->applicableGroups;
	}
	
	public function getCopy()
	{
		return $this->getProperty()->copy;
	}
	
	public function getCreate()
	{
		return $this->getProperty()->create;
	}
	
	public function getDelete()
	{
		return $this->getProperty()->delete;
	}
	
	public function getEdit()
	{
		return $this->getProperty()->edit;
	}
	
	public function getIdentifier()
	{
		$obj                 = new stdClass();
		$obj->id             = $this->getProperty()->id;
		$obj->path           = new stdClass();
		$obj->path->path     = $this->getProperty()->path;
		$obj->path->siteId   = $this->getProperty()->siteId;
		$obj->path->siteName = $this->getProperty()->siteName;
		$obj->type           = T::WORKFLOWDEFINITION;
		$obj->recycled       = false;
		return new Identifier( $obj );
	}
	
	public function getNamingBehavior()
	{
		return $this->getProperty()->namingBehavior;
	}
	
	public function getNonOrderedStep( $step_id )
	{
		if( !isset( $this->non_ordered_step_map[ $step_id ] ) )
			throw new NoSuchStepException( "The step does not exist." );
			
		return $this->non_ordered_step_map[ $step_id ];
	}
	
	public function getNonOrderedSteps()
	{
		return $this->non_ordered_steps;
	}
	
	public function getOrderedStep( $step_id )
	{
		if( !isset( $this->ordered_step_map[ $step_id ] ) )
			throw new NoSuchStepException( "The step does not exist." );
			
		return $this->ordered_step_map[ $step_id ];
	}
	
	public function getOrderedSteps()
	{
		return $this->ordered_steps;
	}
	
	public function getXml()
	{
		return $this->getProperty()->xml;
	}
	
	public function hasNonOrderedStep( $step_id )
	{
		return isset( $this->non_ordered_step_map[ $step_id ] );
	}
	
	public function hasOrderedStep( $step_id )
	{
		return isset( $this->ordered_step_map[ $step_id ] );
	}
	
	public function isApplicableToGroup( Group $g )
	{
		if( $g == NULL )
		{
			throw new NullAssetException( "The group cannot be NULL." );
		}

		$group_name = $g->getName();
		$group_string = $this->getProperty()->applicableGroups;
		$group_array  = explode( ';', $group_string );
		return in_array( $group_name, $group_array );
	}
	
	public function removeGroup( Group $g )
	{
		if( $g == NULL )
		{
			throw new NullAssetException( "The group cannot be NULL." );
		}
		
		$group_name   = $g->getName();
		$group_string = $this->getProperty()->applicableGroups;
		$group_array  = explode( ';', $group_string );
			
		if( in_array( $group_name, $group_array ) )
		{
			$temp = array();
			foreach( $group_array as $group )
			{
				if( $group != $group_name )
				{
					$temp[] = $group;
				}
			}
			$group_array = $temp;
		}
		$group_string = implode( ';', $group_array );
		$this->getProperty()->applicableGroups = $group_string;
		return $this;
	}
	
	public function setCopy( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );

		$this->getProperty()->copy = $bool;
		return $this;
	}
	
	public function setCreate( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );

		$this->getProperty()->create = $bool;
		return $this;
	}
	
	public function setDelete( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );

		$this->getProperty()->delete = $bool;
		return $this;
	}
	
	public function setEdit( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );

		$this->getProperty()->edit = $bool;
		return $this;
	}
	
	public function setNamingBehavior( $nb )
	{
		if( $nb != self::NAMING_BEHAVIOR_AUTO && 
			$nb != self::NAMING_BEHAVIOR_DEFINITION &&
			$nb != self::NAMING_BEHAVIOR_BLANK
		)
			throw new UnacceptableValueException( "The value $nb is unacceptable." );

		$this->getProperty()->namingBehavior = $nb;
		return $this;
	}
	
	public function setXml( $xml )
	{
		if( trim( $xml ) == "" )
			throw new EmptyValueException( "The xml cannot be empty." );
			
		$this->getProperty()->xml = $nb;
		return $this;
	}
	
	public function toXml()
	{
		$xml_string = "<system-workflow-definition name=\"" . $this->name .
			"\" initial-step=\"" . $this->initial_step . "\">\n";
		$xml_string .= "  <triggers>\n";
		
		foreach( $this->triggers as $trigger )
		{
			$xml_string .= $trigger->toXml();
		}
	
		$xml_string .= "  <steps>\n";
		
		foreach( $this->ordered_steps as $step )
		{
			$xml_string .= $step->toXml();
		}
		
		$xml_string .= "  </steps>\n";
		
		if( count( $this->non_ordered_steps ) > 0 )
		{
			$xml_string .= "  <non-ordered-steps>\n";
		
			foreach( $this->non_ordered_steps as $step )
			{
				$xml_string .= $step->toXml();
			}
		
			$xml_string .= "  </non-ordered-steps>\n";
		}
		$xml_string .= "</system-workflow-definition>\n";
		
		return $xml_string;
	}
	
	private $name;
	private $initial_step;
	private $ordered_steps;
	private $non_ordered_steps;
	private $ordered_step_map;
	private $non_ordered_step_map;
	private $triggers;
}
?>
