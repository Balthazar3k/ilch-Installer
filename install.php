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
	$install->set_name('Download')
	        ->set_version(100)
                 ->set_description('Das Download Script erweitert das Standart Download Script von ilch!')
                 ->set_folders( array(
                    'include/images/download/',
                    'include/images/downcats/'
                 ) );

	$install->module();
	$install->update();
	$install->list_updates();
	$install->log();
        

        ?><pre><?php
        print_r($install->get_version());
        ?></pre><?php