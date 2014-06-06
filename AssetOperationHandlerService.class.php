<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  *  6/6/2014 Fixed a bug in publish and unpublish.
  *  4/17/2014 Modified the signature of retrieve so that the property can be empty.
  *  3/24/2014 Modified createId to throw exceptions and added isHexString.
  *  2/26/2014 Removed workflowConfiguration from property, and twitter feed block from property and type.
  *  2/24/2014 Fixed a type in the Property class.
  *  1/8/2014 Changed all property strings to constants, added the $types array and a getType method
  *  10/30/2013 Fixed a bug in __call.
  *  10/29/2013 Added storeResults
  *  10/28/2013 Added/modified all documentation comments.
  *  10/26/2013 Added retrieve.
  *  10/25/2013 Added the enhanced __call method to generate read and get.
  *  10/21/2013 Added all operation methods.
 */
class AssetOperationHandlerService
{
    // from the constructor
    private $url;              // string
    private $auth;             // array
    private $soapClient;       // the client
    
    // from the reply
    private $message;          // string
    private $success;          // string
    private $createdAssetId;   // string
    private $lastRequest;      // xml string
    private $lastResponse;     // xml string
    private $reply;            // xml string
    private $audits;
    private $searchMatches;
    private $listed_messages;  // trio
    
    // 43 properties
    // property array to generate methods
    private $properties = array(
    	P::ASSETFACTORY,
		P::ASSETFACTORYCONTAINER,
		P::CONNECTORCONTAINER,
		P::CONTENTTYPE,
		P::CONTENTTYPECONTAINER,
		P::DATADEFINITION,
		P::DATADEFINITIONCONTAINER,
		P::DATABASETRANSPORT,
		P::DESTINATION,
		P::FACEBOOKCONNECTOR,
		P::FEEDBLOCK,
		P::FILE,
		P::FILESYSTEMTRANSPORT,
		P::FOLDER,
		P::FTPTRANSPORT,
		P::GOOGLEANALYTICSCONNECTOR,
		P::GROUP,
		P::INDEXBLOCK,
		P::METADATASET,
		P::METADATASETCONTAINER,
		P::PAGE,
		P::PAGECONFIGURATIONSET,
		P::PAGECONFIGURATIONSETCONTAINER,
		P::PUBLISHSET,
		P::PUBLISHSETCONTAINER,
		P::REFERENCE,
		P::ROLE,
		P::SCRIPTFORMAT,
		P::SITE,
		P::SITEDESTINATIONCONTAINER,
		P::SYMLINK,
		P::TARGET,
		P::TEMPLATE,
		P::TEXTBLOCK,
		P::TRANSPORTCONTAINER,
		P::TWITTERCONNECTOR,
		P::USER,
		P::WORDPRESSCONNECTOR,
		P::WORKFLOWDEFINITION,
		P::WORKFLOWDEFINITIONCONTAINER,
		P::XHTMLDATADEFINITIONBLOCK,
		P::XMLBLOCK,
		P::XSLTFORMAT
    );
    
    // 47 types
    private $types = array(
    	T::ASSETFACTORY,
		T::ASSETFACTORYCONTAINER,
		T::CONNECTORCONTAINER,
		T::CONTENTTYPE,
		T::CONTENTTYPECONTAINER,
		T::DATADEFINITION,
		T::DATADEFINITIONCONTAINER,
		T::DESTINATION,
		T::FACEBOOKCONNECTOR,
		T::FEEDBLOCK,
		T::FILE,
		T::FOLDER,
		T::GOOGLEANALYTICSCONNECTOR,
		T::GROUP,
		T::INDEXBLOCK,
		T::MESSAGE,
		T::METADATASET,
		T::METADATASETCONTAINER,
		T::PAGE,
		T::PAGECONFIGURATION,
		T::PAGECONFIGURATIONSET,
		T::PAGECONFIGURATIONSETCONTAINER,
		T::PAGEREGION,
		T::PUBLISHSET,
		T::PUBLISHSETCONTAINER,
		T::REFERENCE,
		T::ROLE,
		T::SCRIPTFORMAT,
		T::SITE,
		T::SITEDESTINATIONCONTAINER,
		T::SYMLINK,
		T::TARGET,
		T::TEMPLATE,
		T::TEXTBLOCK,
		T::TRANSPORTDB,
		T::TRANSPORTFS,
		T::TRANSPORTFTP,
		T::TRANSPORTCONTAINER,
		T::TWITTERCONNECTOR,
		T::USER,
		T::WORDPRESSCONNECTOR,
		T::WORKFLOW,
		T::WORKFLOWDEFINITION,
		T::WORKFLOWDEFINITIONCONTAINER,
		T::XHTMLDATADEFINITIONBLOCK,
		T::XMLBLOCK,
		T::XSLTFORMAT
    );
    
