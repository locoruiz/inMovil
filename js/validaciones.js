// JavaScript Document
function validar_decimales(event, enteros, decimales){
			var oTxt = event.target;
			var key = event.keyCode;
			var indiceEditado = event.target.selectionStart;
			var indicePunto;
			var puntos = 0;
			var ents = 0;
			var decs = 0;
			var pasoPunto = false;
			if(oTxt.value.length == 0 && key == 46)
				return false;
			var str = "";
			
			for(var i = 0; i < oTxt.value.length; i++){
				if(oTxt.value[i] == "."){
					indicePunto = i;
					pasoPunto = true;
					puntos++;
					continue;
				}
				if(!pasoPunto){
					ents++;
				}else{
					decs++;
				}
			}
			if(event.keyCode == 46){
				if(puntos > 0)
					return false;
			}else
				if (key <= 13 || (key >= 48 && key <= 57)){
					if(indiceEditado <= indicePunto){
						pasoPunto = false;
						decs++;
					}
					if(pasoPunto){
						return decs < decimales;
					}else
						return ents < enteros;
				}else
					return false;
		}
		function number_format(numero, dec){
			// Asigna comas separadroas de miles
			if(numero == "")
				numero = 0;
			var str = "1";
			for(var i = 0; i < dec; i++)
				str += "0";
			var mil = parseInt(str);
			var result = (numero*mil/mil).toFixed(dec);
			var str = "";
			var pasoPunto = false;
			var c = 0;
			var indicePunto = result.length - 1 - dec;
			var strFin = result.substring(indicePunto, result.length);
			
			for(var i = indicePunto - 1; i >= 0 ; i--){
				c++;
				str = (c % 3 == 0 && i > 0 ? ","+result[i] : result[i]) + str;
			}
			
			return str+strFin;
		}
		function floatval(str){
			// Elimina las comas deparadoras de miles y devuelve un float.
			if(str == "")
				return 0.0;
			var nstr = str.replace(/,/g, "");
			return parseFloat(nstr);
		}
		function seleccionar(oTxt){
			oTxt.value = floatval(oTxt.value);
			oTxt.select();
		}
		function paste_decimales(e, enteros, decimales){
			// Valida que el Paste sea un numero valido con esa cantidad de enteros y decimales
			var clipboardData, pastedData;
			e.stopPropagation();
			e.preventDefault();
			clipboardData = e.clipboardData || window.clipboardData;
			pastedData = clipboardData.getData('Text');

			// Do whatever with pasteddata
			try{
				var datos = floatval(pastedData);
				if(isNaN(datos))
					return;
				
				var arr = (datos+"").split(".");
				if(arr[0].length > enteros)
					return;
				if(arr.length > 1 && arr[1].length > decimales){
					datos = datos.toFixed(decimales);
				}
				e.target.value = datos;
				e.target.select();
			}catch(e){
				// Esta cualquier cosa en el paste
				console.log(e.message);
			}
		}
		function mostrar_mensaje(id, text){
			var obj = $("#"+id)[0];
			var parent = obj.parentElement;
			var rect = obj.getBoundingClientRect();
			
			var ancho = obj.offsetWidth;
			var alto = obj.offsetHeight;
			var altoDiv = 30;
			var izq = parseFloat(rect.left)+parseFloat(obj.offsetWidth) + 20;
			var top = rect.top + alto/2 - altoDiv/2 - 5; // restar el padding tambien
			
			var anchoDivCont = parent.offsetWidth - izq - 20;
			
			var divCont = document.createElement('div');
			divCont.id = "mensaje";
			divCont.style.cssText = 'position:absolute; top:'+top+'px;left:'+(izq)+'px; opacity:0;';
			
			var flecha = document.createElement('div');
			flecha.id = "flecha";
			
			flecha.style.cssText = 'float:left;height:'+(altoDiv+10)+'px; width:0px; z-index:100;';
			
			var div = document.createElement('div');
			div.innerHTML = text;
			div.style.cssText = 'float:left; min-width:100px;  min-height:'+altoDiv+'px; max-height:'+(2*altoDiv)+'px; z-index:100;background-color:#E6E6E6;font-size:14px; margin-right:10px; overflow:auto; color:red; text-align:left; padding:5px; box-shadow: 5px 5px 5px #888888;';
			
			divCont.appendChild(flecha);
			divCont.appendChild(div);
			
			var botonCerrar = document.createElement('input');
			botonCerrar.type = "button";
			botonCerrar.value = "X";
			botonCerrar.style.cssText = 'float:left;';
			
			divCont.appendChild(botonCerrar);
			parent.appendChild(divCont);
			
			
			$("#mensaje").animate({
				opacity:1
			},500);
			$(botonCerrar).click(function(){
				$("#mensaje").animate({opacity:0}, 500, function(){
					$("#mensaje").remove();
				});
			});
		}