<?php
/**  AccessRightsInformation Recipes
 * Class API
 *  AssetRightsInformation
 *  Cascade
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/access-rights-information-recipes.php
 */

/**  Denying Group Access */
$group = $cascade->getAsset( Group::TYPE, 'hemonc' );
$ari   = $cascade->getAccessRights(
    Page::TYPE, '08e726778b7f08560139425ca408f28b' );
$ari->denyGroupAccess( $group );
$cascade->setAccessRights( $ari );

/**  Denying User Access */
$user = $cascade->getAsset( User::TYPE, 'smithj' );
$ari  = $cascade->getAccessRights(
    Page::TYPE, '08e726778b7f08560139425ca408f28b' );
$ari->denyUserAccess( $user );
$cascade->setAccessRights( $ari );

/**  Granting Group Access */
$group = $cascade->getAsset( Group::TYPE, 'hemonc' );
$ari   = $cascade->getAccessRights(
    Page::TYPE, '08e726778b7f08560139425ca408f28b' );
$ari->addGroupReadAccess( $group );     // read access
// $ari->addGroupWriteAccess( $group ); // write access
$cascade->setAccessRights( $ari );

/**  Granting User Access */
$user = $cascade->getAsset( Group::TYPE, 'smithj' );
$ari  = $cascade->getAccessRights(
    Page::TYPE, '08e726778b7f08560139425ca408f28b' );
$ari->addUserReadAccess( $user );     // read access
// $ari->addUserWriteAccess( $user ); // write access
$cascade->setAccessRights( $ari );

/**  Setting All Access */
$ari  = $cascade->getAccessRights(
    Page::TYPE, '08e726778b7f08560139425ca408f28b' );
$ari->setAllLevel( T::NONE ); // or T::READ, T::WRITE
$cascade->setAccessRights( $ari );