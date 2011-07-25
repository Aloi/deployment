<?php
/* Copyright 2010 aloi-project
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA
 */


/**
 *
 * @author Cameron Manderson <cameronmanderson@gmail.com> (Aloi Contributor)
 *
 */
class Aloi_Deployment_Task_Doctrine implements Aloi_Deployment_Task {
	protected $configurationTask;
	
	public function __construct() {
		// Compose this object with a configuration element
		$this->configurationTask = new Aloi_Deployment_Task_Doctrine_Configure();
	}
	
	public function perform(Aloi_Deployment_Arguments $arguments) {
		// Obtain the target
		$target = $arguments->getArgument('target');
		
		// Include the Doctrine library
		require_once('Doctrine.php');
		spl_autoload_register(array('Doctrine', 'autoload'));
		$manager = Doctrine_Manager::getInstance();
		
		// Look at the configuration parameters to identfy the runtime environment
		$managerConfiguration = $this->configurationTask->retrieveManagerConfiguration($target, $arguments);
		foreach($managerConfiguration['default'] as $property => $value) {
			if(!defined('Doctrine_Core::' . $property)) {
				throw new Exception('Doctrine_Core::' . $property . ' is not defined');
			}
			if(strtolower($value) == 'true' || strtolower($value) == 'false') {
				$value = (strtolower($value) == 'true');
			} else if(!defined('Doctrine_Core::' . $value)) {
				$this->log->error('Settings defined a constant that does not exist: Doctrine_Core::' . $value);
				throw new Exception('Doctrine_Core::' . $value . ' is not defined');
			} else {
				$value = constant('Doctrine_Core::' . $value);
			}
			$manager->setAttribute(constant('Doctrine_Core::' . $property), $value);
		}
		// Obtain the connection section
		$connectionDetails = $this->configurationTask->retrieveConnection($target, $arguments);
		$conn = Doctrine_Manager::connection($connectionDetails['default']['dsn'], 'Default connection');
		
		// Obtain the configuration from the current deployment configuration
		$config = $this->configurationTask->retrieveEnvironmentConfiguration($target, $arguments);
		
		// CLI
		$cli = new Doctrine_Cli($config);
		// TODO: Setup a utility in arguments
		$cli->run($arguments->getNumberedArguments(2));
	}
}