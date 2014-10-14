<?php
class Install {

    protected $module;
    protected $version;
    protected $module_file;
    protected $update = array();
    protected $updates_available = false;
    protected $messages = array(true => array(), false => array() );

    public function setName($name){
        $this->module = (string) $name;
        (@include('include/includes/module/'.$this->module.'.php')) 
            OR die('
                <h1>Fehler: Medium nicht vorhanden!</h1>

                <p>
                    Das Medium zur Installation kann nicht gefunden werden, 
                    unter "include/includes/module/'.$this->module.'.php"
                </p>

                <p>
                    Das Medium brauch den gleichen Name wie das Module, in klein Buchstaben geschrieben und als PHP Datei.
                    Im Medium wird ein Object ben&ouml;tigt mit dem Modulename, der ebenfalls mit kleinbuchstaben geschrieben sein muss.
                </p>

                <p>
                    Das Object braucht eine Methode "install", damit das Script zum ersten mal Installeriert werden kann.
                    Es k&oouml;nnen Updates &uuml;ber Methoden, wie "update_11" f&uuml;r angegeben werden. In dem Fall ist das dann Update 1.1.
                    Das Script erkennt autmatisch alle Updates und ist somit in der lage, diese selbst zu Installieren.
                </p>
            ');

        return $this;
    }

    public function setVersion($version){
        $this->version = (integer) $version;
        return $this;
    }

    public function module(){
        $this->version();

        $module = $this->module;
        $init = new $module();
        $init->install($this);
    }

    public function update(){
        
        $this->updates_available();

        foreach( $this->update['methode_name'] as $key => $update) {
            $status = $this->module_file->$update($this);

            if( is_bool($status) && !$status ){
                $this->message(false, 'Fehler bei Update <b>'.$this->update['version'][$key].'</b>, Installation wurde unterbrochen!');
                break;
            } else {
                $this->message(true, 'Installation von Update <b>'.$this->update['version'][$key].'</b> war erfolgreich!');
            }

            $status = NULL;
        }
    }

    public function updates_available(){

        /* Unterbrechen wenn Updates bereits eingelesen wurden */
        if( $this->updates_available )
            return $this->updates_available;

        /* Starte Module Klasse */
        $module = $this->module;
        $this->module_file = new $module();

        $class_methods = get_class_methods($module);
        foreach( $class_methods as $key => $update) {

            if(preg_match('/update_([0-9]{1,3})/', $update, $res ) ) {
                if( $this->version() < $res[1] ) {
                    $this->updates_available = (bool) true;
                    $this->update['version'][] = self::parse_version($res[1]);
                    $this->update['message'][] = 'Update '. self::parse_version($res[1]) .' ist f&uuml;r Modul: <b>'. ucfirst($this->module) .'</b> vorhanden!';
                    $this->update['methode_name'][] = $update;
                }
            }
        }

        return $this->updates_available;
    }

    public function version($version = false){
        // Diese Function ist bisher ein Test, wird später mit einer Datenbank verbunden!
        if( $version ) {
            echo "Update to Version " . $version ."<br />";
        } else {
            return 10;
        }
    }

    public function message($status, $message){
        $this->messages[$status][] = $message;
    }

    public function log(){

        if (count($this->messages[false]) != 0) {
            $msg = implode("</li>\n<li>", $this->messages[false]);
            echo "<ul id=\"install false\"><li>" . $msg . "</li></ul>";
        }
        
        if (count($this->messages[true]) != 0) {
            $msg = implode("</li>\n<li>", $this->messages[true]);
            echo "<ul id=\"install true\"><li>" . $msg . "</li></ul>";
        }

    }

    public function list_updates(){
        
        if (count($this->update['message']) != 0) {
            $msg = implode("</li>\n<li>", $this->update['message']);
            echo "<ul id=\"install true\"><li>" . $msg . "</li></ul>";
        }
    }

    public static function parse_version($version){
        return implode('.', str_split($version));
    }
}
?>