<?php
/** AssetTree Recipes
 * http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/asset-tree-recipes.php
 */

/** Counting Assets In a Container */
$id = '3557998b7f0000010020a239cf5de95c';

$f  = Folder::getAsset( $service, Folder::TYPE, $id );
$at = new AssetTree( $f );

$function_array = array(
DataDefinitionBlock::TYPE => array( F::COUNT ),
FeedBlock::TYPE => array( F::COUNT ),
IndexBlock::TYPE => array( F::COUNT ),
TextBlock::TYPE => array( F::COUNT ),
File::TYPE => array( F::COUNT ),
Folder::TYPE => array( F::COUNT ),
Page::TYPE => array( F::COUNT ),
ScriptFormat::TYPE => array( F::COUNT ),
Template::TYPE => array( F::COUNT ),
XsltFormat::TYPE => array( F::COUNT ),
);

$results = array();

$at->traverse(
$function_array,
array( F::SKIP_ROOT_CONTAINER => true ), // skip Base Folder
$results );

$keys = array_keys( $results[ F::COUNT ] );

foreach( $keys as $key )
{
echo $key . ": There are " .
$results[ F::COUNT ][ $key ] . " of them." . BR;
}

/* === global function used === */
function assetTreeCount(
    AssetOperationHandlerService $service,
    Child $child, $params=NULL, &$results=NULL )
{
    $type = $child->getType();

    if( !isset( $results[ F::COUNT ][ $type ] ) )
        $results[ F::COUNT ][ $type ] = 1;
    else
        $results[ F::COUNT ][ $type ] =
            $results[ F::COUNT ][ $type ] + 1;
}

/** Creating an Asset Factory-Group Assignment Report */
// get the sites
$sites   = $cascade->getSites();
$results = array();

// traverse every root asset factory container
foreach( $sites as $site )
{
    $site->getAsset( $service )->
        getRootAssetFactoryContainerAssetTree()->traverse(
            array( AssetFactory::TYPE =>
                array( F::REPORT_FACTORY_GROUP ) ),
            array( F::REPORT_FACTORY_GROUP =>
                array( 'site-name' => $site->getPathPath() ) ),
            $results
        );
}

// process the report
$report = $results[ F::REPORT_FACTORY_GROUP ];

foreach( $report as $site => $factory_group_array )
{
    echo "<h2>$site</h2>\n<ul>\n";

    foreach( $factory_group_array as $factory => $groups )
    {
        echo "<li>$factory: $groups</li>\n";
    }
    echo "</ul>\n";
}

/* === global function used === */
function assetTreeReportAssetFactoryGroupAssignment(
    AssetOperationHandlerService $service,
    Child $child, $params=NULL, &$results=NULL )
{
    if( $child->getType() != AssetFactory::TYPE )
    {
        throw new WrongAssetTypeException(
            "The asset tree does not contain asset factories." );
    }

    if( isset( $params[ F::REPORT_FACTORY_GROUP ][ 'site-name' ] )
        &&
        trim( $params[ F::REPORT_FACTORY_GROUP ][ 'site-name' ] ) != ""
        && is_array( $results ) )
    {
        $site_name =
            trim( $params[ F::REPORT_FACTORY_GROUP ][ 'site-name' ] );

        if( !isset( $results[ F::REPORT_FACTORY_GROUP ][ $site_name ] ) )
        {
            $results[ F::REPORT_FACTORY_GROUP ][ $site_name ] = array();
        }

        $af     = $child->getAsset( $service );
        $groups = $af->getApplicableGroups();

        $results[ F::REPORT_FACTORY_GROUP ][ $site_name ]
        [ $af->getName() ] = $groups;
    }
}

/** Creating a Data Definition Flag Report */
$results = array();

// get all data definition blocks with a certain radio button selected
$cascade->getAsset( Folder::TYPE, '980d67ab8b7f0856015997e4b8d84c5d' )->
    getAssetTree()->traverse(
        array( DataBlock::TYPE =>
            array( F::REPORT_DATA_DEFINITION_FLAG ) ),
        array( F::REPORT_DATA_DEFINITION_FLAG => array(
            DataBlock::TYPE => array( 'display' => 'No' ) ) ),
        $results );

if( count( $results[ F::REPORT_DATA_DEFINITION_FLAG ]
    [ DataBlock::TYPE ] ) > 0 )
{
    foreach( $results[ F::REPORT_DATA_DEFINITION_FLAG ]
             [ DataBlock::TYPE ] as $child )
    {
        // get the block object
        // $block = $child->getAsset( $service );
        // do something with the block

        // just echo the ID
        echo $child->getId() . BR;
    }
}
else
{
    echo "There are none." . BR;
}

