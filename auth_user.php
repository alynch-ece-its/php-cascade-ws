<?php 
require_once( 'ws_lib.php' );

$wsdl = "http://localhost:8080/ws/services/AssetOperationService?wsdl";
$auth           = new stdClass();
$auth->username = $_SERVER['PHP_AUTH_USER'];
$auth->password = $_SERVER['PHP_AUTH_PW'];

try
{
	// set up the service
	$service = new AssetOperationHandlerService( $wsdl, $auth );
	$cascade = new Cascade( $service );

	// create an asset for one-time use
	$asset = new stdClass();
}
catch( ServerException $e )
{
	echo S_PRE . $e . E_PRE;
	throw $e;
}
?>
