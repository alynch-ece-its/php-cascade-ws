<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class Format extends ContainedAsset
{
	const DEBUG = false;

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
	
	public function getLastModifiedBy()
	{
		return $this->getProperty()->lastModifiedBy;
	}
	
	public function getLastModifiedDate()
	{
		return $this->getProperty()->lastModifiedDate;
	}
}
?>
