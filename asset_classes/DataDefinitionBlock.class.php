<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class DataDefinitionBlock extends Block
{
	const DEBUG = false;
	const TYPE  = T::DATABLOCK;

    /**
    * The constructor
    * @param $service the AssetOperationHandlerService object
    * @param $identifier the identifier object
    */
	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		if( $this->getProperty()->structuredData != NULL )
		{
			$this->processStructuredData();
		}
		else
		{
			$this->xhtml = $this->getProperty()->xhtml;
		}
	}

	public function appendSibling( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		//if( self::DEBUG ) { echo "DDB::L51 calling SD::appendSibling" . BR; }
		$this->structured_data->appendSibling( $node_name );
		$this->edit();
		return $this;
	}
	
	public function copyDataTo( $block )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$block->setStructuredData( $this->getStructuredData() );
		return $this;
	}
	
	public function createNInstancesForMultipleField( $number, $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
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
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
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
	
	public function edit()
	{
		// edit the asset
		$asset = new stdClass();
		$block = $this->getProperty();
		
		$block->metadata = $this->getMetadata()->toStdClass();
		
		if( $this->structured_data != NULL )
		{
			$block->structuredData = $this->structured_data->toStdClass();
			$block->xhtml          = NULL;
		}
		else
		{
			$block->structuredData = NULL;
			$block->xhtml          = $this->xhtml;
		}

		$asset->{ $p = $this->getPropertyName() } = $block;
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
	
	public function getAssetNodeType( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getAssetNodeType( $identifier );
	}

	public function getBlockId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getBlockId( $node_name );
	}
	
	public function getBlockPath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getBlockPath( $node_name );
	}
	
	public function getDataDefinition()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getDataDefinition();
	}
	
	public function getFileId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getFileId( $node_name );
	}
	
	public function getFilePath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getFilePath( $node_name );
	}
	
	public function getIdentifiers()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getIdentifiers();
	}
	
	public function getLinkableId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getLinkableId( $node_name );
	}
	
	public function getLinkablePath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getLinkablePath( $node_name );
	}
	
	public function getNodeType( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getNodeType( $identifier );
	}
	
	public function getNumberOfSiblings( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
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

	public function getPageId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getPageId( $node_name );
	}
	
	public function getPagePath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getPagePath( $node_name );
	}
	
	public function getStructuredData()
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data;
	}
	
	public function getSymlinkId( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getSymlinkId( $node_name );
	}
	
	public function getSymlinkPath( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getSymlinkPath( $node_name );
	}
	
	public function getText( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getText( $node_name );
	}
	
	public function getTextNodeType( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->getTextNodeType( $identifier );
	}

	public function getXhtml()
	{
		return $this->xhtml;
	}
	
	public function hasIdentifier( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK . " " . $this->getPath() );
		}
		
		return $this->hasNode( $node_name );
	}
	
	public function hasNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->hasNode( $node_name );
	}
	
	public function hasStructuredData()
	{
		return $this->structured_data != NULL;
	}
	
	public function isAssetNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->isAssetNode( $node_name );
	}
	
	public function isGroupNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->isGroupNode( $node_name );
	}
	
	public function isMultiple( $field_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->getDataDefinition()->isMultiple( $field_name );
	}
	
	public function isRequired( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->isRequired( $identifier );
	}

	public function isTextNode( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->isTextNode( $node_name );
	}
	
	public function isWYSIWYG( $identifier )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->isWYSIWYG( $identifier );
	}

	public function removeLastSibling( $node_name )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->removeLastSibling( $node_name );
		$this->edit();
		return $this;
	}
	
	public function replaceByPattern( $pattern, $replace, $include=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->replaceByPattern( $pattern, $replace, $include );
		return $this;
	}
	
	public function replaceXhtmlByPattern( $pattern, $replace )
	{
		if( $this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_XHTML_BLOCK );
		}
		
		$this->xhtml = preg_replace( $pattern, $replace, $this->xhtml );
		
		return $this;
	}
	
	public function replaceText( $search, $replace, $include=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->replaceText( $search, $replace, $include );
		return $this;
	}
	
	public function searchText( $string )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		return $this->structured_data->searchText( $string );
	}
	
	public function searchXhtml( $string )
	{
		if( $this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_XHTML_BLOCK );
		}

		return strpos( $this->xhtml, $string ) !== false;
	}

	public function setBlock( $node_name, Block $block=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->setBlock( $node_name, $block );
		return $this;
	}
	
	public function setFile( $node_name, File $file=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->setFile( $node_name, $file );
		return $this;
	}

	public function setLinkable( $node_name, Linkable $linkable=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->setLinkable( $node_name, $linkable );
		return $this;
	}

	public function setPage( $node_name, Page $page=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->setPage( $node_name, $page );
		return $this;
	}
	
	public function setStructuredData( StructuredData $structured_data )
	{
		$this->structured_data = $structured_data;
		$this->edit();
		$this->processStructuredData();
		return $this;
	}

	public function setSymlink( $node_name, Symlink $symlink=NULL )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->setSymlink( $node_name, $symlink );
		return $this;
	}

	public function setText( $node_name, $text )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
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
			throw new WrongBlockTypeException( M::NOT_XHTML_BLOCK );
		}
		return $this;
	}

	public function swapData( $node_name1, $node_name2 )
	{
		if( !$this->hasStructuredData() )
		{
			throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
		}
		
		$this->structured_data->swapData( $node_name1, $node_name2 );
		$this->edit()->processStructuredData();
		
		return $this;
	}

	private function processStructuredData()
	{
		$this->structured_data = new StructuredData( 
			$this->getProperty()->structuredData, 
			$this->getService() );
	}

	private $structured_data;
	private $xhtml;
}
?>
