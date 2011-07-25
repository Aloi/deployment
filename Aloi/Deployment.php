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
 * The Aloi_Deployment component bootstraps the ability to invoke
 * different tasks from the command line, such as configuring your
 * application using the Deployment Configuration file.
 *
 * The deployment configuration file allows you to configure default
 * environments and overwrite those configuration with target specific
 * deployments, such as a local, staging or live environment.
 *
 * It provides the bases of a bridge from the CLI into Aloi_Deployment_Tasks
 * allowing you to create automation and command line tools to use in your
 * development.
 *
 * @author Cameron Manderson <cameronmanderson@gmail.com> (Aloi Contributor)
 */
class Aloi_Deployment {
	protected static $instance;
	
	protected $configurationXML;
	protected $path;
	protected $buildPath;
	
	/**
	 * Perform the required action
	 * Enter description here ...
	 * @param $arguments
	 */
	public function perform($path, Aloi_Deployment_Arguments $arguments) {
		// Configure the core
		$this->setPath($path);
		$configurationXMLFile = $arguments->getArgument('conf');
		if(empty($configurationXMLFile)) {
			$configurationXMLFile = 'deployment' . DIRECTORY_SEPARATOR . 'configuration.xml';
		}
		$this->setConfigurationFile($configurationXMLFile);
		
		// Check the action
		$action = $arguments->getArgument(0);
		if(empty($action)) throw new Exception('Please specify the action to perform argument[0]');
		
		// Switch the action
		switch($action) {
			case 'task':
				// Ensure we have a second task
				$taskName = $arguments->getArgument(1);
				if(empty($taskName)) throw new Exception('Please specify the task name');
				if(!class_exists($taskName) && !Aloi::autoload($taskName)) {
					throw new Exception('Unknown task: ' . $taskName);
				}
				
				// Perform the task
				$task = new $taskName;
				$task->perform($arguments);
				break;
			default:
				throw new Exception('Unknown action to perform: ' . $action);
				break;
		}
	}
	
	/**
	 * Return with the path to Aloi
	 * @return string
	 */
	public function getPath() {
		if(!$this->path) {
			$this->path = realpath(dirname(__FILE__));
		}
		return $this->path;
	}
	
	/**
	 * Set the path to Aloi
	 * @param $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}
	
	/**
	 * Sets the environment for 'building' out transient files
	 * @param $path Path to the build folder
	 */
	public function setBuildPath($path) {
		$this->buildPath = $path;
	}
	
	/**
	 * Returns with the build path configured in the application
	 */
	public function getBuildPath() {
		return $this->buildPath;
	}
	
	/**
	 * Sets the location of the configuration file to be accessed
	 * by the Aloi_Deployment environment.
	 * @param $configurationFile The path to the deployment configuration file
	 */
	public function setConfigurationFile($configurationFile) {
		if(!is_readable($configurationFile)) {
			throw new Exception('Unable to read the configuration file: ' . $configurationFile);
		}
		$this->configurationXML = new SimpleXMLElement(file_get_contents($configurationFile));
	}
	
	/**
	 * Returns with a SimpleXMLElement that represents the configuration
	 * of the application.
	 * @return SimpleXMLElement configuration object
	 */
	public function getConfigurationXML() {
		return $this->configurationXML;
	}
	
	/**
	 * Singleton for accessing the Aloi_Deployment register
	 */
	public static function getInstance() {
		if(is_null(self::$instance)) {
			self::$instance = new Aloi_Deployment();
		}
		return self::$instance;
	}
}