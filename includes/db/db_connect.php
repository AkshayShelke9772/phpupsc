<?php

class DB_CONNECT {

    protected $con;
    private $server = "mysql:host=localhost;dbname=upscmcqs";
//    private $server = "mysql:host=127.0.0.1:3307;dbname=upscmcqs";
    private $pass = "";
    private $user = "root";
    private $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,);

    function __construct() {
        $this->connect();
    }

    function __destruct() {
        
    }

    function connect() {
        try {
            $this->con = new PDO($this->server, $this->user, $this->pass, $this->options);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $this->con;
    }

    function close() {
        $this->con = null;
    }

}

?>