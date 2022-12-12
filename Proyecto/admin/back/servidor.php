<?php
//LIBRERIAS, CLASES Y VARIABLES
include 'BD.php';
//require 'vendor/autoload.php';

date_default_timezone_set("Europe/Madrid");

$base = new BD();
$hoy = date("Y-m-d 00:00:00");
$ahora = date("Y-m-d H:i:s");
$hoyCorto = date("Y-m-d");

//FUNCIONES



//SESION
ini_set('session.cache_expire', 200000);
ini_set('session.cache_limiter', 'none');
ini_set('session.cookie_lifetime', 2000000);
ini_set('session.gc_maxlifetime', 200000); //el mas importante


session_start();



//REQUEST
//ver si hay session abierta
if(isset($_POST['session'])){
    if(empty($_SESSION)){
        echo "";
    }else if($_POST['acceso']){
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA 
            FROM USUARIOS 
            LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
            WHERE EMAIL = '".$_SESSION['user']."'");
        echo json_encode($logado[0]);

    }else{
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA 
            FROM USUARIOS 
            LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
            WHERE EMAIL = '".$_SESSION['user']."'");
        echo json_encode($logado[0]);
    }
}

//Cerrar Session
if(isset($_POST['cerrarSession'])){
    session_destroy();
    echo "ok";
}



//login
if(isset($_POST['login'])){
    $usuario = $_POST['usuario'];
    $pass =  md5($_POST['pass']);
    $base  = new BD();
    $query = "SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, TRABAJADORES.ROL 
    FROM USUARIOS 
    LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
    WHERE USUARIOS.ACTIVO = 1 and EMAIL = '$usuario' AND PASSWORD = '$pass' and  (ROL = 'ADMINISTRADOR' OR ROL = 'PRODUCCION' OR ROL = 'CONTABILIDAD')";
    $logado = $base->query($query);
    if(!empty($logado)){
        $_SESSION['user'] = $logado[0]['EMAIL'];
        $_SESSION['id'] = $logado[0]['ID'];
        $_SESSION['ROL'] = $logado[0]['ROL'];
    }
    //echo $query;
    echo json_encode($logado[0]);
}

