var cuerpo = "";

function verSession() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            session: "ok"
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
};


function login(usuario, pass) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            login: "ok",
            usuario: usuario,
            pass: pass
        },
        method: "POST",
        error: function () {
            alert("No se puede conectar al Servidor");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);

        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
};

function cerrarSession() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            cerrarSession: "ok"
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);

        },
        complete: function () {
            salida = resString;
            acceso = "";
        }
    });

    return salida;
};

function parteAbierto() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            parteAbierto: "ok"
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);

        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function datosAgente() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            datosAgente: "ok"
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);

        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function comunicacionAgente() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            comunicacionAgente: "ok"
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);

        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function cerrarParte(idParte) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            cerrarParte: "ok",
            idParte: idParte
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            salida = resString;
            if (salida == "ok") {
                idParteAbierto = "";
                anuncio("<div style='text-align:center'><img src='img/salir.gif' height='120px'></div><h5>Parte cerrado correctamente!</h5>");
                datosAcceso(acceso);
            } else {
                anuncio("<div style='text-align:center'><img src='img/error.gif' height='120px'></div><h5>Algo ha ocurrido, intentalo de nuevo...</h5>")
            }
        }
    });
}

function datosAcceso(acceso) {
    espera();
setTimeout(function(){
    $("#header").load("inc/header.html", function () {
        $("#nombre").append(acceso['NOMBRE'] + " " + acceso['PRIMERAPELLIDO']);
    });
    $("#main").load("inc/login.html", function () {
        //MENU
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $('#menuLateral').toggleClass('active');
        });
        $("#listadoLink").click(function () {
            $("#layout").load("inc/listado.html", function () {
                //Cargamos tabulator
            })
        });
        $("#personalLink").click(function () {
            $("#layout").load("inc/empleados.html", function () {
                //Cargamos tabulator
            })
        });
        $("#horariosLink").click(function () {
            $("#layout").load("inc/horarios.html", function () {
                //Cargamos tabulator
            })
        });
        $("#incidenciasLink").click(function () {
            $("#layout").load("inc/incidencias.html", function () {
                //Cargamos tabulator
            })
        });
        $("#tarjetasLink").click(function () {
            $("#layout").load("inc/tarjetas.html", function () {
                //Cargamos tabulator
            })
        });
        $("#gastosLink").click(function () {
            $("#layout").load("inc/gastos.html", function () {
                //Cargamos tabulator
            })
        });
        $("#cm").click(function () {
            $("#layout").load("inc/cm.html", function () {
                //Cargamos tabulator
            })
        });
        //INICIO
        $("#layout").load("inc/cm.html", function () {
            //Cargamos tabulator
        });
        eliminaspam();
    },500);
    });


}

function abrirParte(firma) {
    if (!existe(firma)) {
        anuncio("<div style='text-align:center'><img src='img/error.gif' height='120px'></div><h5>La firma esta vacía</h5>")
    } else {
        var salida = "";
        $.ajax({
            url: "back/servidor.php",
            method: "POST",
            async: false,
            data: {
                abrirParte: "ok",
                firma: firma
            },
            method: "POST",

            error: function () {
                alert("No se puede conectar al Servidor o no estas registrado");
            },
            success: function (respuesta) {
                resString = "";
                //resString = JSON.parse(respuesta);
                resString = respuesta;
                console.log(respuesta);
            },
            complete: function () {
                salida = resString;
                if (salida == "ok") {
                    idParteAbierto = "";
                    anuncio("<div style='text-align:center'><img src='img/oficina.gif' height='120px'></div><h5>Parte creado correctamente!</h5>");
                    datosAcceso(acceso);
                } else {
                    anuncio("<div style='text-align:center'><img src='img/error.gif' height='120px'></div><h5>Algo ha ocurrido, intentalo de nuevo...</h5>");
                }
            }
        });
    }

}

function creaPdf(html) {
    espera();
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            creaPdf: "ok",
            html: html
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            salida = resString;
            if (salida == "ok") {
                idParteAbierto = "";
                anuncio("<div style='text-align:center'><img src='img/ok.gif' height='120px'></div><h5>Listado Generado!</h5>");
                datosAcceso(acceso);
            } else {
                anuncio("<div style='text-align:center'><img src='img/error.gif' height='120px'></div><h5>Algo ha ocurrido, intentalo de nuevo...</h5>")
            }
        }
    });
}

