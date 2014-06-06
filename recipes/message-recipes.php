<?php
/** Message Recipes
 * Introduction
 * When a folder is published, some pages in the folder may fail to publish, due to one reason or other. One good reason is the Velocity-Oracle bug External Icon. The following shows such a message with 6 errors:
 *
 * (Message with errors): http://www.upstate.edu/cascade-admin/images/projects/publish-jobs-with-errors.jpg
 *
 * When some pages fail to publish, the errors are logged in the message sent by the system. We can use the corresponding Message object to republish all these failed pages by calling Message::republishFailedJobs.
 *
 * Class API
 * Cascade
 * Message
 *
 * Reference: http://www.upstate.edu/cascade-admin/projects/web-services/oop/recipes/message-recipes.php
 */

/**  Republishing All Failed Jobs */
$cascade->getMessage( '1f02b0908b7f08560134f6e80b61d0ea' )->
republishFailedJobs( $cascade );