<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Folder extends Container
{
	const DEBUG = false;
	const TYPE  = T::FOLDER;
	
	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		$this->processMetadata();
	}
	
	// Adds a workflow definition to the settings
	public function addWorkflow( WorkflowDefinition $wf )
	{
		$this->getWorkflowSettings()->addWorkflowDefinition( $wf->getIdentifier() );
		return $this;
	}
	
	// Copies the folder
	public function copy( stdClass $par_id, $new_name )
	{
		if( $this->getProperty()->parentFolderId == NULL )
		{
			throw new CopyErrorException( M::COPY_BASE_FOLDER );
		}
		
		$service         = $this->getService();
		$self_identifier = $service->createId( Folder::TYPE, $this->getId() );
		
		$service->copy( $self_identifier, $par_id, $new_name, false );
		
		if( $service->isSuccessful() )
		{
			// get the parent
			$parent_id = $par_id->id;
			$parent    = $service->retrieve(
				$service->createId( T::FOLDER, $parent_id ), 
				P::FOLDER);
				
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
			// get the digital id of child
			$child_id = $child_found->id;
			// return new Folder object
			return new Folder( $service, $service->createId( Folder::TYPE, $child_id ) );
		}
		else
		{
			throw new CopyErrorException( M::COPY_ASSET_FAILURE );
		}
	}

	public function editWorkflowSettings( 
	    $apply_inherit_workflows_to_children, $apply_require_workflow_to_children )
	{
		if( !BooleanValues::isBoolean( $apply_inherit_workflows_to_children ) )
			throw new UnacceptableValueException( 
			    "The value $apply_inherit_workflows_to_children must be a boolean." );
			    
		if( !BooleanValues::isBoolean( $apply_require_workflow_to_children ) )
			throw new UnacceptableValueException( 
			    "The value $apply_require_workflow_to_children must be a boolean." );
	
		$service = $this->getService();
		$service->editWorkflowSettings( $this->workflow_settings->toStdClass(),
		    $apply_inherit_workflows_to_children, $apply_require_workflow_to_children );
		    
		if( !$service->isSuccessful() )
		{
			throw new EditingFailureException( 
				M::EDIT_WORKFLOW_SETTINGS_FAILURE . $service->getMessage() );
		}
		return $this;
	}
	
	public function getCreatedBy()
	{
		return $this->getProperty()->createdBy;
	}
	
	public function getCreatedDate()
	{
		return $this->getProperty()->createdDate;
	}
	
	public function getDynamicField( $name )
	{
		return $this->metadata->getDynamicField( $name );
	}
	
	public function getDynamicFields()
	{
		return $this->metadata->getDynamicFields();
	}
	
	public function getExpirationFolderId()
	{
		return $this->getProperty()->expirationFolderId;
	}
	
	public function getExpirationFolderPath()
	{
		return $this->getProperty()->expirationFolderPath;
	}
	
	public function getExpirationFolderRecycled()
	{
		return $this->getProperty()->expirationFolderRecycled;
	}
	
	public function getFolderChildrenIds()
	{
		return $this->getContainerChildrenIds();
	}

	public function getLastModifiedBy()
	{
		return $this->getProperty()->lastModifiedBy;
	}
	
	public function getLastModifiedDate()
	{
		return $this->getProperty()->lastModifiedDate;
	}
	
	public function getLastPublishedBy()
	{
		return $this->getProperty()->lastPublishedBy;
	}
	
	public function getLastPublishedDate()
	{
		return $this->getProperty()->lastPublishedDate;
	}
	
	public function getMetadata()
	{
		return $this->metadata;
	}
	
	public function getMetadataSet()
	{
		$service = $this->getService();
		//echo $this->metadataSetId . BR;
		
		return new MetadataSet( 
			$service, 
			$service->createId( MetadataSet::TYPE, 
			    $this->getProperty()->metadataSetId ) );
	}
	
	public function getMetadataSetId()
	{
		return $this->getProperty()->metadataSetId;
	}
	
	public function getMetadataSetPath()
	{
		return $this->getProperty()->metadataSetPath;
	}
	
	public function getParentFolderId()
	{
		return $this->getParentContainerId();
	}
	
	public function getParentFolderPath()
	{
		return $this->getParentContainerPath();
	}

	public function getShouldBeIndexed()
	{
		return $this->getProperty()->shouldBeIndexed;
	}
	
	public function getShouldBePublished()
	{
		return $this->getProperty()->shouldBePublished;
	}

	public function getWorkflowSettings()
	{
		if( $this->workflow_settings == NULL )
		{
			$service = $this->getService();
		
			$service->readWorkflowSettings( 
				$service->createId( self::TYPE, $this->getProperty()->id ) );
	
			if( $service->isSuccessful() )
			{
				//echo S_PRE;
				//var_dump( $service->getReply()->readWorkflowSettingsReturn->workflowSettings );
				//echo E_PRE;
				$this->workflow_settings = new WorkflowSettings( 
					$service->getReply()->readWorkflowSettingsReturn->workflowSettings );
			}
			else
			{
				throw new Exception( $service->getMessage() );
			}
		}
		return $this->workflow_settings;
	}
	
	public function hasDynamicField( $name )
	{
		return $this->metadata->hasDynamicField( $name );
	}
	
	public function publish()
	{
		if( $this->getProperty()->shouldBePublished )
		{
			$service = $this->getService();
			$service->publish( 
				$service->createId( self::TYPE, $this->getProperty()->id ) );
		}
		return $this;
	}
	
	public function setMetadataSet( MetadataSet $m )
	{
		if( $m == NULL )
		{
			throw new NullAssetException( M::NULL_ASSET );
		}
	
		$this->getProperty()->metadataSetId   = $m->getId();
		$this->getProperty()->metadataSetPath = $m->getPath();
		$this->edit()->reloadProperty();
		$this->processMetadata();
		
		return $this;
	}
	
	public function setShouldBeIndexed( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean" );
			
		$this->getProperty()->shouldBeIndexed = $bool;
		return $this;
	}
	
	public function setShouldBePublished( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean" );
			
		$this->getProperty()->shouldBePublished = $bool;
		return $this;
	}

	private function processMetadata()
	{
		$this->metadata = new Metadata( 
		    $this->getProperty()->metadata, 
		    $this->getService(), 
		    $this->getProperty()->metadataSetId );
	}

	private $metadata;
	private $children;
	private $workflow_settings;
}
?>
