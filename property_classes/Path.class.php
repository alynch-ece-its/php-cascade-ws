<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Path extends Property
{
	public function __construct( stdClass $p )
	{
		if( $p != NULL )
		{
			$this->path      = $p->path;
			$this->site_id   = $p->siteId;
			$this->site_name = $p->siteName;
		}
	}
	
	public function getPath()
	{
		return $this->path;
	}
	
	public function getSiteId()
	{
		return $this->site_id;
	}
	
	public function getSiteName()
	{
		return $this->site_name;
	}

	public function toStdClass()
	{
		$obj           = new stdClass();
		$obj->path     = $this->path;
		$obj->siteId   = $this->site_id;
		$obj->siteName = $this->site_name;
		return $obj;
	}

	private $path;
	private $site_id;
	private $site_name;
}
?>
