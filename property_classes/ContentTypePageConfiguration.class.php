<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class ContentTypePageConfiguration extends Property
{
	public function __construct( stdClass $ctpc )
	{
		$this->page_configuration_id   = $ctpc->pageConfigurationId;
		$this->page_configuration_name = $ctpc->pageConfigurationName;
		$this->publish_mode            = $ctpc->publishMode;
		$this->destinations            = $ctpc->destinations;
	}
	
	public function display()
	{
		echo $this->page_configuration_name . ": " . $this->publish_mode . BR;
		return $this;
	}
	
	public function getPageConfigurationId()
	{
		return $this->page_configuration_id;
	}
	
	public function getPageConfigurationName()
	{
		return $this->page_configuration_name;
	}
	
	public function getPublishMode()
	{
		return $this->publish_mode;
	}
	
	public function getDestinations()
	{
		return $this->destinations;
	}
	
	public function setPublishMode( $mode )
	{
		if( $mode != ContentType::PUBLISH_MODE_ALL_DESTINATIONS && 
			$mode != ContentType::PUBLISH_MODE_DO_NOT_PUBLISH )
		{
			throw new Exception( "The mode $mode is not supported." );
		}
		$this->publish_mode = $mode;
		
		return $this;
	}
	
	public function toStdClass()
	{
		$obj = new stdClass();
		$obj->pageConfigurationId = $this->page_configuration_id;
		$obj->pageConfigurationName = $this->page_configuration_name;
		$obj->publishMode = $this->publish_mode;
		$obj->destinations = $this->destinations;
		
		return $obj;
	}
	
	private $page_configuration_id;
	private $page_configuration_name;
	private $publish_mode;
	private $destinations;
}
?>