function cargaUsuarios(selectUsuarios) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            listadoTrabajadores: "ok",
        },
        method: "POST",
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            $.each(resString, function (key, value) {
                selectUsuarios.append("<option value='" + value['ID'] + "'>" + value['NOMBRE'] + "</option>");
            })
        }
    });
}

function cargaTarjetasUsuario(selectTarjeta,usuario_id) {
    espera();
    selectTarjeta.empty();
    selectTarjeta.append("<option value='contado'>Contado</option>");
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            cargaTarjetasUsuario: "ok",
            usuario_id: usuario_id
        },
        method: "POST",
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            $.each(resString, function (key, value) {
                selectTarjeta.append("<option value='" + value['ID'] + "'>" + value['NOMBRE'] + "</option>");
            })
            eliminaspam();
        }
    });
}

function addParteManual(hentrada, hsalida, agente, obs) {
    //form = objecto jquery
    //formulario = $(form);
    //datosForm = new FormData(formulario);
    //datosForm.append("parteManual","ok");
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        //enctype: 'multipart/form-data',
        data: {
            parteManual: "ok",
            hentrada: hentrada,
            hsalida: hsalida,
            agente: agente,
            obs: obs
        },
        async: false,
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (!existe(resString)) {
                aviso("<p><img src='img/error.gif' width='90px'></p><p>Ha ocurrido un error, intentalo de nuevo</p>");
            } else {
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Parte nº: " + resString + " creado correctamente</p>");
            }
        }
    });

}

function verificaUsuario(usuario) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            verificaUsuario: "ok",
            USUARIO: usuario
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);

        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function addUsuario(divForm) {
    form = $(divForm)[0];
    var formularioEnvio = new FormData(form);
    formularioEnvio.append("addUsuario", "ok");
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: formularioEnvio,
        cache: false, // El valor predeterminado es verdadero, pero generalmente no almacena en caché
        processData: false, // utilizado para serializar el parámetro de datos, aquí debe ser falso; si es verdadero, FormData se convertirá al tipo String
        contentType: false, // Algunas cargas de archivos están relacionadas con el protocolo http, Baidu, si hay archivos cargados, solo se puede establecer en false
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (existe(resString)) {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro nº: " + resString + " creado correctamente</p>");
                table.setData();
            } else {
                aviso("<p><img src='img/error.gif' width='90px'></p><p>Ha ocurrido un error, intentalo de nuevo</p>");
            }
        }
    });

}

function datosUsuario(usuario) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            datosUsuario: "ok",
            usuario: usuario
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(resString);
        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function modificaUsuario(divForm) {
    form = $(divForm)[0];
    var formularioEnvio = new FormData(form);
    formularioEnvio.append("modificaUsuario", "ok");
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: formularioEnvio,
        cache: false, // El valor predeterminado es verdadero, pero generalmente no almacena en caché
        processData: false, // utilizado para serializar el parámetro de datos, aquí debe ser falso; si es verdadero, FormData se convertirá al tipo String
        contentType: false, // Algunas cargas de archivos están relacionadas con el protocolo http, Baidu, si hay archivos cargados, solo se puede establecer en false
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString = 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Usuario cambiado correctamente</p>");
            } else {
                aviso("<p><img src='img/error.gif' width='90px'></p><p>Ha ocurrido un error, intentalo de nuevo</p>");
            }
        }
    });

}

