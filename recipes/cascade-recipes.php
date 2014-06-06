<?php
/** Cascade Recipes
 * The $cascade object has been initialized and made available in the environment. There is no need to create it.
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/cascade-recipes.php
 */


/** Checking In/Out an Asset */
//$cascade->checkOut(
    //$cascade->getAsset( Page::TYPE, 'f240a9b18b7f085600a467d45f06acd6' ) );
$cascade->checkIn(
    $cascade->getAsset( Page::TYPE, 'f240a9b18b7f085600a467d45f06acd6' ),
    'Comment' );

/** Clearing Permissions */
$cascade->clearPermissions(
    Folder::TYPE, 'ff736a7a8b7f085600adcd8137563987',
    NULL, // $site_name
    true  // $applied_to_children
);

/** Copying a Site */
$seed = $cascade->getSite( '_seed' );
$new_site = $cascade->copySite( $seed, 'test3' );

/** Creating Asset Objects */
// using id string
$p = $cascade->getAsset( Page::TYPE,
    '7c3fcd958b7ffe3b01bcfced60737f5b' );
// using path and site name
$p = $cascade->getAsset( Page::TYPE,
    'index', 'cascade-admin' );
// other id''s
$cascade->getAsset( User::TYPE, 'tuw' );

/** Deleting All Message */
$cascade->deleteAllMessages();

/** Denying Access to All */
$cascade->denyAllAccess(
    Folder::TYPE, 'ff736a7a8b7f085600adcd8137563987',
    NULL, // $site_name
    true  // $applied_to_children
);

/** Denying Access to a Group/User */
$cascade->denyAccess(
    Folder::TYPE, 'ff736a7a8b7f085600adcd8137563987',
    NULL, // $site_name
    true, // $applied_to_children
    $cascade->getAsset( User::TYPE, 'tuw' ) );

/** Getting an Asset Tree */
$tree = $cascade->getSite( '22q' )->getAssetTree();

/** Getting Audits */
$audits =
    $cascade->getAudits(
        $cascade->getAsset( User::TYPE, 'chanw' ), // audited asset
        T::LOGOUT, // type, optional
        new DateTime( "2014-04-01 00:00:00" ), // start date, optional
        new DateTime( "2014-05-04 00:00:00" )  // end date, optional
    );

foreach( $audits as $audit )
{
    $audit->display();
}

/** Getting a Message */
$cascade->getMessage( '1f02b0908b7f08560134f6e80b61d0ea' )->
    display();

/** Getting Messages */
$messages = $cascade->getAllMessages();

/** Getting a Role By Name */
$name = 'Administrator';
if( $cascade->hasRoleName( $name ) )
    $admin = $cascade->getRoleAssetByName( $name );

/** Granting Access to a Group/User */
$cascade->grantAccess(
    Folder::TYPE, 'ff736a7a8b7f085600adcd8137563987',
    NULL, // $site_name
    true, // $applied_to_children
    $cascade->getAsset( User::TYPE, 'chanw' ), T::WRITE );

/** Listing Sites */
$sites = $cascade->getSites(); // Identifier objects

foreach( $sites as $site )
{
    echo $site->getPathPath() . BR;
}

/** Searching */
// search using all
echo S_PRE;
var_dump( $cascade->searchForAll(
    'index',
    'Cascade',
    'Cascade',
    S::SEARCHPAGES
) );
echo E_PRE;

// search using any and name, at most 250 results
echo S_PRE;
var_dump( $cascade->searchForAssetName(
    'index',
    S::SEARCHPAGES
) );
echo E_PRE;

// search using any and content, at most 250 results
echo S_PRE;
var_dump( $cascade->searchForAssetContent(
    'Cascade',
    S::SEARCHPAGES
) );
echo E_PRE;

// search using any and metadata, at most 250 results
echo S_PRE;
var_dump( $cascade->searchForAssetMetadata(
    'Cascade',
    S::SEARCHPAGES
) );
echo E_PRE;

// search using any and name and wild-card, at most 250 results
echo S_PRE;
var_dump( $cascade->searchForAssetName(
    '*',
    S::SEARCHGROUPS
) );
echo E_PRE;

/** Setting Access Rights */
$cascade->setAccessRights(
    $cascade->getAccessRights(
        Page::TYPE, '08e726778b7f08560139425ca408f28b' )->
        grantUserReadAccess(
            $cascade->getAsset( User::TYPE, 'tuw' )
        )
);

/** Setting All Level */
$cascade->setAllLevel(
    Folder::TYPE, 'ff736a7a8b7f085600adcd8137563987',
    NULL,    // $site_name
    T::NONE, // none level
    true     // $applied_to_children
);