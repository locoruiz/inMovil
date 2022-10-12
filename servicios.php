<?php
// Esta clase hace todas las consultas a la bd, no hace inserts ni updates,por lo tanto no necesita autorizacion
// Devuelve todo en JSON, nada de HTML

include 'Conexion.php';
$con = ConexionDeFBE();
if(!$con){
	// TODO: No mostrar ese error cuando este en linea
    $resultado = array('success' => 0,
                        'mensaje' => 'No pudo conectar'.$pdo_error);
    echo json_encode($resultado);
    exit() or die();
}

$func = $_POST["funcion"];

$func();

function buscarVehi(){
	global $con;
	try{
		$array = array();
		
		$array[] = $_POST["tipo"]; // 1 casa, 2 depar, 3 ofi, 4 terreno
		
		$query = "";
		
		if(isset($_POST["texto"])){
			$txt = $_POST["texto"];
			// TODO: despues mejoramos el algoritmo de busqueda de textos
			$query .= " and ( descripcion like ? or detalle like ? or marca like ? or modelo like ? or color like ? or traccion like ? )";
			// COLLATE UTF8_GENERAL_CI antes del like para cada campo si se quiere buscar insensitive
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
		}
		
		if(isset($_POST["ano"])){
			$ano = $_POST["ano"];
			if(strlen($ano) == 2)
				$ano = "20".$ano;
			$query .= " and ano = ? ";
			$array[] = $ano;
		}
		
		if(isset($_POST["minSup"])){
			$query .= " and (kilometraje between ? and ?) ";
			$array[] = $_POST["minSup"];
			$array[] = $_POST["maxSup"];
		}
		
		$BOB_USD = 6.96;
		$EUR_USD = 1.1;
		if(isset($_POST["precioMin"]) || isset($_POST["precioMax"])){
			// Este servicio web trae el ultimo tipo de cambio!
			// create curl resource 
			$ch = curl_init();

			// set url 
			curl_setopt($ch, CURLOPT_URL, "http://free.currencyconverterapi.com/api/v3/convert?q=BOB_USD&compact=ultra"); 

			//return the transfer as a string 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

			// $output contains the output string 
			$output = curl_exec($ch); 
			$salida = json_decode($output);
			
			$BOB_USD = $salida->BOB_USD;
			
			curl_setopt($ch, CURLOPT_URL, "http://free.currencyconverterapi.com/api/v3/convert?q=EUR_USD&compact=ultra"); 
			
			$output = curl_exec($ch); 
			$salida = json_decode($output);
			
			$EUR_USD = $salida->EUR_USD;
			
			// close curl resource to free up system resources 
			curl_close($ch);
		}
		
		
		$sql = "select * from Vehiculos where tipo = ? ".$query." and CURDATE() between fecha_inicial and fecha_final  order by fecha desc";
		$result = $con->SelectPrepared($sql, $array);
		$autos = array();
		$i = -1;
		
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			
			$res = $con->Select("select * from Fotos where tipo = 2 and id_rel = ".$fila["id"]." order by indice");
			$fotos = array();
			while($f = $res->fetch(PDO::FETCH_ASSOC)){
				$fotos[] = "fotos/".$f["archivo"];
			}

			$fila["fotos"] = $fotos;
			
			$moneda = $fila["moneda"]; // 1 bolivianos, 2 dolares, 3 euros
			
			$precio = (float)$fila["precio"];
			if($moneda == 1)
				$precio = $precio * $BOB_USD;
			else if ($moneda == 3)
				$precio = $precio * $EUR_USD;
			
			
			if(isset($_POST["precioMin"])){
				$precioMin = (float)$_POST["precioMin"];
				if($precio < $precioMin)
					continue;
			}
			if(isset($_POST["precioMax"])){
				$precioMax = (float)$_POST["precioMax"];
				if($precio > $precioMax)
					continue;
			}
			$fila["unidadCil"] = $fila["unidad_cil"] == 1 ? "cm3" : "cc";
			
			$i++;
			$autos[$i] = $fila;
		}
		
		$resultado = array('success' => 1,
						   'autos' => $autos,
								'mensaje' => 'query:'.$query);
		echo json_encode($resultado);
	}catch (Exception $e){
		
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());
		echo json_encode($resultado);
	}
}

