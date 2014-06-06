<?php
/**  DataDefinitionBlock Recipes
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/data-definition-block-recipes.php
 * Basic Steps
 * When working with a data definition block, follow these steps:
 * Create a DataBlock object.
 * Use DataBlock::getIdentifiers() to retrieve all fully qualified identifiers.
 * When adding or removing nodes to or from muiltiple fields, always work with the fully qualified identifier of the first instance
 * Add or remove nodes to or from multiple fields.
 * When working with all nodes in a multiple field, use DataBlock::getNumberOfSiblings to get the number of instances and use a for loop to iterate any operation
 * When working with old data, swap nodes by using BataBlock::swapData.
 * Use DataBlock::setText to insert data into nodes
 * When necessary, use DataBlock::copyDataTo( $block2 ) to overwrite everything in $block2, including the data definition
 * When done, call DataBlock::edit (if there are data changes)
 * There is no need to call DataBlock::edit when there are only structural changes (like adding or removing nodes, swapping nodes, etc.)
 * Note that these steps are also relevant for pages. For more information, see Key Performance Indicators (http://www.upstate.edu/cascade-admin/projects/web-services/kpi/index.php).
 */

/** Adding a Node To a Multiple Field */
// the fully qualified identifier of the first node
$node_name = 'test-multiple-text2;0';
$block->appendSibling( $node_name );

/** Copying Data To Another Block */
$block1->copyDataTo( $block2 );

/** Getting a Data Block Object */
$block = $cascade->getAsset(
DataBlock::TYPE, '_cascade/blocks/data/test', 'cascade-admin' );

/** Getting the Number of Siblings In a Multiple Field */
$num = $block->getNumberOfSiblings( $first_name );

/** Looping Through a Multiple Field */
$block = $cascade->getAsset(
DataBlock::TYPE, '669de3b98b7f08560061bea77f816d9c' );
$first_group_id = "data;county;0";
$shared_id      = "data;county;";
$num_of_groups  = $block->getNumberOfSiblings( $first_group_id );

for( $i = 0; $i < $num_of_groups; $i++ )
{
echo $block->getText( $shared_id . $i . ";county-name" ) . BR;
}

/** Making Sure That There Are N Instances In a Multiple Field */
$block = $cascade->getAsset(
DataBlock::TYPE, '42d967628b7f08560061bea73b6c1c59' );
$instances_wanted  = 2;
$first_instance_id = "multiple;multiple-group;0";
$block->createNInstancesForMultipleField(
$instances_wanted, $first_instance_id );

/** Outputting All Fully Qualified Identifiers */
echo S_PRE;
var_dump( $block->getIdentifiers() );
echo E_PRE;

/** Removing a Node From a Multiple Field */
// the fully qualified identifier of the first node
$node_name = 'test-multiple-text2;0';
$block->removeLastSibling( $node_name );

/** Replacing the Data Definition */
// retrieve the old block
$old_block = Asset::getAsset(
$service, DataBlock::TYPE,
'db0760568b7f085600adcd81ddb2cbbc' );
// retrieve an empty new block attached with the new data definition
$new_block = Asset::getAsset(
$service, DataBlock::TYPE,
'db0d72958b7f085600adcd811611a271' );
// store the old data
$data      = $old_block->getText( 'test-single-text' );
// get the new structured data with the new data definition info
$structured_data = $new_block->getStructuredData();
// restore old data in corresponding field
$structured_data->setText( "test-group;0;test-text", $data );
// commit changes
$old_block->setStructuredData( $structured_data );

/** Setting Text */
$numemps_block->setText(
"trend-line;0;trend-line-start-value", $numemps_start_value )->
setText( "trend-line;0;trend-line-end-value", $numemps_end_value )->
edit();

/** Swapping Data of Two Nodes In a Multiple Field */
// 1 becomes 2, 2 becomes 1
$block->swapData( "single;1", "single;2" )->
// 3 becomes 2, 2 (originally 1) becomes 3
swapData( "single;2", "single;3" );