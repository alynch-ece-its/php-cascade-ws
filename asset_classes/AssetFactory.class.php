<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/23/2014 Fixed a bug in setBaseAsset.
  * 5/22/2014 Added setAllowSubfolderPlacement, 
  *   setFolderPlacementPosition, setOverwrite, and setBaseAsset.
  * 5/21/2014 Fixed some bugs related to foreach.
 */
class AssetFactory extends ContainedAsset
{
	const DEBUG = false;
	const TYPE  = T::ASSETFACTORY;
	
	// mode: folder-controlled, factory-controlled, none
	public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
	{
		parent::__construct( $service, $identifier );
		
		if( $this->getProperty()->plugins->plugin != NULL )
		{
			$this->processPlugins();
		}
	}
	
	public function addGroup( Group $g )
	{
		if( $g == NULL )
		{
			throw new NullAssetException( M::NULL_GROUP );
		}
	
		$group_name   = $g->getName();
		$group_string = $this->getProperty()->applicableGroups;
		$group_array  = explode( ';', $group_string );
		
		if( !in_array( $group_name, $group_array ) )
		{
			$group_array[] = $group_name;
		}
		
		$group_string = implode( ';', $group_array );
		$this->getProperty()->applicableGroups = $group_string;
		return $this;
	}
	
	public function copy( stdClass $par_id, $new_name )
	{
		$service         = $this->getService();
		$self_identifier = $service->createId( AssetFactory::TYPE, $this->getId() );
		
		$service->copy( $self_identifier, $par_id, $new_name, false );
		
		if( $service->isSuccessful() )
		{
			// get the parent
			$parent_id = $par_id->id;
			$parent    = $service->retrieve(
				$service->createId( T::ASSETFACTORYCONTAIER, $parent_id ), 
				P::ASSETFACTORYCONTAIER);
				
			// look for the new child
			foreach( $parent->children->child as $child )
			{
				$child_path = $child->path->path;
				$child_path_array = explode( '/', $child_path );
				
				if( in_array( $new_name, $child_path_array ) )
				{
					$child_found = $child;
					break;
				}
			}
			// get the digital id
			$child_id = $child_found->id;
			// return new object
			return new AssetFactory( $service, $service->createId( AssetFactory::TYPE, $child_id ) );
		}
		else
		{
			throw new Exception( M::COPY_ASSET_FAILURE );
		}
	}
	
