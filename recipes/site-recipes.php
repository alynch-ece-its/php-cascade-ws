<?php
/** Site Recipes
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/site-recipes.php
 */

/** Copying a Site */
$seed     = $cascade->getSite( '_seed' ); // current site
$new_site = $cascade->copySite( $seed, 'test3' );

/** Creating a Site Object */
// with an id string
$site = Asset::getAsset( $service, Site::TYPE,
'777537238b7f0856015997e4a7e94366' );

// with a name
$site = Asset::getAsset( $service, Site::TYPE, '_seed' );

// or using Cascade
$site = $cascade->getSite( '_seed' );
// or
$site = $cascade->getAsset( Site::TYPE, '_seed' );
// or
$site = $cascade->getAsset(
Site::TYPE, '777537238b7f0856015997e4a7e94366' );

/** Getting an Asset Tree */
$tree = $site->getAssetTree();

/** Listing All Sites */
$sites = $cascade->getSites(); // Child objects
$site_array = array();

foreach( $sites as $site )
{
// just to print them
echo $site->getPathPath() . BR;
// create Site objects
// warning: you might have a lot of sites!!!
$site_array[] = $site->getAsset( $service );
}

/** Publishing a Site */
$site->publish();

/** Publishing All Sites */
// first way, using Cascade and Child
$sites = $cascade->getSites(); // Child objects

foreach( $sites as $site )
{
$service->publish( $site->toStdClass() );
}
// second way, using AssetTree
// this way has a tighter control on what asset type should be published
$sites = $cascade->getSites();

foreach( $sites as $site )
{
$asset_tree = $site->getAsset()->getAssetTree();

$function_array = array(
File::TYPE => array( F::PUBLISH ),
Page::TYPE => array( F::PUBLISH )
);

$asset_tree->traverse( $function_array );
}