function buscarInm(){
	global $con;
	try{
		$array = array();
		
		$array[] = $_POST["tipo"]; // 1 casa, 2 depar, 3 ofi, 4 terreno
		
		$tipoC = $_POST["tipoC"]; // 1 venta, 2 alquiler, 3 anticretico
		
		$query = "";
		if($tipoC == 1){
			$query = " precio_venta > 0 ";
		}else if($tipoC == 2){
			$query = " precio_alquiler > 0 ";
		}else{
			$query = " precio_anticretico > 0 ";
		}
		
		if(isset($_POST["texto"])){
			$txt = $_POST["texto"];
			// TODO: despues mejoramos el algoritmo de busqueda de textos
			$query .= " and ( descripcion like ? or detalle like ? or barrio like ? or zona like ? or direccion like ? or provincia like ? )";
			// COLLATE UTF8_GENERAL_CI antes del like para cada campo si se quiere buscar insensitive
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
			$array[] = "%$txt%";
		}
		
		if(isset($_POST["cuartos"])){
			$cuartos = (int)$_POST["cuartos"];
			$operador = "=";
			if($cuartos >= 6)
				$operador = ">=";
			$query .= " and dormitorios ".$operador." ".$cuartos;
		}
		if(isset($_POST["banos"])){
			$cuartos = (int)$_POST["banos"];
			$operador = "=";
			if($cuartos >= 6)
				$operador = ">=";
			$query .= " and banos ".$operador." ".$cuartos;
		}
		$BOB_USD = 6.96;
		$EUR_USD = 1.1;
		if(isset($_POST["precioMin"]) || isset($_POST["precioMax"])){
			// Este servicio web trae el ultimo tipo de cambio!
			// create curl resource 
			$ch = curl_init();

			// set url 
			curl_setopt($ch, CURLOPT_URL, "http://free.currencyconverterapi.com/api/v3/convert?q=BOB_USD&compact=ultra"); 

			//return the transfer as a string 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

			// $output contains the output string 
			$output = curl_exec($ch); 
			$salida = json_decode($output);
			
			$BOB_USD = $salida->BOB_USD;
			
			curl_setopt($ch, CURLOPT_URL, "http://free.currencyconverterapi.com/api/v3/convert?q=EUR_USD&compact=ultra"); 
			
			$output = curl_exec($ch); 
			$salida = json_decode($output);
			
			$EUR_USD = $salida->EUR_USD;
			
			// close curl resource to free up system resources 
			curl_close($ch);
		}
		
		
		$sql = "select * from Inmuebles where tipo = ? and ".$query." and CURDATE() between fecha_inicial and fecha_final  order by fecha desc";
		$result = $con->SelectPrepared($sql, $array);
		$casas = array();
		$i = -1;
		
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			
			$res = $con->Select("select * from Fotos where tipo = 1 and id_rel = ".$fila["id"]." order by indice");
			$fotos = array();
			while($f = $res->fetch(PDO::FETCH_ASSOC)){
				$fotos[] = "fotos/".$f["archivo"];
			}

			$fila["fotos"] = $fotos;
			
			$umed = "";
			// TODO: comparar las superficies y continue
			$supEnMetros = $fila["superficie"];
			switch($fila["unidad_sup"]){
				case 1:
					$umed = "m2";
					break;
				case 2:
					$supEnMetros = $supEnMetros * 10000; // Ha a m2
					$umed = "Ha";
					break;
				case 3:
					$supEnMetros = $supEnMetros * 1000000; // km2 a m2
					$umed = "Km2";
					break;
				default:
					$umed = "m2";
					break;
			}
			
			if(isset($_POST["minSup"])){
				if((int)$_POST["minSup"] <= $supEnMetros && $supEnMetros <= (int)$_POST["maxSup"]){
					// Esta bien
				}else
					continue;
			}
			
			
			$umedC = "";
			switch($fila["unidad_sup_con"]){
				case 1:
					$umedC = "m2";
					break;
				case 2:
					$umedC = "Ha";
					break;
				case 3:
					$umedC = "Km2";
					break;
				default:
					$umedC = "m2";
					break;
			}
			$fila["umed"] = $umed;
			$fila["umedC"] = $umedC;
			
			$moneda = $fila["moneda"]; // 1 bolivianos, 2 dolares, 3 euros
			
			if($tipoC == 1){
				$fila["precio"] = $fila["precio_venta"];
			}else if($tipoC == 2){
				$fila["precio"] = $fila["precio_alquiler"];
			}else{
				$fila["precio"] = $fila["precio_anticretico"];
			}
			
			$precio = (float)$fila["precio"];
			if($moneda == 1)
				$precio = $precio * $BOB_USD;
			else if ($moneda == 3)
				$precio = $precio * $EUR_USD;
			
			if(isset($_POST["precioMin"])){
				$precioMin = (float)$_POST["precioMin"];
				if($precio < $precioMin)
					continue;
			}
			if(isset($_POST["precioMax"])){
				$precioMax = (float)$_POST["precioMax"];
				if($precio > $precioMax)
					continue;
			}
			$i++;
			$casas[$i] = $fila;
		}
		
		$resultado = array('success' => 1,
						   'casas' => $casas,
								'mensaje' => 'query:'.$query);

		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());
		echo json_encode($resultado);
	}
}

