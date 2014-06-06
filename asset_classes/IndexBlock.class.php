<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class IndexBlock extends Block
{
	const DEBUG = false;
	const TYPE  = T::INDEXBLOCK;
	
	// Copies the index block
	public function copy( stdClass $par_id, $new_name )
	{
		if( $this->getProperty()->parentFolderId == NULL )
		{
			throw new CopyErrorException( M::COPY_BASE_FOLDER );
		}
		
		$service         = $this->getService();
		$self_identifier = $service->createId( self::TYPE, $this->getId() );
		
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
			// get the digital id of child
			$child_id = $child_found->id;
			// return new index block
			return new IndexBlock( $service, $service->createId( self::TYPE, $child_id ) );
		}
		else
		{
			throw new CopyErrorException( M::COPY_ASSET_FAILURE );
		}
	}

	public function getAppendCallingPageData()
	{
		return $this->getProperty()->appendCallingPageData;
	}
	
	public function getDepthOfIndex()
	{
		return $this->getProperty()->depthOfIndex;
	}
	
	public function getIndexAccessRights()
	{
		return $this->getProperty()->indexAccessRights;
	}
	
	public function getIndexBlocks()
	{
		return $this->getProperty()->indexBlocks;
	}
	
	// no setter
	public function getIndexBlockType()
	{
		return $this->getProperty()->indexBlockType;
	}
	
	public function getIndexedContentTypeId()
	{
		return $this->getProperty()->indexedContentTypeId;
	}
	
	public function getIndexedContentTypePath()
	{
		return $this->getProperty()->indexedContentTypePath;
	}
	
	public function getIndexedFolderId()
	{
		return $this->getProperty()->indexedFolderId;
	}
	
	public function getIndexedFolderPath()
	{
		return $this->getProperty()->indexedFolderPath;
	}
	
	public function getIndexedFolderRecycled()
	{
		return $this->getProperty()->indexedFolderRecycled;
	}
	
	public function getIndexFiles()
	{
		return $this->getProperty()->indexFiles;
	}
	
	public function getIndexLinks()
	{
		return $this->getProperty()->indexLinks;
	}
	
	public function getIndexPages()
	{
		return $this->getProperty()->indexPages;
	}
	
	public function getIndexRegularContent()
	{
		return $this->getProperty()->indexRegularContent;
	}
	
	public function getIndexSystemMetadata()
	{
		return $this->getProperty()->indexSystemMetadata;
	}
	
	public function getIndexUserInfo()
	{
		return $this->getProperty()->indexUserInfo;
	}
	
	public function getIndexUserMetadata()
	{
		return $this->getProperty()->indexUserMetadata;
	}
	
	public function getIndexWorkflowInfo()
	{
		return $this->getProperty()->indexWorkflowInfo;
	}
	
	public function getMaxRenderedAssets()
	{
		return $this->getProperty()->maxRenderedAssets;
	}
	
	public function getPageXML()
	{
		return $this->getProperty()->pageXML;
	}
	
	public function getRenderingBehavior()
	{
		return $this->getProperty()->renderingBehavior;
	}
	
	public function getSortMethod()
	{
		return $this->getProperty()->sortMethod;
	}
	
	public function getSortOrder()
	{
		return $this->getProperty()->sortOrder;
	}
	
	public function isContent()
	{
		return $this->getProperty()->indexBlockType == T::CONTENTTYPEINDEX;
	}
	
	public function isFolder()
	{
		return $this->getProperty()->indexBlockType == T::FOLDER;
	}
	
	public function setAppendCallingPageData( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->appendCallingPageData = $b;
		
		return $this;
	}
	
	public function setDepthOfIndex( $num )
	{
		if( intval( $num ) < 1 )
		{
			throw new UnacceptableValueException( "The value $num is unacceptable." );
		}
		
		if( $this->getIndexBlockType() != Folder::TYPE )
		{
			throw new Exception( "This block is not a folder index block." );
		}
		
		$this->getProperty()->depthOfIndex = $num;
		return $this;
	}
	
	public function setContentType( ContentType $content_type )
	{
		if( $this->getIndexBlockType() != T::CONTENTTYPEINDEX )
		{
			throw new Exception( "This block is not a content type index block." );
		}
	
		$this->getProperty()->indexedContentTypeId   = $content_type->getId();
		$this->getProperty()->indexedContentTypePath = $content_type->getPath();
		return $this;
	}
	
	public function setFolder( Folder $folder )
	{
		if( $this->getIndexBlockType() != Folder::TYPE )
		{
			throw new Exception( "This block is not a folder index block." );
		}
	
		$this->getProperty()->indexedFolderId = $folder->getId();
		$this->getProperty()->indexedFolderPath = $folder->getPath();
		return $this;
	}
	
	public function setIndexAccessRights( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexAccessRights = $b;
		return $this;
	}
	
	public function setIndexBlocks( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexBlocks = $b;
		return $this;
	}
	
	public function setIndexFiles( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexFiles = $b;
		return $this;
	}
	
	public function setIndexLinks( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexLinks = $b;
		return $this;
	}
	
	public function setIndexPage( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexPages = $b;
		return $this;
	}
	
	public function setIndexRegularContent( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexRegularContent = $b;
		return $this;
	}
	
	public function setIndexSystemMetadata( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexSystemMetadata = $b;
		return $this;
	}
	
	public function setIndexUserInfo( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexUserInfo = $b;
		return $this;
	}
	
	public function setIndexUserMetadata( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexUserMetadata = $b;
		return $this;
	}
	
	public function setIndexWorkflowInfo( $b )
	{
		if( !BooleanValues::isBoolean( $b ) )
		{
			throw new UnacceptableValueException( "The value $b is not a boolean." );
		}
	
		$this->getProperty()->indexWorkflowInfo = $b;
		return $this;
	}
	
	public function setMaxRenderedAssets( $num )
	{
		if( intval( $num ) < 0 )
		{
			throw new UnacceptableValueException( "The value $num is unacceptable." );
		}
		
		$this->getProperty()->maxRenderedAssets = $num;
		return $this;
	}
	
	public function setPageXML( $page_xml )
	{
		if( $page_xml != T::NORENDER &&
			$page_xml != T::RENDER &&
			$page_xml != T::RENDERCURRENTPAGEONLY
		)
		{
			throw new UnacceptableValueException( "The pageXML $page_xml is unacceptable." );
		}
	
		$this->getProperty()->pageXML = $page_xml;
		return $this;
	}
	
	public function setRenderingBehavior( $behavior )
	{
		if( $behavior != T::RENDERNORMALLY &&
			$behavior != T::HIERARCHY &&
			$behavior != T::HIERARCHYWITHSIBLINGS &&
			$behavior != T::HIERARCHYSIBLINGSFORWARD
		)
		{
			throw new UnacceptableValueException( "The behavior $behavior is unacceptable." );
		}
		
		$this->getProperty()->renderingBehavior = $behavior;
		return $this;
	}
	
	public function setSortMethod( $method )
	{
		if( $method != T::FOLDERORDER &&
			$method != T::ALPHABETICAL &&
			$method != T::LASTMODIFIEDDATE &&
			$method != T::CREATEDDATE
		)
		{
			throw new UnacceptableValueException( "The method $method is unacceptable." );
		}
		
		$this->getProperty()->sortMethod = $method;
		return $this;
	}
	
	public function setSortOrder( $order )
	{
		if( $order != T::ASCENDING &&
			$order != T::DESCENDING
		)
		{
			throw new UnacceptableValueException( "The order $order is unacceptable." );
		}
		
		$this->getProperty()->sortOrder = $order;
		return $this;
	}
}
?>