function compruebaHorarioUno() {
    var salida = "";
    //Comprueba primer horario
    //Dias seleccionados
    var diasUno = new Array;
    $(".DiaSemana").each(function () {
        if ($(this).prop("checked")) {
            diasUno.push($(this).val());
        }
    });
    if (diasUno.length == 0) {
        salida += "Selecciona al menos un día. ";
    }
    //horarios
    //primera hora
    hent1 = parseFloat($("#hentrada11").val()) * 60 + parseFloat($("#mentrada11").val());
    hsal1 = parseFloat($("#hsalida11").val()) * 60 + parseFloat($("#msalida11").val());
    tramo1 = hsal1 - hent1;
    if (isNaN(tramo1) || tramo1 < 0) {
        salida += "Error en el primer tramo de horas, o hay campos vacios o la salida es menor a la entrada. ";
    }
    //segunda hora
    hent2 = parseFloat($("#hentrada12").val()) * 60 + parseFloat($("#mentrada12").val());
    hsal2 = parseFloat($("#hsalida12").val()) * 60 + parseFloat($("#msalida12").val());
    tramo2 = hsal2 - hent2;
    if (!existe($("#hentrada12").val()) && !existe($("#mentrada12").val())
        && !existe($("#hsalida12").val()) && !existe($("#msalida12").val())) {
    } else {
        if (isNaN(tramo2) || tramo2 < 0) {
            salida += "Error en el segundo tramo horario, o hay campos vacios o la salida es menor a la entrada. ";
        }
        if (((hent2 >= hent1 && hent2 <= hsal1) || (hsal2 >= hent1 && hsal2 <= hsal1)) ||
            ((hent1 >= hent2 && hent1 <= hsal2) || (hsal1 >= hent2 && hsal1 <= hsal2))) {
            salida += "Los horarios de los tramos se solapan, revisar. ";
        }
    }
    return salida;
}

function compruebaHorarioDos() {
    var salida = "";
    //Comprueba primer horario
    //Dias seleccionados
    var diasUno = new Array;
    $(".DiaSemana2").each(function () {
        if ($(this).prop("checked")) {
            diasUno.push($(this).val());
        }
    });
    if (diasUno.length == 0) {
        salida += "Selecciona al menos un día en el segundo horario. ";
    }
    //horarios
    //primera hora
    hent1 = parseFloat($("#hentrada21").val()) * 60 + parseFloat($("#mentrada21").val());
    hsal1 = parseFloat($("#hsalida21").val()) * 60 + parseFloat($("#msalida21").val());
    tramo1 = hsal1 - hent1;
    if (isNaN(tramo1) || tramo1 < 0) {
        salida += "Error en el primer tramo de horas en el segundo horario, o hay campos vacios o la salida es menor a la entrada. ";
    }
    //segunda hora
    hent2 = parseFloat($("#hentrada22").val()) * 60 + parseFloat($("#mentrada22").val());
    hsal2 = parseFloat($("#hsalida22").val()) * 60 + parseFloat($("#msalida22").val());
    tramo2 = hsal2 - hent2;
    if (!existe($("#hentrada22").val()) && !existe($("#mentrada22").val())
        && !existe($("#hsalida22").val()) && !existe($("#msalida22").val())) {
    } else {
        if (isNaN(tramo2) || tramo2 < 0) {
            salida += "Error en el segundo tramo horario en el segundo horario, o hay campos vacios o la salida es menor a la entrada. ";
        }
        if (((hent2 >= hent1 && hent2 <= hsal1) || (hsal2 >= hent1 && hsal2 <= hsal1)) ||
            ((hent1 >= hent2 && hent1 <= hsal2) || (hsal1 >= hent2 && hsal1 <= hsal2))) {
            salida += "Los horarios de los tramos en el segundo horario se solapan, revisar. ";
        }
    }

    return salida;
}

