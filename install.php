<?php
define("main", true);

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@ini_set('display_errors', 'On');

//include("include/includes/config.php");
//include("include/includes/loader.php");
require('include/includes/class/install.php');

//db_connect();

$install = new Install();
$install->set_name('Download')
        ->set_version(100)
        ->set_description('Das Download Script erweitert das Standart Download Script von ilch!')
        ->set_folders(array(
            'include/images/download/',
            'include/images/downcats/'
        ));
?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
        <script type="text/javascript" src="http://getbootstrap.com/dist/js/bootstrap.js"></script>
        <link type="text/css" rel="stylesheet" href="http://getbootstrap.com/dist/css/bootstrap.css"/>
        <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
        <style>
            body {
                font-family: Ubuntu;
                margin: 50px 0;
            }
        </style>
        <title><?= $install->get_name() ?>-Modul <b><?= $install->get_version() ?></title>
    </head>
    <body>
        <div class="col-lg-6 col-lg-offset-3 col-xs-12 col-md-12">
            <h1 class="text-center">Installation von: <?= $install->get_name() ?>-Modul <b><?= $install->get_version() ?></b></h1>
            <div class="text-center"><?= $install->get_description() ?></div>
            <hr/>
            <?php switch($_GET['step']) { default: ?>
            <section id="step-0">
                <div class="panel panel-<?= ($install->get_folder_status() ? 'success' : 'danger'); ?>">
                    <div class="panel-heading">
                        <b>&Uuml;berpr&uuml;fe Ordner f&uuml;r das <?= $install->get_name() ?>-Modul, ob alle Schreibrechte vorhanden sind!</b>
                    </div>
                    <?php if( !$install->get_folder_status() ) : ?>
                    <div class="panel-body text-info">
                        <b>Bitte &auml;ndern Sie die Schreibrechte f&uuml;r die fehlerhaften Verzeichnisse, dann k&ouml;nnen sie die Installation fortsetzen!
                            Es kann unter umst&auml;nden vorkommen, dass die Verzeichnisse garnicht exestieren, legen Sie diese dann Bitte an.</b>
                    </div>
                    <?php endif; ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Verzeichnis</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $folders = $install->get_folders();
                                foreach ($folders['path'] as $id => $path) :
                            ?>
                                <tr>
                                    <td><?= $path ?></td>
                                    <td class="<?= ( $folders['status'][$id] ? 'success' : 'danger') ?> text-center"><b><?= ( $folders['status'][$id] ? 'ok' : 'fehler') ?></b></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="panel-footer text-right">
                        <a href="?step=install" class="btn btn-<?= ($install->get_folder_status() ? 'success' : 'danger disabled'); ?>">Weiter zur Installation...</a>
                    </div>
                </div>
            </section>
            <?php break; case 'install' : ?>
            <section>
                <p>
                    <h3>Welche Art der Installation m&ouml;chten Sie vornehmen?</h3>
                    Ihnen stehen folgende m&ouml;glichkeiten zur verf&uuml;gung!
                </p>


                <div class="alert alert-info" role="alert">
                    Mit dem Klicken der Buttons "Installieren" erkl&auml;ren Sie sich damit einverstanden, dass der Module entwickler & der entwickler des Installations Script auf Ihren eigenen wunsch das Module installiert.
                    Wir &uuml;bernnehmen keine Haftung an Sch&auml;den, die durch diese Script enstehen k&ouml;nnten. Wir empfehlen zu Ihrer eigen Sicherheit ein Backup zu erstellen, sowohl die Datein als auch die Datenbank.
                </div>

                
                <br />
                
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <?php if( $install->can_update() ) : ?>
                    <div class="col-lg-12 col-md-12 col-xs-12">
                        <div class="panel panel-success">
                            <div class="panel-heading"><b>Es sind <?=$install->get_update_num()?>x Update's vorhanden!</b></div>
                            <div class="panel-body">
                                Hier werden nur alle Update's installiert, die Neu sind, sofern welche vorhanden sind!
                                <p>
                                    <?=$install->list_updates()?>
                                </p>
                            </div>
                            <div class="panel-footer text-center">
                                <a class="btn btn-success" href="">Installiere alle <b><?=$install->get_update_num()?></b> Update's</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if( $install->updates_available() && $install->can_install() ) : ?>
                    <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><b>Volle Installation</b></div>
                            <div class="panel-body">
                                Bei der Vollen installation handelt es sich um, der Hauptinstallation des $module und seiner gesamten Updates!
                            </div>
                            <div class="panel-footer text-center">
                                <a class="btn btn-success" href="">Installieren</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if( $install->can_install() ) : ?>
                    <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><b>nur die erste Version installieren</b></div>
                            <div class="panel-body">
                                Es wird nur das Modul installiert, ohne irgendwelche Update's. Dadurch kann man Version 1.0 ausprobieren.
                            </div>
                            <div class="panel-footer text-center">
                                <a class="btn btn-success" href="">Installieren</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if( $install->can_deinstall() ) : ?>
                    <div class="col-lg-6 col-md-6 col-xs-12">
                        <div class="panel panel-warning">
                            <div class="panel-heading"><b>Deinstallieren</b></div>
                            <div class="panel-body">
                                Es wird das Modul deinstalliert & versucht alle Datein zu entfernen.
                            </div>
                            <div class="panel-footer text-center">
                                <a class="btn btn-success" href="">DeInstallieren</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php break; }?>
            <section id="step-2">
            </section>

            <div class="col-lg-12">
                <hr/>
                <small class="col-lg-6">
                    <?= $install->get_name() ?> Module &copy; $text <br />
                    Installations Script &copy; 2014 by Balthazar3k
                </small>

                <small class="col-lg-6 text-right">
                    Das Installations Modul gibt es bei, <br />
                    Balthazar3k zu Downloaden
                </small>
            </div>
        </div>
    </body>
</html>
<pre>
<?php
//db_colse();
print_r($install);
?>
</pre>