/* === global function used === */
function assetTreeReportDataDefinitionFlag(
    AssetOperationHandlerService $service,
    Child $child, $params=NULL, &$results=NULL )
{
    if( isset( $params[ F::REPORT_DATA_DEFINITION_FLAG ]
        [ $child->getType() ] ) &&
        is_array( $params[ F::REPORT_DATA_DEFINITION_FLAG ]
        [ $child->getType() ] ) )
    {
        // only one value per dynamic field
        $identifier_text_array =
            $params[ F::REPORT_DATA_DEFINITION_FLAG ]
            [ $child->getType() ];

        if( !isset( $results[ F::REPORT_DATA_DEFINITION_FLAG ]
        [ $child->getType() ] ) )
        {
            $results[ F::REPORT_DATA_DEFINITION_FLAG ]
            [ $child->getType() ] = array();
        }

        foreach( $identifier_text_array as $identifier => $text )
        {
            $asset = $child->getAsset( $service );

            if( $asset->hasStructuredData() &&
                $asset->hasIdentifier( $identifier ) &&
                $text == $asset->getText( $identifier ) )
            {
                $results[ F::REPORT_DATA_DEFINITION_FLAG ]
                [ $child->getType() ][] = $child;
            }
        }
    }
}

/** Creating a Metadata Flag Report */
$results = array();

// get all pages with a certain checkbox checked
$cascade->getAsset( Folder::TYPE, '699c77aa8b7f085600ebf23e6667f93b' )->
    getAssetTree()->traverse(
        array( Page::TYPE => array( F::REPORT_METADATA_FLAG ) ),
        array( F::REPORT_METADATA_FLAG => array(
            Page::TYPE => array( 'exclude-from-left' => 'Yes' ) ) ),
        $results );

if( count( $results[ F::REPORT_METADATA_FLAG ][ Page::TYPE ] ) > 0 )
{
    foreach(
        $results[ F::REPORT_METADATA_FLAG ][ Page::TYPE ] as $child )
    {
        // get the page object
        // $page = $child->getAsset( $service );
        // do something with the page

        // just echo the ID
        echo $child->getId() . BR;
    }
}
else
{
    echo "There are none." . BR;
}

/* === global function used === */
function assetTreeReportMetadataFlag(
    AssetOperationHandlerService $service,
    Child $child, $params=NULL, &$results=NULL )
{
    if( isset( $params[ F::REPORT_METADATA_FLAG ][ $child->getType() ] )
        &&
        is_array( $params[ F::REPORT_METADATA_FLAG ]
        [ $child->getType() ] ) )
    {
        // only one value per dynamic field
        $name_value_array = $params[ F::REPORT_METADATA_FLAG ]
        [ $child->getType() ];

        if( !isset( $results[ F::REPORT_METADATA_FLAG ]
        [ $child->getType() ] ) )
        {
            $results[ F::REPORT_METADATA_FLAG ][ $child->getType() ] =
                array();
        }

        foreach( $name_value_array as $field => $value )
        {
            $asset = $child->getAsset( $service );

            if( $asset->hasDynamicField( $field )
                &&
                in_array( $value, $asset->getMetadata()->
                    getDynamicFieldValues( $field ) ) )
            {
                $results[ F::REPORT_METADATA_FLAG ]
                [ $child->getType() ][] = $child;
            }
        }
    }
}

/** Creating an Orphan Report */
$results = array();

Asset::getAsset(
    $service, Folder::TYPE, '3557998b7f0000010020a239cf5de95c' )->
    getAssetTree()->
    traverse(
        array( File::TYPE => array( F::REPORT_ORPHANS ) ),
        NULL,
        $results );

echo S_PRE;
var_dump( $results );
echo E_PRE;

/* === global function used === */
function assetTreeReportOrphans(
    AssetOperationHandlerService $service,
    Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) )
    {
        $subscribers = $child->getAsset( $service )->getSubscribers();

        if( $subscribers == NULL )
        {
            $results[ F::REPORT_ORPHANS ][ $child->getType() ][] =
                $child->getPathPath();
        }
    }
}

/** Getting an Asset Tree */
// using $cascade
$tree = $cascade->getSite( '22q' )->getAssetTree();
$tree = $cascade->getSite( '22q' )->
    getRootContentTypeContainerAssetTree();
// using Container
$tree = Asset::getAsset(
    $service, MetadataSetContainer::TYPE,
    '647db3ab8b7f085600ae2282d55a5b6d' )->
    getAssetTree();
// using AssetTree
$tree = new AssetTree( Asset::getAsset(
    $service, MetadataSetContainer::TYPE,
    '647db3ab8b7f085600ae2282d55a5b6d' ) );

/** Listing All Assets In a Container */
echo Asset::getAsset(
    $service, Folder::TYPE,
    '3557998b7f0000010020a239cf5de95c')->getAssetTree()->
    toListString();

/** Listing All Assets In a Container */
$at = Asset::getAsset(
    $service, Folder::TYPE,
    '3557998b7f0000010020a239cf5de95c')->getAssetTree();
echo S_PRE . XMLUtility::replaceBrackets( $at->toXml() ) . E_PRE;