function addHorario() {
    //Preparamos los campos Horario Tramo1
    var diasUno = new Array;
    $(".DiaSemana").each(function () {
        if ($(this).prop("checked")) {
            diasUno.push($(this).val());
        }
    });
    //horarios
    //primera hora
    hent11 = minutosToHora(parseFloat($("#hentrada11").val()) * 60 + parseFloat($("#mentrada11").val()));
    hsal11 = minutosToHora(parseFloat($("#hsalida11").val()) * 60 + parseFloat($("#msalida11").val()));
    //segunda hora
    hent12 = minutosToHora(parseFloat($("#hentrada12").val()) * 60 + parseFloat($("#mentrada12").val()));
    hsal12 = minutosToHora(parseFloat($("#hsalida12").val()) * 60 + parseFloat($("#msalida12").val()));
    //Preparamos campos Horario Tramo2
    var diasDos = new Array;
    $(".DiaSemana2").each(function () {
        if ($(this).prop("checked")) {
            diasDos.push($(this).val());
        }
    });
    //horarios
    //primera hora
    hent21 = minutosToHora(parseFloat($("#hentrada21").val()) * 60 + parseFloat($("#mentrada21").val()));
    hsal21 = minutosToHora(parseFloat($("#hsalida21").val()) * 60 + parseFloat($("#msalida21").val()));
    //segunda hora
    hent22 = minutosToHora(parseFloat($("#hentrada22").val()) * 60 + parseFloat($("#mentrada22").val()));
    hsal22 = minutosToHora(parseFloat($("#hsalida22").val()) * 60 + parseFloat($("#msalida22").val()));

    //ENVIAMOS A SERVIDOR
    espera();
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            addHorario: "ok",
            ref: $("#ref").val(),
            diasUno: diasUno.toString(),
            hent11: hent11,
            hsal11: hsal11,
            hent12: hent12,
            hsal12: hsal12,
            diasDos: diasDos.toString(),
            hent12: hent12,
            hsal12: hsal12,
            hent22: hent22,
            hsal22: hsal22
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            salida = resString;
            $("#modalDiv").modal('hide');
            anuncio("<div style='text-align:center'><img src='img/ok.gif' height='120px'></div><h5>Horario nº " + salida + " creado correctamente.</h5>");
        },
    })

};

function existeRefHorario(ref) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            existeRefHorario: "ok",
            ref: ref
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(resString);
        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function cargaPartes(selectParte, fecha, usuario) {
    espera();
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            listadoParte: "ok",
            fecha: fecha,
            usuario: usuario,
        },
        method: "POST",
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            selectParte.empty();
            selectParte.append("<option value='no'>Sin Partes</option>");
            $.each(resString, function (key, value) {
                hinicioVista = existe(value['HINICIO']) ? value['HINICIO'].substring(value['HINICIO'].length - 8) : " - ";
                hfinVista = existe(value['HFIN']) ? value['HFIN'].substring(value['HFIN'].length - 8) : " - ";
                selectParte.append("<option value='" + value['ID'] + "' hinicio='"+hinicioVista+"' hfin='"+hfinVista+"'>" + hinicioVista + " h.  a " + hfinVista + "h. </option>");
            })
            eliminaspam();
        }
    });

}

function addTarjeta() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            addTarjeta: "ok",
            nombreTarjeta: $("#nombreTarjeta").val(),
            usuario: $("#usuario").val(),
            numero: $("#numero").val()
        },
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString == 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro creado correctamente</p>");
            }
        }

    })
}

function modTarjeta() {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            modTarjeta: "ok",
            nombreTarjeta: $("#nombreTarjeta").val(),
            usuario: $("#usuario").val(),
            numero: $("#numero").val(),
            idTarjeta: $("#idTarjeta").val()
        },
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString == 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro modificado correctamente</p>");
            }
        }

    })
}

function datosTarjeta(tarjeta) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            datosTarjeta: "ok",
            tarjeta: tarjeta
        },
        method: "POST",

        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },

        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(resString);
        },
        complete: function () {
            salida = resString;
        }
    });

    return salida;
}

function borraTarjeta(registro) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            borraTarjeta: "ok",
            idTarjeta: registro
        },
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString == 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro borrado correctamente</p>");
            }
        }

    })
}

function addGasto(divForm) {
    form = $(divForm)[0];
    var formularioEnvio = new FormData(form);
    formularioEnvio.append("addGasto", "ok");
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: formularioEnvio,
        cache: false, // El valor predeterminado es verdadero, pero generalmente no almacena en caché
        processData: false, // utilizado para serializar el parámetro de datos, aquí debe ser falso; si es verdadero, FormData se convertirá al tipo String
        contentType: false, // Algunas cargas de archivos están relacionadas con el protocolo http, Baidu, si hay archivos cargados, solo se puede establecer en false
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (existe(resString)) {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");

                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro nº: " + resString + " creado correctamente</p>");
            } else {
                aviso("<p><img src='img/error.gif' width='90px'></p><p>Ha ocurrido un error, intentalo de nuevo</p>");
            }
        }
    });

}

