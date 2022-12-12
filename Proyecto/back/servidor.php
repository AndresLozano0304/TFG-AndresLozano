<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
//header('content-type: application/json; charset=utf-8');

//LIBRERIAS, CLASES Y VARIABLES
include 'BD.php';
//require 'vendor/autoload.php';

date_default_timezone_set("Europe/Madrid");

$base = new BD();
$hoy = date("Y-m-d 00:00:00");
$ahora = date("Y-m-d H:i:s");
$hoyCorto = date("Y-m-d");
$ayer = date("Y-m-d",strtotime($hoyCorto."- 1 day"));
$fechaUnix = time();
//FUNCIONES

//SESION
ini_set('session.cache_expire', 200000);
ini_set('session.cache_limiter', 'none');
ini_set('session.cookie_lifetime', 2000000);
ini_set('session.gc_maxlifetime', 200000); //el mas importante


session_start();

$base  = new BD();

//REQUEST
//ver si hay session abierta
if(isset($_POST['session'])){
    if(!empty($_SESSION)){
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, USUARIOS.TOKEN 
        FROM USUARIOS 
        LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
        WHERE EMAIL = '".$_SESSION['user']."'");
        echo json_encode($logado[0]);
    }else if(!empty($_POST['acceso'])){
            $datosToken = explode(",",$_POST['acceso']);
            //fecha
            if($datosToken[0]<$fechaUnix+15*24*60*60*60){
                echo "token caducado";
            }else{
                $logado = $base->query("SELECT 
                (SELECT MAX(TOKEN.USUARIO_ID) FROM TOKEN WHERE TOKEN.TOKEN = '".$datosToken[1]."' GROUP BY USUARIO_ID) AS ID, 
                USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA,
                (SELECT MAX(TOKEN.TOKEN) FROM TOKEN WHERE TOKEN.TOKEN = '".$datosToken[1]."' GROUP BY USUARIO_ID) AS TOKEN 
                FROM USUARIOS
                LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
                WHERE TRABAJADORES.ID = (SELECT MAX(TOKEN.USUARIO_ID) FROM TOKEN WHERE TOKEN.TOKEN = '".$datosToken[1]."' GROUP BY USUARIO_ID)");
                // $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, USUARIOS.TOKEN 
                // FROM USUARIOS 
                // LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
                // WHERE  TRABAJADORES.ID = '".$datosToken[1]."'");
                echo json_encode($logado[0]);
            }
        }else{
          echo "ni token ni session";
        }
}
        

//Cerrar Session
if(isset($_POST['cerrarSession'])){
    session_destroy();
    $token = $_POST['acceso'];
    $varia = $base->query("UPDATE TRABAJADORES SET TOKEN = '' WHERE TOKEN = ' ".$token."'");
    echo "ok";
}


//login
if(isset($_POST['login'])){
    $usuario = $_POST['usuario'];
    $pass =  md5($_POST['pass']);
    $base  = new BD();
    $query = "SELECT TRABAJADORES.ID, USUARIOS.ID as 'IDUSU', USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA 
    FROM USUARIOS 
    LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
    WHERE USUARIOS.ACTIVO = 1 and EMAIL = '$usuario' AND PASSWORD = '$pass'";
    $logado = $base->query($query);
    //CREAR TOKEN
    $token = $fechaUnix.",".$logado[0]['ID'].",".random_int(0,9).random_int(0,9).random_int(0,9).random_int(0,9).random_int(0,9).random_int(0,9);
    $base->nuevoRegistro("TOKEN", ['TOKEN','USUARIO_ID'],[$token,$logado[0]['ID']]);
    $logado[0]['TOKEN'] = $token; 
    if(!empty($logado)){
        $_SESSION['user'] = $logado[0]['EMAIL'];
        $_SESSION['id'] = $logado[0]['ID'];
    }
    //echo $query;
    echo json_encode($logado[0]);
}

if (isset($_POST['parteAbierto'])){
    if (empty($_SESSION['user'])){
        $token = $_POST['acceso'];
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, TOKEN.TOKEN
        FROM USUARIOS 
        LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
        LEFT JOIN TOKEN ON TOKEN.USUARIO_ID = TRABAJADORES.ID
        WHERE USUARIOS.ACTIVO = 1 AND TOKEN.TOKEN = (SELECT TOKEN.TOKEN FROM TOKEN WHERE TOKEN.USUARIO_ID = TRABAJADORES.ID AND TOKEN.TOKEN = '".$token."' GROUP BY TOKEN)");
        $usuario =  $logado[0]['EMAIL'];
    }else{
        $usuario = $_SESSION['user'];
    }
    
    $ultimoParte = $base->query("SELECT PARTES.ID,HINICIO
    FROM PARTES
    LEFT JOIN TRABAJADORES ON TRABAJADORES.ID = PARTES.TRABAJADOR_ID
    WHERE HINICIO > '".$hoy."' AND HFIN IS NULL AND TRABAJADORES.USUARIO_EMAIL = '".$usuario."'");

    echo json_encode($ultimoParte[0]);

}




if (isset($_POST['datosAgente'])){
    if (empty($_SESSION['user'])){
        $token = $_POST['acceso'];
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, TOKEN.TOKEN
        FROM USUARIOS 
        LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
        LEFT JOIN TOKEN ON TOKEN.USUARIO_ID = TRABAJADORES.ID
        WHERE USUARIOS.ACTIVO = 1 AND TOKEN.TOKEN = (SELECT TOKEN.TOKEN FROM TOKEN WHERE TOKEN.USUARIO_ID = TRABAJADORES.ID AND TOKEN.TOKEN = '".$token."' GROUP BY TOKEN)");
        $usuario =  $logado[0]['EMAIL'];
    }else{
        $usuario = $_SESSION['user'];
    }
        $trabajador = $base->query("SELECT *
        FROM TRABAJADORES WHERE TRABAJADORES.USUARIO_EMAIL = '".$usuario."'");
        echo json_encode($trabajador[0]);
    
    }

if (isset($_POST['comunicacionAgente'])){
    if (empty($_SESSION['user'])){
        $token = $_POST['acceso'];
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, TOKEN.TOKEN
        FROM USUARIOS 
        LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
        LEFT JOIN TOKEN ON TOKEN.USUARIO_ID = TRABAJADORES.ID
        WHERE USUARIOS.ACTIVO = 1 AND TOKEN.TOKEN = (SELECT TOKEN.TOKEN FROM TOKEN WHERE TOKEN.USUARIO_ID = TRABAJADORES.ID AND TOKEN.TOKEN = '".$token."' GROUP BY TOKEN)");
        $usuario =  $logado[0]['EMAIL'];
    }else{
        $usuario = $_SESSION['user'];
    }
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
    if (empty($_SESSION['user'])){
        $token = $_POST['acceso'];
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, TOKEN.TOKEN
        FROM USUARIOS 
        LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
        LEFT JOIN TOKEN ON TOKEN.USUARIO_ID = TRABAJADORES.ID
        WHERE USUARIOS.ACTIVO = 1 AND TOKEN.TOKEN = (SELECT TOKEN.TOKEN FROM TOKEN WHERE TOKEN.USUARIO_ID = TRABAJADORES.ID AND TOKEN.TOKEN = '".$token."' GROUP BY TOKEN)");
        $usuario =  $logado[0]['ID'];
    }else{
        $usuario = $_SESSION['id'];
    }
        if(!empty($_POST['firma'])){
            $keys = ['FIRMA','HINICIO','TRABAJADOR_ID','DIRECCION'];
            $valores = [$_POST['firma'],$ahora,$usuario,$_POST['direccion']];
            $cerrarParte = $base->nuevoRegistro("PARTES", $keys,$valores);
            echo "ok";
        }else{
            echo "";
        }
    }
               
if (isset($_POST['incidenciaUsuario'])){
    if (empty($_SESSION['user'])){
        $token = $_POST['acceso'];
        $logado = $base->query("SELECT TRABAJADORES.ID, USUARIOS.EMAIL, TRABAJADORES.NOMBRE, TRABAJADORES.PRIMERAPELLIDO, TRABAJADORES.NOMBREEMPRESA, TOKEN.TOKEN
        FROM USUARIOS 
        LEFT JOIN TRABAJADORES ON TRABAJADORES.USUARIO_EMAIL = USUARIOS.EMAIL
        LEFT JOIN TOKEN ON TOKEN.USUARIO_ID = TRABAJADORES.ID
        WHERE USUARIOS.ACTIVO = 1 AND TOKEN.TOKEN = (SELECT TOKEN.TOKEN FROM TOKEN WHERE TOKEN.USUARIO_ID = TRABAJADORES.ID AND TOKEN.TOKEN = '".$token."' GROUP BY TOKEN)");
        $usuario =  $logado[0]['ID'];
    }else{
        $usuario = $_SESSION['id'];
    }
    //INCIDENCIA
    $keys = ['ESTADO','FECHA','USUARIO_ID','TIPO','MOTIVO'];
    $valores = [
        "abierto",
        $_POST['fecha'],
        $usuario,
        "usuario",
        $_POST['motivo']
    ];
    $incidenciaOk = $base->nuevoRegistro("INCIDENCIAS", $keys,$valores);
    //ITEM
    $keysItem = ['INCIDENCIA_ID','DESCRIPCION','USUARIO_ID'];
    $valoresItem = [
        $incidenciaOk,
        $_POST['descripcion'],
        $_SESSION['id']
    ];
    
    $itemOk = $base->nuevoRegistro("ITEM_INCIDENCIAS", $keysItem,$valoresItem);
    
    echo $incidenciaOk;

}  
   

//CRON

if(isset($_GET['olvidos'])){
    $listadoOlvidados = $base->query("SELECT * FROM PARTES WHERE LEFT(HINICIO,10) = '$ayer' AND HFIN IS NULL");
    foreach($listadoOlvidados as $olvido){
        //INCIDENCIA
    $keys = ['ESTADO','FECHA','USUARIO_ID','TIPO','MOTIVO'];
    $valores = [
        "abierto",
        $ayer,
        $olvido['TRABAJADOR_ID'],
        "cron",
        "Parte sin cerrar"
    ];
    $incidenciaOk = $base->nuevoRegistro("INCIDENCIAS", $keys,$valores);
    //ITEM
    $keysItem = ['INCIDENCIA_ID','DESCRIPCION','USUARIO_ID'];
    $valoresItem = [
        $incidenciaOk,
        "apertura automático",
        "15"
    ];
    
    $itemOk = $base->nuevoRegistro("ITEM_INCIDENCIAS", $keysItem,$valoresItem);
    
    echo $incidenciaOk;
    }
}