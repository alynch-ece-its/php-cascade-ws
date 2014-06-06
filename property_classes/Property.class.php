<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class Property
{
    public abstract function __construct( stdClass $obj );
    public abstract function toStdClass();
}
?>
