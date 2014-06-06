<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class Block extends ContainedAsset
{
	const DEBUG = false;

	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		$this->processMetadata();
	}
	
	// Copies the block
	public function copy( $par_id, $new_name )
	{
		$service         = $this->getService();
		$self_identifier = $service->createId( $this->getType(), $this->getId() );
		
		$service->copy( $self_identifier, $par_id, $new_name, false );
		
		if( $service->isSuccessful() )
		{
			// get the parent
			$parent_id = $par_id->id;
			
			if( $par_id->id == NULL )
			{
				$parent_id = $par_id->path->path;
				$site_name = $par_id->path->siteName;
			}
			
			$parent = $service->retrieve(
				$service->createId( T::FOLDER, $parent_id, $site_name ), 
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
			// return new block object
			return Asset::getAsset( $service, $this->getType(), $child_id );
		}
		else
		{
			throw new CopyErrorException( M::COPY_ASSET_FAILURE . $service->getMessage() );
		}
	}
	
	public function edit()
	{
		$asset                           = new stdClass();
		$this->getProperty()->metadata   = $this->metadata->toStdClass();
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
		
	public function getLastModifiedBy()
	{
		return $this->getProperty()->lastModifiedBy;
	}
	
	public function getLastModifiedDate()
	{
		return $this->getProperty()->lastModifiedDate;
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
	
	public function hasDynamicField( $name )
	{
		return $this->metadata->hasDynamicField( $name );
	}
	
	public function setMetadataSet( MetadataSet $m )
	{
		if( $m == NULL )
		{
			throw new NullAssetException( M::NULL_ASSET );
		}
	
		$this->getProperty()->metadataSetId   = $m->getId();
		$this->getProperty()->metadataSetPath = $m->getPath();
		$this->edit();
		$this->processMetadata();
		
		return $this;
	}
	
	private function processMetadata()
	{
		$this->metadata = new Metadata( 
		    $this->getProperty()->metadata, 
		    $this->getService(), 
		    $this->getProperty()->metadataSetId );
	}	

	private $block;          // the property of asset
	private $metadata;
}
?>
