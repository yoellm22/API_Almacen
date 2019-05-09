<?php
class Conexion {
    public static function getPDO(){
        $dbhost = "localhost";
        $dbuser = "root";
        $dbpass = "";
        $dbname = "inventariodb";
        $dns = "mysql:host={$dbhost};dbname={$dbname}";
        try{
          $pdo=new PDO($dns, $dbuser, $dbpass,array(PDO::ATTR_PERSISTENT=>true));
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $pdo->exec("set names utf8");
          return $pdo;
        }catch(PDOException $e) {
            echo $e->getMessage();
        }
    }
}
?>
