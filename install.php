<?php

	$data = array(

		/* der Titel des Modules */
		'title' => 'Download Module',


		/* Modulname, mit diesem wert findet er die Installations Datei */ 
		'module' => 'download',


		/* Version, kann bei einem neuen Module beibehalten werden 100, ergibt version 1.0.0 */
		'version' => '100'
	);

	require('include/includes/class/install.php');

	$install = new Install();
	$install->setName('download')
	        ->setVersion(100);

	$install->module();
	$install->update();
	$install->list_updates();
	$install->log();

?>