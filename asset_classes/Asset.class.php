<?php 
abstract class Asset
{
	const DEBUG = false;
	const DUMP  = false;
	
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        if( $service == NULL )
            throw new NullServiceException( M::NULL_SERVICE );
            
        if( $identifier == NULL )
            throw new NullIdentifierException( M::NULL_IDENTIFIER );
        
        if( self::DEBUG && self::DUMP )
        {
        	echo "A::L17 " . S_PRE; var_dump( $identifier ); echo E_PRE;
        }
        
		// get the property
		$property = $service->retrieve( 
			$identifier, T::$type_property_name_map[ $identifier->type ] );
		    
		if( $property == NULL )
		{
			throw new NullAssetException(
			    "The property " . 
			    T::$type_property_name_map[ $identifier->type ] . 
			    " cannot be retrieved. " . $service->getMessage() );
		}
            
        // store information
        $this->service       = $service;
        $this->identifier    = $identifier;
        $this->type          = $identifier->type;
        $this->property_name = T::$type_property_name_map[ $this->type ];
        $this->property      = $property;
        $this->id            = $property->id;
        $this->name          = $property->name;
        $this->path          = $property->path;
        $this->site_id       = $property->siteId;
        $this->site_name     = $property->siteName;
    }
    
    public function display()
	{
		echo S_H2 . "A::display" . E_H2 .
		     L::ID .            $this->id .            BR .
		     L::NAME .          $this->name .          BR .
			 L::PATH .          $this->path .          BR .
			 L::SITE_ID .       $this->site_id .       BR .
			 L::SITE_NAME .     $this->site_name .     BR .
			 L::PROPERTY_NAME . $this->property_name . BR .
			 L::TYPE .          $this->type .          BR .
			 HR;
		return $this;
	}

	public function dump( $formatted=false )
	{
		if( $formatted ) echo S_H2 . L::READ_DUMP . E_H2 . S_PRE;
		var_dump( $this->property );
		if( $formatted ) echo E_PRE . HR;
		
		return $this;
	}
	
	public function edit()
	{
		return $this->reloadProperty();
	}
	
    public function getId()
    {
    	return $this->id;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function getName()
    {
    	return $this->name;
    }
    
    public function getPath()
    {
    	return $this->path;
    }
    
    public function getProperty()
    {
    	return $this->property;
    }
    
    public function getPropertyName()
    {
    	if( self::DEBUG ) { echo "A::L95 From Asset::getPropertyName " . 
    	    $this->property_name . BR; }
    	return $this->property_name;
    }
    
    public function getService()
    {
        return $this->service;
    }
    
    public function getSiteId()
    {
    	return $this->site_id;
    }
  
    public function getSiteName()
    {
    	return $this->site_name;
    }
    
    public function getSubscribers()
    {
    	$this->service->listSubscribers( $this->identifier );
    		
    	if( $this->service->isSuccessful() )
    	{
			if( self::DEBUG )
			{
				echo "A::L123 Successfully listing subscribers" . BR;
			}
    		$results = array();
    		
    		// there are subscribers
    		if( $this->service->getReply()->listSubscribersReturn->subscribers->assetIdentifier != 
    			NULL )
    		{
    			$subscribers = 
    				$this->service->getReply()->listSubscribersReturn->subscribers->assetIdentifier;
    			
    			if( !is_array( $subscribers ) )
    				$subscribers = array( $subscribers );
    				
    			foreach( $subscribers as $subscriber )
    			{
    				$identifier = new Identifier( $subscriber );
    				$results[] = $identifier;
    			}
    		}
    		return $results;
    	}
    	else
    	{
    		echo $this->service->getMessage();
    	}
    	return NULL;
    }
    
    public function getType()
    {
    	return $this->type;
    }
    
    public function publishSubscribers( Destination $destination=NULL )
	{
		$subscriber_ids = $this->getSubscribers();
		
		if( $destination != NULL )
		{
			$destination_std           = new stdClass();
			$destination_std->id       = $destination->getId();
			$destination_std->type     = $destination->getType();
		}
		
		if( $subscriber_ids != NULL )
		{
			foreach( $subscriber_ids as $subscriber_id )
			{
				if( self::DEBUG ) { echo "A::L177 Publishing " . $subscriber_id->getId() . BR; }
				$this->getService()->publish( $subscriber_id->toStdClass(), $destination_std );
			}
		}
		return $this;
	}
    
    public function reloadProperty()
	{
		$this->property = 
			$this->service->retrieve( $this->identifier, $this->property_name );
		return $this;
	}
	
	public static function getAsset( $service, $type, $id_path, $site_name=NULL )
	{
		if( !in_array( $type, T::getTypeArray() ) )
			throw new NoSuchTypeException( "The type $type does not exist." );
			
    	$class_name = T::$type_class_name_map[ $type ]; // get class name
    	return new $class_name(                         // call constructor
    	    $service, 
    	    $service->createId( $type, $id_path, $site_name ) );
	}
	
	private $service;       // the AssetOperationHandlerService object
    private $identifier;    // the identifier (stdClass object) of the property
    private $type;          // the type string
    private $property_name; // the property name
    private $property;      // the property stdClass object
    private $id;            // the id string
    private $name;          // the name string
    private $path;          // the path string
    private $site_id;       // the site id string
    private $site_name;     // the site name string
}
?>