<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Reference extends ContainedAsset
{
	const DEBUG = false;
	const TYPE  = T::REFERENCE;

	public function getCreatedBy()
	{
		return $this->getProperty()->createdBy;
	}
	
	public function getCreatedDate()
	{
		return $this->getProperty()->createdDate;
	}
	
	public function getLastModifiedBy()
	{
		return $this->getProperty()->lastModifiedBy;
	}
	
	public function getLastModifiedDate()
	{
		return $this->getProperty()->lastModifiedDate;
	}
	
	public function getParentFolderId()
	{
		return $this->getProperty()->parentFolderId;
	}
	
	public function getParentFolderPath()
	{
		return $this->getProperty()->parentFolderPath;
	}
	
	public function getReferencedAsset()
	{
		return Asset::getAsset( 
		    $this->getService(),
		    $this->getProperty()->referencedAssetType,
		    $this->getProperty()->referencedAssetId );
	}
	
	public function getReferencedAssetId()
	{
		return $this->getProperty()->referencedAssetId;
	}
	
	public function getReferencedAssetPath()
	{
		return $this->getProperty()->referencedAssetPath;
	}
	
	public function getReferencedAssetType()
	{
		return $this->getProperty()->referencedAssetType;
	}
}
?>