<?php
define("main", true);

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@ini_set('display_errors', 'On');

include("include/includes/config.php");
include("include/includes/loader.php");
require('include/includes/class/install.php');

db_connect();

$install = new Install();
$install->set_name('firsttry')
        ->set_version(100)
        ->set_description()
        ->set_folders();
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
        <title><?= $install->get_name() ?>-Modul <?= $install->get_version() ?></title>
    </head>
    <body>
        <div class="col-lg-6 col-lg-offset-3 col-xs-12 col-md-12">
            <h1 class="text-center">Installation von: <?= $install->get_name() ?>-Modul <b><?= $install->get_version() ?></b></h1>
            <div class="text-center"><?= $install->get_description() ?></div>
            <hr/>
            <?php switch ($_GET['step']) {
                default: ?>
                    <section id="step-0">
                        <div class="panel panel-<?= ($install->get_folder_status() ? 'success' : 'danger'); ?>">
                            <div class="panel-heading">
                                <b>&Uuml;berpr&uuml;fe Ordner f&uuml;r das <?= $install->get_name() ?>-Modul, ob alle Schreibrechte vorhanden sind!</b>
                            </div>
                                <?php if (!$install->get_folder_status()) : ?>
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
                                    if (count($folders) > 0) :
                                        foreach ($folders['path'] as $id => $path) :
                                            ?>
                                            <tr>
                                                <td><?= $path ?></td>
                                                <td class="<?= ( $folders['status'][$id] ? 'success' : 'danger') ?> text-center"><b><?= ( $folders['status'][$id] ? 'ok' : 'fehler') ?></b></td>
                                            </tr>
                                        <?php
                                        endforeach;
                                    else :
                                        ?>
                                        <tr>
                                            <td colspan="2">Es sind keine Ordner zum &uuml;berpr&uuml;fen vorhanden!</td>
                                        </tr>
                                    <?php
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                            <div class="panel-footer text-right">
                                <a href="?step=install_methodes" class="btn btn-<?= ($install->get_folder_status() ? 'success' : 'danger disabled'); ?>">Weiter zur Installation...</a>
                            </div>
                        </div>
                    </section>
                    <?php break;
                    case 'install_methodes' : ?>
                    <section>
                        <p>
                        <h3>Welche Art der Installation m&ouml;chten Sie vornehmen?</h3>
                        Ihnen stehen folgende m&ouml;glichkeiten zur verf&uuml;gung!
                        </p>

                        <div class="alert alert-info" role="alert">
                            Mit dem Klicken der Buttons "Installieren", "Update" oder "Deinstallieren" erkl&auml;ren Sie sich damit einverstanden, dass der Module entwickler & der entwickler des Installations Script auf Ihren eigenen wunsch das <b><?=$install->get_name()?></b> Modul installiert.
                            Wir &uuml;bernnehmen keine Haftung an Sch&auml;den, die durch diese Script enstehen k&ouml;nnten. Wir empfehlen zu Ihrer eigen Sicherheit ein Backup zu erstellen, sowohl die Datein als auch die Datenbank.
                        </div>

                        <br />

                        <div class="col-lg-12 col-md-12 col-xs-12">

                            <?php if ($install->can_update()) : ?>
                                <div class="col-lg-12 col-md-12 col-xs-12">
                                    <div class="panel panel-success">
                                        <div class="panel-heading"><b>Es sind <?= $install->get_update_num() ?>x Update's vorhanden!</b></div>
                                        <div class="panel-body">
                                            Hier werden nur alle Update's installiert, die Neu sind, sofern welche vorhanden sind!
                                            <p>
                                                <?= $install->list_updates() ?>
                                            </p>
                                        </div>
                                        <div class="panel-footer text-center">
                                            <a class="btn btn-success" href="?step=update">Installiere alle <b><?= $install->get_update_num() ?></b> Update's</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($install->updates_available() && $install->can_install()) : ?>
                                <div class="col-lg-6 col-md-6 col-xs-12">
                                    <div class="panel panel-info">
                                        <div class="panel-heading"><b>Volle Installation</b></div>
                                        <div class="panel-body">
                                            Bei der Vollen installation handelt es sich um, der Hauptinstallation des $module und seiner gesamten Updates!
                                        </div>
                                        <div class="panel-footer text-center">
                                            <a class="btn btn-success" href="?step=install">Installieren</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($install->can_install()) : ?>
                                <div class="col-lg-6 col-md-6 col-xs-12">
                                    <div class="panel panel-info">
                                        <div class="panel-heading"><b>nur die erste Version installieren</b></div>
                                        <div class="panel-body">
                                            Es wird nur das Modul installiert, ohne irgendwelche Update's. Dadurch kann man Version 1.0 ausprobieren.
                                        </div>
                                        <div class="panel-footer text-center">
                                            <a class="btn btn-success" href="?step=mininstall">Installieren</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($install->can_deinstall()) : ?>
                                <div class="col-lg-6 col-md-6 col-xs-12">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading"><b>Deinstallieren</b></div>
                                        <div class="panel-body">
                                            Es wird das Modul deinstalliert & versucht alle Datein zu entfernen.
                                        </div>
                                        <div class="panel-footer text-center">
                                            <a class="btn btn-success" href="?step=deinstall">DeInstallieren</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                    <?php
                    break;
                case 'install' :
                    if ($install->is_installed())
                        break;

                    $install->module();
                    $install->update();
                    $install->log();
                    ?>
                    <div class="btn-group btn-group-justified">
                        <a class="btn btn-info" href="index.php">auf die Startseite</a>
                        <a class="btn btn-warning" href="?step=install_methodes">Installations &uuml;bersicht!</a>
                        <!--<a class="btn btn-success" href="admin.php?install">Module im Adminbereich</a>-->
                    </div>
                    <?php
                    break;
                case 'mininstall' :
                    if ($install->is_installed())
                        break;

                    $install->module();
                    $install->log();
                    ?>
                    <div class="btn-group btn-group-justified">
                        <a class="btn btn-info" href="index.php">auf die Startseite</a>
                        <a class="btn btn-warning" href="?step=install_methodes">Installations &uuml;bersicht!</a>
                    </div>
                    <?php
                    break;
                case 'update' :
                    if (!$install->is_installed())
                        break;

                    $install->update();
                    $install->log();
                    ?>
                    <div class="btn-group btn-group-justified">
                        <a class="btn btn-info" href="index.php">auf die Startseite</a>
                        <a class="btn btn-warning" href="?step=install_methodes">Installations &uuml;bersicht!</a>
                    </div>
                    <?php
                    break;
                case 'deinstall' :
                    if (!$install->is_installed())
                        break;

                    $install->deinstall();
                    $install->log();
                    ?>
                    <div class="btn-group btn-group-justified">
                        <a class="btn btn-info" href="index.php">auf die Startseite</a>
                        <a class="btn btn-warning" href="?step=install_methodes">Installations &uuml;bersicht!</a>
                    </div>
                <?php break; } ?>

            <div class="col-lg-12">
                <hr/>
                <small class="col-lg-6">
                    <?= $install->get_name() ?> Module &copy; by <?=$install->get_author()?> <br />
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

<?php
db_close();
?>