    private $read_methods = array();
    private $get_methods  = array();
    private $read_assets  = array();
    
    /**
    * The constructor
    * @param $url the url of the WSDL
    * @param $auth the authentication object
    */
    public function __construct( $url, $auth )
    {
        $this->url            = $url;
        $this->auth           = $auth;
        $this->message        = '';
        $this->success        = '';
        $this->createdAssetId = '';
        $this->lastRequest    = '';
        $this->lastResponse   = '';
        
        foreach( $this->properties as $property )
        {
        	// turn a property like 'publishSet' to 'PublishSet'
        	$property = strtoupper( substr( $property, 0, 1 ) ) . substr( $property, 1 );
        	// attach the prefixes 'read' and 'get'
        	$this->read_methods[] = 'read' . $property;
        	$this->get_methods[]  = 'get'  . $property;
        }
        
        try
        {
        	$this->soapClient = new SoapClient( $this->url, array( 'trace' => 1 ) );
        }
        catch( Exception $e )
        {
        	throw new ServerException( $e->getMessage() );
        }
    }
    
    /**
    * Function to dynamically generate the read and get methods
    * @param $func the function name
    * @param $params the parameters fed into the function
    */
    function __call( $func, $params )
    {
    	// derive the property name from method name
    	if( strpos( $func, 'read' ) === 0 )
    	{
    		$property = substr( $func, 4 );
    	}
    	else if( strpos( $func, 'get' ) === 0 )
    	{
    		$property = substr( $func, 3 );
    	}
    	
    	$property = strtolower( substr( $property, 0, 1 ) ) . substr( $property, 1 );
    	
    	// read
        if( in_array( $func, $this->read_methods ) )
        {
			$read_param = new stdClass();
			$read_param->authentication = $this->auth;
			$read_param->identifier     = $params[0];
	
			$this->reply = $this->soapClient->read( $read_param );
		
			if( ( $this->reply->readReturn->success == 'true' ) && 
			      isset( $this->reply->readReturn->asset->$property ) )
			{
				// store the property
				$this->read_assets[$property] = $this->reply->readReturn->asset->$property; 
			}
   
   			$this->storeResults( $this->reply->readReturn );
		}
		// get
		else if( in_array( $func, $this->get_methods ) )
		{
			// could be NULL
			return $this->read_assets[$property];
        }
    }
    
    // getters
    
    /**
    * Function to return the audits object after the call of readAudits()
    */
    public function getAudits()
    {
        return $this->audits;
    }
    
    /**
    * Function to return the id of an asset newly created
    */
    public function getCreatedAssetId()
    {
        return $this->createdAssetId;
    }
    
