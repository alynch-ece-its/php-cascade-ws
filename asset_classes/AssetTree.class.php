<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class AssetTree
{
	const DEBUG = false;

	public function __construct( Container $container )
	{
		if( $container == NULL )
		{
			throw new NullAssetException( M::NULL_CONTAINER );
		}
		$this->root         = $container;
		$root_children      = $container->getChildren();
		$this->has_children = count( $root_children ) > 0;
		
		if( $this->has_children )
		{
			$this->children = array();
			
			foreach( $root_children as $root_child )
			{
				if( $root_child->getType() == $container->getType() )
				{
					$class_name = T::$type_class_name_map[ $container->getType() ];
				
					$this->children[] = new AssetTree( 
						$class_name::getAsset( $this->root->getService(),
							$container->getType(),
							$root_child->getId() )
					);
				}
				else
				{
					$this->children[] = $root_child;
				}
			}
		}
	}
	
	public function hasChildren()
	{
		return $this->has_children;
	}
	
	public function toListString()
	{
		$list_string = S_UL . S_LI;
		
		$list_string .= $this->root->getType() . " " .
			$this->root->getPath() . " " .
			$this->root->getId();
			
		if( $this->has_children )
		{
			if( get_class( $this->children[ 0 ] ) != get_class() )
				$list_string .= S_UL;
			
			foreach( $this->children as $child )
			{
				if( get_class( $child ) == 'Child' )
				{
					$list_string .= $child->toLiString();
				}
				else
				{
					$list_string .= $child->toListString();
				}
			}
			
			if( get_class( $this->children[ 0 ] ) != get_class() )
				$list_string .= E_UL;
		}
		
		$list_string .= E_LI . E_UL;
		
		return $list_string;
	}
	
	public function toXml( $indent="" )
	{
		$xml_string = $indent . "<" . $this->root->getType() . " path=\"" .
			$this->root->getPath() . "\" id=\"" .
			$this->root->getId() . "\"";
			
		$child_indent = $indent . "  ";
			
		if( $this->has_children )
		{
			$xml_string .= ">\n";
			
			foreach( $this->children as $child )
			{
				$xml_string .= $child->toXml( $child_indent );
			}
			$xml_string .= $indent . "</" . $this->root->getType() . ">\n";
		}
		else
		{
			$xml_string .= "/>\n";
		}
		
		return $xml_string;
	}
	
	public function traverse( $function_array, $params=NULL, &$results=NULL )
	{
		$service = $this->root->getService();
		
		if( $params != NULL && isset( $params[ F::SKIP_ROOT_CONTAINER ] ) && 
		    $params[ F::SKIP_ROOT_CONTAINER ] == true )
		{
			// reset flag for child containers
			$params[ F::SKIP_ROOT_CONTAINER ] = false;
		}
		else // process root container as well
		{
			$this->applyFunctionsToChild( 
				$service, $this->root->toChild(), $function_array, $params, $results );
		}
		
		// process children
		if( $this->has_children )
		{
			foreach( $this->children as $child )
			{
				// child is an asset tree
				if( get_class( $child ) != 'Child' )
				{
					// recursive traversal
					$child->traverse( $function_array, $params, $results );
				}
				else
				{
					$this->applyFunctionsToChild( 
				    	$service, $child, $function_array, $params, $results );
				}
			}
		}
		return $this;
	}
	
	private function applyFunctionsToChild( 
	    AssetOperationHandlerService $service, Child $child, 
	    $function_array, $params=NULL, &$results=NULL )
	{
		$type = $child->getType();
		
		if( isset( $function_array[ $type ] ) )
		{
			$functions  = $function_array[ $type ];
			$func_count = count( $functions );
			
			for( $i = 0; $i < $func_count; $i++ )
			{
				if( !function_exists( $functions[ $i ] ) )
				{
					throw new NoSuchFunctionException( "The function " . $functions[ $i ] .
						" does not exist." );
				}
			}
			
			for( $i = 0; $i < $func_count; $i++ )
			{
				$func_name = $functions[ $i ];
				$func_name( $service, $child, $params, $results );
			}
		}
	}
	
	private $root;
	private $has_children;
	private $children;
}
?>