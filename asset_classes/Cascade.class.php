<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 6/2/2014 Added deleteExpirationMessages.
  * 5/22/2014 Fixed some bugs.
  * 5/21/2014 Added message related methods.
  * 5/14/2014 Added search methods.
  * 5/14/2014 Added checkIn and checkOut.
  * 5/12/2014 Added getAudits.
 */
class Cascade
{
	const DEBUG = false;
	const DUMP  = false;

	public function __construct( AssetOperationHandlerService $service )
	{
		try
		{
			$this->service = $service;
		}
		catch( Exception $e )
		{
			echo S_PRE . $e . E_PRE;
		}
	}
	
	public function checkIn( Asset $a, $comments='' )
	{
		if( $a == NULL )
		{
			throw new NullAssetException( M::NULL_ASSET );
		}
		
		if( !is_string( $comments ) )
		{
			throw new Exception( COMMENT_NOT_STRING );
		}
		
		$this->service->checkIn( $a->getIdentifier(), $comments );
		return $this;
	}
	
	public function checkOut( Asset $a )
	{
		if( $a == NULL )
		{
			throw new NullAssetException( M::NULL_ASSET );
		}
		
		$this->service->checkOut( $a->getIdentifier() );
		return $this;
	}
	
	public function clearPermissions( $type, $id_path, $site_name=NULL, $applied_to_children=false )
	{
		$ari = $this->getAccessRights( $type, $id_path, $site_name );
		$ari->clearPermissions();
		$this->setAccessRights( $ari, $applied_to_children );
		return $this;
	}
	
	public function copySite( Site $s, $new_name )
	{
		$this->service->siteCopy( $s->getId(), $s->getName(), $new_name );
		
		if( $this->service->isSuccessful() )
		{
			return $this->getSite( $new_name );
		}
		
		throw new SiteCreationFailureException( 
		    SITE_CREATION_FAILURE . $this->service->getMessage() );
	}
	
	public function denyAccess( $type, $id_path, $site_name=NULL, $applied_to_children=false, Asset $a=NULL )
	{
		$ari = $this->getAccessRights( $type, $id_path, $site_name );
		
		if( $a == NULL || ( $a->getType() != Group::TYPE && $a->getType() != User::TYPE ) )
		{
			throw new WrongAssetTypeException( M::ACCESS_TO_USERS_GROUPS );
		}
		
		if( $a->getType() == Group::TYPE )
		{
			if( self::DEBUG ) { echo "C::L89 Denying " . $a->getName() . " access" . BR; }
			$func_name = 'denyGroupAccess';
		}
		else
		{
			if( self::DEBUG ) { echo "C::L94 Denying " . $a->getName() . " access" . BR; }
			$func_name = 'denyUserAccess';
		}
		
		$ari->$func_name( $a );
		$this->setAccessRights( $ari, $applied_to_children );
		return $this;
	}
	
	public function denyAllAccess( $type, $id_path, $site_name=NULL, $applied_to_children=false )
	{
		if( self::DEBUG ) { echo "C::L105 Denying all access"; }
		$ari = $this->getAccessRights( $type, $id_path, $site_name );
		$ari->setAllLevel( T::NONE );
		$this->setAccessRights( $ari, $applied_to_children );
		return $this;
	}
	
	public function getAccessRights( $type, $id_path, $site_name=NULL )
	{
		$this->service->readAccessRights(
			$this->service->createId( $type, $id_path, $site_name ) );
			
		if( $this->service->isSuccessful() )
		{
			return new AccessRightsInformation(
			    $this->service->getReadAccessRightInformation() );
		}
		else
		{
			throw new Exception( $this->service->getMessage() );
		}
	}
	
	public function getAsset( $type, $id_path, $site_name=NULL )
	{
		return Asset::getAsset( $this->service, $type, $id_path, $site_name );
	}
	
