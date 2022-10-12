<?php
include("inicioW.php");

$func = $_POST["funcion"];

$func();

function eliminarFoto(){
	global $con;
	try{
		$foto = $_POST["foto"];

		$con->EjecutarPrepared("delete from Fotos where tipo = ? and id_rel = ? and archivo = ?", array($_POST["tipo"], $_POST["id"], $foto));

		if(file_exists("fotos/".$foto))
			unlink("fotos/".$foto);

		$resultado = array('success' => 1,
								'mensaje' => '');

		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());

		echo json_encode($resultado);
	}
}

?>