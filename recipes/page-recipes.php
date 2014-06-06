<?php
/**  Page Recipes
 * Because both DataDefinitionBlock and Page can be associated with data definitions, they share a lot of common features related to data definitions. See DataDefinitionBlock Recipes for more.
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/page-recipes.php
 */

/** Getting a Page Object */
$page = $cascade->getAsset(
    Page::TYPE, '08e726778b7f08560139425ca408f28b' );
// or
$page = $cascade->getAsset(
    Page::TYPE, 'test3', 'cascade-admin' );

/** Getting the Value(s) From a Dynamic Field */
//values = an array
    $cascade->getAsset(
        Page::TYPE, '9d75723b8b7f085601ecbac8939f4f9f'
    )->getMetadata()->getDynamicFieldValues( 'exclude-from-left' );

/** Publishing a Page */
$page->publish();

/** Setting the Content Type */
$page->setContentType(
    $cascade->getAsset(
        ContentType::TYPE, 'a55e8c598b7f0856002a5e116c7ddaa3' ) );

/** Setting the Value(s) of a Dynamic Field */
$page = $cascade->getAsset( Page::TYPE, '9d75723b8b7f085601ecbac8939f4f9f' );
$page->getMetadata()->
    // uncheck the checkbox
    // setDynamicFieldValue( 'exclude-from-left', array( NULL ) );
    // check the checkbox
    setDynamicFieldValue( 'exclude-from-left', array( 'Yes' ) );
$page->edit();