if (isset($_POST['parteAbierto'])){
    $usuario = $_SESSION['user'];
    $ultimoParte = $base->query("SELECT PARTES.ID,HINICIO
    FROM PARTES
    LEFT JOIN TRABAJADORES ON TRABAJADORES.ID = PARTES.TRABAJADOR_ID
    WHERE HINICIO > '".$hoy."' AND HFIN IS NULL AND TRABAJADORES.USUARIO_EMAIL = '".$usuario."'");

    echo json_encode($ultimoParte[0]);

}




if (isset($_POST['datosAgente'])){
        $usuario = $_SESSION['user'];
        $trabajador = $base->query("SELECT *
        FROM TRABAJADORES WHERE TRABAJADORES.USUARIO_EMAIL = '".$usuario."'");
        echo json_encode($trabajador[0]);
    
    }

if (isset($_POST['comunicacionAgente'])){
        $usuario = $_SESSION['user'];
        $comunicacionRespuesta = $base->query("SELECT INCIDENCIAS.*
        FROM INCIDENCIAS
        LEFT JOIN TRABAJADORES ON TRABAJADORES.ID = INCIDENCIAS.TRABAJADOR_ID
        WHERE ESTADO = '2' AND TRABAJADORES.USUARIO_EMAIL = '".$usuario."'");
    
        echo json_encode($comunicacionRespuesta[0]);
    
    }

if (isset($_POST['cerrarParte'])){
    if(!empty($_POST['idParte'])){
        $cerrarParte = $base->modificaID("PARTES", $_POST['idParte'],"HFIN",$ahora);
        echo $cerrarParte;
    }else{
        echo "";
    }
        
    }    


if (isset($_POST['abrirParte'])){
        if(!empty($_POST['firma'])){
            $keys = ['FIRMA','HINICIO','TRABAJADOR_ID'];
            $valores = [$_POST['firma'],$ahora,$_SESSION['id']];
            $cerrarParte = $base->nuevoRegistro("PARTES", $keys,$valores);
            echo "ok";
        }else{
            echo "";
        }
            
        }   
        
if(isset($_REQUEST['desglosePartes'])){
        $listado = $base->query("SELECT PARTES.ID, PARTES.HINICIO, CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO) AS 'NOMBRE', TRABAJADORES.NOMBREEMPRESA, TRABAJADORES.DNI, PARTES.HFIN, PARTES.OBSERVACIONES, PARTES.FIRMA
         FROM PARTES
         LEFT JOIN TRABAJADORES ON TRABAJADORES.ID = PARTES.TRABAJADOR_ID LIMIT 45");
        echo json_encode($listado);
    }

    if(isset($_REQUEST['partesFiltrado'])){
        $query = "SELECT PARTES.ID, PARTES.HINICIO, CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO) AS 'NOMBRE', TRABAJADORES.NOMBREEMPRESA, TRABAJADORES.DNI, PARTES.HFIN, PARTES.OBSERVACIONES, PARTES.FIRMA
        FROM PARTES
        LEFT JOIN TRABAJADORES ON TRABAJADORES.ID = PARTES.TRABAJADOR_ID WHERE  PARTES.HINICIO >= '".$_POST['hinicio']." 00:00:00' and PARTES.HINICIO <= '".$_POST['hfin']." 23:59:00' ";
        $listado = $base->query($query);
        echo json_encode($listado);
    }

if(isset($_REQUEST['listadoTrabajadores'])){
        $listado = $base->query("SELECT ID, CONCAT(NOMBRE,' ',PRIMERAPELLIDO,' ',SEGUNDOAPELLIDO) AS 'NOMBRE' FROM TRABAJADORES");
        echo json_encode($listado);
    }

if (isset($_POST['parteManual'])){
    $hentrada= $_POST['hentrada'];
    $hsalida= $_POST['hsalida'];
    $hentradaFormato = $hentrada;
    $hsalidaFormato = $hsalida;
    $agente=$_POST['agente'];
    $obs=$_POST['obs'];
    $keys = ["HINICIO","HFIN","TRABAJADOR_ID","OBSERVACIONES"];
    $values = [$hentradaFormato,$hsalidaFormato,$agente,$obs];
    $registro = $base->nuevoRegistro("PARTES", $keys,$values);
    echo $registro;

}

if(isset($_REQUEST['listadoPersonal'])){
    $listado = $base->query("SELECT * FROM TRABAJADORES");
    echo json_encode($listado);
}

if(isset($_POST['verificaUsuario'])){
    $listado = $base->query("SELECT * FROM TRABAJADORES where USUARIO_EMAIL = '".$_POST['USUARIO']."'");
    if(empty($listado)){
        echo true;
    }else{
        echo false;
    }
}

if(isset($_REQUEST['addUsuario'])){
    //DATOS FORM
    foreach($_POST as $clave => $valor){
        if($clave == 'PASSWORD' ||  $clave == 'accion' ||  $clave == 'addUsuario'){
            
        }else{
            $key[] = $clave;
            $value[] = $valor;
        }
    }
    //DATOS FOTOS
    if(isset($_FILES)){
        $archivo = $_FILES['FOTO']['name'];
        $tipo = $_FILES['FOTO']['type'];
        $tamano = $_FILES['FOTO']['size'];
        $temp = $_FILES['FOTO']['tmp_name'];
        if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
            //echo 'koFotos';
         }
         else {
            //Si la imagen es correcta en tamaño y tipo
            //Se intenta subir al servidor
            if (move_uploaded_file($temp, '../img/empleados/'.$archivo)) {
                //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                chmod('../img/empleados/'.$archivo, 0777);
                $key[] = 'FOTO';
                $value[] = $archivo;
            }
            else {
               //echo 'koFotos';
            }
          }
       }

     //DATOS USUARIO
     $keyUsuario = ['EMAIL','ACTIVO','PASSWORD','POLITICAPRIVACIDAD'];
     $password = md5($_REQUEST['PASSWORD']);
     $valoresUsuario = [$_REQUEST['USUARIO_EMAIL'],$_REQUEST['ACTIVO'],$password,0];

     //CREAMOS USUARIO
     $usuario = $base->nuevoRegistro("USUARIOS", $keyUsuario,$valoresUsuario);
     $trabajador = $base->nuevoRegistro("TRABAJADORES", $key,$value);

     echo $trabajador;

    }

if(isset($_POST['datosUsuario'])){
        $listado = $base->query("SELECT * FROM TRABAJADORES WHERE USUARIO_EMAIL = '".$_POST['usuario']."'");
        echo json_encode($listado[0]);
    }

if(isset($_POST['modificaUsuario'])){
        //DATOS FORM
        foreach($_POST as $clave => $valor){
            if($clave == 'PASSWORD' ||  $clave == 'accion' ||  $clave == 'USUARIO_EMAIL' ||  $clave == 'modificaUsuario'){
                
            }else{
                $key[] = $clave;
                $value[] = $valor;
            }
        }
        //DATOS FOTOS
        if(isset($_FILES)){
            $archivo = $_FILES['FOTO']['name'];
            $tipo = $_FILES['FOTO']['type'];
            $tamano = $_FILES['FOTO']['size'];
            $temp = $_FILES['FOTO']['tmp_name'];
            if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
                //echo 'koFotos';
             }
             else {
                //Si la imagen es correcta en tamaño y tipo
                //Se intenta subir al servidor
                if (move_uploaded_file($temp, '../img/empleados/'.$archivo)) {
                    //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                    chmod('../img/empleados/'.$archivo, 0777);
                    $key[] = 'FOTO';
                    $value[] = $archivo;
                }
                else {
                   //echo 'koFotos';
                }
              }
           }
    
         //DATOS USUARIO
         if(!empty($_POST['PASSWORD'])){
            $keyUsuario = ['ACTIVO','PASSWORD'];
            $password = md5($_POST['PASSWORD']);
            $valoresUsuario = [$_POST['ACTIVO'],$password];
            $usuario = $base->modificaRegistro("USUARIOS","EMAIL",$_POST['accion'],$keyUsuario,$valoresUsuario);
         }else{
            $keyUsuario = ['ACTIVO'];
            $valoresUsuario = [$_POST['ACTIVO']];
            $usuario = $base->modificaRegistro("USUARIOS","EMAIL",$_POST['accion'],$keyUsuario,$valoresUsuario);
         }
         
    
         //MODIFICA USUARIO
         
         $trabajador = $base->modificaRegistro("TRABAJADORES","USUARIO_EMAIL",$_POST['accion'],$key,$value);
    
         echo $trabajador;
    
        }

if(isset($_POST['addHorario'])){
    $ref = $_POST['ref'];
    $diasUno=$_POST['diasUno'];
    $hent11=$_POST['hent11'];
    $hsal11=$_POST['hsal11'];
    $hent12=$_POST['hent12'];
    $hsal12=$_POST['hsal12'];
    $diasDos=$_POST['diasDos'];
    $hent12=$_POST['hent12'];
    $hsal12=$_POST['hsal12'];
    $hent22=$_POST['hent22'];
    $hsal22=$_POST['hsal22'];

    $keyHorario = ['REF','DS1','HENT11','HSAL11','HENT12','HSAL12','DS2','HENT21','HSAL21','HENT22','HSAL22'];
    $valueHorario = [$ref,$diasUno,$hent11,$hsal11,$hent12,$hsal12,$diasDos,$hent21,$hsal21,$hent22,$hsal22]; 
    $horario = $base->nuevoRegistro("JORNADAS", $keyHorario,$valueHorario);

    echo $ref;
}

if(isset($_POST['existeRefHorario'])){
    $ref = $_POST['ref'];
    $listado = $base->query("SELECT * FROM JORNADAS WHERE REF='".$ref."'");
    if(empty($listado[0][0])){
        echo "ok";
    }else{
        echo "ko";
    }
}

if(isset($_REQUEST['listadoHorarios'])){
    $listado = $base->query("SELECT ID,REF,DS1,HENT11,HSAL11,HENT12,HSAL12,DS2,HENT21,HSAL21,HENT22,HSAL22 FROM JORNADAS");
    echo json_encode($listado);
}

if(isset($_REQUEST['listadoParte'])){
    $listado = $base->query("SELECT * FROM PARTES WHERE LEFT(HINICIO,10) = '".$_POST['fecha']."' 
    AND TRABAJADOR_ID='".$_POST['usuario']."'");
    echo json_encode($listado);
}

if(isset($_REQUEST['listadoTarjetas'])){
    $listado = $base->query("SELECT TARJETAS.ID, TARJETAS.NOMBRE, TARJETAS.NUMERO, 
    (
    SELECT 
    CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO)
    FROM TRABAJADORES WHERE  TRABAJADORES.ID = TARJETAS.USUARIO_ID
    ) AS 'USUARIO'
    
        FROM TARJETAS, TRABAJADORES
    WHERE TRABAJADORES.ID = TARJETAS.ID");
    echo json_encode($listado);
}

if(isset($_POST['addTarjeta'])){
    $nombreTarjeta = $_POST['nombreTarjeta'];
    $usuario=$_POST['usuario'];
    $numero=$_POST['numero'];

    $keyTarjetas = ['NOMBRE','NUMERO','USUARIO_ID'];
    $valueTarjetas = [$nombreTarjeta,$numero,$usuario]; 
    $tarjetaNueva = $base->nuevoRegistro("TARJETAS", $keyTarjetas,$valueTarjetas);

    echo "ok";
}

if (isset($_POST['datosTarjeta'])){
    $tarjeta = $_POST['tarjeta'];
    $tarjetas = $base->query("SELECT *
    FROM TARJETAS WHERE TARJETAS.ID = '".$tarjeta."'");
    echo json_encode($tarjetas[0]);

}

if(isset($_POST['modTarjeta'])){
    $nombreTarjeta = $_POST['nombreTarjeta'];
    $usuario=$_POST['usuario'];
    $numero=$_POST['numero'];
    $idTarjeta = $_POST['idTarjeta'];
    $key = ['NOMBRE','NUMERO','USUARIO_ID'];
    $value = [$nombreTarjeta,$numero,$usuario]; 
    $trabajador = $base->modificaRegistro("TARJETAS","ID",$_POST['idTarjeta'],$key,$value);
    echo "ok";
}

if(isset($_POST['borraTarjeta'])){
    $idTarjeta = $_POST['idTarjeta'];
    $tarjetaKo = $base->query("DELETE FROM TARJETAS WHERE ( ID = ".$idTarjeta.")");
    echo "ok";
}

if(isset($_POST['cargaTarjetasUsuario'])){
    $listado = $base->query("SELECT ID, CONCAT(NOMBRE,' ',NUMERO) AS 'NOMBRE' FROM TARJETAS where USUARIO_ID='".$_POST['usuario_id']."'");
    echo json_encode($listado);
}

if(isset($_POST['addGasto'])){
    //DATOS FORM
    foreach($_POST as $clave => $valor){
        if($clave == 'accion' ||  $clave == 'addGasto' || $clave == 'idGasto' ){
            
        }else{
            $key[] = $clave;
            $value[] = $valor;
        }
    }
    //DATOS FOTOS
    if(isset($_FILES)){
        $archivo = $_FILES['FOTO']['name'];
        $tipo = $_FILES['FOTO']['type'];
        $tamano = $_FILES['FOTO']['size'];
        $temp = $_FILES['FOTO']['tmp_name'];
        if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
            //echo 'koFotos';
         }
         else {
            //Si la imagen es correcta en tamaño y tipo
            //Se intenta subir al servidor
            if (move_uploaded_file($temp, '../img/gasto/'.$archivo)) {
                //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                chmod('../img/gasto/'.$archivo, 0777);
                $key[] = 'URL';
                $value[] = $archivo;
            }
            else {
               //echo 'koFotos';
            }
          }
       }

     //CREAMOS GASTO;
     $gastoRegistro = $base->nuevoRegistro("GASTOS", $key,$value);

     echo $gastoRegistro;

    }

if(isset($_REQUEST['listadoGastos'])){
        $listado = $base->query("SELECT GASTOS.ID, GASTOS.FECHA,
        (SELECT CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO) 
         FROM TRABAJADORES WHERE TRABAJADORES.ID = GASTOS.USUARIO_ID) AS 'USUARIO',
         GASTOS.USUARIO_ID,GASTOS.TARJETA_ID,
         GASTOS.DESCRIPCION,GASTOS.IMPORTE,GASTOS.URL
      FROM GASTOS");
        echo json_encode($listado);
    }

if(isset($_POST['modificaGasto'])){
        //DATOS FORM
        foreach($_POST as $clave => $valor){
            if($clave == 'accion' ||  $clave == 'modificaGasto' || $clave == 'idGasto' ){
                
            }else{
                $key[] = $clave;
                $value[] = $valor;
            }
        }
        //DATOS FOTOS
    if(isset($_FILES)){
        $archivo = $_FILES['FOTO']['name'];
        $tipo = $_FILES['FOTO']['type'];
        $tamano = $_FILES['FOTO']['size'];
        $temp = $_FILES['FOTO']['tmp_name'];
        if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
            //echo 'koFotos';
         }
         else {
            //Si la imagen es correcta en tamaño y tipo
            //Se intenta subir al servidor
            if (move_uploaded_file($temp, '../img/gasto/'.$archivo)) {
                //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                chmod('../img/gasto/'.$archivo, 0777);
                $key[] = 'URL';
                $value[] = $archivo;
            }
            else {
               //echo 'koFotos';
            }
          }
       }
    
    
         //MODIFICA GASTO
         
         $gasto = $base->modificaRegistro("GASTOS","ID",$_POST['idGasto'],$key,$value);
    
         echo $gasto;
    
        }


if(isset($_POST['borraGasto'])){
            $idGasto = $_POST['idGasto'];
            $Ko = $base->query("DELETE FROM GASTOS WHERE ( ID = ".$idGasto.")");
            echo "ok";
        }

    
if(isset($_REQUEST['listadoGastosIncidencias'])){
            $listado = $base->query("SELECT GASTOS.ID, GASTOS.DESCRIPCION,GASTOS.IMPORTE,GASTOS.TARJETA_ID,GASTOS.URL
          FROM GASTOS WHERE GASTOS.FECHA='".$_POST['fecha']."' AND USUARIO_ID='".$_POST['usuario']."'");
            echo json_encode($listado);
        }

        if(isset($_POST['addGasto'])){
    //DATOS FORM
    foreach($_POST as $clave => $valor){
        if($clave == 'accion' ||  $clave == 'addGasto' || $clave == 'idGasto' ){
            
        }else{
            $key[] = $clave;
            $value[] = $valor;
        }
    }
    //DATOS FOTOS
    if(isset($_FILES)){
        $archivo = $_FILES['FOTO']['name'];
        $tipo = $_FILES['FOTO']['type'];
        $tamano = $_FILES['FOTO']['size'];
        $temp = $_FILES['FOTO']['tmp_name'];
        if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
            //echo 'koFotos';
         }
         else {
            //Si la imagen es correcta en tamaño y tipo
            //Se intenta subir al servidor
            if (move_uploaded_file($temp, '../img/gasto/'.$archivo)) {
                //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                chmod('../img/gasto/'.$archivo, 0777);
                $key[] = 'URL';
                $value[] = $archivo;
            }
            else {
               //echo 'koFotos';
            }
          }
       }

     //CREAMOS GASTO;
     $gastoRegistro = $base->nuevoRegistro("GASTOS", $key,$value);

     echo $gastoRegistro;

    }

if(isset($_POST['addComentario'])){
        $parteId = $_POST['parte'];
        $gastoId = $_POST['gasto'];

        //1º NUEVO PARTE
        if($_POST['addParte'] == 'true'){
            $keyParte = ['HINICIO','HFIN','TRABAJADOR_ID'];
            $valParte =[
                $_POST['fechaEntrada'],
                $_POST['fechaSalida'],
                $_POST['USUARIO_ID'],
            ];

            $parteId = $base->nuevoRegistro("PARTES",$keyParte,$valParte);
           
        }

        //2º NUEVO GASTO
        if($_POST['addGasto']== 'true'){
            $rutaGasto = "";
            //DATOS FOTOS
            if(isset($_FILES)){
                $archivo = $_FILES['FOTO']['name'];
                $tipo = $_FILES['FOTO']['type'];
                $tamano = $_FILES['FOTO']['size'];
                $temp = $_FILES['FOTO']['tmp_name'];
                if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
                    //echo 'koFotos';
                }else {
                    //Si la imagen es correcta en tamaño y tipo
                    //Se intenta subir al servidor
                    if (move_uploaded_file($temp, '../img/gasto/'.$archivo)) {
                        //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                        chmod('../img/gasto/'.$archivo, 0777);
                        $rutaGasto = $archivo;
                    }else{
                    //echo 'koFotos';
                    }
                }
                $keyGasto = ['FECHA','TARJETA_ID','USUARIO_ID','DESCRIPCION','IMPORTE','URL'];
                $valoresGasto = [
                    $_POST['FECHA'],
                    $_POST['TARJETA_ID'],
                    $_POST['USUARIO_ID'],
                    $_POST['DESCRIPCION'],
                    $_POST['IMPORTE'],
                    $rutaGasto
                ];
                $gastoId = $base->nuevoRegistro("GASTOS",$keyGasto,$valoresGasto);
                
            }else{
                //INSERTAR GASTOS
            $keyGasto = ['FECHA','TARJETA_ID','USUARIO_ID','DESCRIPCION','IMPORTE',];
            $valoresGasto = [
                $_POST['FECHA'],
                $_POST['TARJETA_ID'],
                $_POST['USUARIO_ID'],
                $_POST['DESCRIPCION'],
                $_POST['IMPORTE'],
            ];
            $gastoId = $base->nuevoRegistro("GASTOS",$keyGasto,$valoresGasto);
            
            }
            

        }

        //3º MOD PARTE
        if($_POST['modParte']){
            $keyParte = ['HFIN','TRABAJADOR_ID'];
            $valParte =[
                $_POST['fechaSalida'],
                $_POST['USUARIO_ID'],
            ];
            $parteMod = $base->modificaRegistro("PARTES","ID",$_POST['parte'],$keyParte,$valParte);
        }

        //4º MOD GASTO
        if($_POST['modGasto']== 'true'){
            $rutaGasto = "";
            //DATOS FOTOS
            if(isset($_FILES)){
                $archivo = $_FILES['FOTO']['name'];
                $tipo = $_FILES['FOTO']['type'];
                $tamano = $_FILES['FOTO']['size'];
                $temp = $_FILES['FOTO']['tmp_name'];
                if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
                    //echo 'koFotos';
                }else {
                    //Si la imagen es correcta en tamaño y tipo
                    //Se intenta subir al servidor
                    if (move_uploaded_file($temp, '../img/gasto/'.$archivo)) {
                        //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
                        chmod('../img/gasto/'.$archivo, 0777);
                        $rutaGasto = $archivo;
                    }else{
                        //echo 'koFotos';
                    }
                }
                 //INSERTAR GASTOS
                $keyGasto = ['TARJETA_ID','USUARIO_ID','DESCRIPCION','IMPORTE','URL'];
                $valoresGasto = [
                    $_POST['TARJETA_ID'],
                    $_POST['USUARIO_ID'],
                    $_POST['DESCRIPCION'],
                    $_POST['IMPORTE'],
                    $archivo
                    ];
                $gastoNuevo = $base->modificaRegistro("GASTOS","ID",$_POST['gasto'],$keyGasto,$valoresGasto);

            }else{
                //INSERTAR GASTOS
                $keyGasto = ['TARJETA_ID','USUARIO_ID','DESCRIPCION','IMPORTE'];
                $valoresGasto = [
                    $_POST['TARJETA_ID'],
                    $_POST['USUARIO_ID'],
                    $_POST['DESCRIPCION'],
                    $_POST['IMPORTE'],
                ];
                $gastoNuevo = $base->modificaRegistro("GASTOS","ID",$_POST['gasto'],$keyGasto,$valoresGasto);

            }
           
        }

        //5º VEMOS SI ES NUEVO
        if($_POST['incidenciaId'] == 'nuevo'){
            //CREAMOS INCIDENCIA
            $keyIncidencia=['ESTADO','FECHA','USUARIO_ID','PARTE_ID','GASTO_ID','TIPO','MOTIVO'];
            $valIncidencia=[
                            $_POST['estado'],
                            $_POST['FECHA'],
                            $_POST['USUARIO_ID'],
                            $parteId,
                            $gastoId,
                            $_POST['tipo'],
                            $_POST['MOTIVO']
                            ];
            $incId = $base->nuevoRegistro("INCIDENCIAS",$keyIncidencia,$valIncidencia);
        }else{
            $keyIncidencia=['ESTADO','FECHA','USUARIO_ID','PARTE_ID','GASTO_ID','TIPO','MOTIVO'];
            $valIncidencia=[
                            $_POST['estado'],
                            $_POST['FECHA'],
                            $_POST['USUARIO_ID'],
                            $parteId,
                            $gastoId,
                            $_POST['tipo'],
                            $_POST['MOTIVO']
                            ];
            $incId = $_POST['incidenciaId'];
            $incMod = $base->modificaRegistro("INCIDENCIAS","ID",$incId,$keyIncidencia,$valIncidencia); 
        }
        //6º AÑADE ITEM
        $keyItems = ['INCIDENCIA_ID','DESCRIPCION','USUARIO_ID'];
        $valItems =[
            $incId,
            $_POST['nuevoItem'],
            $_SESSION['id']
        ];

        $item = $base->nuevoRegistro("ITEM_INCIDENCIAS",$keyItems,$valItems);

        echo $incId;
    
        }

    
