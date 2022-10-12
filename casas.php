<?php

include("inicioW.php");

$func = $_POST["funcion"];

$func();

function eliminarCasa(){
	global $con;
	try{
		$resultado = array();

		$tipo = $_POST["tipo"]; // 1 inmuebles 2 vehiculos
		$id = $_POST["id"];

		$sql = "delete from Inmuebles where id = ?";
		$con->EjecutarPrepared($sql, array($id));

		$result = $con->Select("select archivo from Fotos where tipo =".$con->validar($tipo)." and id_rel = ".$con->validar($id));
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			if(file_exists("fotos/".$fila["archivo"]))
				unlink("fotos/".$fila["archivo"]);
		}

		$sql = "delete from Fotos where tipo = ? and id_rel = ?";
		$con->EjecutarPrepared($sql, array($tipo, $id));

		$resultado["success"] = 1;
		$resultado["mensaje"] = 1;
		echo json_encode($resultado);
	}catch(Exception $e){
		//TODO: averiguar como hacer un rollback
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());

		echo json_encode($resultado);
	}
}

function guardar(){
	global $con;
	try{
		$resultado = array();
		$fotos_ordenadas = array();
		$target_dir = "fotos/";  //direccion donde se colocaran las imagenes
		$img = (int)$_POST["imagenes"]; //cantidad de imagenes recibidas
		
		$tipo = $_POST["tipo"];
		$descripcion = $_POST["descripcion"];
		$detalle = $_POST["detalle"];
		$precioVenta = isset($_POST["precioVenta"]) ? $_POST["precioVenta"] : 0.0;
		$precioAnticretico = isset($_POST["precioAnticretico"]) ? $_POST["precioAnticretico"] : 0.0;
		$precioAlquiler = isset($_POST["precioAlquiler"]) ? $_POST["precioAlquiler"] : 0.0;
		$moneda = $_POST["moneda"];
		$superficie = $_POST["superficie"];
		$superficieC = $_POST["superficieC"];
		$umSupT = $_POST["umSupT"];
		$umSupC = $_POST["umSupC"];
		$cuartos = $_POST["cuartos"];
		$banos = $_POST["banos"];
		$pisos = $_POST["pisos"];
		$direccion = $_POST["direccion"];
		$zona = isset($_POST["zona"]) ? $_POST["zona"] : "";
		$barrio = isset($_POST["barrio"]) ? $_POST["barrio"] : "";
		$provincia = isset($_POST["provincia"]) ? $_POST["provincia"] : "";
		$latitud = isset($_POST["latitud"]) ? $_POST["latitud"] : 0.0;
		$longitud = isset($_POST["longitud"]) ? $_POST["longitud"] : 0.0;
		$fechaIni = isset($_POST["fechaIni"]) && trim($_POST["fechaIni"]) != "" ? $_POST["fechaIni"] : NULL;
		$fechaFin = isset($_POST["fechaFin"]) && trim($_POST["fechaFin"]) != "" ? $_POST["fechaFin"] : NULL;
		

		if($fechaIni != NULL){
			$partes = explode("/", $fechaIni);
			$fechaIni = $partes[2]."-".$partes[1]."-".$partes[0];
		}
		if($fechaFin != NULL){
			$partes = explode("/", $fechaFin);
			$fechaFin = $partes[2]."-".$partes[1]."-".$partes[0];
		}
		$nombre = $_POST["nombre"];
		$telefono = $_POST["telefono"];
		$telefono2 = $_POST["telefono2"];
		

		$sql = "insert into Inmuebles values (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, (CURRENT_TIMESTAMP))";
		$id = $con->EjecutarPrepared($sql, array($tipo, $descripcion, $detalle, $precioVenta, $precioAlquiler, $precioAnticretico,
												 $moneda, $superficie, $umSupT, $superficieC, $umSupC,
												 $cuartos, $banos, $pisos, $direccion, $latitud,
												 $longitud, $zona, $barrio, $provincia, $fechaIni,
												 $fechaFin, $nombre, $telefono, $telefono2));
		$uploadOk = 1;
		$i = 0;
		$c = 0;
		for($j=0; isset($_FILES["fotos"]) && $j < count($_FILES["fotos"]['name']); $j++){
			$nombre = basename($_FILES["fotos"]["name"][$j]);
			$basename = basename($_FILES["fotos"]["name"][$j]);
			if(trim($nombre) == ""){
				continue;
			}
			if(trim($_FILES["fotos"]["tmp_name"][$j]) == ""){
				$c++;
				continue;
			}
			$imageFileType = pathinfo($nombre,PATHINFO_EXTENSION);
			$aux = explode(".", $nombre);
			
			$indice = time() + $i;
			
			$nombre = "1_".$id."_".$indice.".".$imageFileType;

			$target_file = $target_dir . $nombre;
			// Check if image file is a actual image or fake image
			$check = getimagesize($_FILES["fotos"]["tmp_name"][$j]);
			if($check !== false) {
				// Es una imagen, todo bien
			} else {
				$uploadOk = 0;
				continue;
			}
			// Check if file already exists
			if (file_exists($target_file)) {
				$uploadOk = 0;
				continue;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
				&& $imageFileType != "gif" && $imageFileType != "JPG" && $imageFileType != "PNG" && $imageFileType != "JPEG"
				&& $imageFileType != "GIF" ) {
				$resultado["mensaje"] = "Solo se aceptan los siguientes formatos: JPG, JPEG, PNG & GIF.";
				$uploadOk = 0;
				continue;
			}
			// si la foto es muy grande comprimirla!
			
			if (move_uploaded_file($_FILES["fotos"]["tmp_name"][$j], $target_file)) {
				// se subio el archivo
				
				$i++;
				//echo '<script>alert ("'.$nombre.'");</script>';
				$sql = "INSERT INTO Fotos VALUES (1,".$id.",$indice,'".$nombre."')";
				$con->EjecutarSQL($sql);
				
			} else {
				// Hubo un error
				$uploadOk = 0;
			}
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			// No se subieron todos los archivos
			$resultado["mensaje"] = "No se subieron todos los archivos";
		} else {
			// Todos subidos correctamente
			if($i > 0)
				$resultado["mensaje"] = "Se subieron $i archivos correctamente";
			else
				$resultado["mensaje"] = "Se guardo el nuevo orden de las fotos";
		}
		$resultado["success"] = 1;
		echo json_encode($resultado);
	}catch(Exception $e){
		if(isset($target_file) && file_existes($target_file))
			unlink($target_file);
		//TODO: averiguar como hacer un rollback
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());

		echo json_encode($resultado);
	}
}
function modificar(){
	global $con;
	try{
		$resultado = array();
		$fotos_ordenadas = array();
		$target_dir = "fotos/";  //direccion donde se colocaran las imagenes
		$img = (int)$_POST["imagenes"]; //cantidad de imagenes recibidas
		
		$idCasa = 0;
		if(!isset($_POST["idCasa"]) || $_POST["idCasa"] <= 0)
			throw new Exception("No esta enviando el id de la casa a modificar!");

		$idCasa = $_POST["idCasa"];
		$tipo = $_POST["tipo"];

		$descripcion = $_POST["descripcion"];
		$detalle = $_POST["detalle"];
		$precioVenta = isset($_POST["precioVenta"]) ? $_POST["precioVenta"] : 0.0;
		$precioAnticretico = isset($_POST["precioAnticretico"]) ? $_POST["precioAnticretico"] : 0.0;
		$precioAlquiler = isset($_POST["precioAlquiler"]) ? $_POST["precioAlquiler"] : 0.0;
		$moneda = $_POST["moneda"];
		$superficie = $_POST["superficie"];
		$superficieC = $_POST["superficieC"];
		$umSupT = $_POST["umSupT"];
		$umSupC = $_POST["umSupC"];
		$cuartos = $_POST["cuartos"];
		$banos = $_POST["banos"];
		$pisos = $_POST["pisos"];
		$direccion = $_POST["direccion"];
		$zona = isset($_POST["zona"]) ? $_POST["zona"] : "";
		$barrio = isset($_POST["barrio"]) ? $_POST["barrio"] : "";
		$provincia = isset($_POST["provincia"]) ? $_POST["provincia"] : "";
		$latitud = isset($_POST["latitud"]) ? $_POST["latitud"] : 0.0;
		$longitud = isset($_POST["longitud"]) ? $_POST["longitud"] : 0.0;
		$fechaIni = isset($_POST["fechaIni"]) && trim($_POST["fechaIni"]) != "" ? $_POST["fechaIni"] : NULL;
		$fechaFin = isset($_POST["fechaFin"]) && trim($_POST["fechaFin"]) != "" ? $_POST["fechaFin"] : NULL;
		
		if($fechaIni != NULL){
			$partes = explode("/", $fechaIni);
			$fechaIni = $partes[2]."-".$partes[1]."-".$partes[0];
		}
		if($fechaFin != NULL){
			$partes = explode("/", $fechaFin);
			$fechaFin = $partes[2]."-".$partes[1]."-".$partes[0];
		}
		
		$nombre = $_POST["nombre"];
		$telefono = $_POST["telefono"];
		
		$sql = "update Inmuebles set descripcion =  ?, detalle = ?, precio_venta = ?, precio_alquiler = ?, precio_anticretico = ?, moneda = ?, superficie = ?, unidad_sup = ?, superficie_construida = ?, unidad_sup_con = ?,".
									"dormitorios = ?, banos = ?, pisos = ?, direccion = ?, latitud = ?, longitud = ?, zona = ?, barrio = ?, provincia = ?, fecha_inicial = ?, fecha_final = ?, nombre_contacto = ?, telefono_contacto = ? ".
									"where tipo = ? and id = ?";
		$id = $con->EjecutarPrepared($sql, array($descripcion, $detalle, $precioVenta, $precioAlquiler, $precioAnticretico,
												 $moneda, $superficie, $umSupT, $superficieC, $umSupC,
												 $cuartos, $banos, $pisos, $direccion, $latitud,
												 $longitud, $zona, $barrio, $provincia, $fechaIni,
												 $fechaFin, $nombre, $telefono, $tipo, $idCasa));

		$sql = "delete from Fotos where tipo = 1 and id_rel = ".$con->validar($idCasa);
		$con->EjecutarSQL($sql);

		$indices = array();
		for($i = 1; $i <= $img; $i++){
			$nombre = $_POST["img".$i];
			if(file_exists($target_dir.$nombre)){
				$sql = "insert into Fotos values (?, ?, ?, ?)";
				$con->EjecutarPrepared($sql, array(1, $idCasa, $i, $nombre));
			}else{
				$indices[$nombre] = $i;
			}
		}

		$uploadOk = 1;
		$i = 0;
		$c = 0;
		for($j=0; isset($_FILES["fotos"]) && $j < count($_FILES["fotos"]['name']); $j++){
			$nombre = basename($_FILES["fotos"]["name"][$j]);
			$basename = basename($_FILES["fotos"]["name"][$j]);
			if(trim($nombre) == ""){
				continue;
			}
			if (trim($_FILES["fotos"]["tmp_name"][$j]) == ""){
				$c++;
				continue;
			}
			$imageFileType = pathinfo($nombre,PATHINFO_EXTENSION);
			$aux = explode(".", $nombre);
			
			$indice = time() + $j;
			$nombre = "1_".$idCasa."_".$indice.".".$imageFileType;

			$target_file = $target_dir . $nombre;
			// Check if image file is a actual image or fake image
			$check = getimagesize($_FILES["fotos"]["tmp_name"][$j]);
			if($check !== false) {
				// Es una imagen, todo bien
			} else {
				$uploadOk = 0;
				
				continue;
			}
			// Check if file already exists
			if (file_exists($target_file)) {
				$uploadOk = 0;
				continue;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
				&& $imageFileType != "gif" && $imageFileType != "JPG" && $imageFileType != "PNG" && $imageFileType != "JPEG"
				&& $imageFileType != "GIF" ) {
				$resultado["mensaje"] = "Solo se aceptan los siguientes formatos: JPG, JPEG, PNG & GIF.";
				$uploadOk = 0;
				continue;
			}
			// TODO: si la foto es muy grande comprimirla!
			
			if (move_uploaded_file($_FILES["fotos"]["tmp_name"][$j], $target_file)) {
				// se subio el archivo
				
				$indice = $indices[$basename];
				$i++;
				$sql = "INSERT INTO Fotos VALUES (1,".$idCasa.",$indice,'".$nombre."')";
				$con->EjecutarSQL($sql);
			} else {
				// Hubo un error
				$uploadOk = 0;
			}
		}
		$mse = "";
		if($c > 0){
			$mse = "$c archivos no se subieron porque son demasiado pesados.";
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			// No se subieron todos los archivos
			$resultado["mensaje"] = "No se subieron todos los archivos";
		} else {
			// Todos subidos correctamente
			if($i > 0)
				$resultado["mensaje"] = "Se subieron $i archivos correctamente, $mse";
			else
				$resultado["mensaje"] = "Modificado correctamente, $mse";
		}
		$resultado["success"] = 1;
		echo json_encode($resultado);
	}catch(Exception $e){
		//TODO: averiguar como hacer un rollback
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());
		echo json_encode($resultado);
	}
}
function casas(){
	global $con;
	try{
		$result = $con->Select("select * from Inmuebles where tipo = ".$con->validar($_POST["tipo"]));
		$html = "<table class='datos'>";
		$html .= "<tr>".
				"<th style='width:60%'>Descripcion</th>".
				"<th style='width:20%'>Superficie Const.</th>".
				"<th style='width:20%'>Precio</th>".
						"</tr>";
		$casas = array();
		$i = -1;
		if($_POST["tipo"] == 1 || $_POST["tipo"] == 2 || $_POST["tipo"] == 3 || $_POST["tipo"] == 4)
			$tipoFot = 1;
		else $tipoFot = 2;
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
			
			$res = $con->Select("select * from Fotos where tipo = ".$tipoFot." and id_rel = ".$fila["id"]." order by indice");
			$fotos = array();
			while($f = $res->fetch(PDO::FETCH_ASSOC)){
				$fotos[] = $f["archivo"];
			}

			$fila["fotos"] = $fotos;
			
			$i++;
			
			if($fila["fecha_inicial"] != NULL){
				$partes = explode("-", $fila["fecha_inicial"]);
				$fila["fecha_inicial"] = $partes[2]."/".$partes[1]."/".$partes[0];
			}
			if($fila["fecha_final"] != NULL){
				$partes = explode("-", $fila["fecha_final"]);
				$fila["fecha_final"] = $partes[2]."/".$partes[1]."/".$partes[0];
			}

			$casas[$i] = $fila;
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

			$html .= "<tr onclick='seleccionarCasa($i)'>".
						"<td style='width:60%; text-align:left'>".$fila["descripcion"]."</td>".
						"<td style='width:20%'>".number_format($fila["superficie_construida"], 2)." ".$umedC."</td>".
						"<td style='width:20%'>".number_format($fila["precio_venta"], 2)."</td>".
						"</tr>";

		}
		$html .= "</table>";
		$resultado = array('success' => 1,
						   'casasA' => $casas,
								'mensaje' => 'Todo ok',
							'casas' => $html);

		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
								'mensaje' => $e->getMessage());

		echo json_encode($resultado);
	}
}

?>