	public function getAudits( 
		Asset $a, $type="", DateTime $start_time=NULL, DateTime $end_time=NULL )
	{
		if( $a == NULL )
		{
			throw new NullAssetException( M::NULL_ASSET );
		}
		
		if( !is_string( $type ) || !AuditTypes::isAuditType( $type ) )
		{
			if( self::DEBUG && !is_string( $type ) ) { echo "C::L143 not string"; }
				
			throw new NoSuchTypeException( M::WRONG_AUDIT_TYPE );
		}
		
		$start = false;
		$end   = false;
		
		if( $start_time != NULL )
		{
			if( $end_time != NULL )
			{
				if( $end_time < $start_time )
					throw new Exception( SMALLER_END_TIME );
					
				$end = true;
			}
			$start = true;
		}
		
		$a_std = new stdClass();
    	$a_std->identifier = $a->getIdentifier();
    	
    	if( $type != "" )
    		$a_std->auditType  = $type;
    		
		$this->service->readAudits( $a_std );
		$audits = array();
		
		if( $this->service->isSuccessful() )
		{
			if( self::DEBUG ) { echo "C::L174 Audits read" . BR; }
		
			$audit_stds = $this->service->getAudits()->audit;
			
			if( $audit_stds != NULL && !is_array( $audit_stds ) )
			{
				$audit_stds = array( $audit_stds );
			}
			
			foreach( $audit_stds as $audit_std )
			{
				if( self::DEBUG && self::DUMP )
				{
					echo S_PRE;
					var_dump( $audit_std );
					echo E_PRE;
				}
		
				$audit = new Audit( $this->service, $audit_std );
			
				if( $start && $audit->getDate() >= $start_time )
				{
					if( $end && $audit->getDate() <= $end_time )
					{
						$audits[] = $audit;
					}
					else if( !$end )
					{
						$audits[] = $audit;
					}
				}
				else if( !$start )
				{
					if( $end && $audit->getDate() <= $end_time )
					{
						$audits[] = $audit;
					}
					else if( !$end )
					{
						$audits[] = $audit;
					}
				}
			}
			usort( $audits, 'Audit::compare' );
		}
		else
		{
			echo $this->service->getMessage();
		}
		return $audits;
	}
	
	public function getGroups()
	{
		if( $this->groups == NULL )
		{
			$search_for               = new stdClass();
			$search_for->matchType    = T::MATCH_ANY;
			$search_for->searchGroups = true;
			$search_for->assetName    = '*';
	
			$this->service->search( $search_for );
			
			if ( $this->service->isSuccessful() )
			{
				if( !is_null( $this->service->getSearchMatches()->match ) )
				{
					$groups = $this->service->getSearchMatches()->match;
					$this->groups = array();
			
					foreach( $groups as $group )
					{
						$this->groups[] = new Identifier( $group );
					}
				}
			}
		}
		return $this->groups;
	}
	
	/* the message group */
	
	private function deleteMessagesWithIds( $ids )
	{
		if( self::DEBUG ) { echo "C::L258 Inside deleteMessagesWithIds" . BR; }
		
		if( !is_array( $ids ) )
			throw new Exception( M::NOT_ARRAY );
			
		if( count( $ids ) > 0 )
		{
			foreach( $ids as $id )
			{
				$this->service->deleteMessage( 
					$this->service->createIdWithIdType( $id, T::MESSAGE ) );
			}
		}
		
		return $this;
	}
	
	public function deleteAllMessages()
	{
		return $this->deleteMessagesWithIds( 
			MessageArrays::$all_message_ids );
	}
	
	public function deleteAllMessagesWithoutIssues()
	{
		return
			$this->deletePublishMessagesWithoutIssues()->
		           deleteUnpublishMessagesWithoutIssues();
	}
	
	public function deleteExpirationMessages()
	{
		MessageArrays::initialize( $this->service );
		return $this->deleteMessagesWithIds( 
			MessageArrays::$asset_expiration_message_ids );
	}
	
	public function deletePublishMessagesWithoutIssues()
	{
		MessageArrays::initialize( $this->service );
		return $this->deleteMessagesWithIds( 
			MessageArrays::$publish_message_ids_without_issues );
	}
	
	public function deleteSummaryMessagesNoFailures()
	{
		MessageArrays::initialize( $this->service );
		return $this->deleteMessagesWithIds( 
			MessageArrays::$summary_message_ids_no_failures );
	}
	
	public function deleteUnpublishMessagesWithoutIssues()
	{
		MessageArrays::initialize( $this->service );
		return $this->deleteMessagesWithIds( 
			MessageArrays::$unpublish_message_ids_without_issues );
	}
	