if(isset($_REQUEST['listadoIncidencias'])){
    
            $listado = $base->query("SELECT *,
            (SELECT CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO) 
             FROM TRABAJADORES WHERE TRABAJADORES.ID = INCIDENCIAS.USUARIO_ID) AS 'USUARIO'
            FROM INCIDENCIAS");

            echo json_encode($listado);
}


if(isset($_REQUEST['listadoItems'])){
    
            $listado = $base->query("SELECT *,
            (SELECT CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO) 
             FROM TRABAJADORES WHERE TRABAJADORES.ID = ITEM_INCIDENCIAS.USUARIO_ID) AS 'USUARIO'
            FROM ITEM_INCIDENCIAS WHERE ITEM_INCIDENCIAS.INCIDENCIA_ID = ".$_POST['IncId']);

            echo json_encode($listado);
        }


if(isset($_REQUEST['listadoHorariosEmpleados'])){
            $listado = $base->query("SELECT * FROM JORNADAS");
            echo json_encode($listado);
        }


if(isset($_POST['cambiaHorario'])){
            $empleados = explode(",",$_POST['seleccion']);
            $horario_id = $_POST['horario_id'];
            foreach($empleados as $empleado){
                $mod = $base->query("UPDATE `proyectoDBD`.`TRABAJADORES` SET `HORARIO`='".$horario_id."' WHERE  `ID`=".$empleado);
            } 
            echo "ok";
        }

