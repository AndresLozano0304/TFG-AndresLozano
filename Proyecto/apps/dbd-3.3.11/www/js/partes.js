function verSession() {
    var salida = "";
    $.ajax({
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            session: "ok",
            acceso: getCookie('acceso')
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
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
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
            console.log(resString);
            console.log("token: "+resString['TOKEN']);
            setCookie('acceso', resString['TOKEN'], 15);

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
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            cerrarSession: "ok",
            acceso: getCookie('acceso')
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

function parteAbierto() {
    var salida = "";
    $.ajax({
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            parteAbierto: "ok",
            acceso: getCookie('acceso')
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
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            datosAgente: "ok",
            acceso: getCookie('acceso')
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
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            comunicacionAgente: "ok",
            acceso: getCookie('acceso')
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
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            cerrarParte: "ok",
            idParte: idParte,
            acceso: getCookie('acceso')
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
    $("#header").load("inc/header.html", function () {
        $("#nombre").append(acceso['NOMBRE'] + " " + acceso['PRIMERAPELLIDO']);
        if (acceso['NOMBREEMPRESA'] == 'DBD') {
            $("#barra").attr("style", "background-color:#8D9CAA");
            $("#listado").attr("style", "background-color:#8D9CAA");
            $("#logo").html("<img src='img/dbd.jpg'  height='40px' alt=''>");
        } else if (acceso['NOMBREEMPRESA'] == 'MYM') {
            $("#barra").attr("style", "background-color:#DE533E");
            $("#listado").attr("style", "background-color:#DE533E");
            $("#logo").html("<h1>MYM</h1>");
        } else {
            $("#barra").attr("style", "background-color:#669999");
            $("#listado").attr("style", "background-color:#669999");
            $("#logo").html("<h1>RESTYLE</h1>");
        }
    });
    $("#main").load("inc/login.html", function () {
        agente = datosAgente();
        parte = parteAbierto();
        comunicaciones = comunicacionAgente();
        //DATOS AGENTE
        if (agente['TIENEGASTOS'] == '0') {
            $("#gastos").hide();
        }
        $(".zonas").hide();
        $(".letrero").hide();
        $(".file-select").attr("texto", "📸 Seleccionar");

        //REVISAR SI HAY PARTES ABIERTOS
        if (existe(parte)) {
            idParteAbierto = parte['ID'];
            $("#iniciar").hide();
            hinicio = new Date(parte['HINICIO']);
            entradaHora = hinicio.getHours() + ":" + hinicio.getMinutes();
            $("#avisos").html("<div class='row'><div style='text-align:center' class='col-4'><img src='img/oficina.gif' height = '80'></div><div class='col-8'><h2 class='alert-heading'>PARTE ABIERTO</h2><p>Has iniciado un parte a las " + entradaHora + " h. </div></div>");
            $("#avisos").show();
        } else {
            $("#cerrarParte").hide();
        }


    });


}

function abrirParte(firma) {
    if (!existe(firma)) {
        anuncio("<div style='text-align:center'><img src='img/error.gif' height='120px'></div><h5>La firma esta vacía</h5>")
    } else {
        var salida = "";
        geoDirecciona();
        setTimeout(function () {
            $.ajax({
                url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
                method: "POST",
                async: false,
                data: {
                    abrirParte: "ok",
                    firma: firma,
                    direccion: geoDireccion,
                    acceso: getCookie('acceso')
                },
                method: "POST",

                error: function () {
                    alert("No se puede conectar al Servidor o no estas Registrado");
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

        }, 1200);

    }

}


function cargaUsuarios(selectUsuarios) {
    var salida = "";
    $.ajax({
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            listadoTrabajadores: "ok",
            acceso: getCookie('acceso')
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

function enviarIncidencia(fecha, motivo, descripcion) {
    var salida = "";
    $.ajax({
        url: "https://dbd.voluta.studio/dvlp/back/servidor.php",
        method: "POST",
        async: false,
        data: {
            incidenciaUsuario: "ok",
            motivo: motivo,
            descripcion: descripcion,
            fecha: fecha,
            acceso: getCookie('acceso')
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
            if (salida != "ko") {
                anuncio("<div style='text-align:center'><img src='img/ok.gif' height='120px'></div><h5>Incidencia nº " + salida + " creada correctamente</h5>");
                $("#zonaIncidencias").toggle("slow");
                $(".camposDatos").val("");
            } else {
                anuncio("<div style='text-align:center'><img src='img/error.gif' height='120px'></div><h5>Algo ha ocurrido, intentalo de nuevo...</h5>")
            }
        }
    });
}