	public function deleteWorkflowMessagesIsComplete()
	{
		MessageArrays::initialize( $this->service );
		return $this->deleteMessagesWithIds( 
			MessageArrays::$workflow_message_ids_is_complete );
	}
	
	public function getAllMessages()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$all_messages;
	}
	
	public function getMessage( $id )
	{
		MessageArrays::initialize( $this->service );
	
		if( isset( MessageArrays::$id_obj_map[ $id ] ) )
			return MessageArrays::$id_obj_map[ $id ];
			
		return NULL;
	}
	
	public function getMessageIdObjMap()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$id_obj_map;
	}
	
	public function getPublishMessages()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$publish_messages;
	}
	
	public function getPublishMessagesWithIssues()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$publish_messages_with_issues;
	}
	
	public function getPublishMessagesWithoutIssues()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$publish_messages_without_issues;
	}
	
	public function getSummaryMessages()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$summary_messages;
	}
	
	public function getSummaryMessagesNoFailures()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$summary_messages_no_failures;
	}
	
	public function getSummaryMessagesWithFailures()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$summary_messages_with_failures;
	}
	
	public function getUnpublishMessages()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$unpublish_messages;
	}
	
	public function getUnpublishMessagesWithIssues()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$unpublish_messages_with_issues;
	}
	
	public function getUnpublishMessagesWithoutIssues()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$unpublish_messages_without_issues;
	}
	
	public function getWorkflowMessages()
	{
		MessageArrays::initialize( $this->service );
		return $workflow_messages;
	}
	
	public function getWorkflowMessagesIsComplete()
	{
		MessageArrays::initialize( $this->service );
		return $workflow_messages_complete;
	}
	
	public function getWorkflowMessagesOther()
	{
		MessageArrays::initialize( $this->service );
		return MessageArrays::$workflow_messages_other;
	}
	
	
	/* the role group */
	public function getRoleAssetById( $role_id )
	{
		if( $this->roles == NULL )
		{
			$this->getRoles();
		}
		
		if( !$this->hasRoleId( $role_id ) )
			throw new NullAssetException( M::WRONG_ROLE );
		
		return $this->role_id_object_map[ $role_id ];
	}
	
	public function getRoleAssetByName( $role_name )
	{
		if( $this->roles == NULL )
		{
			$this->getRoles();
		}
		
		if( !$this->hasRoleName( $role_name ) )
			throw new NullAssetException( M::WRONG_ROLE );
		
		return $this->role_name_object_map[ $role_name ];
	}
	
	public function getRoleIds()
	{
		if( $this->roles == NULL )
		{
			$this->getRoles();
		}
		return array_keys( $this->role_id_object_map );
	}
	
	public function getRoleNames()
	{
		if( $this->roles == NULL )
		{
			$this->getRoles();
		}
		return array_keys( $this->role_name_object_map );
	}
	
	public function getRoles()
	{
		if( $this->roles == NULL )
		{
			$this->role_name_object_map = array();
			$this->role_id_object_map   = array();
		
			$search_for              = new stdClass();
			$search_for->matchType   = T::MATCH_ANY;
			$search_for->searchRoles = true;
			$search_for->assetName   = '*';
	
			$this->service->search( $search_for );
			
			if ( $this->service->isSuccessful() )
			{
				if( !is_null( $this->service->getSearchMatches()->match ) )
				{
					$roles = $this->service->getSearchMatches()->match;
					$this->roles = array();
			
					foreach( $roles as $role )
					{
						$role_identifier = new Identifier( $role );
						$this->roles[]   = $role_identifier;
						$role_object     = $role_identifier->getAsset( $this->service );
						$this->role_name_object_map[ $role_object->getName() ] = $role_object;
						$this->role_id_object_map[ $role_object->getId() ] = $role_object;
					}
				}
			}
		}
		return $this->roles;
	}	

	public function getService()
	{
		return $this->service;
	}
	
	public function getSite( $site_name )
	{
		if( !isset( $this->name_site_map[ $site_name ] ) )
		{
			throw new NoSuchSiteException( "The site $site_name does not exist." );
		}
		
		return Asset::getAsset( $this->service, Site::TYPE, 
		    $this->name_site_map[ $site_name ]->getId() );
	}
	
	public function getSites()
	{
		if( $this->sites == NULL )
		{
			$this->service->listSites();
			$this->name_site_map = array();
			
			if( $this->service->isSuccessful() )
			{
				$assetIdentifiers = $this->service->getReply()->listSitesReturn->sites->assetIdentifier;
				
				foreach( $assetIdentifiers as $identifier )
				{
					$site = new Identifier( $identifier );
					$this->sites[] = $site;
					$this->name_site_map[ $identifier->path->path ] = $site;
				}
			}
			else
			{
				throw new Exception( $service->getMessage() );
			}
		}
		return $this->sites;
	}
	
	public function getUsers()
	{
		$user_name_array = array();
		
		// maximally returns 250 users
		if( $this->users == NULL )
		{
			$search_for              = new stdClass();
			$search_for->matchType   = T::MATCH_ANY;
			$search_for->searchUsers = true;
			$search_for->assetName   = '*';
	
			$this->service->search( $search_for );
			
			if ( $this->service->isSuccessful() )
			{
				if( !is_null( $this->service->getSearchMatches()->match ) )
				{
					$users = $this->service->getSearchMatches()->match;
					$this->users = array();
			
					foreach( $users as $user )
					{
						$this->users[] = new Identifier( $user );
						$user_name_array[]  = $user->id;
					}
				}
			}
		}
		
		// add those that belong to groups
		$extra_names = array();
		$extra_users = array();
		
		foreach( $this->groups as $group )
		{
			$users = $group->getAsset( $this->service )->getUsers();
			
			$users = explode( ';', $users ); // array
				
			foreach( $users as $user )
			{
				if( trim( $user ) != "" && !in_array( $user, $user_name_array ) && !in_array( $user, $extra_names ) )
				{
					$user_std       = new stdClass();
					$user_std->id   =  $user;
					$user_std->path = new stdClass();
					$user_std->path->path = NULL;
					$user_std->path->siteName = NULL;
					$user_std->type = User::TYPE;
					$user_std->recycled = false;
					$extra_users[] = new Identifier( $user_std );
					$extra_names[] = $user;
				}
			}
		}
		return array_merge( $this->users, $extra_users );
	}
	
	public function hasRoleId( $role_id )
	{
		if( $this->roles == NULL )
		{
			$this->getRoles();
		}
		return in_array( $role_id, array_keys( $this->role_id_object_map ) );
	}

	public function hasRoleName( $role_name )
	{
		if( $this->roles == NULL )
		{
			$this->getRoles();
		}
		return in_array( $role_name, array_keys( $this->role_name_object_map ) );
	}

	public function grantAccess( $type, $id_path, $site_name=NULL, $applied_to_children=false, 
		Asset $a=NULL, $level=T::READ )
	{
		$ari = $this->getAccessRights( $type, $id_path, $site_name );
		
		if( $a == NULL || ( $a->getType() != Group::TYPE && $a->getType() != User::TYPE ) )
		{
			throw new WrongAssetTypeException( M::ACCESS_TO_USERS_GROUPS );
		}
		
		if( !LevelValues::isLevel( $level ) )
		{
			throw new UnacceptableValueException( "The level $level is unacceptable." );
		}
		
		if( $a->getType() == Group::TYPE && $level == T::READ )
		{
			if( self::DEBUG ) { echo "C::L628 Granting " . $a->getName() . " read access to " . $id_path . BR; }
			$func_name = 'grantGroupReadAccess';
		}
		else if( $a->getType() == Group::TYPE && $level == T::WRITE )
		{
			if( self::DEBUG ) { echo "C::L632 Granting " . $a->getName() . " write access to " . $id_path . BR; }
			$func_name = 'grantGroupWriteAccess';
		}
		else if( $a->getType() == User::TYPE && $level == T::READ )
		{
			if( self::DEBUG ) { echo "C::L637 Granting " . $a->getName() . " read access to " . $id_path . BR; }
			$func_name = 'grantUserReadAccess';
		}
		else if( $a->getType() == User::TYPE && $level == T::WRITE )
		{
			if( self::DEBUG ) { echo "C::L642 Granting " . $a->getName() . " write access to " . $id_path . BR; }
			$func_name = 'grantUserWriteAccess';
		}
		
		if( isset( $func_name ) )
		{
			$ari->$func_name( $a );
			$this->setAccessRights( $ari, $applied_to_children );
		}
		else
		{
			if( self::DEBUG ) { echo "C::L653 The function name is not set" . BR; }
		}
		return $this;
	}
	
	public function searchForAll( $asset_name, $asset_content, $asset_metadata, $search_type )
	{
		return $this->search( T::MATCH_ALL, $asset_name, $asset_content, $asset_metadata, $search_type );
	}
	
	public function searchForAssetContent( $asset_content, $search_type )
	{
		if( trim( $asset_content ) == "" )
		{
			throw new EmptyValueException( M::EMPTY_ASSET_CONTENT );
		}
		return $this->search( T::MATCH_ANY, "", $asset_content, "", $search_type );
	}
	
	public function searchForAssetName( $asset_name, $search_type )
	{
		if( trim( $asset_name ) == "" )
		{
			throw new EmptyNameException( M::EMPTY_ASSET_NAME );
		}
		return $this->search( T::MATCH_ANY, $asset_name, "", "", $search_type );
	}
	
	public function searchForAssetMetadata( $asset_metadata, $search_type )
	{
		if( trim( $asset_metadata ) == "" )
		{
			throw new EmptyValueException( M::EMPTY_ASSET_METADATA );
		}
		return $this->search( T::MATCH_ANY, "", "", $asset_metadata, $search_type );
	}
	
	public function setAccessRights( AccessRightsInformation $ari, $apply_to_children=false )
	{
		if( !BooleanValues::isBoolean( $apply_to_children ) )
			throw new UnacceptableValueException( "The value $apply_to_children must be a boolean." );
	
		if( $ari != NULL )
		{
			if( self::DEBUG && self::DUMP ) { echo "C::L697 " . BR; var_dump( $ari->toStdClass() ); }
		
			$this->service->editAccessRights( $ari->toStdClass(), $apply_to_children ); 
		}
		return $this;
	}
	
	public function setAllLevel( $type, $id_path, $site_name=NULL, $level=T::NONE, $applied_to_children=false )
	{
		$ari = $this->getAccessRights( $type, $id_path, $site_name );
		$ari->setAllLevel( $level );
		$this->setAccessRights( $ari, $applied_to_children );
		return $this;
	}
	
	private function search( 
		$match_type=T::MATCH_ANY, 
		$asset_name='', 
		$asset_content='', 
		$asset_metadata='', // metadata overrides others when any
		$search_type='' )
	{
		if( !SearchTypes::isSearchType( trim( $search_type ) ) )
		{
			throw new NoSuchTypeException( "The search type $search_type does not exist. " );
		}
		
		if( $match_type != T::MATCH_ANY && $match_type != T::MATCH_ALL )
		{
			throw new NoSuchTypeException( "The match type $match_type does not exist. " );
		}
	
		$search_for = new stdClass();
		$search_for->matchType     = $match_type;
		$search_for->$search_type  = true;
		
		if( trim( $asset_name ) != "" )
			$search_for->assetName = $asset_name;
		if( trim( $asset_content ) != "" )
			$search_for->assetContent = $asset_content;
		if( trim( $asset_metadata ) != "" )
			$search_for->assetMetadata = $asset_metadata;
			
		if( self::DEBUG && self::DUMP )
		{
			echo S_PRE; var_dump( $search_for ); echo E_PRE;
		}
			
		$this->service->search( $search_for );
	
		// if succeeded
		if ( $this->service->isSuccessful() )
		{
			$results = array();
			
			if( !is_null( $this->service->getSearchMatches()->match ) )
			{
				$temp = $this->service->getSearchMatches()->match;
				
				if( !is_array( $temp ) )
				{
					$temp = array( $temp );
				}
					
				foreach( $temp as $match )
				{
					$results[] = new Identifier( $match );
				}
			}
			return $results;
		}
		else
		{
			throw new SearchException( $this->service->getMessage() );
		}
	}
	
	private $service;
	private $sites;
	private $name_site_map;
	private $groups;
	private $roles;
	private $role_name_object_map;
	private $role_id_object_map;
	private $users;
}
?>