function maximos(){
	global $con;
	try{
		$sql = "select superficie, unidad_sup, tipo from Inmuebles where CURDATE() between fecha_inicial and fecha_final";
		$result = $con->Select($sql);
		$maxCasas = 0;
		$maxDepars = 0;
		$maxOfis = 0;
		$maxTerrenos = 0;
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			$valor = (float)$fila["superficie"];
			// Convertir en metros cuadrados
			if($fila["unidad_sup"] == 2){
				$valor = $valor * 10000; // Ha a m2
			}else if($fila["unidad_sup"] == 3){ 
				$valor = $valor * 1000000; // km2 a m2
			}
			switch($fila["tipo"]){
				case 1:
					$maxCasas = max($maxCasas, $valor);
					break;
				case 2:
					$maxDepars = max($maxDepars, $valor);
					break;
				case 3:
					$maxOfis = max($maxOfis, $valor);
					break;
				case 4:
					$maxTerrenos = max($maxTerrenos, $valor);
					break;
			}
		}
		$maxAutos = 100; // recorrido en km
		$maxMotos = 100;
		$sql = "select max(kilometraje) as kilom, tipo from Vehiculos where CURDATE() between fecha_inicial and fecha_final group by tipo order by tipo";
		$result = $con->Select($sql);
		$fila = $result->fetch(PDO::FETCH_ASSOC);
		$maxAutos = $fila["kilom"];
		$fila = $result->fetch(PDO::FETCH_ASSOC);
		$maxMotos = $fila["kilom"];
		
		
		$resultado = array('success' => 1,
						   'maxCasas' => $maxCasas,
						   'maxDepars' => $maxDepars,
						   'maxOfis' => $maxOfis,
						   'maxTerrenos' => $maxTerrenos,
						   'maxAutos' => $maxAutos,
						   'maxMotos' => $maxMotos,
								'mensaje' => "Todo ok");

		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());
		echo json_encode($resultado);
	}
}

function casas(){
	global $con;
	try{
		
		$tipoC = $_POST["tipoC"]; // 1 venta, 2 alquiler, 3 anticretico
		
		$query = "";
		if($tipoC == 1){
			$query = " precio_venta > 0 ";
		}else if($tipoC == 2){
			$query = " precio_alquiler > 0 ";
		}else{
			$query = " precio_anticretico > 0 ";
		}
		$sql = "select * from Inmuebles where tipo = ".$con->validar($_POST["tipo"])." and ".$query." and CURDATE() between fecha_inicial and fecha_final  order by fecha desc";
		$result = $con->Select($sql);
		$casas = array();
		$i = -1;
		
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			
			$res = $con->Select("select * from Fotos where tipo = 1 and id_rel = ".$fila["id"]." order by indice");
			$fotos = array();
			while($f = $res->fetch(PDO::FETCH_ASSOC)){
				$fotos[] = "fotos/".$f["archivo"];
			}

			$fila["fotos"] = $fotos;
			
			$i++;
			$umed = "";
			switch($fila["unidad_sup"]){
				case 1:
					$umed = "m2";
					break;
				case 2:
					$umed = "Ha";
					break;
				case 3:
					$umed = "Km2";
					break;
				default:
					$umed = "m2";
					break;
			}
			$umedC = "";
			switch($fila["unidad_sup_con"]){
				case 1:
					$umedC = "m2";
					break;
				case 2:
					$umedC = "Ha";
					break;
				case 3:
					$umedC = "Km2";
					break;
				default:
					$umedC = "m2";
					break;
			}
			$fila["umed"] = $umed;
			$fila["umedC"] = $umedC;
			
			if($tipoC == 1){
				$fila["precio"] = $fila["precio_venta"];
			}else if($tipoC == 2){
				$fila["precio"] = $fila["precio_alquiler"];
			}else{
				$fila["precio"] = $fila["precio_anticretico"];
			}
			
			$casas[$i] = $fila;
		}
		$resultado = array('success' => 1,
						   'casas' => $casas,
								'mensaje' => 'Todo ok');

		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());

		echo json_encode($resultado);
	}
}
function vehiculos(){
	global $con;
	try{
		
		$sql = "select * from Vehiculos where tipo = ".$con->validar($_POST["tipo"])." and CURDATE() between fecha_inicial and fecha_final  order by fecha desc";
		$result = $con->Select($sql);
		$autos = array();
		$i = -1;
		
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			
			$res = $con->Select("select * from Fotos where tipo = 2 and id_rel = ".$fila["id"]." order by indice");
			$fotos = array();
			while($f = $res->fetch(PDO::FETCH_ASSOC)){
				$fotos[] = "fotos/".$f["archivo"];
			}

			$fila["fotos"] = $fotos;
			
			$fila["unidadCil"] = $fila["unidad_cil"] == 1 ? "cm3" : "cc";
			
			$i++;
			
			$autos[$i] = $fila;
		}
		$resultado = array('success' => 1,
						   'autos' => $autos,
								'mensaje' => 'Todo ok');

		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());

		echo json_encode($resultado);
	}
}
?>