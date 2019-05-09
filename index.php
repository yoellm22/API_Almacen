<?php
/**
* Created by PhpStorm.
* User: yoel
* Date: 29/04/19
* Time: 21:47
*/

require "conexion/Conexion.php";
require 'vendor/autoload.php';


$conn = Conexion::getPDO();

/**Conexion a BBDD**/
function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="";
    $dbname="inventariodb";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
/**Fin conexion a BBDD**/

$app = new \Slim\App();


// Mostramos tabla informacion uso API
$app->get('/',function(){
    echo '<h1 style="text-align: center; background-color: #0000c0; color: #C0C0FF;">API ALMACEN IES AZARQUIEL</h1>';
    echo '<table border="1" style="width: 40%;margin: 0 auto; background-color: #C0C0FF; color:#0000c0;">';
    echo "<tr style='background-color: #0000c0; color: #C0C0FF;'><th>Method</th><th>Url</th><th>Description</th></tr>";
    echo "<tr><td>get </td><td>/pieza</td><td>Añadir pieza al almacen(Devuelve msg tanto para correcto como para incorrecto)</td></tr>";
    echo "</table>";
});

// Insertamos pieza
$app->post('/pieza', function (Request $request, Response $response, array $args) use ($conn){

    $codPropietario = $request->getParam('codpropietario');
    $codPieza = $request->getParam('codpieza');
    $codNif = $request->getParam('codnif');
    $codPropietarioPadre = $request->getParam('codpropietariopadre');
    $codPiezaPadre = $request->getParam('codpropietariopadre');
    $codModelo = $request->getParam('codmodelo');
    $identificador = $request->getParam('identificador');
    $prestable = $request->getParam('prestable');
    $contenedor = $request->getParam('contenedor');
    $altapieza = $request->getParam('altapieza');

    if (!isset($codPropietario) || !isset($codPieza) || !isset($codNif) || !isset($codPropietarioPadre) || !isset($codPiezaPadre) || !isset($codModelo) || !isset($identificador) || !isset($prestable) || !isset($contenedor) || !isset($altapieza)){
        $body = $request->getBody();
        $jsonobj = json_decode($body);
        if ($jsonobj != null) {
            $codPropietario = $jsonobj->{'codpropietario'};
            $codPieza = $jsonobj->{'codpieza'};
            $codNif = $jsonobj->{'codnif'};
            $codPropietarioPadre = $jsonobj->{'codpropietariopadre'};
            $codPiezaPadre = $jsonobj->{'codpropietariopadre'};
            $codModelo = $jsonobj->{'codmodelo'};
            $identificador = $jsonobj->{'identificador'};
            $prestable = $jsonobj->{'prestable'};
            $contenedor = $jsonobj->{'contenedor'};
            $altapieza = $jsonobj->{'altapieza'};
        }
    }

    $sql = "INSERT INTO pieza (CodPropietario, CodPieza,CodNIF,CodPropietarioPadre,CodPiezaPadre,CodModelo,Identificador,Prestable,Contenedor,AltaPieza) VALUES (:CodPropietario,:CodPieza,:CodNIF,:CodPropietarioPadre,:CodPiezaPadre,:CodModelo,:Identificador,:Prestable,:Contenedor,:AltaPieza)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("CodPropietario",$codPropietario,PDO::PARAM_STR);
        $stmt->bindParam("CodPieza", $codPieza,PDO::PARAM_STR);
        $stmt->bindParam("CodNIF", $codNif,PDO::PARAM_STR);
        $stmt->bindParam("CodPropietarioPadre", $codPropietarioPadre,PDO::PARAM_STR);
        $stmt->bindParam("CodPiezaPadre", $codPiezaPadre,PDO::PARAM_STR);
        $stmt->bindParam("CodModelo", $codModelo,PDO::PARAM_STR);
        $stmt->bindParam("Identificador", $identificador,PDO::PARAM_STR);
        $stmt->bindParam("Prestable", $prestable,PDO::PARAM_STR);
        $stmt->bindParam("Contenedor", $contenedor,PDO::PARAM_STR);
        $stmt->bindParam("AltaPieza", $altapieza,PDO::PARAM_STR);
        $conn->beginTransaction();
        $stmt->execute();
        $idlast = $conn->lastInsertId();
        $conn->commit();

    } catch(PDOException $e) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(["msg"=>"Violada Constraint BD..."]));
    }

  return $response->withStatus(200)
       ->withHeader('Content-Type', 'application/json')
       ->write(json_encode(["msg"=>["Pieza añadida correctamente"]]));
});

