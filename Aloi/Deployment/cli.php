<?php
// BOOTSTRAP
require 'lib/aloi/Aloi.php';
Aloi::registerAutoload(array('lib/doctrine', 'lib/aloi', 'lib', 'src'));

// Setup the arguments
$arguments = new Aloi_Deployment_Arguments($_SERVER['argv']);

// Set the default build path
$buildPath = $arguments->getArgument('buildPath');
if(empty($buildPath)) $buildPath = '../../build';

// Configure the deployment
$deployment = Aloi_Deployment::getInstance();
$deployment->setBuildPath(getcwd() . DIRECTORY_SEPARATOR . $buildPath);
$deployment->perform(getcwd(), $arguments);