if(isset($_REQUEST['personalActivo'])){
            $listado = $base->query("SELECT PARTES.HINICIO AS 'HINICIO',
            (SELECT CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO) 
              FROM TRABAJADORES WHERE PARTES.TRABAJADOR_ID = TRABAJADORES.ID ) AS 'NOMBRE',
            
            (SELECT TRABAJADORES.FOTO
              FROM TRABAJADORES WHERE PARTES.TRABAJADOR_ID = TRABAJADORES.ID ) AS 'FOTO'   
            
            FROM PARTES 
            
            WHERE  LEFT(PARTES.HINICIO, 10) = '$hoyCorto' AND PARTES.HFIN IS NULL");
            echo json_encode($listado);
        }


if(isset($_REQUEST['listadoIncidenciasAbiertas'])){
    
            $listado = $base->query("SELECT *,
            (
           SELECT CONCAT(TRABAJADORES.NOMBRE,' ',TRABAJADORES.PRIMERAPELLIDO,' ',TRABAJADORES.SEGUNDOAPELLIDO)
           FROM TRABAJADORES
           WHERE TRABAJADORES.ID = INCIDENCIAS.USUARIO_ID) AS 'USUARIO'
           FROM INCIDENCIAS
           
           WHERE INCIDENCIAS.ESTADO = 'abierto'
           ");

            echo json_encode($listado);

        
}

if(isset($_REQUEST['ListadoFiltrado'])){


    $listado = $base-> query("SELECT * FROM `PARTES` WHERE HINICIO >= '".$_POST["fecha_inicio"]."' and HINICIO <= '".$_POST["fecha_final"]."'");

    echo json_encode($listado);
}