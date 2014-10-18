<?php
class Install {

    protected $module;
    protected $version;
    protected $description;
    
    
    protected $module_file;
    protected $update = array('num' => 0);
    protected $updates_available = false;
    protected $messages = array(true => array(), false => array() );
    protected $methodes = array();
    
    protected $folders = array('path' => array(), 'status' => array() );

    public function set_name($name){
        $this->module = (string) ucfirst($name);
        (@include('include/includes/module/'.$this->module.'.php')) 
            OR die('
                <h1>Fehler: Medium nicht vorhanden!</h1>

                <p>
                    Das Medium zur Installation kann nicht gefunden werden, 
                    unter "include/includes/module/'.$this->module.'.php"
                </p>

                <p>
                    Das Medium brauch den gleichen Name wie das Module.
                    Im Medium wird ein Object ben&ouml;tigt mit dem Modulename, der den ersten buchtsaben in groß geschrieben sein muss, der rest klein.
                </p>

                <p>
                    Das Object braucht eine Methode "install", damit das Script zum ersten mal Installeriert werden kann.
                    Es k&oouml;nnen Updates &uuml;ber Methoden, wie "update_11" f&uuml;r angegeben werden. In dem Fall ist das dann Update 1.1.
                    Das Script erkennt autmatisch alle Updates und ist somit in der lage, diese selbst zu Installieren.
                </p>
            ');

        return $this;
    }  
    public function get_name(){
        return $this->module;
    }
    public function get_update_num(){
        return $this->update['num'];
    }

    public function set_version($version){
        $this->version = (integer) $version;
        return $this;
    }   
    public function get_version(){
        $this->updates_available();
        return @max($this->update['version']);
    }

    public function set_description($description) {
        $this->description = (string) $description;
        return $this;
    }    
    public function get_description() {
        return $this->description;
    }
    
    public function set_folders(array $chmod) {

        foreach( $chmod as $key => $path ){
                      
            $this->folders['path'][] = $path;

            if( is_writeable( $path ) ){
                $this->folders['status'][] = true;
            } else {
                $this->folders['status'][] = false;
            }
        }
        
        return $this;
    }    
    public function get_folders() {
        return $this->folders;
    }
    public function get_folder_status() {
        return !in_array( false, $this->folders['status'] );
    }
   
    public function is_methode($is) {
        return in_array($is, $this->methodes);
    }   
    public function is_installed() {
        return (integer) db_result(db_query("SELECT v6 FROM prefix_allg WHERE k='".$this->module."-module' LIMIT 1;"),0);
    }

    public function can_install() {
        return ($this->is_methode('install') && !$this->is_installed() );
    }
    public function can_update() {
        return ( $this->updates_available && $this->is_installed() );
    }  
    public function can_deinstall() {
        return ($this->is_methode('deinstall') && $this->is_installed());
    }
    
    public function version($version = false){
        if( $version ) {
            $current_version = $this->version();
            if( in_null($current_version) ){
                db_query("INSERT INTO prefix_allg (k,v1) VALUES('".$this->module."-module','".$version."');");
            } else {
                db_query("UPDATE prefix_allg SET v1='".$version."' WHERE k='".$this->module."-module';");
            }
        } else {
            return db_result(db_query("SELECT v1 FROM prefix_allg WHERE k='".$this->module."-module' LIMIT 1;"),0);
        }
    }   
    public function installed($status = true){
        db_query("UPDATE prefix_allg SET v6='".$status."' WHERE k='".$this->module."-module';");
    }

    public function module(){
        $this->version();

        $module = $this->module;
        $init = new $module();
        $status = $init->install($this);
        $this->installed($status);
    }

    public function update(){
        
        $this->updates_available();

        foreach( $this->update['methode_name'] as $key => $update) {
            $status = $this->module_file->$update($this);

            if( is_bool($status) && !$status ){
                $this->message(false, 'Fehler bei Update <b>'.$this->update['version'][$key].'</b>, Installation wurde unterbrochen!');
                break;
            } else {
                $this->version($this->update['version'][$key]);
                $this->message(true, 'Installation von Update <b>'.$this->update['version'][$key].'</b> war erfolgreich!');
            }

            $status = NULL;
        }
    }
    
    public function deinstall(){
        $module = $this->module;
        $init = new $module();
        $init->deinstall($this);
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
            /* Filtert andere methoden aus */
            if( preg_match('/(install|deinstall|update_[0-9]*)/', $update, $res ) ) {
                $this->methodes[] = $res[1];
            }

            if( preg_match('/update_([0-9]*)/', $update, $res ) ) {
                if( $this->version() < $res[1] ) {
                    $this->updates_available = (bool) true;
                    $this->update['num'] ++;
                    $this->update['version'][] = self::parse_version($res[1]);
                    $this->update['message'][] = 'Update '. self::parse_version($res[1]) .' ist f&uuml;r Modul: <b>'. ucfirst($this->module) .'</b> vorhanden!';
                    $this->update['methode_name'][] = $update;
                }
            }
        }

        return $this->updates_available;
    }

    public function message($status, $message){
        $this->messages[$status][] = $message;
    }

    public function log(){

        if (count($this->messages[false]) != 0) {
            $msg = implode("</li>\n<li>", $this->messages[false]);
            echo "<div class=\"alert alert-danger\"><ul><li>" . $msg . "</li></ul></div>";
        }
        
        if (count($this->messages[true]) != 0) {
            $msg = implode("</li>\n<li>", $this->messages[true]);
            echo "<div class=\"alert alert-success\"><ul><li>" . $msg . "</li></ul></div>";
        }

    }

    public function list_updates() {
        
        if (count($this->update['message']) != 0) {
            $msg = implode("</li>\n<li>", $this->update['message']);
            echo "<div class=\"alert alert-info\"><b>Update's Information</b><br /><ol><li>" . $msg . "</li></ol></div>";
        }
    }

    public static function parse_version($version) {
        return implode('.', str_split($version));
    }
}
?>