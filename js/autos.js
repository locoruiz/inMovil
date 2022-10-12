function recargarAutos(){
	$.post("autos.php", 
	{
		tipo:1,
		funcion:"autos",
		token:csrf
	},
	function(data, status){
		if (status == "success") {
			if(isJson(data)){
				data = jQuery.parseJSON(data);
				if(data.success == 1){
					$("#columnaIzq").html(data.Autos);
					Autos = data.autosA;
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
}



function limpiarAuto(){
	$("#btnGuardar").val("Guardar");
	$("#btnEliminar").css("display", "none");
	idSeleccionado = 0;
	$("#sortable").html("");
	$("#descripcion").val("");
	$("#detalle").val("");
	
	$("input[type=number],input[type=text], textarea").val("");
	$("#fileFotos").val("");
	$("select").val(1);
	imgs = [];
	imagenes = [];
	moverMapa();
}

function seleccionarAuto(i){

	$("#btnEliminar").css("display", "");
	idSeleccionado = Autos[i].id;
	$("#tituloAuto").html("Detalle Auto");
	$("#btnGuardar").val("Modificar");
	$("#tipo").val(Autos[i].tipo);
	$("#descripcion").val(Autos[i].descripcion);
	$("#detalle").val(Autos[i].detalle);
	$("#moneda").val(Autos[i].moneda);
	$("#precioVenta").val(Autos[i].precio);
	
	$("#modelo").val(Autos[i].modelo);
	$("#marca").val(Autos[i].marca);
	$("#ano").val(Autos[i].ano);
	$("#color").val(Autos[i].color);
	$("#cilindrada").val(Autos[i].cilindrada);
	$("#umCilT").val(Autos[i].unidad_cil);
	$("#puertas").val(Autos[i].puertas);
	$("#caja").val(Autos[i].caja);
	$("#combustible").val(Autos[i].combustible);
	$("#kilometraje").val(Autos[i].kilometraje);
	$("#traccion").val(Autos[i].traccion);
	
	$("#fechaIni").val(Autos[i].fecha_inicial);
	$("#fechaFin").val(Autos[i].fecha_final);
	$("#nombre").val(Autos[i].nombre_contacto);
	$("#telefono").val(Autos[i].telefono_contacto);
	$("#telefono2").val(Autos[i].telefono_contacto_1);
	// TODO: Cargar las fotos de las Autos
	var Auto = Autos[i];
	$("#sortable").html("");
	imgs = [];
	imagenes = [];
	for(var j = 0; j < Auto.fotos.length; j++){
		$("#sortable").append("<li  class='fotos' style='position:relative; display: inline-block;'><img id='img"+(j+1)+"' onclick='img_click(this);' alt='"+ Auto.fotos[j]+"' src='fotos/"+Auto.fotos[j]+"' width='100%' height='100%' />"+
								"<div class='cerrar' style=' top:0px; right:0px; font-size: 15px; background-color:#000000; height:15px;width:15px; "+
								" text-align: center;vertical-align: middle;line-height: 15px;' onclick='eliminarFoto(this)'>x</div></li>");
		imagenes.push("img"+(j+1));
	}
	$("#sortable").sortable();
}
function guardarAuto(){
	var formData = new FormData();
	if(idSeleccionado == 0){
		formData.append("funcion", "guardar");
	}else{
		formData.append("funcion", "modificar");
		formData.append("idAuto", idSeleccionado);
	}
	formData.append("token", csrf);
	
	formData.append("tipo", $("#tipo").val());
	
	if($.trim($("#descripcion").val()) == ""){
		alert("Debe escribir una descripcion del Vehiculo!");
		$("#descripcion").focus();
		return;
	}
	if($.trim($("#detalle").val()) == ""){
		alert("Debe escribir un detalle del Vehiculo!");
		$("#detalle").focus();
		return;
	}
	
	formData.append("descripcion", $("#descripcion").val());
	formData.append("detalle", $("#detalle").val());
	formData.append("moneda", $("#moneda").val());
	
	var hayPrecio = false;
	if($.trim($("#precioVenta").val()) == "" || $("#precioVenta").val() <= 0){
		
	}else{
		formData.append("precioVenta", floatval($("#precioVenta").val()));
		hayPrecio = true;
	}
	
	if(!hayPrecio){
		alert("Debe asignar por lo menos un precio!");
		$("#precioVenta").focus();
		return;
	}
	if($.trim($("#marca").val()) == ""){
		alert("Debe asignar una marca valida!");
		$("#marca").focus();
		return;
	}
	if($.trim($("#ano").val()) == "" || $("#ano").val() <= 0){
		alert("Debe especificar el Año!");
		$("#ano").focus();
		return;
	}
	if($.trim($("#modelo").val()) == ""){
		alert("Debe asignar un modelo valido!");
		$("#modelo").focus();
		return;
	}
	formData.append("marca", $("#marca").val());
	formData.append("ano", $("#ano").val());
	formData.append("modelo", $("#modelo").val());
	formData.append("caja", $("#caja").val());
	formData.append("combustible", $("#combustible").val());
	formData.append("traccion", $("#traccion").val());
	if($.trim($("#color").val()) == ""){
		alert("Debe asignar un color!");
		$("#color").focus();
		return;
	}
	formData.append("cilindrada",floatval($("#cilindrada").val()));
	if($.trim($("#cilindrada").val()) == "" || $("#cilindrada").val() <= 0){
		formData.append("cilindrada",2);
		//alert("Debe especificar el numero de cilindrada!");
		//$("#cilindrada").focus();
		//return;
	}
	formData.append("color", $("#color").val());
	formData.append("umCilT", $("#umCilT").val());
	
	formData.append("puertas",floatval($("#puertas").val()));
	if($.trim($("#puertas").val()) == "" || $("#puertas").val() < 0){ //si tiene 0 puertas, es moto
		formData.append("puertas", 0);
		//alert("Debe especificar el numero de puertas!");
		//$("#puertas").focus();
		//return;
	}
	
	formData.append("kilometraje",floatval($("#kilometraje").val()));
	formData.append("fechaIni", $("#fechaIni").val());
	formData.append("fechaFin", $("#fechaFin").val());
	
	formData.append("nombre", $("#nombre").val());
	formData.append("telefono", $("#telefono").val());
	formData.append("telefono2", $("#telefono2").val());
	
	formData.append("imagenes", $("#sortable").children().size());
	for(var i = 0; i < imgs.length; i++){
		formData.append("fotos[]", imgs[i]);
	}
	/////////cargar las imagenes del vector con sus verdaderos nombres//////
	for(var i = 0; i < $("#sortable").children().size(); i++){
		var li = $("#sortable").children()[i];
		var img = li.childNodes[0];
		formData.append("img"+(i+1), img.alt);
	}
	// TODO: mostrar cargando
	zoom_in();
	////////////////////////////////////////////////////////////
	$.ajax({
        url: "autos.php",  //Server script to process data
        type: 'POST',
        //Ajax events
        success: function(datos, textStatus, XMLHttpRequest){
			zoom_out();
			console.log(datos);
			if(isJson(datos)){
				var data = jQuery.parseJSON(datos);
				if (textStatus == "success"){
					if (parseInt(data.success) == 1 ){
						imgs = [];
						document.getElementById("fileFotos").value = "";
						limpiarAuto();
						recargarAutos();
						alert(data.mensaje);
					}else{
						alert(data.mensaje);
					}
				}else{
					alert(textStatus);
				}
			}else{
				alert(datos);
				return;
			}
		},
        error:  function(jqXHR, textStatus, errorThrown ){
				console.log("Hubo un error:"+errorThrown);
				alert(errorThrown);
		},
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
		cache:false,
        contentType: false,
        processData: false
    });
}
function eliminarAuto(){
	if(idSeleccionado == 0)
		return;
	if(confirm("Seguro que quiere eliminar este vehiculo?")){
		var datos = {};
		datos.funcion = "eliminarAuto";
		
		datos.id = idSeleccionado;
		datos.token = csrf;

		// TODO: mostrar una panralla de cargando
		$.post("autos.php", datos, function (data, textStatus, XMLHttpRequest)
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
					limpiarAuto();
					recargarAutos();
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
}
function agregarEventosAutos(){
	$("#btnLimpiar").button();
	$("#btnVerFotos").button()
				.click(function(){
					$("#modalFotos").dialog("open");
					// TODO: cargar fotos del inmueble
				});

	$(".tooltip").tooltip();
	$("#btnGuardar").button().click(guardarAuto);
	$("#btnEliminar").button().click(eliminarAuto);
	$(".datepicker").datepicker({dateFormat:"dd/mm/yy"});
	$(".datepicker").datepicker("option", "dateFormat", "dd/mm/yy");
	$( ".datepicker" ).datepicker( "option", $.datepicker.regional[ "es" ] );
	$("#fechaIni").change(function(){
		$("#fechaFin").datepicker("option", "minDate", $("#fechaIni").datepicker("getDate"));
	});
	$("#fechaFin").change(function(){
		$("#fechaIni").datepicker("option", "maxDate", $("#fechaFin").datepicker("getDate"));
	});
	$( ".spinner" ).spinner({
      spin: function( event, ui ) {
        if ( ui.value <= 0 ) {
          $( this ).spinner( "value", 0 );
          return false;
        }
      }
    });
	$( ".spinner" ).width(50);
	
	
}