function modGasto(divForm) {
    form = $(divForm)[0];
    var formularioEnvio = new FormData(form);
    formularioEnvio.append("modificaGasto", "ok");
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: formularioEnvio,
        cache: false, // El valor predeterminado es verdadero, pero generalmente no almacena en caché
        processData: false, // utilizado para serializar el parámetro de datos, aquí debe ser falso; si es verdadero, FormData se convertirá al tipo String
        contentType: false, // Algunas cargas de archivos están relacionadas con el protocolo http, Baidu, si hay archivos cargados, solo se puede establecer en false
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString = 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro cambiado correctamente</p>");
            } else {
                aviso("<p><img src='img/error.gif' width='90px'></p><p>Ha ocurrido un error, intentalo de nuevo</p>");
            }
        }
    });

}

function borraGasto(registro) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            borraGasto: "ok",
            idGasto: registro
        },
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString == 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro borrado correctamente</p>");
            }
        }

    })
}

function cargaGastoIncidencia(selectGasto, fecha, usuario) {
    espera();
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            listadoGastosIncidencias: "ok",
            fecha: fecha,
            usuario: usuario,
        },
        method: "POST",
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            selectGasto.empty();
            selectGasto.append("<option value='no'>Sin Gastos</option>");
            $.each(resString, function (key, value) {
                selectGasto.append("<option value='" 
                + value['ID'] + "' DESCRIPCION ='"+value['DESCRIPCION']+"' IMPORTE='"+value['IMPORTE']+
                "' TARJETA_ID='"+value['TARJETA_ID']+"' URL='"+value['URL']+"'>" + value['DESCRIPCION'] + " - " + value['IMPORTE'] + " €</option>");
            })
            eliminaspam();
        }
    });

}

function addComentario(divForm,parteVista,gastoVista,modParteEstado,modGastoEstado) {
    form = $(divForm)[0];
    var formularioEnvio = new FormData(form);
    formularioEnvio.append("addComentario", "ok");
    formularioEnvio.append("addParte", parteVista);
    formularioEnvio.append("modParte", modParteEstado);
    formularioEnvio.append("addGasto", gastoVista);
    formularioEnvio.append("modGasto", modGastoEstado);
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: formularioEnvio,
        cache: false, // El valor predeterminado es verdadero, pero generalmente no almacena en caché
        processData: false, // utilizado para serializar el parámetro de datos, aquí debe ser falso; si es verdadero, FormData se convertirá al tipo String
        contentType: false, // Algunas cargas de archivos están relacionadas con el protocolo http, Baidu, si hay archivos cargados, solo se puede establecer en false
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {

            if (existe(resString)) {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");

                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro nº: " + resString + " creado correctamente</p>");
            } else {
                aviso("<p><img src='img/error.gif' width='90px'></p><p>Ha ocurrido un error, intentalo de nuevo</p>");
            }
        }
    });

}

function cargaHorario(selectUsuarios) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            listadoHorariosEmpleados: "ok",
        },
        method: "POST",
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            resString = JSON.parse(respuesta);
            //resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            $.each(resString, function (key, value) {
                selectUsuarios.append("<option value='" + value['ID'] + "'>" + value['REF'] + "</option>");
            })
        }
    });
}

function cambiaHorario(seleccion,horario_id) {
    var salida = "";
    $.ajax({
        url: "back/servidor.php",
        method: "POST",
        async: false,
        data: {
            cambiaHorario: "ok",
            seleccion: seleccion,
            horario_id:horario_id
        },
        error: function () {
            alert("No se puede conectar al Servidor o no estas registrado");
        },
        success: function (respuesta) {
            resString = "";
            //resString = JSON.parse(respuesta);
            resString = respuesta;
            console.log(respuesta);
        },
        complete: function () {
            if (resString == 'ok') {
                $("#cuerpoModal").empty();
                $("#modalDiv").modal("hide");
                aviso("<p><img src='img/ok.gif' width='90px'></p><p>Registro/s cambiado/s correctamente</p>");
            }
        }

    })
}




