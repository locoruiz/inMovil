function recargarCasas(){
	$.post("casas.php", 
	{
		tipo:tipoSeleccionado,
		funcion:"casas",
		token:csrf
	},
	function(data, status){
		if (status == "success") {
			if(isJson(data)){
				data = jQuery.parseJSON(data);
				if(data.success == 1){
					$("#columnaIzq").html(data.casas);
					casas = data.casasA;
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


function limpiarCasa(){
	$("#btnGuardar").val("Guardar");
	$("#btnEliminar").css("display", "none");
	idSeleccionado = 0;
	$("#sortable").html("");
	$("#descripcion").val("");
	$("#detalle").val("");
	$("#chVenta")[0].checked = false;
	$("#precioVenta").css("display", "none");
	$("#chAlquiler")[0].checked = false;
	$("#precioAlquiler").css("display", "none");
	$("#chAnticretico")[0].checked = false;
	$("#precioAnticretico").css("display", "none");
	$("input[type=text], textarea").val("");
	$("#fileFotos").val("");
	imgs = [];
	imagenes = [];
	moverMapa();
}

function seleccionarCasa(i){
	$("#btnEliminar").css("display", "");
	idSeleccionado = casas[i].id;
	$("#btnGuardar").val("Modificar");
	$("#descripcion").val(casas[i].descripcion);
	$("#detalle").val(casas[i].detalle);
	$("#moneda").val(casas[i].moneda);
	if(casas[i].precio_venta > 0){
		$("#precioVenta").val(casas[i].precio_venta);
		$("#precioVenta")[0].disabled = false;
		$("#chVenta")[0].checked = true;
		$("#precioVenta").css("display", "");
	}else{
		$("#precioVenta").val("");
		$("#chVenta")[0].checked = false;
		$("#precioVenta").css("display", "none");
	}
	if(casas[i].precio_alquiler > 0){
		$("#precioAlquiler").val(casas[i].precio_alquiler);
		$("#precioAlquiler")[0].disabled = false;
		$("#chAlquiler")[0].checked = true;
		$("#precioAlquiler").css("display", "");
	}else{
		$("#precioAlquiler").val("");
		$("#chAlquiler")[0].checked = false;
		$("#precioAlquiler").css("display", "none");
	}
	if(casas[i].precio_anticretico > 0){
		$("#precioAnticretico").val(casas[i].precio_anticretico);
		$("#precioAnticretico")[0].disabled = false;
		$("#chAnticretico")[0].checked = true;
		$("#precioAnticretico").css("display", "");
	}else{
		$("#precioAnticretico").val("");
		$("#chAnticretico")[0].checked = false;
		$("#precioAnticretico").css("display", "none");
	}

	$("#superficie").val(casas[i].superficie);
	$("#umSupT").val(casas[i].unidad_sup);
	$("#superficieC").val(casas[i].superficie_construida);
	$("#umSupC").val(casas[i].unidad_sup_con);
	$("#cuartos").val(casas[i].dormitorios);
	$("#banos").val(casas[i].banos);
	$("#pisos").val(casas[i].pisos);
	$("#direccion").val(casas[i].direccion);
	$("#zona").val(casas[i].zona);
	$("#barrio").val(casas[i].barrio);
	$("#provincia").val(casas[i].provincia);
	$("#latitud").val(casas[i].latitud);
	$("#longitud").val(casas[i].longitud);
	$("#latitud").blur();
	var lat = $("#latitud").val();
	var long = $("#longitud").val();
	marker = new google.maps.Marker({
			  position: new google.maps.LatLng(lat, long),
			  map: map
			});
	$("#fechaIni").val(casas[i].fecha_inicial);
	$("#fechaFin").val(casas[i].fecha_final);
	$("#nombre").val(casas[i].nombre_contacto);
	$("#telefono").val(casas[i].telefono_contacto);
	$("#telefono2").val(casas[i].telefono_contacto_1);
	// TODO: Cargar las fotos de las casas
	var casa = casas[i];
	$("#sortable").html("");
	imgs = [];
	imagenes = [];
	for(var j = 0; j < casa.fotos.length; j++){
		$("#sortable").append("<li  class='fotos' style='position:relative; display: inline-block;'><img id='img"+(j+1)+"' onclick='img_click(this);' alt='"+ casa.fotos[j]+"' src='fotos/"+casa.fotos[j]+"' width='100%' height='100%' />"+
								"<div class='cerrar' style=' top:0px; right:0px; font-size: 15px; background-color:#000000; height:15px;width:15px; "+
								" text-align: center;vertical-align: middle;line-height: 15px;' onclick='eliminarFoto(this)'>x</div></li>");
		imagenes.push("img"+(j+1));
	}
	$("#sortable").sortable();
}
function guardarCasa(){
	var formData = new FormData();
	if(idSeleccionado == 0){
		formData.append("funcion", "guardar");
	}else{
		formData.append("funcion", "modificar");
		formData.append("idCasa", idSeleccionado);
	}
	formData.append("token", csrf);
	
	formData.append("tipo", tipoSeleccionado);
	
	if($.trim($("#descripcion").val()) == ""){
		alert("Debe escribir una descripcion de la casa!");
		$("#descripcion").focus();
		return;
	}
	if($.trim($("#detalle").val()) == ""){
		alert("Debe escribir un detalle de la casa!");
		$("#detalle").focus();
		return;
	}
	
	formData.append("descripcion", $("#descripcion").val());
	formData.append("detalle", $("#detalle").val());
	formData.append("moneda", $("#moneda").val());
	
	var hayPrecio = false;
	if($("#chVenta")[0].checked){
		if($.trim($("#precioVenta").val()) == "" || $("#precioVenta").val() <= 0){
			
		}else{
			formData.append("precioVenta", floatval($("#precioVenta").val()));
			hayPrecio = true;
		}
	}
	if($("#chAlquiler")[0].checked){
		if($.trim($("#precioAlquiler").val()) == "" || $("#precioAlquiler").val() <= 0){
			
		}else{
			formData.append("precioAlquiler", floatval($("#precioAlquiler").val()));
			hayPrecio = true;
		}
	}
	if($("#chAnticretico")[0].checked){
		if($.trim($("#precioAnticretico").val()) == "" || $("#precioAnticretico").val() <= 0){
			
		}else{
			formData.append("precioAnticretico", floatval($("#precioAnticretico").val()));
			hayPrecio = true;
		}
	}
	if(!hayPrecio){
		alert("Debe asignar por lo menos un precio! Haga click en el checkbox para que se habilite el campo de precio");
		return;
	}
	formData.append("superficie", floatval($("#superficie").val()));
	formData.append("superficieC", floatval($("#superficieC").val()));
	if(($.trim($("#superficie").val()) == "" || $("#superficie").val() <= 0 || 
		  	$.trim($("#superficieC").val()) == "" || $("#superficieC").val() <= 0) && (tipoSeleccionado == "1" || tipoSeleccionado == "2")){
		formData.append("superficie", floatval(0));
		formData.append("superficieC", floatval(0));
		// alert("Debe asignar una superficie valida!");
		// $("#superficie").focus();
		// return;
	}
	
	
	formData.append("umSupT", $("#umSupT").val());
	formData.append("umSupC", $("#umSupC").val()); // unida de medida de la superficie
	
	if(tipoSeleccionado != "4"){
		formData.append("cuartos", $("#cuartos").val());
		formData.append("banos", $("#banos").val());
		formData.append("pisos", $("#pisos").val());
	}else{
		formData.append("cuartos", 0);
		formData.append("banos", 0);
		formData.append("pisos", 0);
	}

	if($.trim($("#cuartos").val()) == "" || $("#cuartos").val() <= 0){
		if(tipoSeleccionado != "4" && tipoSeleccionado != "3"){
			formData.append("cuartos", 0);
			// alert("Debe especificar el numero de cuartos!");
			// $("#cuartos").focus();
			// return;
		}
	}
	if($.trim($("#banos").val()) == "" || $("#banos").val() <= 0){
			if(tipoSeleccionado != "4" && tipoSeleccionado != "3"){
				formData.append("banos", 0);
				// alert("Debe especificar el numero de baños!");
				// $("#banos").focus();
				// return;
			}
	}
	if($.trim($("#pisos").val()) == "" || $("#pisos").val() <= 0){
		if(tipoSeleccionado != "4" && tipoSeleccionado != "3"){
			formData.append("pisos", 0);
			// alert("Debe especificar el numero de pisos!");
			// $("#pisos").focus();
			// return;
		}
	}
	if($.trim($("#direccion").val()) == "" || $("#direccion").val() <= 0){
		alert("Debe especificar la direccion!");
		$("#direccion").focus();
		return;
	}
	if(tipoSeleccionado != "4"){
		formData.append("cuartos", $("#cuartos").val());
		formData.append("banos", $("#banos").val());
		formData.append("pisos", $("#pisos").val());
	}else{
		formData.append("cuartos", 0);
		formData.append("banos", 0);
		formData.append("pisos", 0);
	}
	if (tipoSeleccionado == "3"){
		if ($.trim($("#cuartos").val()) == "" || $("#cuartos").val() <= 0) 
			formData.append("cuartos", 0);
		else formData.append("cuartos", $("#cuartos").val());
		if ($.trim($("#banos").val()) == "" || $("#banos").val() <= 0) 
			formData.append("banos", 0);
		else formData.append("banos", $("#banos").val());
		if ($.trim($("#pisos").val()) == "" || $("#pisos").val() <= 0) 
			formData.append("pisos", 0);
		else formData.append("pisos", $("#pisos").val());
	}
	formData.append("direccion", $("#direccion").val());
	formData.append("zona", $("#zona").val());
	formData.append("barrio", $("#barrio").val());
	formData.append("provincia", $("#provincia").val());
	
	if($("#latitud").val() == ""){
		if(!confirm("No asignó una ubicación en el mapa, ¿quiere continar sin ubicación?")){
			$("#modalMapa").dialog("open");
			return;
		}
	}
	formData.append("latitud", $("#latitud").val());
	formData.append("longitud", $("#longitud").val());
	
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
        url: "casas.php",  //Server script to process data
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
						limpiarCasa();
						recargarCasas();
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
function eliminarCasa(){
	if(idSeleccionado == 0)
		return;
	if(confirm("Seguro que quiere eliminar esta casa?")){
		var datos = {};
		datos.funcion = "eliminarCasa";
		
		datos.tipo = tipoSeleccionado;
		datos.id = idSeleccionado;
		datos.token = csrf;

		// TODO: mostrar una panralla de cargando
		$.post("casas.php", datos, function (data, textStatus, XMLHttpRequest)
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
					limpiarCasa();
					recargarCasas();
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
function agregarEventosCasas(){
	$("#btnLimpiar").button();
	$("#btnVerFotos").button()
				.click(function(){
					$("#modalFotos").dialog("open");
					// TODO: cargar fotos del inmueble
				});
	$("#btnMapa").button()
				.click(function(){
					$("#modalMapa").dialog("open");
				});
	$(".tooltip").tooltip();
	$("#btnGuardar").button().click(guardarCasa);
	$("#btnEliminar").button().click(eliminarCasa);
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
	
	$("#chVenta").change(function(){
		$("#precioVenta")[0].disabled = !$("#chVenta")[0].checked;
		
		if($("#chVenta")[0].checked){
			$("#precioVenta").css("display", "");
			$("#precioVenta").focus();
		}else
			$("#precioVenta").css("display", "none");
	});
	$("#chAnticretico").change(function(){
		$("#precioAnticretico")[0].disabled = !$("#chAnticretico")[0].checked;
		
		if($("#chAnticretico")[0].checked){
			$("#precioAnticretico").css("display", "");
			$("#precioAnticretico").focus();
		}else
			$("#precioAnticretico").css("display", "none");
	});
	$("#chAlquiler").change(function(){
		$("#precioAlquiler")[0].disabled = !$("#chAlquiler")[0].checked;
		
		if($("#chAlquiler")[0].checked){
			$("#precioAlquiler").css("display", "");
			$("#precioAlquiler").focus();
		}else
			$("#precioAlquiler").css("display", "none");
	});
}