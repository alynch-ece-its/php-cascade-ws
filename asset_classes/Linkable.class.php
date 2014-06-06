<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class Linkable extends ContainedAsset
{
	const DEBUG = false;

	public function __construct( AssetOperationHandlerService $service, 
		stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		// Skip page for content type to be set
		if( $this->getType() == File::TYPE || $this->getType() == Symlink::TYPE )
		{
			$this->processMetadata();
		}
	}
	
	public function copy( $par_id, $new_name )
	{
		$service         = $this->getService();
		$self_identifier = $service->createId( $this->getType(), $this->getId() );
		
		$service->copy( $self_identifier, $par_id, $new_name, false );
		
		if( $service->isSuccessful() )
		{
			// get the parent
			$parent_id = $par_id->id;
			$parent    = $service->retrieve(
				$service->createId( T::FOLDER, $parent_id ), 
				P::FOLDER );
				
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
			// get the child information
			$child_id   = $child_found->id;
			$child_type = $child_found->type;
			// return new object
			return Asset::getAsset( $service, $child_type, $child_id );
		}
		else
		{
			throw new Exception( "Failed to copy the asset." );
		}
	}
	
	public function edit()
	{
		$asset                          = new stdClass();
		$this->getProperty()->metadata  = $this->metadata->toStdClass();
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
	
	public function setPageContentType( ContentType $c )
	{
		if( $this->getType() != Page::TYPE )
		{
			throw new WrongAssetTypeException( );
		}
		if( $c == NULL )
		{
			throw new NullAssetException( NULL_ASSET );
		}
		$this->page_content_type = $c;
		$this->processMetadata();
		return $this;
	}
		
	private function processMetadata()
	{
		if( $this->getType() == Page::TYPE && $this->page_content_type != NULL )
		{
			$metadata_set_id = $this->page_content_type->getMetadataSetId();
		}
		else
		{
			$metadata_set_id = $this->getProperty()->metadataSetId;
		}
		
		$this->metadata = new Metadata( 
		    $this->getProperty()->metadata, 
		    $this->getService(), $metadata_set_id
		);
	}

	private $metadata;
	private $page_content_type;
}
?>