<?php
/**  AssetFactory Recipes
 * Class API
 * AssetFactory
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/asset-factory-recipes.php
 */

/**  Setting Base Asset */
$cascade->getAsset(
    AssetFactory::TYPE, "Upstate/New Folder", "_seed"
)->setBaseAsset(
        $cascade->getAsset(
            Folder::TYPE, "fd17fe218b7f085600a0fcdcff1a5037" )
    )->edit();

/**  Setting Placement Folder For an Asset Factory In Every Site */
try
{
    require_once('php-cascade-ws/auth_user.php');

    $sites = $cascade->getSites();

    foreach( $sites as $site )
    {
        try
        {
            $af = $cascade->getAsset(
                AssetFactory::TYPE,
                "Upstate/Upload 520X270 Image", // factory name
                $site->getPathPath()            // site name
            );
            $af->setPlacementFolder(
                $cascade->getAsset(
                    Folder::TYPE, 'images', $site->getPathPath() )
            )->
                setAllowSubfolderPlacement( true )->
                setOverwrite( true )->
                setFolderPlacementPosition( 0 )->
                edit();
        }
        catch( Exception $e ) // if the factory does not exist in a site
        {
            echo $site->getPathPath() .
                " failed to modify Upload 520X270 Image" . BR;
            echo S_PRE . $e . E_PRE;
            continue;
        }
    }
}
catch( Exception $e )
{
    echo S_PRE . $e . E_PRE;
}