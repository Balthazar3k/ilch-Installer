<?php
class Install {

    protected $module;
    protected $version;
    protected $description;
    
    
    protected $module_file;
    protected $update = array('num' => 0);
    protected $updates_available = false;
    protected $messages = array(true => array(), false => array() );
    
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
    
    public function version($version = false){
        // Diese Function ist bisher ein Test, wird später mit einer Datenbank verbunden!
        if( $version ) {
            echo "Update to Version " . $version ."<br />";
        } else {
            return 12;
        }
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
            echo "<ul id=\"install false\"><li>" . $msg . "</li></ul>";
        }
        
        if (count($this->messages[true]) != 0) {
            $msg = implode("</li>\n<li>", $this->messages[true]);
            echo "<ul id=\"install true\"><li>" . $msg . "</li></ul>";
        }

    }

    public function list_updates() {
        
        if (count($this->update['message']) != 0) {
            $msg = implode("</li>\n<li>", $this->update['message']);
            echo "<ul id=\"install true\"><li>" . $msg . "</li></ul>";
        }
    }

    public static function parse_version($version) {
        return implode('.', str_split($version));
    }
}
?>