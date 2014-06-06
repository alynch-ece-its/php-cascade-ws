<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 6/5/2014 Fixed a bug in getPageLevelRegionBlockFormat.
  * 5/13/2014 Added createNInstancesForMultipleField 
  *   and replaced all string literals with constants
 */
class Page extends Linkable
{
	const DEBUG = false;
	const DUMP  = false;
	const TYPE  = T::PAGE;

	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		$this->content_type = new ContentType( 
		    $service, $service->createId( ContentType::TYPE, 
		    $this->getProperty()->contentTypeId ) );
		    
		parent::setPageContentType( $this->content_type );
		    
		if( $this->getProperty()->structuredData != NULL )
		{
			$this->data_definition_id = $this->content_type->getDataDefinitionId();

			// structuredDataNode could be empty for xml pages
			if( isset( $this->getProperty()->structuredData->structuredDataNodes->structuredDataNode ) )
			{
				$this->processStructuredData( $this->data_definition_id );
			}
		}
		else
		{
			$this->xhtml = $this->getProperty()->xhtml;
		}
		
		$this->processPageConfigurations( $this->getProperty()->pageConfigurations->pageConfiguration );
	}

	public function appendSibling( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->appendSibling( $node_name );
		return $this;
	}

	public function createNInstancesForMultipleField( $number, $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$number = intval( $number );
		
		if( ! $number > 0 )
		{
			throw new UnacceptableValueException( "The value $number is not a number." );
		}
		
		if( !$this->hasNode( $node_name ) )
		{
			throw new NodeException( "The node $node_name does not exist." );
		}
		
		$num_of_instances  = $this->getNumberOfSiblings( $node_name );
	
		if( $num_of_instances < $number ) // more needed
		{
			while( $this->getNumberOfSiblings( $node_name ) != $number )
			{
				$this->appendSibling( $node_name );
			}
		}
		else if( $instances_wanted < $number )
		{
			while( $this->getNumberOfSiblings( $node_name ) != $number )
			{
				$this->removeLastSibling( $node_name );
			}
		}
		return $this;
	}
	
	public function displayDataDefinition()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->getDataDefinition()->displayXML();
		return $this;
	}
	
	public function displayXhtml()
	{
		if( !$this->hasStructuredData() )
		{
			$xhtml_string = XMLUtility::replaceBrackets( $this->xhtml );
			echo S_H2 . 'XHTML' . E_H2;
			echo $xhtml_string . HR;
		}
		return $this;
	}
	
	public function edit( 
		Workflow $wf=NULL, 
		WorkflowDefinition $wd=NULL, 
		$new_workflow_name="", 
		$comment="")
	{
		$asset = new stdClass();
		$page  = $this->getProperty();
		
		if( self::DEBUG )
		{
			echo "P::L80 " . S_PRE;
			//var_dump( $page->pageConfigurations );
			echo E_PRE;
		}

		$page->metadata = $this->getMetadata()->toStdClass();
		
		if( $this->structured_data != NULL )
		{
			$page->structuredData = $this->structured_data->toStdClass();
			$page->xhtml = NULL;
		}
		else
		{
			$page->structuredData = NULL;
			$page->xhtml = $this->xhtml;
		}
		
		$page->pageConfigurations->pageConfiguration = array();
		
		foreach( $this->page_configurations as $config )
		{
			$page->pageConfigurations->pageConfiguration[] = $config->toStdClass();
		}
		
		if( self::DEBUG )
		{
			echo "P::L152 " . S_PRE;
			//var_dump( $page->pageConfigurations );
			echo E_PRE;
		}
		
		if( $wf != NULL )
		{
			if( trim( $comment ) == "" )
				throw new EmptyValueException( M::EMPTY_COMMENT );
				
			$wf_config                       = new stdClass();
			$wf_config->workflowName         = $wf->getName();
			$wf_config->workflowDefinitionId = $wf->getId();
			$wf_config->workflowComments     = $comment;
			$asset->workflowConfiguration    = $wf_config;
		}
		else if( $wd != NULL )
		{
			if( trim( $comment ) == "" )
				throw new EmptyValueException( M::EMPTY_COMMENT );
				
			if( trim( $new_workflow_name ) == "" )
				throw new EmptyValueException( M::EMPTY_WORKFLOW_NAME );
				
			$wf_config                       = new stdClass();
			$wf_config->workflowName         = $new_workflow_name;
			$wf_config->workflowDefinitionId = $wd->getId();
			$wf_config->workflowComments     = $comment;
			$asset->workflowConfiguration    = $wf_config;
		}
		
		$asset->{ $p = $this->getPropertyName() } = $page;
		// edit asset
		$service = $this->getService();
		$service->edit( $asset );
		
		if( !$service->isSuccessful() )
		{
			throw new EditingFailureException( 
				M::EDIT_ASSET_FAILURE . $service->getMessage() );
		}
		return $this;
	}
	
	public function getAssetNodeType( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		
		return $this->structured_data->getAssetNodeType( $identifier );
	}
	
	public function getBlockId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		
		return $this->structured_data->getBlockId( $node_name );
	}
	
	public function getBlockPath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		
		return $this->structured_data->getBlockPath( $node_name );
	}
	
	public function getConfigurationSet()
	{
		return $this->getPageConfigurationSet();
	}
	
	public function getConfigurationSetId()
	{
		return $this->getProperty()->configurationSetId; // NULL for page
	}
	
	public function getConfigurationSetPath()
	{
		return $this->getProperty()->configurationSetPath; // NULL for page
	}
	
	public function getContentType()
	{
		$service = $this->getService();
		
		return Asset::getAsset( $service,
			ContentType::TYPE,
			$this->getProperty()->contentTypeId );
	}

	public function getContentTypeId()
	{
		return $this->getProperty()->contentTypeId;
	}
	
	public function getContentTypePath()
	{
		return $this->getProperty()->contentTypePath;
	}
	
	public function getDataDefinition()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getDataDefinition();
	}
	
	public function getFileId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getFileId( $node_name );
	}
	
	public function getFilePath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getFilePath( $node_name );
	}
	
	public function getIdentifiers()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getIdentifiers();
	}
	
	public function getLastPublishedDate()
	{
		return $this->getProperty()->lastPublishedDate;
	}
	
	public function getLastPublishedBy()
	{
		return $this->getProperty()->lastPublishedBy;
	}
	
	public function getLinkableId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getLinkableId( $node_name );
	}
	
	public function getLinkablePath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getLinkablePath( $node_name );
	}
	
	public function getMaintainAbsoluteLinks()
	{
		return $this->getProperty()->maintainAbsoluteLinks;
	}
	
	public function getNodeType( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getNodeType( $identifier );
	}

	public function getNumberOfSiblings( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		if( trim( $node_name ) == "" )
		{
			throw new EmptyValueException( M::EMPTY_IDENTIFIER );
		}
		
		if( !$this->hasIdentifier( $node_name ) )
		{
			throw new NodeException( "The node $node_name does not exist" );
		}
		return $this->structured_data->getNumberOfSiblings( $node_name );
	}

	public function getPageConfigurationSet()
	{
		// the page does not store page configuration set info
		return $this->content_type->getPageConfigurationSet();
	}
	
	public function getPageId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getPageId( $node_name );
	}
	
	public function getPageLevelRegionBlockFormat()
	{
		$block_format_array = array();
		
		$configuration       = $this->getContentType()->getConfigurationSet()->getDefaultConfiguration();
		$configuration_name  = $configuration->getName();
		$config_page_regions = $configuration->getPageRegions();
		$config_region_names = $configuration->getPageRegionNames();
		
		if( self::DEBUG )
		{
			echo "P::L393:" . S_PRE;
			//var_dump( $config_region_names );
			echo E_PRE;
		}
		
		$page_level_config  = $this->page_configuration_map[ $configuration_name ];
		$page_level_regions = $page_level_config->getPageRegions();
		$page_region_names  = $page_level_config->getPageRegionNames();
		
		if( self::DEBUG && self::DUMP )
		{
			echo "P::L404:" . S_PRE;
			var_dump( $page_region_names );
			echo E_PRE;
		}
		
		$template = $this->getContentType()->getConfigurationSet()->
			getPageConfigurationTemplate( $configuration_name );
		$template_region_names = $template->getPageRegionNames();
		
		foreach( $page_region_names as $page_region_name )
		{
			// initialize id variables
			$block_id = NULL;
			$format_id = NULL;

			if( self::DEBUG )
			{
				echo "P::L421 " . $page_region_name . BR;

				if( $template->hasPageRegion( $page_region_name ) )
				{
					echo "template block: " . $template->getPageRegion( $page_region_name )->getBlockId() . BR;
					echo "template format: " . $template->getPageRegion( $page_region_name )->getFormatId() . BR;
				}
			
				if( $configuration->hasPageRegion( $page_region_name ) )
				{
					echo "Config block: " . $configuration->getPageRegion( $page_region_name )->getBlockId() . BR;
					echo "Config format: " . $configuration->getPageRegion( $page_region_name )->getFormatId() . BR;
				}
				
				if( $page_level_config->hasPageRegion( $page_region_name ) )
				{
					echo "Page block: " . $page_level_config->getPageRegion( $page_region_name )->getBlockId() . BR;
					echo "Page format: " . $page_level_config->getPageRegion( $page_region_name )->getFormatId() . BR;
				} 
			}
			
			if( $template->hasPageRegion( $page_region_name ) )
			{
				$template_block_id  = $template->getPageRegion( $page_region_name )->getBlockId();
				$template_format_id = $template->getPageRegion( $page_region_name )->getFormatId();
			}
		
			if( $configuration->hasPageRegion( $page_region_name ) )
			{
				$config_block_id  = $configuration->getPageRegion( $page_region_name )->getBlockId();
				$config_format_id = $configuration->getPageRegion( $page_region_name )->getFormatId();
			}
			else
			{
				$config_block_id  = NULL;
				$config_format_id = NULL;
			}
			
			if( $page_level_config->hasPageRegion( $page_region_name ) )
			{
				$page_block_id  = $page_level_config->getPageRegion( $page_region_name )->getBlockId();
				$page_format_id = $page_level_config->getPageRegion( $page_region_name )->getFormatId();
			} 

			if( isset( $page_block_id ) )
			{
				$block_id = NULL;
				
				if( !isset( $config_block_id ) )
				{
					if( $page_block_id != $template_block_id )
					{
						$block_id = $page_block_id;
					}
				}
				else if( $config_block_id != $page_block_id )
				{
					$block_id = $page_block_id;
				}
			}

			if( isset( $page_format_id ) )
			{
				$format_id = NULL;
				
				if( !isset( $config_format_id ) )
				{
					if( $page_format_id != $template_format_id )
					{
						$format_id = $page_format_id;
					}
				}
				else if( $config_format_id != $page_format_id )
				{
					$format_id = $page_format_id;
				}
			}
			
			if( $block_id != NULL )
			{
				if( !isset( $block_format_array[ $page_region_name ] ) )
				{
					$block_format_array[ $page_region_name ] = array();
				}
				
				$block_format_array[ $page_region_name ][ 'block' ] = $block_id;
			}
			
			if( $format_id != NULL )
			{
				if( !isset( $block_format_array[ $page_region_name ] ) )
				{
					$block_format_array[ $page_region_name ] = array();
				}
				
				$block_format_array[ $page_region_name ][ 'format' ] = $format_id;
			}
		}
		
		return $block_format_array;
	}
	
	public function getPagePath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getPagePath( $node_name );
	}
	
	public function getPageRegionNames( $config_name )
	{
		if( !isset( $this->page_configuration_map[ $config_name ] ) )
		{
			throw NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
		}
		
		return $this->page_configuration_map[ $config_name ]->getPageRegionNames();
	}

	public function getShouldBePublished()
	{
		return $this->getProperty()->shouldBePublished;
	}
	
	public function getStructuredData()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data;
	}
	
	public function getSymlinkId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getSymlinkId( $node_name );
	}
	
	public function getSymlinkPath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getSymlinkPath( $node_name );
	}
	
	public function getText( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->getText( $node_name );
	}
	
	public function getTextNodeType( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		
		return $this->structured_data->getTextNodeType( $identifier );
	}

	public function getWorkflow()
	{
		$service = $this->getService();
		$service->readWorkflowInformation( $service->createId( self::TYPE, $this->getProperty()->id ) );
		
		if( $service->isSuccessful() )
		{
			if( $service->getReply()->readWorkflowInformationReturn->workflow != NULL )
				return new Workflow( $service->getReply()->readWorkflowInformationReturn->workflow, $service );
			else
				return NULL; // no workflow
		}
		else
		{
			throw new NullAssetException( M::READ_WORKFLOW_FAILURE );
		}
	}
	
	public function getXhtml()
	{
		return $this->getProperty()->xhtml;
	}
	
	public function hasIdentifier( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->hasNode( $node_name );
	}
	
	public function hasNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->hasNode( $node_name );
	}
	
	public function hasPageRegion( $config_name, $region_name )
	{
		return $this->page_configuration_map[ $config_name ]->
			hasPageRegion( $region_name );
	}
	
	public function hasStructuredData()
	{
		return $this->structured_data != NULL;
	}
	
	public function isAssetNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->isAssetNode( $node_name );
	}
	
	public function isGroupNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->isGroupNode( $node_name );
	}
	
	public function isMultiple( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->isMultiple( $identifier );
	}
	
	public function isRequired( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->isRequired( $identifier );
	}

	public function isTextNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->isTextNode( $node_name );
	}
	
	public function isWYSIWYG( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->isWYSIWYG( $identifier );
	}
	
	public function publish()
	{
		if( $this->getProperty()->shouldBePublished )
		{
			$service = $this->getService();
			$service->publish( 
				$service->createId( $this->getType(), $this->getId() ) );
		}
		return $this;
	}

	public function removeLastSibling( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->removeLastSibling( $node_name );
		return $this;
	}
	
	public function replaceByPattern( $pattern, $replace, $include=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->replaceByPattern( $pattern, $replace, $include );
		return $this;
	}
	
	public function replaceXhtmlByPattern( $pattern, $replace )
	{
		if( $this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		
		$this->xhtml = preg_replace( $pattern, $replace, $this->xhtml );
		
		return $this;
	}
	
	public function replaceText( $search, $replace, $include=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->replaceText( $search, $replace, $include );
		return $this;
	}
	
	public function searchText( $string )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		return $this->structured_data->searchText( $string );
	}
	
	public function searchXhtml( $string )
	{
		if( $this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}

		return strpos( $this->xhtml, $string ) !== false;
	}

	public function setBlock( $node_name, Block $block=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->setBlock( $node_name, $block );
		return $this;
	}
	
	public function setContentType( ContentType $c )
	{
		// nothing to do
		if( $c->getId() == $this->getContentType()->getId() )
		{
			echo "Nothing to do" . BR;
			return $this;
		}
	
		// part 1: get the page level blocks and formats
		$block_format_array = $this->getPageLevelRegionBlockFormat();
		
		$default_configuration       = $this->getContentType()->getConfigurationSet()->getDefaultConfiguration();
		$default_configuration_name  = $default_configuration->getName();
		$default_config_page_regions = $default_configuration->getPageRegions();
		$default_region_names        = $default_configuration->getPageRegionNames();
		
		$page_level_config  = $this->page_configuration_map[ $default_configuration_name ];
		$page_level_regions = $page_level_config->getPageRegions();
		$page_region_names  = $page_level_config->getPageRegionNames();
		
		if( self::DEBUG && self::DUMP ) { echo "P::L820 " . S_PRE;
			var_dump( $block_format_array );
			echo E_PRE; }
		
		// part 2: switch content type
		if( $c == NULL )
			throw new NullAssetException( M::NULL_ASSET );

		$page = $this->getProperty();
		$page->contentTypeId      = $c->getId();
		$page->contentTypePath    = $c->getPath();
		
		$configuration_array = array();
		$new_configurations = $c->getPageConfigurationSet()->getPageConfigurations();
		
		foreach( $new_configurations as $new_configuration )
		{
			$configuration_array[] = $new_configuration->toStdClass();
		}
		
		$page->pageConfigurations->pageConfiguration = $configuration_array;
		
		if( self::DEBUG && self::DUMP )
		{
			echo "P::L844 " . S_PRE;
			var_dump( $page->pageConfigurations );
			echo E_PRE;
		}
		
		$asset = new stdClass();
		$asset->{ $p = $this->getPropertyName() } = $page;
		// edit asset
		$service = $this->getService();
		$service->edit( $asset );
		
		if( !$service->isSuccessful() )
		{
			throw new EditingFailureException( 
				M::EDIT_ASSET_FAILURE . $service->getMessage() );
		}
		
		if( self::DEBUG && self::DUMP )
		{
			echo "P::L863 " . S_PRE;
			var_dump( $this->getProperty()->pageConfigurations );
			echo E_PRE;
		}
		
		$this->reloadProperty();
		$this->processPageConfigurations( $this->getProperty()->pageConfigurations->pageConfiguration );
		
		$this->content_type = $c;
		parent::setPageContentType( $this->content_type );
		    
		if( $this->getProperty()->structuredData != NULL )
		{
			$this->data_definition_id = $this->content_type->getDataDefinitionId();

			// structuredDataNode could be empty for xml pages
			if( isset( $this->getProperty()->structuredData->structuredDataNodes->structuredDataNode ) )
			{
				$this->processStructuredData( $this->data_definition_id );
			}
		}
		else
		{
			$this->xhtml = $this->getProperty()->xhtml;
		}

		// part 3: plug the blocks and formats back in
		$count = count( array_keys( $block_format_array) );
		
		if( $count > 0 )
		{
			$service = $this->getService();
			$page_level_config  = $this->page_configuration_map[ $default_configuration_name ];
			$page_region_names  = $page_level_config->getPageRegionNames();
			
			if( self::DEBUG && self::DUMP )
			{
				echo "P::L900 " . S_PRE;
				var_dump( $page_region_names );
				echo E_PRE;
			}
			
			foreach( $block_format_array as $region => $block_format )
			{
				// only if the region exists in the current config
				if( in_array( $region, $page_region_names ) )
				{
					if( isset( $block_format[ 'block' ] ) )
					{
						$block_id = $block_format[ 'block' ];
					}
					if( isset( $block_format[ 'format' ] ) )
					{
						$format_id = $block_format[ 'format' ];
					}
				
					if( isset( $block_id ) )
					{
						$block = $this->getAsset( $service, $service->getType( $block_id ), $block_id );
						$this->setRegionBlock( $default_configuration_name, $region, $block );
					}
				
					if( isset( $format_id ) )
					{
						$format = $this->getAsset( $service, $service->getType( $format_id ), $format_id );
						$this->setRegionFormat( $default_configuration_name, $region, $format );
					}
				}
			}
			
			$this->edit()->reloadProperty();
		}


		$page  = $this->getProperty();
		
		if( self::DEBUG && self::DUMP )
		{
			echo "P::L941 " . S_PRE;
			var_dump( $page->pageConfigurations );
			echo E_PRE;
		}

		return $this;
	}
	
	public function setFile( $node_name, File $file=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->setFile( $node_name, $file );
		return $this;
	}
	
	public function setLinkable( $node_name, Linkable $linkable=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->setLinkable( $node_name, $linkable );
		return $this;
	}
	
	public function setMaintainAbsoluteLinks( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean" );
		
		$this->getProperty()->maintainAbsoluteLinks = $bool;
		
		return $this;
	}
	
	public function setPage( $node_name, Page $page=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->setPage( $node_name, $page );
		return $this;
	}
	
	public function setRegionBlock( $config_name, $region_name, Block $block=NULL )
	{
		if( !isset( $this->page_configuration_map[ $config_name ] ) )
		{
			throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
		}
	
		if( self::DEBUG )
		{
			echo "P::L1000 Setting block to region" . BR .
				"Region name: " . $region_name . BR;
				if( $block != NULL )
					"Block ID: " . $block->getId() . BR ;
		}
		
		$this->page_configuration_map[ $config_name ]->setRegionBlock( $region_name, $block );
		
		return $this;
	}
	
	public function setRegionFormat( $config_name, $region_name, Format $format=NULL )
	{
		if( !isset( $this->page_configuration_map[ $config_name ] ) )
		{
			throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
		}
	
		$this->page_configuration_map[ $config_name ]->setRegionFormat( $region_name, $format );
		
		return $this;
	}
	
	public function setRegionNoBlock( $config_name, $region_name, $no_block )
	{
		if( !isset( $this->page_configuration_map[ $config_name ] ) )
		{
			throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
		}
	
		$this->page_configuration_map[ $config_name ]->setRegionNoBlock( $region_name, $no_block );
		
		return $this;
	}
	
	public function setRegionNoFormat( $config_name, $region_name, $no_format )
	{
		if( !isset( $this->page_configuration_map[ $config_name ] ) )
		{
			throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
		}
	
		$this->page_configuration_map[ $config_name ]->setRegionNoFormat( $region_name, $no_format );
		
		return $this;
	}
	
	public function setShouldBeIndexed( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean" );
			
		$this->getProperty()->shouldBeIndexed = $bool;
		return $this;
	}
	
	public function setShouldBePublished( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean" );
			
		$this->getProperty()->shouldBePublished = $bool;
		return $this;
	}
	
	public function setStructuredData( StructuredData $structured_data )
	{
		$this->structured_data = $structured_data;
		$this->edit();
		$this->reloadProperty();
		$this->processStructuredData();
		return $this;
	}

	public function setSymlink( $node_name, Symlink $symlink=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->setSymlink( $node_name, $symlink );
		return $this;
	}
	
	public function setText( $node_name, $text )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
		}
		
		$this->structured_data->setText( $node_name, $text );
		return $this;
	}
	
	public function setXhtml( $xhtml )
	{
		if( !$this->hasStructuredData() )
		{
			$this->xhtml = $xhtml;
		}
		else
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		return $this;
	}
	
	public function swapData( $node_name1, $node_name2 )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
		}
		
		$this->structured_data->swapData( $node_name1, $node_name2 );
		$this->edit()->reloadProperty()->processStructuredData( $this->data_definition_id );

		return $this;
	}

	private function processPageConfigurations( $page_config_std )
	{
		$this->page_configurations = array();
		
		if( !is_array( $page_config_std ) )
		{
			$page_config_std = array( $page_config_std );
		}
		
		if( self::DEBUG && self::DUMP )
		{
			echo "P::L1134 " . S_PRE;
			var_dump( $page_config_std );
			echo E_PRE;
		}
		
		foreach( $page_config_std as $pc_std )
		{
			$pc = new PageConfiguration( $pc_std, self::TYPE, $this->getService() );
			$this->page_configurations[] = $pc;
			$this->page_configuration_map[ $pc->getName() ] = $pc;
		}
	}

	private function processStructuredData( $data_definition_id )
	{
		$this->structured_data = new StructuredData( 
			$this->getProperty()->structuredData, 
			$this->getService(),
			$data_definition_id
		);
	}

	private $structured_data;
	private $page_configurations; // an array of objects
	private $page_configuration_map;
	private $data_definition_id;
	private $content_type;
}