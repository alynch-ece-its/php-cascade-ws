<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Audit
{
	const DEBUG = false;

	public function __construct( 
		AssetOperationHandlerService $service, stdClass $audit_std )
	{
		if( $service == NULL )
		{
			throw new NullServiceException( M::NULL_SERVICE );
		}
		
		if( $audit_std == NULL )
		{
			throw new EmptyValueException( EMPTY_AUDIT );
		}
		
		if( self::DEBUG )
		{
			echo S_PRE;
			var_dump( $audit_std->identifier );
			echo E_PRE;
		}
		
		$this->service    = $service;
		$this->user       = $audit_std->user;
		$this->action     = $audit_std->action;
		$this->identifier = new Identifier( $audit_std->identifier );
		$this->date_time  = new DateTime( $audit_std->date );
	}
	
	public function display()
	{
		echo L::USER       . $this->user . BR .
			 L::ACTION     . $this->action . BR .
			 L::ID         . $this->identifier->getId() . BR .
			 L::ASSET_TYPE . $this->identifier->getType() . BR .
			 L::DATE       . date_format( $this->date_time, 'Y-m-d H:i:s' ) . BR . HR;
		
		return $this;
	}
	
	public function getAction()
	{
		return $this->action;
	}
	
	public function getAuditedAsset()
	{
		return $this->identifier->getAsset();
	}
	
	public function getDate()
	{
		return $this->date_time;
	}
	
	public function getIdentifier()
	{
		return $this->identifier;
	}
	
	public function getUser()
	{
		return Asset::getAsset( $service, User::TYPE, $this->user );
	}
	
	/* for sorting, ascending */
	public static function compare( Audit $a1, Audit $a2 )
	{
		if( $a1->getDate() == $a2->getDate() )
		{
			return 0;
		}
		else if( $a1->getDate() < $a2->getDate() )
		{
			return -1;
		}
		else
		{
			return 1;
		}
	}

	private $service;
	private $user;
	private $action;
	private $identifier;
	private $date_time;
}
?>