    /**
    * Function to return the last request
    */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }
    
    /**
    * Function to return the last response
    */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
    
    /**
    * Function to return the messages object after the call of listMessages()
    */
    public function getListedMessages()
    {
    	return $this->listed_messages;
    }
    
    /**
    * Function to return the message after an operation
    */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
    * Function to return the accessRightInformation object after the call of readAccessRightInformation()
    */
    public function getReadAccessRightInformation()
    {
        return $this->reply->readAccessRightsReturn->accessRightsInformation;
    }
    
    /**
    * Function to return the asset object after the call of read()
    */
    public function getReadAsset()
    {
        return $this->reply->readReturn->asset;
    }
    
    /**
    * Function to return the file object after the call of read()
    */
    public function getReadFile()
    {
        return $this->reply->readReturn->asset->file;
    }
       
    /**
    * Function to return the workflow object after the call of readWorkflow()
    */
    public function getReadWorkflow()
    {
        return $this->reply->readWorkflowInformationReturn->workflow;
    }
    
    /**
    * Function to return the workflowSettings object after the call of readWorkflowSettings()
    */
    public function getReadWorkflowSettings()
    {
        return $this->reply->readWorkflowSettingsReturn->workflowSettings;
    }
    
    /**
    * Function to return the response object after an operation
    */
    public function getReply()
    {
        return $this->reply;
    }
    
    /**
    * Function to return the searchMatches object after the call of search()
    */
    public function getSearchMatches()
    {
    	return $this->searchMatches;
    }
    
    /**
    * Function to return the string 'true' or 'false' after an operation
    */
    public function getSuccess()
    {
        return $this->success;
    }
    
    /**
    * Function to return the boolean true if an operation is successful
    */
    public function isSuccessful()
    {
        return $this->success == 'true';
    }
    
    /**
    * Function to return the property name
    * @param $id_string the 32-digit id string
    */
    public function getType( $id_string )
    {
    	$type_count = count( $this->types );
    	
    	for( $i = 0; $i < $type_count; $i++ )
		{
			$id = $this->createId( $this->types[ $i ], $id_string );
			$operation = new stdClass();
			$read_op   = new stdClass();
	
			$read_op->identifier = $id;
			$operation->read     = $read_op;
			$operations[]        = $operation;
		}
		
		$this->batch( $operations );
		
		$reply_array = $this->getReply()->batchReturn;
		
		for( $j = 0; $j < $type_count; $j++ )
		{
			if( $reply_array[ $j ]->readResult->success == 'true' )
			{
				foreach( T::$type_property_name_map as $type => $property )
				{
					if( $reply_array[ $j ]->readResult->asset->$property != NULL )
						return $type;
				}
			}
		}
		
		return "The id does not match any asset type.";
    }
    
    /**
    * Function to print the XML of the last request
    */
    public function printLastRequest()
    {
        print_r( $this->lastRequest );
    }
    
    /**
    * Function to print the XML of the last response
    */
    public function printLastResponse()
    {
        print_r( $this->lastResponse );
    }
    
    /**
    * Function for batch-execution
    * @param $operations the array of operations
    */
    function batch( $operations )
    {
        $batch_param = new stdClass();
        $batch_param->authentication = $this->auth;
        $batch_param->operation = $operations;
        
        $this->reply = $this->soapClient->batch( $batch_param );
        // the returned object is an array
        $this->storeResults();
    }
    
    /**
    * Function to check in an asset with the given identifier
    * @param $identifier the identifier of the asset to be checked in
    * @param $comments the comments to be added
    */
    function checkIn( $identifier, $comments='' )
    {
        $checkin_param = new stdClass();
        $checkin_param->authentication = $this->auth;
        $checkin_param->identifier     = $identifier;
        $checkin_param->comments       = $comments;
        
        $this->reply = $this->soapClient->checkIn( $checkin_param );
        $this->storeResults( $this->reply->checkInReturn );
    }
    
    /**
    * Function to check out an asset with the given identifier
    * @param $identifier the identifier of the asset to be checked out
    */
    function checkOut( $identifier )
    {
        $checkout_param = new stdClass();
        $checkout_param->authentication = $this->auth;
        $checkout_param->identifier     = $identifier;
        
        $this->reply = $this->soapClient->checkOut( $checkout_param );
        $this->storeResults( $this->reply->checkOutReturn );
    }
    
    /**
    * Function to copy the asset with the given identifier
    * @param $identifier the identifier of the object to be copied
    * @param $newIdentifier the new identifier of the new object
    * @param $newName the new name assigned to the new object
    * @param $doWorkflow whether to do any workflow
    */
    public function copy( $identifier, $newIdentifier, $newName, $doWorkflow ) 
    {
        $copy_params = new stdClass();
        $copy_params->authentication = $this->auth;
        $copy_params->identifier     = $identifier;
        $copy_params->copyParameters->destinationContainerIdentifier = $newIdentifier;
        $copy_params->copyParameters->newName = $newName;
        $copy_params->copyParameters->doWorkflow = $doWorkflow;
        
        $this->reply = $this->soapClient->copy( $copy_params );
        $this->storeResults( $this->reply->copyReturn );
    }
    
    /**
    * Function to create the given asset
    * @param $asset the asset to be created
    * @return the new id;
    */
    public function create( $asset ) 
    {
        $create_params = new stdClass();
        $create_params->authentication = $this->auth;
        $create_params->asset = $asset;
        
        $this->reply = $this->soapClient->create( $create_params );
        $this->storeResults( $this->reply->createReturn );
        
        return $this->reply->createReturn->createdAssetId;
    }
    
    /*
    * Function to delete the asset with the given identifier
    * @param $identifier the identifier of the object to be deleted
    */
    public function delete( $identifier )
    {
        $delete_params = new stdClass();
        $delete_params->authentication = $this->auth;
        $delete_params->identifier     = $identifier;
        
        $this->reply = $this->soapClient->delete( $delete_params );
        $this->storeResults( $this->reply->deleteReturn );
    }
    
    /*
    * Function to delete the message with the given identifier
    * @param $identifier the identifier of the message to be deleted
    */
    public function deleteMessage( $identifier )
    {
        $delete_message_params = new stdClass();
        $delete_message_params->authentication = $this->auth;
        $delete_message_params->identifier     = $identifier;
        
        $this->reply = $this->soapClient->deleteMessage( $delete_message_params );
        $this->storeResults( $this->reply->deleteMessageReturn );
    }
    
    
    /**
    * Function to edit the given asset
    * @param $asset the asset to be edited
    */
    public function edit( $asset )
    {
        $edit_params = new stdClass();
        $edit_params->authentication = $this->auth;
        $edit_params->asset = $asset;
        
        $this->reply = $this->soapClient->edit( $edit_params );
        $this->storeResults( $this->reply->editReturn );
    }
    
    /**
    * Function to edits the given accessRightsInformation
    * @param $accessRightsInformation the accessRightsInformation to be edited
    * @param $applyToChildren whether to apply the settings to children
    */
    public function editAccessRights( $accessRightsInformation, $applyToChildren )
    {
        $edit_params = new stdClass();
        $edit_params->authentication = $this->auth;
        $edit_params->accessRightsInformation = $accessRightsInformation;
        $edit_params->applyToChildren = $applyToChildren;
        
        $this->reply = $this->soapClient->editAccessRights( $edit_params );
        $this->storeResults( $this->reply->editAccessRightsReturn );
    }
    
    /**
    * Function to edit the given workflowSettings
    * @param $workflowSettings the workflowSettings to be edited
    * @param $applyInheritWorkflowsToChildren whether to apply inherited workflows to children
    * @param $applyRequireWorkflowToChildren whether to apply required workflows to children
    */
    public function editWorkflowSettings( 
    	$workflowSettings, $applyInheritWorkflowsToChildren, $applyRequireWorkflowToChildren )
    {
        $edit_params = new stdClass();
        $edit_params->authentication = $this->auth;
        $edit_params->workflowSettings = $workflowSettings;
        $edit_params->applyInheritWorkflowsToChildren = $applyInheritWorkflowsToChildren;
        $edit_params->applyRequireWorkflowToChildren = $applyRequireWorkflowToChildren;
        
        $this->reply = $this->soapClient->editWorkflowSettings( $edit_params );
        $this->storeResults( $this->reply->editWorkflowSettingsReturn );
    }
    
    /**
    * Function to list all messages
    */
    public function listMessages()
    {
        $list_messages_params = new stdClass();
        $list_messages_params->authentication = $this->auth;
        
        $this->reply = $this->soapClient->listMessages( $list_messages_params );
        $this->storeResults( $this->reply->listMessagesReturn );
        
        if( $this->isSuccessful() )
        {
        	$this->listed_messages = $this->reply->listMessagesReturn->messages;
        }
    }
    
    /**
    * Function to list all sites
    */
    public function listSites()
    {
        $list_sites_params = new stdClass();
        $list_sites_params->authentication = $this->auth;
        
        $this->reply = $this->soapClient->listSites( $list_sites_params );
        $this->storeResults( $this->reply->listSitesReturn );
    }
    
    /**
    * Function to list all subscribers of an asset
    * @param $identifier the identifier of the asset
    */
    public function listSubscribers( $identifier )
    {
        $list_subscribers_params = new stdClass();
        $list_subscribers_params->authentication = $this->auth;
        $list_subscribers_params->identifier     = $identifier;
        
        $this->reply = $this->soapClient->listSubscribers( $list_subscribers_params );
        $this->storeResults( $this->reply->listSubscribersReturn );
    }
    
    /**
    * Function to mark a message as 'read' or 'unread'
    * @param $identifier the identifier of the message
    * @param $markType the string 'read' or 'unread'
    */
    public function markMessage( $identifier, $markType )
    {
        $mark_message_params = new stdClass();
        $mark_message_params->authentication = $this->auth;
        $mark_message_params->identifier     = $identifier;
        $mark_message_params->markType       = $markType;
        
        $this->reply = $this->soapClient->markMessage( $mark_message_params );
        $this->storeResults( $this->reply->markMessageReturn );
    }
    
    /**
    * Function to move the asset with the given identifier
    * @param $identifier the identifier of the object to be moved
    * @param $newIdentifier the new container identifier
    * @param $newName the new name assigned to the object moved
    * @param $doWorkflow whether to do workflow
    */
    function move( $identifier, $newIdentifier, $newName, $doWorkflow ) 
    {
        $move_params = new stdClass();
        $move_params->authentication = $this->auth;
        $move_params->identifier     = $identifier;
        $move_params->moveParameters->destinationContainerIdentifier = $newIdentifier;
        $move_params->moveParameters->newName = $newName;
        $move_params->moveParameters->doWorkflow = $doWorkflow;
        
        $this->reply = $this->soapClient->move( $move_params );
        $this->storeResults( $this->reply->moveReturn );
    }
    
    /**
    * Function to perform the workflow transition
    * @param $workflowId the current workflow id
    * @param $actionIdentifier the identifier of the action
    * @param $transitionComment the comments
    */
    public function performWorkflowTransition( 
    	$workflowId, $actionIdentifier, $transitionComment='' )
    {
        $workflowTransitionInformation = new stdClass();
        $workflowTransitionInformation->workflowId        = $workflowId;
        $workflowTransitionInformation->actionIdentifier  = $actionIdentifier;
        $workflowTransitionInformation->transitionComment = $transitionComment;
        
        $transition_params = new stdClass();
        $transition_params->authentication                = $this->auth;
        $transition_params->workflowTransitionInformation = $workflowTransitionInformation;
        
        $this->reply = $this->soapClient->performWorkflowTransition( $transition_params );
        $this->storeResults( $this->reply->performWorkflowTransitionReturn );
    }
    
    /**
    * Function to publish the asset with the given identifier
    * @param $identifier the identifier of the object to be published
    * @param $destination the destination(s) where the asset should be published
    */
    public function publish( $identifier, $destination=NULL ) 
    {
        $publish_param = new stdClass();
        $publish_info  = new stdClass();
        $publish_param->authentication = $this->auth;
        $publish_info->identifier      = $identifier;
        
        if( $destination != NULL )
        {
            if( is_array( $destination ) )
                $publish_info->destinations = $destination;
            else
                $publish_info->destinations = array( $destination );
        }
        
        $publish_info->unpublish           = false;
        $publish_param->publishInformation = $publish_info;
        
        $this->reply = $this->soapClient->publish( $publish_param );
        $this->storeResults( $this->reply->publishReturn );
    }
    
    /**
    * Function to read the asset with the given identifier
    * @param $identifier the identifier of the object to be read
    */
    public function read( $identifier ) 
    {
        $read_param = new stdClass();
        $read_param->authentication = $this->auth;
        $read_param->identifier     = $identifier;
        
        $this->reply = $this->soapClient->read( $read_param );
        $this->storeResults( $this->reply->readReturn );
    }
       
    /**
    * Function to read the access rights of the asset with the given identifier
    * @param $identifier the identifier of the object to be read
    */
    public function readAccessRights( $identifier ) 
    {
        $read_param = new stdClass();
        $read_param->authentication = $this->auth;
        $read_param->identifier     = $identifier;
        
        $this->reply = $this->soapClient->readAccessRights( $read_param );
        $this->storeResults( $this->reply->readAccessRightsReturn );
    }
    
    /**
    * Function to read the audits of the asset with the given parameters
    * @param $params the parameters of readAudits
    */
    public function readAudits( $params ) 
    {
        $read_audits_param = new stdClass();
        $read_audits_param->authentication  = $this->auth;
        $read_audits_param->auditParameters = $params;
        
        $this->reply = $this->soapClient->readAudits( $read_audits_param );
        $this->storeResults( $this->reply->readAuditsReturn );
        $this->audits  = $this->reply->readAuditsReturn->audits;
    }
    
    /**
    * Function to read the workflow information associated with the given identifier
    * @param $identifier the identifier of the object to be read
    */
    public function readWorkflowInformation( $identifier ) 
    {
        $read_param = new stdClass();
        $read_param->authentication = $this->auth;
        $read_param->identifier     = $identifier;
        
        $this->reply = $this->soapClient->readWorkflowInformation( $read_param );
        $this->storeResults( $this->reply->readWorkflowInformationReturn );
    }    
    
    /**
    * Function to read the workflow settings associated with the given identifier
    * @param $identifier the identifier of the object to be read
    */
    public function readWorkflowSettings( $identifier ) 
    {
        $read_param = new stdClass();
        $read_param->authentication = $this->auth;
        $read_param->identifier     = $identifier;
        
        $this->reply = $this->soapClient->readWorkflowSettings( $read_param );
        $this->storeResults( $this->reply->readWorkflowSettingsReturn );
    }
    
    /**
    * Function to retrieve a property of an asset
    * @param $id the id of the property
    * @param $property the property name
    */

	function retrieve( $id, $property="" )
	{
		if( $property == "" )
		{
			$property = T::$type_property_name_map[ $id->type ];
		}
		
		$read_param = new stdClass();
		$read_param->authentication = $this->auth;
		$read_param->identifier     = $id;

		$this->reply = $this->soapClient->read( $read_param );
		$this->storeResults( $this->reply->readReturn );

		return $this->reply->readReturn->asset->$property;
	}
	
    /**
    * Function to search for some entity
    * @param $searchInfo the searchInfo object
    */
    public function search( $searchInfo ) 
    {
        $search_info_param = new stdClass();
        $search_info_param->authentication    = $this->auth;
        $search_info_param->searchInformation = $searchInfo;
        
        $this->reply = $this->soapClient->search( $search_info_param );
        $this->searchMatches = $this->reply->searchReturn->matches;
        $this->storeResults( $this->reply->searchReturn );
    }        
    
    /**
    * Function to send a message
    * @param $message the message object to be sent
    */
    public function sendMessage( $message ) 
    {
        $send_message_param = new stdClass();
        $send_message_param->authentication = $this->auth;
        $send_message_param->message        = $message;
        
        $this->reply = $this->soapClient->sendMessage( $send_message_param );
        $this->storeResults( $this->reply->sendMessageReturn );
    }    
    
    /**
    * Function to copy the site with the given identifier
    * @param $original_id the id of the site to be copied
    * @param $original_name the name of the site to be copied
    * @param $new_name the name assigned to the new site
    */
    function siteCopy( $original_id, $original_name, $new_name ) 
    {
        $site_copy_params = new stdClass();
        $site_copy_params->authentication = $this->auth;
        $site_copy_params->originalSiteId = $original_id;
        $site_copy_params->originalSiteName = $original_name;
        $site_copy_params->newSiteName = $new_name;

        $this->reply = $this->soapClient->siteCopy( $site_copy_params );
        $this->storeResults( $this->reply->siteCopyReturn );
    }
    
    /**
    * Function to un-publish the asset with the given identifier
    * @param $identifier the identifier of the object to be un-published
    */
    public function unpublish( $identifier, $destination=NULL ) 
    {
        $publish_param = new stdClass();
        $publish_info  = new stdClass();
        $publish_param->authentication = $this->auth;
        $publish_info->identifier      = $identifier;
        
        if( $destination != NULL )
        {
            if( is_array( $destination ) )
                $publish_info->destinations = $destination;
            else
                $publish_info->destinations = array( $destination );
        }
        
        $publish_info->unpublish           = true;
        $publish_param->publishInformation = $publish_info;
        
        $this->reply = $this->soapClient->publish( $publish_param );
        $this->storeResults( $this->reply->publishReturn );
    }
    
    /**
    * Function to create an id object for an asset
    * @param $id the id string of an asset
    * @param $type the type of the asset
    */
    public function createIdWithIdType( $id, $type )
    {
        $identifier       = new stdClass();
        $identifier->id   = $id;
        $identifier->type = $type;
        return $identifier;
    }

    /**
    * Function to create an id object for an asset
    * @param $path the path and name of an asset
    * @param $siteName the site name
    * @param $type the type of the asset
    */
    public function createIdWithPathSiteNameType( $path, $site_name, $type )
    {
        $identifier                 = new stdClass();
        $identifier->path->path     = $path;
        $identifier->path->siteName = $site_name;
        $identifier->type           = $type;
        return $identifier;
    }

    /**
    * Function to create an id object for an asset
    * @param $type the type of the asset
    * @param $id_path either the id or the path of an asset
    * @param $siteName the site name
    */
    public function createId( $type, $id_path, $site_name = NULL )
    {
    	$non_digital_id_types = array(
    		T::GROUP, T::ROLE, T::SITE, T::USER
    	);
    
        $identifier = new stdClass();
        
    	if( $this->isHexString( $id_path ) )
    	{
    		// if id string is passed in, ignore site name
    		$identifier->id = $id_path;
    	}
    	else if( in_array( $type, $non_digital_id_types ) )
    	{
    		if( $type != T::SITE ) // not a site
    		{
    			$identifier->id = $id_path;
    		}
    		else // a site
    		{
    			$identifier->path->path = $id_path;
    		}
    	}
    	else
    	{
    		if( trim( $site_name ) == "" )
    		{
    			throw new EmptyValueException( M::EMPTY_SITE_NAME );
    		}
    		
        	$identifier->path->path     = $id_path;
        	$identifier->path->siteName = $site_name;
        }
        $identifier->type = $type;
        return $identifier;
    }
    
    public function isHexString( $string )
    {
    	$pattern = "/[0-9a-f]{32}/";
    	$matches = array();
    	
    	preg_match( $pattern, $string, $matches );
    	
    	return $matches[ 0 ] == $string;
    }

    /**
    * Function to create a file object
    * @param $parentFolderId the id object of the parent folder
    * @param $siteName the site name
    * @param $name the name of the file
    * @param $data the data of the file
    */
    public function createFileWithParentIdSiteNameNameData( $parentFolderId, $siteName, $name, $data )
    {
        $file = new stdClass();
        $file->parentFolderId = $parentFolderId;
        $file->siteName       = $siteName;
        $file->name           = $name;
        $file->data           = $data;
        return $file;
    }
    
    // helper function
    private function storeResults( $return = NULL )
    {
    	if( $return != NULL )
    	{
    		$this->success      = $return->success;
			$this->message      = $return->message;
		}
		$this->lastRequest  = $this->soapClient->__getLastRequest();
		$this->lastResponse = $this->soapClient->__getLastResponse();
    }
}
?>