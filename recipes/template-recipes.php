<?php
/** Template Recipes
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/template-recipes.php
 */

/** Attaching a Block to a Region */
$template->setPageRegionBlock( 'DEFAULT', $block )->
edit();

/** Attaching a Format to a Region */
$template->setPageRegionFormat( 'DEFAULT', $format )->
edit();

/** ADetaching the Block From a Region */
$template->setPageRegionBlock( 'DEFAULT', NULL )->
edit();

/** ADetaching the Format From a Region */
$template->setPageRegionFormat( 'DEFAULT', NULL )->
edit();

/** AGetting the Block of a Region */
$block = $template->getPageRegionBlock( $region_name );

/** AGetting the Format of a Region */
$format = $template->getPageRegionFormat( $region_name );

/** AGetting the Template Format */
$format = $template->getFormat();

/** ASetting the Template Format */
$format_id = "d87bcef68b7f085600a0fcdcaf6a2ae6";
$template->setFormat( $cascade->getAsset(
XsltFormat::TYPE, $format_id ) )->edit();