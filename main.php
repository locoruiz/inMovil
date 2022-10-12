<?php
	session_start();
	header('Cache-control: private');
	if(!isset($_SESSION["usuario"])){
		header("Location: login.html");
	}
	include 'Conexion.php';
	$con = ConexionDeFBE();
	$usu = $_SESSION["usuario"];
	if(!$con){
		header("Location: login.html");
	}
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>inM&oacute;vil</title>
<link href="css/multiColumnTemplate.css" rel="stylesheet" type="text/css">
<link href="../estilo/principal.css" rel="stylesheet" type="text/css">
<link href="jquery-ui.css" rel="stylesheet">
<script src="external/jquery/jquery.js"></script>
<script type="text/javascript" src="external/jquery/sortable.js" ></script>
<script src="jquery-ui.js"></script>
<script src="../datepicker-es.js"></script>
<script type="text/javascript" src="js/casas.js"></script>
<script type="text/javascript" src="js/autos.js"></script>
	<script type="text/javascript">
		var csrf = '<?php echo $_SESSION["token"]; ?>';
		var ulSeleccionado = null;
		var map, marker;
		var plaza = {lat: -21.533333, lng: -64.733333};
		
		var imgs = [];
		var imagenes = [];
		var modalImg;
		var modal;
		var captionText;
		var seleccionada;
		var casas = [];
		var Autos = [];

		var tipoSeleccionado = 1; // 1 inmuebles, 2 vehiculos
		var idSeleccionado = 0; 
		
		var max_width = 1280; // Maximo ancho de la imagenes
		var max_height = 720; // maximo alto de las imagenes
		var max_size = 200000; // maximo peso de la imagen en bytes
		
		$(document).ready(onLoad);
		function onLoad(){
			$(document).keydown(function(e) {
				switch(e.which) {
					case 37: // left
					if(modal.style.display == "block")
						$("#izq").click();
					break;

					case 39: // right
					if(modal.style.display == "block")
						$("#der").click();
					break;

					case 27:
					if(modal.style.display == "block")
						modal.style.display = "none";
					break;

					default: return; // exit this handler for other keys
				}
				e.preventDefault(); // prevent the default action (scroll / move caret)
			});
			seleccionarCasas();
			agregarEventosCasas();
			$("#ulCasas").click(seleccionarCasas);
			$("#ulDepartamentos").click(seleccionarDepars);
			$("#ulOficinas").click(seleccionarOficinas);
			$("#ulTerrenos").click(seleccionarTerrenos);
			$("#ulVehiculos").click(seleccionarVehiculos);
			//TODO: seleccionar oficinas, terrenos, autos, etc.
			
			$("#latitud").blur(moverMapa);
			$("#longitud").blur(moverMapa);

			$( "#modalFotos" ).dialog({
				autoOpen: false,
				modal:true,
				width: 700,
				height:600,
				buttons: [
					{
						text: "Guardar",
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});
			$("#fileFotos").change(function(){readURL(this);});
			// Get the modal
			modal = document.getElementById('myModal');

			modalImg = document.getElementById("img01");
			captionText = document.getElementById("caption");
			// Get the <span> element that closes the modal
			var span = document.getElementsByClassName("close")[0];

			// When the user clicks on <span> (x), close the modal
			span.onclick = function() {
				modal.style.display = "none";
			}
			$("#izq").click(function(){
				seleccionada--;
				if(seleccionada < 0)
					seleccionada = imagenes.length-1;
				console.log("ind:"+seleccionada+"\nval:"+imagenes[seleccionada]);
				var img = $("#"+imagenes[seleccionada])[0];
				modalImg.src = img.src;
				captionText.innerHTML = img.alt;
			});
			$("#der").click(function(){
				seleccionada++;
				if(seleccionada > imagenes.length-1)
					seleccionada = 0;
				var img = $("#"+imagenes[seleccionada])[0];
				modalImg.src = img.src;
				captionText.innerHTML = img.alt;
			});
			$("#botonborrar").click(eliminarFoto);
		}
		function actualizarUl(ulSel){
			if (ulSeleccionado != null)
			{
				ulSeleccionado.css('background','linear-gradient(#504658, #32263B)');
				ulSeleccionado.css('color','white');
			}
			ulSeleccionado = ulSel;
			ulSeleccionado.css('background',' linear-gradient(#32263B, #504658)');
			ulSeleccionado.css('color','white');
		}
		function seleccionarCasas(){
			zoom_in();
			actualizarUl($("#ulCasas"));
			tipoSeleccionado = 1;
			$.post("casas.php", 
			{
				tipo:1,
				funcion:"casas",
				token:csrf
			},
			function(data, status){
				zoom_out();	
				if (status == "success") {
					if(isJson(data)){
						data = jQuery.parseJSON(data);
						if(data.success == 1){
							
							$("#columnaIzq").html(data.casas);
							$("#columnaDer").html(data.formulario);

							casas = data.casasA;
							agregarEventosCasas();
						}else{
							alert(data.mensaje);
						}
					}else{
						alert(data);
					}
				}else{
					alert("hubo un error: "+status);
				}
			});
			$.post("casas_frm.html", 
			{
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					$("#columnaDer").html(data);
					$("#tituloCasa").html("Registrar Casa");
				}else{
					alert("hubo un error: "+status);
				}
			});
			
		}
		function seleccionarDepars(){
			zoom_in();
			actualizarUl($("#ulDepartamentos"));
			tipoSeleccionado = 2;
			$.post("casas.php", 
			{
				tipo:2,
				funcion:"casas",
				token:csrf
			},
			function(data, status){
				zoom_out();	
				if (status == "success") {
					if(isJson(data)){
						data = jQuery.parseJSON(data);
						if(data.success == 1){
							$("#columnaIzq").html(data.casas);
							$("#columnaDer").html(data.formulario);

							casas = data.casasA;
							agregarEventosCasas();
						}else{
							alert(data.mensaje);
						}
					}else{
						alert(data);
					}
				}else{
					alert("hubo un error: "+status);
				}
			});
			$.post("casas_frm.html", 
			{
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					$("#columnaDer").html(data);
					$("#tituloCasa").html("Registrar Departamento");
				}else{
					alert("hubo un error: "+status);
				}
			});
		}
		function seleccionarOficinas(){
			actualizarUl($("#ulOficinas"));
			tipoSeleccionado = 3;
			$.post("casas.php", 
			{
				tipo:3,
				funcion:"casas",
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					if(isJson(data)){
						data = jQuery.parseJSON(data);
						if(data.success == 1){
							$("#columnaIzq").html(data.casas);
							$("#columnaDer").html(data.formulario);

							casas = data.casasA;
							agregarEventosCasas();
						}else{
							alert(data.mensaje);
						}
					}else{
						alert(data);
					}
				}else{
					alert("hubo un error: "+status);
				}
			});
			$.post("casas_frm.html", 
			{
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					$("#columnaDer").html(data);
					$("#tituloCasa").html("Registrar Oficina");
					
				}else{
					alert("hubo un error: "+status);
				}
			});
		}
		function seleccionarTerrenos(){
			actualizarUl($("#ulTerrenos"));
			tipoSeleccionado = 4;
			$.post("casas.php", 
			{
				tipo:4,
				funcion:"casas",
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					if(isJson(data)){
						data = jQuery.parseJSON(data);
						if(data.success == 1){
							$("#columnaIzq").html(data.casas);
							$("#columnaDer").html(data.formulario);

							casas = data.casasA;
							agregarEventosCasas();
						}else{
							alert(data.mensaje);
						}
					}else{
						alert(data);
					}
				}else{
					alert("hubo un error: "+status);
				}
			});
			$.post("casas_frm.html", 
			{
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					$("#columnaDer").html(data);
					$("#tituloCasa").html("Registrar Terreno");
					$("#cuartosTR").css("display","none");
					$("#pisosTR").css("display","none");
					$("#banosTR").css("display","none");
				}else{
					alert("hubo un error: "+status);
				}
			});
		}
		function seleccionarVehiculos(){
			actualizarUl($("#ulVehiculos"));
			tipoSeleccionado = 1;
			$.post("autos.php", 
			{
				funcion:"autos",
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					if(isJson(data)){
						data = jQuery.parseJSON(data);
						if(data.success == 1){
							$("#columnaIzq").html(data.Autos);
							$("#columnaDer").html(data.formulario);

							Autos = data.autosA;
							agregarEventosAutos();
						}else{
							alert(data.mensaje);
						}
					}else{
						alert(data);
					}
				}else{
					alert("hubo un error: "+status);
				}
			});
			$.post("autos_frm.html", 
			{
				token:csrf
			},
			function(data, status){
				if (status == "success") {
					$("#columnaDer").html(data);
				}else{
					alert("hubo un error: "+status);
				}
			});
		}
		
		
		//------------ Mapa -------------
		function initMap() {
	        map = new google.maps.Map(document.getElementById('map'), {
	          zoom: 15,
	          center: plaza
	        });
			google.maps.event.addListener(map, 'click', function(event) {
				if(marker == null){
					marker = new google.maps.Marker({
					  position: event.latLng,
					  map: map
					});
				}else{
					marker.setPosition(event.latLng);
				}
				$("#latitud").val(event.latLng.lat());
				$("#longitud").val(event.latLng.lng());
			});
			$( "#modalMapa" ).dialog({
				autoOpen: false,
				modal:true,
				open:function(){
					google.maps.event.trigger(map, "resize");
					if($.trim($("#latitud").val()) == ""){
						map.panTo(plaza);
					}else{
						map.panTo({lat: parseFloat($("#latitud").val()), lng: parseFloat($("#longitud").val())});
					}
				},
				width: 700,
				height:600,
				buttons: [
					{
						text: "Guardar",
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});
	    }
		function moverMapa(){
			if($.trim($("#latitud").val()) == "" || $.trim($("#longitud").val())=="" ){
				$("#latidud").val("");
				$("#longitud").val("");
				map.panTo(plaza);
			}else{
				map.panTo({lat: parseFloat($("#latitud").val()), lng: parseFloat($("#longitud").val())});
			}
			if(marker != null){
				marker.setMap(null);
				marker = null;
			}
		}
		//---------- imagenes
		function readURL(input) {
			
			if ('files' in input) {
					if (input.files.length == 0) {
					   // debe elegir una o mas fotos
					} else {
						var file ,txt;
						txt="";
						for(var i = 0; i < input.files.length ; i++){	
						    file = input.files[i];
							
							if( !( /image/i ).test( file.type ) )
							{
								alert( "El archivo "+ file.name +" no es una imagen!" );
								return false;
							}
							
							var reader = new FileReader();
							reader.ultimo = i == input.files.length - 1;
							reader.nombre = input.files[i].name;
							reader.i = i+1;
							reader.arch = file;
							reader.onload = function (e) {
								
								var blob = new Blob([e.target.result]); // create blob...
								window.URL = window.URL || window.webkitURL;
								var blobURL = window.URL.createObjectURL(blob); // and get it's URL
								
								var tam = 0;
								if(imagenes.length > 0){						
									var id = imagenes[imagenes.length-1];
									var aux = id.substring(3, id.length);
									tam = parseInt(aux);
								}
								tam++;
								$("#sortable").append("<li  class='fotos' style='position:relative; display: inline-block;'><img id='img"+tam+"' onclick='img_click(this);' alt='"+ this.nombre+"' src='"+blobURL+"' width='100%' height='100%' />"+
														"<div class='cerrar' style=' top:0px; right:0px; font-size: 15px; background-color:#000000; height:15px;width:15px; "+
														" text-align: center;vertical-align: middle;line-height: 15px;' onclick='eliminarFoto(this)'>x</div></li>");
								imagenes[tam-1] = "img"+tam;
								
								$("#sortable").sortable();
								
								// helper Image object
								var image = new Image();
								image.src = blobURL;
								image.arch = this.arch;
								image.onload = function() {
									// have to wait till it's loaded
									if(image.width > max_width || image.height > max_height || this.arch.size > max_size){
										resizeMe(image, this.arch);
										/*
										var resized = resizeMe(image); // send it to canvas
										
										var parts = [
										  new Blob([resized], {type: 'image/jpeg'})
										];
										

										// Construct a file
										var file = new File(parts, this.arch.name, {type:'image/jpeg'});
										
										
										imgs.push(file);
										*/
									}else{
										imgs.push(this.arch);
									}
								}								
							}
						   //reader.readAsDataURL(file);
						   reader.readAsArrayBuffer(file);
						}
					}
			} else {
				if (input.value == "") {
					// no hay fotos
				} else {
					alert("El browser no soporta la pripiedad ´files´, por favor utilice Google Chrome!");
				}
			}
		}
		
		// === RESIZE ====

		function resizeMe(img, file) {
			
			var canvas = document.createElement('canvas');
			

			var width = img.width;
			var height = img.height;

			// calculate the width and height, constraining the proportions
			if (width > height) {
				if (width > max_width) {
					//height *= max_width / width;
					height = Math.round(height *= max_width / width);
					width = max_width;
				}
			  } else {
				if (height > max_height) {
					//width *= max_height / height;
					width = Math.round(width *= max_height / height);
				  	height = max_height;
				}
			  }
			canvas.file = file;
			  // resize the canvas and draw the image data into it
			  canvas.width = width;
			  canvas.height = height;
			  var ctx = canvas.getContext("2d");
			  ctx.drawImage(img, 0, 0, width, height);
			  canvas.toBlob(function(blob){
				  var file = new File([blob], canvas.file.name, {type:'image/jpeg'});
				  imgs.push(file);
			  }, "image/jpeg",0.7);
		  //return canvas.toDataURL("image/jpeg",0.7); // get the data from canvas as 70% JPG (can be also PNG, etc.)
		}
		function img_click(img){
			var id = img.id;
			$.each(imagenes, function(j){
				if(imagenes[j] === id) {
					seleccionada = j;
				}
			});
			modal.style.display = "block";
			modalImg.src = img.src;
			captionText.innerHTML = img.alt;
		}
		function eliminarFoto(div){
			
			if(confirm("Seguro que quiere eliminar la foto?")){
				var img = div.parentNode.children[0];
				var id = img.id;
				captionText.innerHTML = img.alt;
				$.each(imagenes, function(j){
					if(imagenes[j] === id) {
						seleccionada = j;
					}
				});
				var file = $("#fotos")[0];
				var hay = false;
				for(var i = 0; i < imgs.length; i++){
					if(imgs[i].name == captionText.innerHTML){
						hay = true;
						imgs.splice(i, 1);
						$("#sortable")[0].removeChild($("#"+imagenes[seleccionada])[0].parentNode);
						imagenes.splice(seleccionada, 1);
					}
				}
				if(!hay){
					// era imagen del server
					var datos = {};
					datos.funcion = "eliminarFoto";
					
					datos.foto = $("#"+imagenes[seleccionada])[0].alt;
					datos.tipo = tipoSeleccionado;
					datos.id = idSeleccionado;
					datos.token = csrf;

					// TODO: mostrar una panralla de cargando
					$.post("fotos.php", datos, function (data, textStatus, XMLHttpRequest)
					{
						if ( ! isJson(data) )
						{
							alert(data);
							return;
						}
						data = jQuery.parseJSON(data);
						if (textStatus == "success")
						{
							if (parseInt(data.success) == 1 )
							{
								$("#sortable")[0].removeChild($("#"+imagenes[seleccionada])[0].parentNode);
								imagenes.splice(seleccionada, 1);
								if(data.mensaje.length > 0)
									alert(data.mensaje);
							}
							else
							{
								alert("Error:"+data.mensaje);
							}
						}
						else
						{
							alert("Error al cargar:"+textStatus);
						}
					});
				}
				modal.style.display = "none";	
			}
		}
		function isJson(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		}
		
		function zoom_in(){
			$("#contenedorLoader").css("display","");
		}

		function zoom_out()
		{
			$("#contenedorLoader").css("display","none");
		}
	</script>

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
  <header>
    <div class="primary_header">
      <h1 class="title" align="center">inM&oacute;vil</h1>
      <p class="title">Bienvenido <?php echo $usu; ?> <button onclick="logout()">Cerrar Sesión</button></p>
    </div>
    <nav class="secondary_header" id="menu">
      <ul>
        <li id="ulCasas">CASAS</li>
        <li id="ulDepartamentos">DEPARTAMENTOS</li>
        <li id="ulOficinas">OFICINAS</li>
        <li id="ulTerrenos">TERRENOS</li>
        <li id="ulVehiculos">VEHICULOS</li>
      </ul>
    </nav>
  </header>
 
  <div class="row blockDisplay">
    <div class="column_half left_half" id="columnaIzq">
    </div>
    <div class="column_half right_half" id="columnaDer">
    </div>
  </div>
 
  <footer class="secondary_header footer">
    <div class="copyright">&copy;2017 - <strong>RoscoSoft</strong></div>
  </footer>
</div>
	<div id="modalMapa" title="Ubicaci&oacute;n">
		<table style="margin: auto">
			<tr><td>Latitud:</td><td><input type="text" id="latitud" /></td><td style="padding-left: 20px">Longitud:</td><td><input type="text" id="longitud"/></td></tr>
		</table>
		<div id="map" style="width:100%;height:430px" autofocus>
			
		</div>
	</div>
	<div id="modalFotos" title="Fotos">
		Agregar Fotos: &nbsp; <input type="file" id="fileFotos" accept="image/*" multiple/>
		<div id="contenedorFotos" style="width:100%;height:430px; overflow:auto" >
			<ul id="sortable" class="sortable grid">
                        
			</ul>
		</div>
	</div>
	<!-- The Modal -->
	<div id="myModal" class="modal" style="z-index: 1001">
	  <span class="close">×</span>
	  <div class="modal-content" style="vertical-align:middle">
	  <center>
	  <span id='izq' class="flechas"><</span>
	  <span id='botonborrar' class="flechas" style="display:none">Eliminar</span>
	  <span id='der' class="flechas">></span>
	  <br/>          
	  <img width="80%" id="img01">
	  </center>
	  </div>
	  <div id="caption"></div>
	</div>
	<!--Loader-->
		<div id="contenedorLoader" style="height: 100%;width: 100%;position: absolute;top: 0;z-index: 10000;background-color: rgba(0,0,0,0.9);display: none">
			<div id="loader" class="loader"></div>
		</div>
	<script async defer
    		src="https://maps.googleapis.com/maps/api/js?key=YourKEY&callback=initMap">
    </script>
</body>
</html>