//Nos traemos piezas segun su propietario
$app->get('/{propietario/piezas}', function ($request, $response, $args) use ($conn){
    $codpropietario = $args['propietario'];

    $sql = "SELECT * FROM pieza WHERE CodPropietario = :cod";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam("cod",$codpropietario,PDO::PARAM_STR);
    $conn->beginTransaction();
    $stmt->execute();
    $salida = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    if ($salida != null) {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=UTF8')
            ->write(json_encode(["piezas" => $salida], JSON_UNESCAPED_UNICODE));
    } else {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=UTF8')
            ->write(json_encode(["piezas" => null], JSON_UNESCAPED_UNICODE));
    }

    });
//Nos traemos los propietarios
$app->get('/propietarios}', function ($request, $response, $args) use ($conn){

    $sql = "SELECT * FROM propietarios order by CodPropietario";

    $stmt = $conn->prepare($sql);
    $conn->beginTransaction();
    $stmt->execute();
    $salida = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    if ($salida != null) {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=UTF8')
            ->write(json_encode(["propietarios" => $salida], JSON_UNESCAPED_UNICODE));
    } else {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=UTF8')
            ->write(json_encode(["propietarios" => null], JSON_UNESCAPED_UNICODE));
    }

});

// Insertamos modelo
$app->post('/modelo', function ($request, $response, array $args) use ($conn){

    $codModelo = $request->getParam('codmodelo');
    $DescModelo = $request->getParam('descmodelo');
    $CodFabricante = $request->getParam('codfabricante');
    $CodTipo = $request->getParam('codtipo');
    $CodSubTipo = $request->getParam('codsubtipo');
    $Foto = $request->getParam('foto');

    if (!isset($codModelo) || !isset( $DescModelo) || !isset($CodFabricante) || !isset($CodTipo) || !isset($CodSubTipo) || !isset( $Foto) ){
        $body = $request->getBody();
        $jsonobj = json_decode($body);
        if ($jsonobj != null) {
            $codModelo = $jsonobj->{'codmodelo'};
            $DescModelo = $jsonobj->{'descmodelo'};
            $CodFabricante = $jsonobj->{'codfabricante'};
            $CodTipo = $jsonobj->{'codtipo'};
            $CodSubTipo = $jsonobj->{'codsubtipo'};
            $Foto = $jsonobj->{'foto'};
        }
    }

    $sql = "INSERT INTO modelo (CodModelo,DescModelo,CodFabricante,CodTipo,CodSubTipo,Foto) VALUES (:CodModelo,:DescModelo,:CodFabricante,:CodTipo,:CodSubTipo,:Foto)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("CodModelo",$codModelo,PDO::PARAM_STR);
        $stmt->bindParam("DescModelo", $DescModelo,PDO::PARAM_STR);
        $stmt->bindParam("CodFabricante", $CodFabricante,PDO::PARAM_STR);
        $stmt->bindParam("CodTipo", $CodTipo,PDO::PARAM_STR);
        $stmt->bindParam("CodSubTipo", $CodSubTipo,PDO::PARAM_STR);
        $stmt->bindParam("Foto", $Foto,PDO::PARAM_STR);

        $conn->beginTransaction();
        $stmt->execute();
        $idlast = $conn->lastInsertId();
        $conn->commit();
        $modelo = ["codmodelo"=>$idlast,"descmodelo"=>$DescModelo, "codfabricante"=>$CodFabricante,"codtipo"=>$CodTipo,"codsubtipo"=>$CodSubTipo,"foto"=>$Foto];

    } catch(PDOException $e) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(["msg"=>"Violada Constraint BD..."]));
    }

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode(["modelo"=>[$modelo]]));
});

$app->run();
