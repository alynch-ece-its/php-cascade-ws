<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class FtpTransport extends Transport
{
	const DEBUG = false;
	const TYPE  = T::FTPTRANSPORT;
	
	public function getDirectory()
	{
		return $this->transport->directory;
	}
	
	public function getDoPASV()
	{
		return $this->transport->doPASV;
	}
	
	public function getDoSFTP()
	{
		return $this->transport->doSFTP;
	}
	
	public function getHostName()
	{
		return $this->transport->hostName;
	}
	
	public function getPassword()
	{
		return $this->transport->password;
	}
	
	public function getPort()
	{
		return $this->transport->port;
	}
	
	public function getUsername()
	{
		return $this->transport->username;
	}
	
	public function setDirectory( $d )
	{
		$this->transport->directory = $d;
		return $this;
	}
	
	public function setDoPASV( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );

		$this->transport->doPASV = $bool;
		return $this;
	}
	
	public function setDoSFTP( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );

		$this->transport->doSFTP = $bool;
		return $this;
	}
	
	public function setHostName( $h )
	{
		if( trim( $h ) == "" )
			throw new EmptyValueException( "The host name cannot be empty." );
		$this->transport->hostName = $h;
		return $this;
	}
	
	public function setPort( $p )
	{
		if( !is_numeric( $p ) )
			throw new UnacceptableValueException( "The port must be numeric." );
		$this->transport->port = $p;
		return $this;
	}
	
	public function setPassword( $pw )
	{
		if( trim( $pw ) == "" )
			throw new EmptyValueException( "The password cannot be empty." );
		$this->transport->password = $pw;
		return $this;
	}
	
	public function setUsername( $u )
	{
		if( trim( $u ) == "" )
			throw new EmptyValueException( "The username cannot be empty." );
		$this->transport->username = $u;
		return $this;
	}
}
?>