	public function edit()
	{
		$asset = new stdClass();
		$this->getProperty()->plugins->plugin = array();
		
		if( count( $this->plugins ) > 0 )
		{
			foreach( $this->plugins as $plugin )
			{
				$this->getProperty()->plugins->plugin[] = $plugin->toStdClass();
			}
		}

		$asset->{ $p = $this->getPropertyName() } = $this->getProperty();
		
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
	
	public function getAllowSubfolderPlacement()
	{
		return $this->getProperty()->allowSubfolderPlacement;
	}

	public function getApplicableGroups()
	{
		return $this->getProperty()->applicableGroups;
	}
	
	public function getAssetType()
	{
		return $this->getProperty()->assetType;
	}
	
	public function getBaseAssetId()
	{
		return $this->getProperty()->baseAssetId;
	}
	
	public function getBaseAssetPath()
	{
		return $this->getProperty()->baseAssetPath;
	}
	
	public function getBaseAssetRecycled()
	{
		return $this->getProperty()->baseAssetRecycled;
	}
	
	public function getFolderPlacementPosition()
	{
		return $this->getProperty()->folderPlacementPosition;
	}
	
	public function getOverwrite()
	{
		return $this->getProperty()->overwrite;
	}
	
	public function getPlacementFolderId()
	{
		return $this->getProperty()->placementFolderId;
	}
	
	public function getPlacementFolderPath()
	{
		return $this->getProperty()->placementFolderPath;
	}
	
	public function getPlacementFolderRecycled()
	{
		return $this->getProperty()->placementFolderRecycled;
	}

	public function getPlugin( $name )
	{
		if( $this->hasPlugin( $name ) )
		{
			foreach( $this->plugins as $plugin )
			{
				if( $plugin->getName() == $name )
				{
					return $plugin;
				}
			}
		}
		throw new NoSuchPluginException( "The plugin $name does not exist." );	
	}
	
	public function getPluginNames()
	{
		$names = array();
		
		if( count( $this->plugins ) > 0 )
		{
			foreach( $this->plugins as $plugin )
			{
				$names[] = $plugin->getName();
			}
		}
		return $names;
	}
	
	public function getWorkflowDefinitionId()
	{
		return $this->getProperty()->workflowDefinitionId;
	}
	
	public function getWorkflowDefinitionPath()
	{
		return $this->getProperty()->workflowDefinitionPath;
	}
	
	public function getWorkflowMode()
	{
		return $this->getProperty()->workflowMode;
	}
	
	public function hasPlugin( $name )
	{
		if( count( $this->plugins ) > 0 )
		{
			foreach( $this->plugins as $plugin )
			{
				if( $plugin->getName() == $name )
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public function isApplicableToGroup( Group $g )
	{
		if( $g == NULL )
		{
			throw new NullAssetException( M::NULL_GROUP );
		}

		$group_name = $g->getName();
		$group_string = $this->getProperty()->applicableGroups;
		$group_array  = explode( ';', $group_string );
		return in_array( $group_name, $group_array );
	}
	
	public function removeGroup( Group $g )
	{
		if( $g == NULL )
		{
			throw new NullAssetException( M::NULL_GROUP );
		}
		
		$group_name   = $g->getName();
		$group_string = $this->getProperty()->applicableGroups;
		$group_array  = explode( ';', $group_string );
			
		if( in_array( $group_name, $group_array ) )
		{
			$temp = array();
			
			foreach( $group_array as $group )
			{
				if( $group != $group_name )
				{
					$temp[] = $group;
				}
			}
			$group_array = $temp;
		}
		
		$group_string = implode( ';', $group_array );
		$this->getProperty()->applicableGroups = $group_string;
		
		return $this;
	}
	
	public function setAllowSubfolderPlacement( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );
			
		$this->getProperty()->allowSubfolderPlacement = $bool;
		
		return $this;
	}
	
	public function setBaseAsset( Asset $a=NULL )
	{
		if( $a != NULL )
		{
			$type = $a->getType();
			
			if( StringUtility::startsWith( strtolower( $type ), 'block' ) )
			{
				$type = 'block';
			}
			else if( StringUtility::startsWith( strtolower( $type ), 'format' ) )
			{
				$type = 'format';
			}
			
			$this->getProperty()->assetType     = $type;
			$this->getProperty()->baseAssetId   = $a->getId();
			$this->getProperty()->baseAssetPath = $a->getPath();
		}
		else
		{
			$this->getProperty()->assetType     = File::TYPE; // dummpy type
			$this->getProperty()->baseAssetId   = NULL;
			$this->getProperty()->baseAssetPath = NULL;
		}
		return $this;
	}
	
	public function setFolderPlacementPosition( $value )
	{
		if( is_nan( $value ) )
		{
			throw new UnacceptableValueException( "$value is not a number" );
		}
		
		$this->getProperty()->folderPlacementPosition = intval( $value );
		
		return $this;
	}
	
	public function setOverwrite( $bool )
	{
		if( !BooleanValues::isBoolean( $bool ) )
			throw new UnacceptableValueException( "The value $bool must be a boolean." );
			
		$this->getProperty()->overwrite = $bool;
		
		return $this;
	}
	
	public function setPlacementFolder( Folder $folder )
	{
		if( $folder == NULL )
			throw new NullAssetException( M::NULL_FOLDER );
			
		$this->getProperty()->placementFolderId   = $folder->getId();
		$this->getProperty()->placementFolderPath = $folder->getPath();
		//$this->edit()->reloadProperty();
		
		return $this;
	}
	
	public function setPluginParameterValue( $plugin_name, $param_name, $param_value )
	{
		$plugin = $this->getPlugin( $plugin_name );
		$parameter = $plugin->getParameter( $param_name );
		
		if( isset( $parameter ) )
			$parameter->setValue( $param_value );
		
		return $this;
	}
	
	private function processPlugins()
	{
		$this->plugins = array();

		$plugins = $this->getProperty()->plugins->plugin;
		    
		if( !is_array( $plugins ) )
		{
			$plugins = array( $plugins );
		}
		
		$count = count( $plugins );
		
		for( $i = 0; $i < $count; $i++ )
		{
			$this->plugins[] = 
				new Plugin( $plugins[ $i ] );
		}
	}
	
	private $plugins;
}
?>
