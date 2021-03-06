<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class StructuredData extends Property
{
	const DEBUG = false;

	public function __construct( stdClass $sd, $service, $data_definition_id=NULL )
	{
		// a data definition block will have a data definition id in the sd object
		// a page will need to pass into the data definition id
		if( $sd != NULL )
		{
			// store the data
			if( $sd->definitionId != NULL )
			{
				$this->definition_id = $sd->definitionId;
				$this->type = DataDefinitionBlock::TYPE;
			}
			else if( $data_definition_id != NULL )
			{
				$this->definition_id = $data_definition_id;
				$this->type = Page::TYPE;
			}
				
			$this->definition_path = $sd->definitionPath;
			// initialize the arrays
			$this->children        = array();
			$this->node_map        = array();
			
			// store the data definition
			
			$this->data_definition = new DataDefinition( 
		    	$service, $service->createId( DataDefinition::TYPE, $this->definition_id ) );
			// turn structuredDataNode into an array
			if( !is_array( $sd->structuredDataNodes->structuredDataNode ) )
			{
				$child_nodes = array( $sd->structuredDataNodes->structuredDataNode );
			}
			else
			{
				$child_nodes = $sd->structuredDataNodes->structuredDataNode;
				if( self::DEBUG ) echo "SD::L44 " . "Number of nodes in std: " . count( $child_nodes ) . BR;
			}
			// convert stdClass to objects
			StructuredDataNode::processStructuredDataNodes( 
				'', $this->children, $child_nodes, $this->data_definition );
		}
		else
		{
			echo "NULL stdClass()";
		}
		
		$this->node_map    = $this->getIdentifierNodeMap();
		$this->identifiers = array_keys( $this->node_map );
		
		if( self::DEBUG ) echo "SD::L58 " . "First node ID: " . $first_node_id . BR;
	}
	
	public function appendSibling( $first_node_id )
	{
		if( self::DEBUG ) echo "SD::L63 " . "First node ID: " . $first_node_id . BR;
		
		if( !$this->hasIdentifier( $first_node_id ) )
		{
			throw new NodeException( "The node $first_node_id does not exist." );
		}
		
		$first_node = $this->node_map[ $first_node_id ];
		$field_id   = StructuredDataNode::getFieldIdentifier( $first_node_id );
		if( self::DEBUG ) echo "SD::L72 " . "Field ID: " . $field_id . BR;
		
		// non-ambiguous path, no multipled-parent
		// no ;digits in the identifier
		if( strpos( $first_node_id, $field_id ) !== false )
		{
			if( self::DEBUG ) echo "SD::L78 " . "non_ambiguous" . BR;
			return $this->appendNodeToField( $field_id );
		}
		
		// ambiguous, with multiple ancestors
		$parent_id   = $first_node->getParentId();
		$parent_node = $this->getNode( $parent_id );
		
		if( self::DEBUG ) { echo "SD::L86 Parent ID: " . $parent_id . BR;
		$shared_id = StructuredDataNode::removeLastIndex( $first_node_id );
		echo "SD::L88 Shared ID: " . $shared_id . BR; }

		$parent_node->addChildNode( $first_node_id );
		
		$temp = $this->node_map;
		asort( $temp );
		$this->identifiers = array_keys( $temp );

		return $this;
	}
	
	public function getAssetNodeType( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		$node = $this->node_map[ $identifier ];
		
		if( $node->getType() != T::ASSET )
		{
			throw new NodeException( "This node is not an asset node." );
		}

		return $node->getAssetType();
	}
	
	public function getBlockId( $node_name )
	{
		return $this->node_map[ $node_name ]->getBlockId();
	}
	
	public function getBlockPath( $node_name )
	{
		return $this->node_map[ $node_name ]->getBlockPath();
	}
	
	public function getDataDefinition()
	{
		return $this->data_definition;
	}
	
	public function getDefinitionId()
	{
		return $this->definition_id;
	}
	
	public function getDefinitionPath()
	{
		return $this->definition_path;
	}
	
	public function getFileId( $node_name )
	{
		return $this->node_map[ $node_name ]->getFileId();
	}
	
	public function getFilePath( $node_name )
	{
		return $this->node_map[ $node_name ]->getFilePath();
	}
	
	public function getIdentifierNodeMap()
	{
		foreach( $this->children as $child )
		{
			$this->node_map = array_merge( 
			    $this->node_map, $child->getIdentifierNodeMap() );
		}
		
		return $this->node_map;
	}
	
	public function getIdentifiers()
	{
		return $this->identifiers;
	}
	
	public function getLinkableId( $node_name )
	{
		return $this->node_map[ $node_name ]->getLinkableId();
	}
	
	public function getLinkablePath( $node_name )
	{
		return $this->node_map[ $node_name ]->getLinkablePath();
	}
	
	public function getNode( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}

		return $this->node_map[ $identifier ];
	}
	
	public function getNodeType( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}

		return $this->node_map[ $identifier ]->getType();
	}
	
	public function getNumberOfChildren()
	{
		return count( $this->children );
	}
	
	public function getNumberOfSiblings( $node_name )
	{
		if( self::DEBUG ) { echo "SD::L207 " . "Node ID: " . $node_name . BR; }
		$par_id     = $this->node_map[ $node_name ]->getParentId();
		if( self::DEBUG ) { echo "SD::L209 " . "Parent ID: " . $par_id . BR; }

		if( !in_array( $node_name, $this->identifiers ) )
		{
			throw new NodeException( "The node $node_name does not exist" );
		}
		
		if( $par_id != '' )
		{
			$siblings = $this->node_map[ $par_id ]->getChildren();
		}
		else
		{
			$siblings = $this->children;
		}
		
		// remove ;0
		$field_id = StructuredDataNode::removeLastIndex( $node_name );
		if( self::DEBUG ) { echo "SD::L219 " . "Field ID: " . $field_id . BR; }
		
		$last_sibling_index = StructuredDataNode::getPositionOfLastNode( $siblings, $field_id );
		$last_id  = $siblings[ $last_sibling_index ]->getIdentifier();
		
		return StructuredDataNode::getLastIndex( $last_id ) + 1;
	}
	
	public function getPageId( $node_name )
	{
		return $this->node_map[ $node_name ]->getPageId();
	}
	
	public function getPagePath( $node_name )
	{
		return $this->node_map[ $node_name ]->getPagePath();
	}
	
	public function getSymlinkId( $node_name )
	{
		return $this->node_map[ $node_name ]->getSymlinkId();
	}
	
	public function getSymlinkPath( $node_name )
	{
		return $this->node_map[ $node_name ]->getSymlinkPath();
	}
	
	public function getStructuredDataNode( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ];
	}
	
	public function getText( $node_name )
	{
		return $this->node_map[ $node_name ]->getText();
	}
	
	public function getTextNodeType( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		$node = $this->node_map[ $identifier ];
		
		if( $node->getType() != T::TEXT )
		{
			throw new NodeException( "This node is not a text node." );
		}

		return $node->getTextType();
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function hasIdentifier( $identifier )
	{
		return $this->hasNode( $identifier );
	}
	
	public function hasNode( $identifier )
	{
		return in_array( $identifier, $this->identifiers );
	}
	
	public function isAssetNode( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ]->isAssetNode();
	}
	
	public function isGroupNode( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ]->isGroupNode();
	}
	
	public function isMultiple( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ]->isMultiple();
	}
	
	public function isRequired( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ]->isRequired();
	}

	public function isTextNode( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ]->isTextNode();
	}
	
	public function isWYSIWYG( $identifier )
	{
		if( !in_array( $identifier, $this->identifiers ) )
		{
			throw new NodeException( "The node $identifier does not exist" );
		}
		
		return $this->node_map[ $identifier ]->isWYSIWYG();
	}
	
	public function removeLastSibling( $first_node_id )
	{
		if( !$this->hasIdentifier( $first_node_id ) )
		{
			throw new NodeException( "The node $first_node_id does not exist." );
		}
		
		$first_node = $this->node_map[ $first_node_id ];
		$field_id   = StructuredDataNode::getFieldIdentifier( $first_node_id );
		
		// non-ambiguous path, no multiple ancestor
		if( strpos( $first_node_id, $field_id ) !== false )
		{
			return $this->removeLastNodeFromField( $field_id );
		}
		// with multiple ancestor
		$parent_id   = $first_node->getParentId();
		$parent_node = $this->node_map[ $parent_id ];
		
		if( self::DEBUG ) { echo "SD::L373 Parent ID: " . $parent_id . BR;
		$shared_id = StructuredDataNode::removeLastIndex( $first_node_id );
		echo "SD::L375 Shared ID: " . $shared_id . BR; }
		
		$shared_id = StructuredDataNode::removeLastIndex( $first_node_id );
		$parent_node->removeLastChildNode( $shared_id );
		$this->identifiers = array_keys( $this->node_map );

		return $this;
	}
	
	public function replaceByPattern( $pattern, $replace, $include=NULL )
	{
		$check = false;
		
		if( is_array( $include ) )
		{
			$check = true;
		}
		
		foreach( $this->identifiers as $identifier )
		{
			if( $check && !in_array( $identifier, $include ) )
			{
				continue; // skip this one
			}
			
			$cur_node = $this->node_map[ $identifier ];
		
			$current_text = $cur_node->getText();
		
			// including WYSIWYG
			if( $cur_node->getType() == T::TEXT &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_CHECKBOX &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_DROPDOWN &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_RADIO &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_SELECTOR
			)
			{
				$new_text = preg_replace( $pattern, $replace, $current_text );
				
				$this->setText(
					$identifier,
					$new_text
				);
			}
		}
		return $this;
	}
	
	public function replaceText( $search, $replace, $include=NULL )
	{
		$check = false;
		
		if( is_array( $include ) )
		{
			$check = true;
		}
		
		foreach( $this->identifiers as $identifier )
		{
			if( $check && !in_array( $identifier, $include ) )
			{
				continue; // skip this one
			}
			
			$cur_node = $this->node_map[ $identifier ];
		
			$current_text = $cur_node->getText();
		
			// including WYSIWYG
			if( $cur_node->getType() == T::TEXT &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_CHECKBOX &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_DROPDOWN &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_RADIO &&
				$cur_node->getTextType() != StructuredDataNode::TEXT_TYPE_SELECTOR &&
			    strpos( $current_text, $search ) !== false )
			{
				$new_text = str_replace( $search, $replace, $current_text );
				
				$this->setText( 
					$identifier,
					$new_text
				);
			}
		}
		return $this;
	}
	
	public function searchText( $string )
	{
		$identifiers = array();
		
		foreach( $this->identifiers as $identifier )
		{
			$cur_node = $this->node_map[ $identifier ];
		
			if( $cur_node->getType() == T::TEXT && 
			    strpos( $cur_node->getText(), $string ) !== false )
			{
				$identifiers[] = $identifier;
			}
		}
		return $identifiers;
	}
	
	public function searchWYSIWYG( $pattern )
	{
		$identifiers = array();
		
		foreach( $this->identifiers as $identifier )
		{
			$cur_node = $this->node_map[ $identifier ];
		
			// only one instance is enough, hence preg_match, not preg_match_all
			if( $cur_node->getType() == T::TEXT &&
			    $cur_node->isWYSIWYG() &&
			    preg_match( $pattern, $cur_node->getText() ) == 1 )
			{
				$identifiers[] = $identifier;
			}
		}
		return $identifiers;
	}
	
	public function setBlock( $node_name, Block $block=NULL )
	{
		$this->node_map[ $node_name ]->setBlock( $block );
		return $this;
	}
	
	public function setDataDefinition( DataDefinition $dd )
	{
		$this->definition_id   = $dd->getId();
		$this->definition_path = $dd->getPath();
		return $this;
	}
	
	public function setFile( $node_name, File $file=NULL )
	{
		$this->node_map[ $node_name ]->setFile( $file );
		return $this;
	}
	
	public function setLinkable( $node_name, Linkable $linkable=NULL )
	{
		$this->node_map[ $node_name ]->setLinkable( $linkable );
		return $this;
	}
	
	public function setPage( $node_name, Page $page=NULL )
	{
		$this->node_map[ $node_name ]->setPage( $page );
		return $this;
	}
	
	public function setSymlink( $node_name, Symlink $symlink=NULL )
	{
		$this->node_map[ $node_name ]->setSymlink( $symlink );
		return $this;
	}
	
	public function setText( $node_name, $text )
	{
		if( !isset( $this->node_map[ $node_name ] ) )
		{
			throw new NodeException( "The node $node_name does not exists." );
		}
		$this->node_map[ $node_name ]->setText( $text );
		return $this;
	}
	
	public function swapData( $node_name1, $node_name2 )
	{
		if( !$this->hasIdentifier( $node_name1 ) )
		{
			throw new NodeException( "The node $node_name1 does not exists." );
		}
		
		if( !$this->hasIdentifier( $node_name2 ) )
		{
			throw new NodeException( "The node $node_name2 does not exists." );
		}
		
		// must be siblings
		if( StructuredDataNode::removeLastIndex( $node_name1 ) != 
		    StructuredDataNode::removeLastIndex( $node_name2 ) )
		{
			throw new NodeException( "The nodes $node_name1 and $node_name2 are not siblings." );
		}
		
		$par_id     = $this->node_map[ $node_name1 ]->getParentId();
		$node1_data = $this->node_map[ $node_name1 ]->toStdClass();
		$node2_data = $this->node_map[ $node_name2 ]->toStdClass();
		
		if( self::DEBUG ) { echo "SD::L568 " . "Parent ID: $par_id" . BR; echo "Node 1: $node_name1" . BR .
		"Node 2: $node_name2" . BR; }
		
		if( $par_id != '' )
			$siblings = $this->node_map[ $par_id ]->getChildren();
		else
			$siblings = $this->children;
			
		$sibling_count = count( $siblings );
		
		if( self::DEBUG ) { echo "SD::L578 " . "Sibling count: $sibling_count" . BR; }
		
		for( $i = 0; $i < $sibling_count; $i++ )
		{
			if( self::DEBUG ) { echo "SD::L582 ID: " . $siblings[ $i ]->getIdentifier() . BR; }
			
			if( $siblings[ $i ]->getIdentifier() == $node_name1 )
			{
				$node_pos1 = $i;
				if( self::DEBUG ) { echo "SD::L587 " . "Node 1 position: $node_pos1" . BR; }
			}
			if( $siblings[ $i ]->getIdentifier() == $node_name2 )
			{
				$node_pos2 = $i;
				if( self::DEBUG ) { echo "SD::L592 " . "Node 2 position: $node_pos2" . BR . BR; }
			}
		}
		
		$new_node1 = new structuredDataNode( 
		    $node2_data, $this->data_definition, $node_pos1, $par_id . structuredDataNode::DELIMITER );
		$new_node2 = new structuredDataNode( 
		    $node1_data, $this->data_definition, $node_pos2, $par_id . structuredDataNode::DELIMITER );
		
		// must assign new nodes to the original arrays, not $siblings
		if( $par_id != '' )
		{
			$this->node_map[ $par_id ]->swapChildren( $node_pos1, $new_node1, $node_pos2, $new_node2 );
		}
		else
		{
			$this->children[ $node_pos1 ] = $new_node1;
			$this->children[ $node_pos2 ] = $new_node2;
		}
		
		$this->node_map[ $node_name1 ] = $new_node1;
		$this->node_map[ $node_name2 ] = $new_node2;
		
		return $this;
	}
	
	public function toStdClass()
	{
		$obj = new stdClass();
		
		if( $this->type == DataDefinitionBlock::TYPE )
		{
			$obj->definitionId   = $this->definition_id;
			$obj->definitionPath = $this->definition_path;
		}
		
		$child_count = count( $this->children );
		
		if( self::DEBUG ) { echo "SD::L625 " . "child count: $child_count" . BR; }
		
		if( $child_count == 1 )
		{
			$obj->structuredDataNodes->structuredDataNode = $this->children[0]->toStdClass();
		}
		else
		{
			$obj->structuredDataNodes->structuredDataNode = array();
			
			for( $i = 0; $i < $child_count; $i++ )
			{
				$obj->structuredDataNodes->structuredDataNode[] = $this->children[$i]->toStdClass();
			}
		}
		return $obj;
	}
	
	private function appendNodeToField( $field_name )
	{
		if( !$this->data_definition->hasIdentifier( $field_name ) )
		{
			throw new NoSuchFieldException( 
			    "The field name $field_name does not exist." );
		}
		
		if( !$this->data_definition->isMultiple( $field_name ) )
		{
			throw new NodeException( "The field $field_name is not multiple" );
		}
		
		// get the parent id through the first node
		// alternative: use the field name to work out the parent id
		$first_node = $this->getNode( $field_name . DataDefinition::DELIMITER . '0' );
		$par_id     = $first_node->getParentId();
		
		if( $par_id == '' ) // top level
		{
			$child_count = count( $this->children );
			//$first_pos   = StructuredDataNode::getPositionOfFirstNode( $this->children, $field_name );
			$last_pos    = StructuredDataNode::getPositionOfLastNode( $this->children, $field_name );
			$cloned_node = $this->children[ $last_pos ]->cloneNode();

			if( $child_count > $last_pos + 1 ) // in the middle
			{
				$before = array_slice( $this->children, 0, $last_pos + 1 );
				$after  = array_slice( $this->children, $last_pos + 1 );
				$this->children = array_merge( $before, array( $cloned_node ), $after );
			}
			else // the last one
			{
				$this->children[] = $cloned_node;
			}
			
			// add new node to map
			$this->node_map = array_merge( 
				$this->node_map, array( $cloned_node->getIdentifier() => $cloned_node ) );
		}
		else
		{
			$this->getNode( $par_id )->addChildNode( $field_name );
		}
		// add new identifier to identifiers
		$temp = $this->node_map;
		asort( $temp );
		$this->identifiers = array_keys( $temp );

		return $this;
	}
	
	private function removeLastNodeFromField( $field_name )
	{
		if( !$this->data_definition->hasIdentifier( $field_name ) )
		{
			throw new NoSuchFieldException( 
			    "The field name $field_name does not exist." );
		}
		
		if( !$this->data_definition->isMultiple( $field_name ) )
		{
			throw new NodeException( "The field $field_name is not multiple" );
		}
	
		$first_node = $this->getNode( $field_name . DataDefinition::DELIMITER . '0' );
		$par_id     = $first_node->getParentId();

		if( $par_id == '' ) // top level
		{
			$last_pos  = StructuredDataNode::getPositionOfLastNode( $this->children, $field_name );
			$first_pos = StructuredDataNode::getPositionOfFirstNode( $this->children, $field_name );
			$last_id   = $this->children[ $last_pos ]->getIdentifier();
			
			if( $first_pos == $last_pos ) // the only node
			{
				throw new NodeException( "Cannot remove the only node in the field" );
			}
			
			$child_count = count( $this->children );
			
			if( $child_count > $last_pos )
			{
				$before = array_slice( $this->children, 0, $last_pos );
				$after = array_slice( $this->children, $last_pos + 1 );
				$this->children = array_merge( $before, $after );
			}
		}
		else
		{
			$this->getNode( $par_id )->removeLastChildNode( $field_name );
		}
		
		unset( $this->node_map[ $last_id ] );
		$this->identifiers = array_keys( $this->node_map );

		return $this;
	}	

	private $definition_id;
	private $definition_path;
	private $children;
	private $identifiers;
	private $data_definition;
	private $node_map;
	private $type; // block or page
}
?>