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
class Aloi_Deployment_Task_Configure implements Aloi_Deployment_Task {
	private $configurationXML;

	public function perform(Aloi_Deployment_Arguments $arguments) {
		// Look through the configuration, and perform
	}

	public function retrieveConfiguration($target, $key, $arguments, $ignoreDefaults = false) {
		$xml = Aloi_Deployment::getInstance()->getConfigurationXML();
		if(empty($xml)) throw new Exception('Failed to locate the XML configuration');

		// Configure the app
		$configuration = array();

		// TODO: Consider reading in more than one configuration if named
		$xpath = "//configuration[@id = '" . $target . "']/" . $key;
		$configurationMatches = $xml->xpath($xpath);
		if(empty($configurationMatches)) {
			// We have no specific settings at a deployment
			$xpath = '//configuration-defaults/' . $key;
			$configurationMatches = $xml->xpath($xpath);
			// We have speicfic settings
			foreach($configurationMatches as $configurationMatch) {
				// Is it named?
				$id = $configurationMatch->xpath('@id');
				if(!empty($id)) {
					$id = (string)current($id);
				} else $id = 'default';

				// Configuration Setup
				$configuration[$id] = array();
				
				// Process the target specific
				self::processConfigurationEntry($configurationMatch, $configuration[$id], $arguments);
			}
		} else {
			// We have speicfic settings
			foreach($configurationMatches as $configurationMatch) {
				// Is it named?
				$id = $configurationMatch->xpath('@id');
				if(!empty($id)) {
					$id = (string)current($id);
				} else $id = 'default';

				// Configuration Setup
				$configuration[$id] = array();

				// Set the default
				if(!$ignoreDefaults) {
					$xpath = '//configuration-defaults/' . $key;
					if(!empty($id)) {
						// Add in a selector to find any default un-named settings or specific id ones
						$xpath .= '[not(@id) or @id = "' . $id . '"]';
					}
					$defaultConfigurationMatches = $xml->xpath($xpath);
					if(!empty($defaultConfigurationMatches)) {
						foreach($defaultConfigurationMatches as $defaultConfigurationMatch) {
							self::processConfigurationEntry($defaultConfigurationMatch, $configuration[$id], $arguments);
						}
					}
				}

				// Process the target specific
				self::processConfigurationEntry($configurationMatch, $configuration[$id], $arguments);
			}
		}

		return $configuration;
	}

	protected function processConfigurationEntry(SimpleXMLElement $xml, &$configuration, $arguments) {
		// We have some default configuration matches
		foreach($xml->attributes() as $key => $value) {
			$key = (string)$key;
			$value = (string)$value;
			$configuration[$key] = $this->substituteArgumentValue($value, $arguments);
		}
		
		// Obtain from 'set-property' calls
		$setProperties = $xml->xpath('set-property');
		foreach($setProperties as $setProperty) {
			$key = (string)current($setProperty->xpath('@key'));
			$value = (string)current($setProperty->xpath('@value'));
			$configuration[$key] = $this->substituteArgumentValue($value, $arguments);
		}
	}
	
	protected function substituteArgumentValue($value, $arguments) {
		if(preg_match_all('/\$\{([A-z\-]+)\}/', $value, $matches)) {
			if(!empty($matches[1])) {
				foreach($matches[1] as $match) {
					// We want to substitute a value
					$argument = $arguments->getArgument($match);
					if(!empty($argument)) {
						$value = preg_replace('/\$\{' . $match . '\}/', $argument, $value);
					}
				}
			}
		}
		return $value;
	}
}