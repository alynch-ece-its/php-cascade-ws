<?php
/**  Asset Recipes
 * Class APIAssetOperationHandlerService
 * Asset
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/asset-recipes.php
 */

/** Copying an Asset */
$service->copy( // no returned value
    $page->getIdentifier(),
    $folder->getIdentifier(), // destination container
    'new-page', // new name
    true // or false, do workflow
);

// if the copy method is defined in a class
$new_file = $file->copy( // returns an Asset object
    $folder->getIdentifier(), // destination container
    'test2.css' // new name
);

/** Creating an Asset Object */
// using id string
$p = Asset::getAsset( $service, Page::TYPE,
    '7c3fcd958b7ffe3b01bcfced60737f5b' );

// using path and site name
$p = Asset::getAsset( $service, Page::TYPE,
    'index', 'cascade-admin' );

/** Deleting an Asset */
$service->delete( $p->getIdentifier() );

/** Displaying Some Basic Information */
$p->display();

/** Dumping a Property */
$p->dump( true );

/** Getting All Subscribers */
// array of Identifier object
$subscribers = $block->getSubscribers();

/** Moving an Asset */
$p->move(
    Asset::getAsset( $service, Folder::TYPE,
        '7c3fcd958b7ffe3b01bcfced60737f5b' )
); // no workflow
$p->move(
    Asset::getAsset( $service, Folder::TYPE,
        '7c3fcd958b7ffe3b01bcfced60737f5b' ),
    true
); // do workflow

/** Publishing All Subscribers */
$block->publishSubscribers();

/** Renaming an Asset */
$p->rename( 'test3' ); // no workflow
$p->rename( 'test3', true ); // do workflow