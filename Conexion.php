<?php
	$pdo_error;
	class Conexion{
		// siempre desconectar: $this->con = null; o llamando a close() porque hay que validar los campos
		public $con;
		public $error;
		function EjecutarSQL($sql){ 
			// Debe haber hecho el quote siempre antes
			try{
				$this->con->exec($sql);
				return $this->con->lastInsertId();
			}catch(PDOException $ex){
				throw new Exception('Error al ejecutar el comando:'.$ex->getMessage());
			}
		}
		function EjecutarPrepared($sql, $array){
			try{
				$this->con->prepare($sql)->execute($array);
				return $this->con->lastInsertId();
			}catch(PDOException $ex){
				throw new Exception('Error al ejecutar prepared :'.$ex->getMessage());
			}
		}
		function Select($sql){
			// Debe hacer hecho el quote antes
			try{
				$result = $this->con->query($sql);
				return $result;
			}catch(PDOException $ex){
				throw new Exception('Error al hacer el select: '.$ex->getMessage()." sql= ".$sql);
			}
		}
		function SelectPrepared($sql, $array){
			// con array
			try{
				$result = $this->con->prepare($sql);
				if($result->execute($array)){
					return $result;
				}else{
					throw new Exception("");
				}
			}catch(PDOException $ex){
				throw new Exception('Error al hacer el select: '.$ex->getMessage()." sql= ".$sql);
			}
		}
		function validar($valor){
			return $this->con->quote($valor);
		}
		function close(){
			$this->con = null;
		}
	}
	
	// conexion para fbe
	function ConexionDeFBE(){	
		global $pdo_error;		
		try{
			$conex = new Conexion;
			$conex->con = new PDO("mysql:host=localhost;dbname=Inmovil",
								 "inmovil",
								 "Inmovil123",
								 array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		return $conex;
		}catch(PDOException $ex){
			$pdo_error = "Error en la conexion: ".$ex->getMessage();
			return false;
		}
	}
	
?>