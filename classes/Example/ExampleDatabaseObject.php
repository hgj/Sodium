<?php

//
// Sodium 2.0.10-alpha
//
// This file is part of the Sodium PHP framework, released under the
// Creative Commons Attribution-NonCommercial-ShareAlike licence.
//
// The framework is created and maintaned by Gergely J. Horváth.
// More information should be available at http://hgj.hu
//
// Copyright 2013 by Gergely J. Horváth.
//

namespace Example;

class ExampleDatabaseObject extends \Sodium\DatabaseObject {
	
	// Use the 'CarsDatabase' connection for this model
	protected static $connection = 'CarsDatabase';
	
	// Override the name of the object (table in the database)
	protected static $name = 'Cars';
	
	// These keys define one row in the table
	// Other keys can be defined if more than one identifies a row
	protected static $keys = array('ID');
	
	protected static $associations = array(
		'Owner' => array(
			'type' => '11',
			'foreignKey' => 'Cars.ownerID',
		),
		'Wheel' => array(
			'type' => '1N',
			'foreignKey' => 'Wheels.carID',
		),
		'Group' => array(
			'type' => 'NM',
			'joinTable' => 'CarsGroups',
			'selfKey' => 'carID',
			'foreignKey' => 'groupID',
		)
	);
	
	protected static $attributes = array(
		'ID' => array(
			'autoIncrement' => true,
			'canBeNull' => false, // Default for a key
			'type' => 'INT(10)', // This should work with the used driver
		),
		'color' => array(
			'canBeNull' => true,
			'type' => array( // This will work with all drivers
				'type' => 'string',
				'length' => '255',
			),
		),
	);
	
}