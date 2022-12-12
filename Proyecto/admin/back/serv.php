<?php
//LIBRERIAS, CLASES Y VARIABLES
include '../class/BD.php';
require 'vendor/autoload.php';
require '../class/word.php';

$urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
$base = new BD();
$usuario = $_POST['login'];
$key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
$keyAPI = $key[0][0];

//FUNCIONES
session_start();

function callAPI($method, $apikey, $url, $data = false)
{
    $curl = curl_init();
    $httpheader = ['DOLAPIKEY: '.$apikey];

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            $httpheader[] = "Content-Type:application/json";

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            break;

        case "PUT":
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            $httpheader[] = "Content-Type:application/json";

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
           break;   
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    //    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function creaCA($datos){
    $salida;
    $soc = $datos['clienteId'];
    $sociedades = json_decode($datos['sociedades'],true);
    $base = new BD();
    $partes = json_decode($datos['partes'],true);
    $usuario = $datos['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    //0º CREAR CA
    if($datos['accion'] == 'presupuesto'){
        $fechaContrato = $datos['fecha'];
    }else{
        $fechaContrato = $datos['anioContrato']."-".$datos['mesContrato']."-".$datos['diaContrato'];
    }
    $tipo = $datos['tipoContrato'];
    $fecha = $datos['fecha'];
    $clausulas = $datos['clausulasAdicionales'];
    $referencia = $datos['referencia'];
    $partes = $datos['partes'];
    $keys = ['fecha','clausulasAdicionales','tipo','fechaActivo','referencia','partes'];
    $valores = [$fechaContrato,$clausulas,$tipo,$fecha,$referencia,$partes];
    $CA = $base->nuevoRegistro("vol_CA", $keys,$valores);
    $salida['id']= $CA;
    $salida['referencia'] = $referencia;
    //1 CREAR SOCIEDADES
     //1.1.1 SOCIEDAD PPAL 
    $keys = ['fk_CA','fk_soc'];
    $valores = [$CA,$soc];
    $CA_soc = $base->nuevoRegistro("vol_CA_soc", $keys,$valores);
    $salida['soc'][$soc]['id']= $soc;
    //1.1.2 CREAR PAGO
     //1.1.2.1 por Banco
        if($datos['banco'] == 'on'){
            //IMPORTE
            $importe = $datos['importeBanco'];
            //FORMA
            $formaPago = $datos['formaPagoBanco'];
            //IBAN
            if(isset($datos['ibanNuevo']) && $datos['ibanNuevo'] != ''){
                //Realizamos datos para API
                $datosApi['label'] = "CASTILLO";
                $datosApi['bank'] = "CASTILLO";
                $datosApi['bic'] = "CASTILLO";
                $datosApi['iban'] = $datos['ibanNuevo'];
                $datosString = json_encode($datosApi);
                //Creamos iban
                $iban = json_decode(callAPI("POST", $keyAPI, $urlAPI."thirdparties/".$soc."/bankaccounts",$datosString),true)['id'];
                $fk_CA_emp = $iban;

            }else{
                $fk_CA_emp = $datos['ibanCliente'];
            }
            //MESES
            $Enero = isset($datos['EneroBanco'])?1:0;
            $Febrero = isset($datos['FebreroBanco'])?1:0;
            $Marzo = isset($datos['MarzoBanco'])?1:0;
            $Abril = isset($datos['AbrilBanco'])?1:0;
            $Mayo = isset($datos['MayoBanco'])?1:0;
            $Junio = isset($datos['JunioBanco'])?1:0;
            $Julio = isset($datos['JulioBanco'])?1:0;
            $Agosto = isset($datos['AgostoBanco'])?1:0;
            $Septiembre = isset($datos['SeptiembreBanco'])?1:0;
            $Octubre = isset($datos['OctubreBanco'])?1:0;
            $Noviembre = isset($datos['NoviembreBanco'])?1:0;
            $Diciembre = isset($datos['DiciembreBanco'])?1:0;
            $keys = ['fk_CA','tipo','fk_iban_emp','formaPago','importe','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            $valores = [$CA_soc,'banco',$fk_CA_emp,$formaPago,$importe,$Enero,$Febrero,$Marzo,$Abril,$Mayo,$Junio,$Julio,$Agosto,$Septiembre,$Octubre,$Noviembre,$Diciembre];
            $pagoBanco = $base->nuevoRegistro("vol_CA_pago", $keys,$valores);
            $salida['soc'][$soc]['pago']['banco'] = $pagoBanco;
        }
    //1.1.2.2 EFECTIVO

        if($datos['efectivo'] == 'on'){
            //IMPORTE
            $importe = $datos['importeEfectivo'];
            //FORMA
            $formaPago = $datos['formaPagoEfectivo'];
            //MESES
            $Enero = isset($datos['EneroEfectivo'])?1:0;
            $Febrero = isset($datos['FebreroEfectivo'])?1:0;
            $Marzo = isset($datos['MarzoEfectivo'])?1:0;
            $Abril = isset($datos['AbrilEfectivo'])?1:0;
            $Mayo = isset($datos['MayoEfectivo'])?1:0;
            $Junio = isset($datos['JunioEfectivo'])?1:0;
            $Julio = isset($datos['JulioEfectivo'])?1:0;
            $Agosto = isset($datos['AgostoEfectivo'])?1:0;
            $Septiembre = isset($datos['SeptiembreEfectivo'])?1:0;
            $Octubre = isset($datos['OctubreEfectivo'])?1:0;
            $Noviembre = isset($datos['NoviembreEfectivo'])?1:0;
            $Diciembre = isset($datos['DiciembreEfectivo'])?1:0;
            $keys = ['fk_CA','tipo','formaPago','importe','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            $valores = [$CA_soc,'efectivo',$formaPago,$importe,$Enero,$Febrero,$Marzo,$Abril,$Mayo,$Junio,$Julio,$Agosto,$Septiembre,$Octubre,$Noviembre,$Diciembre];
            $pagoEfectivo = $base->nuevoRegistro("vol_CA_pago", $keys,$valores);
            $salida['soc'][$soc]['pago']['efectivo'] = $pagoEfectivo;
        }
    //1.1.2.3 TRANSFERENCIA

        if($datos['transferencia'] == 'on'){
            //IMPORTE
            $importe = $datos['importeTransferencia'];
            //FORMA
            $formaPago = $datos['formaPagoTransferencia'];
            //MESES
            $Enero = isset($datos['EneroTransferencia'])?1:0;
            $Febrero = isset($datos['FebreroTransferencia'])?1:0;
            $Marzo = isset($datos['MarzoTransferencia'])?1:0;
            $Abril = isset($datos['AbrilTransferencia'])?1:0;
            $Mayo = isset($datos['MayoTransferencia'])?1:0;
            $Junio = isset($datos['JunioTransferencia'])?1:0;
            $Julio = isset($datos['JulioTransferencia'])?1:0;
            $Agosto = isset($datos['AgostoTransferencia'])?1:0;
            $Septiembre = isset($datos['SeptiembreTransferencia'])?1:0;
            $Octubre = isset($datos['OctubreTransferencia'])?1:0;
            $Noviembre = isset($datos['NoviembreTransferencia'])?1:0;
            $Diciembre = isset($datos['DiciembreTransferencia'])?1:0;

            //IBAN CASTILLO
            $ibanCastillo = $datos['isbnTransferencia'];
            $keys = ['fk_CA','tipo','fk_iban_soc','formaPago','importe','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            $valores = [$CA_soc,'transferencia',$ibanCastillo,$formaPago,$importe,$Enero,$Febrero,$Marzo,$Abril,$Mayo,$Junio,$Julio,$Agosto,$Septiembre,$Octubre,$Noviembre,$Diciembre];
            $pagoTransferencia = $base->nuevoRegistro("vol_CA_pago", $keys,$valores);
            $salida['soc'][$soc]['pago']['transferencia'] = $pagoTransferencia;
        }

    //1.1.A OTRAS SOCIEDADES
        foreach($sociedades as $socOtro){
            $salida['soc'][$socOtro]['id'] = $socOtro;
            $keys = ['fk_CA','fk_soc'];
            $valores = [$CA,$socOtro];
            $CA_socOtro = $base->nuevoRegistro("vol_CA_soc", $keys,$valores);
            //1.1.A.2 CREAR PAGO
            //1.1.A.2.1 por Banco
            if($datos['banco'.$socOtro] == 'on'){
            //IMPORTE
            $importe = $datos['importeBanco'.$socOtro];
            //FORMA
            $formaPago = $datos['formaPagoBanco'.$socOtro];
            //IBAN
            if(isset($datos['ibanNuevo'.$socOtro]) && $datos['ibanNuevo'.$socOtro] != ''){
                //Realizamos datos para API
                $datosApi['label'] = "CASTILLO";
                $datosApi['bank'] = "CASTILLO";
                $datosApi['bic'] = "CASTILLO";
                $datosApi['iban'] = $datos['ibanNuevo'.$socOtro];
                $datosString = json_encode($datosApi);
                //Creamos iban
                $iban = json_decode(callAPI("POST", $keyAPI, $urlAPI."thirdparties/".$socOtro."/bankaccounts",$datosString),true)['id'];
                $fk_CA_emp = $iban;

            }else{
                $fk_CA_emp = $datos['ibanCliente'.$socOtros];
            }
            //MESES
            $Enero = isset($datos['EneroBanco'.$socOtro])?1:0;
            $Febrero = isset($datos['FebreroBanco'.$socOtro])?1:0;
            $Marzo = isset($datos['MarzoBanco'.$socOtro])?1:0;
            $Abril = isset($datos['AbrilBanco'.$socOtro])?1:0;
            $Mayo = isset($datos['MayoBanco'.$socOtro])?1:0;
            $Junio = isset($datos['JunioBanco'.$socOtro])?1:0;
            $Julio = isset($datos['JulioBanco'.$socOtro])?1:0;
            $Agosto = isset($datos['AgostoBanco'.$socOtro])?1:0;
            $Septiembre = isset($datos['SeptiembreBanco'.$socOtro])?1:0;
            $Octubre = isset($datos['OctubreBanco'.$socOtro])?1:0;
            $Noviembre = isset($datos['NoviembreBanco'.$socOtro])?1:0;
            $Diciembre = isset($datos['DiciembreBanco'.$socOtro])?1:0;
            $keys = ['fk_CA','tipo','fk_iban_emp','formaPago','importe','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            $valores = [$CA_socOtro,'banco',$fk_CA_emp,$formaPago,$importe,$Enero,$Febrero,$Marzo,$Abril,$Mayo,$Junio,$Julio,$Agosto,$Septiembre,$Octubre,$Noviembre,$Diciembre];
            $pagoBanco = $base->nuevoRegistro("vol_CA_pago", $keys,$valores);
            $salida['soc'][$socOtro]['pago']['banco'] = $pagoBanco;
        }
            //1.1.2.2 EFECTIVO
            if($datos['efectivo'.$socOtro] == 'on'){
                //IMPORTE
                $importe = $datos['importeEfectivo'.$socOtro];
                //FORMA
                $formaPago = $datos['formaPagoEfectivo'.$socOtro];
                //MESES
                $Enero = isset($datos['EneroEfectivo'.$socOtro])?1:0;
                $Febrero = isset($datos['FebreroEfectivo'.$socOtro])?1:0;
                $Marzo = isset($datos['MarzoEfectivo'.$socOtro])?1:0;
                $Abril = isset($datos['AbrilEfectivo'.$socOtro])?1:0;
                $Mayo = isset($datos['MayoEfectivo'.$socOtro])?1:0;
                $Junio = isset($datos['JunioEfectivo'.$socOtro])?1:0;
                $Julio = isset($datos['JulioEfectivo'.$socOtro])?1:0;
                $Agosto = isset($datos['AgostoEfectivo'.$socOtro])?1:0;
                $Septiembre = isset($datos['SeptiembreEfectivo'.$socOtro])?1:0;
                $Octubre = isset($datos['OctubreEfectivo'.$socOtro])?1:0;
                $Noviembre = isset($datos['NoviembreEfectivo'.$socOtro])?1:0;
                $Diciembre = isset($datos['DiciembreEfectivo'.$socOtro])?1:0;
                $keys = ['fk_CA','tipo','formaPago','importe','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                $valores = [$CA_socOtro,'efectivo',$formaPago,$importe,$Enero,$Febrero,$Marzo,$Abril,$Mayo,$Junio,$Julio,$Agosto,$Septiembre,$Octubre,$Noviembre,$Diciembre];
                $pagoEfectivo = $base->nuevoRegistro("vol_CA_pago", $keys,$valores);
                
                $salida['soc'][$socOtro]['pago']['efectivo'] = $pagoEfectivo;
        }
    //1.1.2.3 TRANSFERENCIA

        if($datos['transferencia'.$socOtro] == 'on'){
            //IMPORTE
            $importe = $datos['importeTransferencia'.$socOtro];
            //FORMA
            $formaPago = $datos['formaPagoTransferencia'.$socOtro];
            //MESES
            $Enero = isset($datos['EneroTransferencia'.$socOtro])?1:0;
            $Febrero = isset($datos['FebreroTransferencia'.$socOtro])?1:0;
            $Marzo = isset($datos['MarzoTransferencia'.$socOtro])?1:0;
            $Abril = isset($datos['AbrilTransferencia'.$socOtro])?1:0;
            $Mayo = isset($datos['MayoTransferencia'.$socOtro])?1:0;
            $Junio = isset($datos['JunioTransferencia'.$socOtro])?1:0;
            $Julio = isset($datos['JulioTransferencia'.$socOtro])?1:0;
            $Agosto = isset($datos['AgostoTransferencia'.$socOtro])?1:0;
            $Septiembre = isset($datos['SeptiembreTransferencia'.$socOtro])?1:0;
            $Octubre = isset($datos['OctubreTransferencia'.$socOtro])?1:0;
            $Noviembre = isset($datos['NoviembreTransferencia'.$socOtro])?1:0;
            $Diciembre = isset($datos['DiciembreTransferencia'.$socOtro])?1:0;

            //IBAN CASTILLO
            $ibanCastillo = $datos['isbnTransferencia'.$socOtro];
            $keys = ['fk_CA','tipo','fk_iban_soc','formaPago','importe','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            $valores = [$CA_socOtro,'transferencia',$ibanCastillo,$formaPago,$importe,$Enero,$Febrero,$Marzo,$Abril,$Mayo,$Junio,$Julio,$Agosto,$Septiembre,$Octubre,$Noviembre,$Diciembre];
            $pagoTransferencia = $base->nuevoRegistro("vol_CA_pago", $keys,$valores);
            $salida['soc'][$socOtro]['pago']['transferencia'] = $pagoTransferencia;
        }

        }


    
    //3º ALCANCES
    //3.1 ALCANTARILLADO
       //3.1.1. CREAR CA-ALC
       if (
           (isset($datos['redSubtCant']) && $datos['redSubtCant'] !=0) || 
           (isset($datos['redAerCant']) && $datos['redAerCant'] !=0) || 
           (isset($datos['redBomCant']) && $datos['redBomCant'] !=0) 
        ){
            $keys = ['fk_CA','camion','furgon','equiposObs','obsAlcant'];
            $valores = [$CA,$datos['camion'],$datos['furgon'],$datos['equiposObs'],$datos['obsAlcant']];
            $CA_Alc = $base->nuevoRegistro("vol_CA_alc", $keys,$valores);
            $salida['alcantarillado']['id'] = $CA_Alc;
      }
       //3.1.2 CREAR REDES
            //3.1.2.1 REDES SUBTERRANEAS
            if (isset($datos['redSubtCant']) && $datos['redSubtCant'] !=0){
                $keys = ['fk_CA_alc','tipo','arqSif','arqMue','cantidad','arqGra','redObs','arqReg','rev','sum','tub','urg'];
                $valores =[
                    $CA_Alc,
                    'Subt',
                    $datos['redSubtArqSif'],
                    $datos['redSubtArqMue'],
                    $datos['redSubtCant'],
                    $datos['redSubtGra'],
                    $datos['redSubtObs'],
                    $datos['redSubtReg'],
                    $datos['redSubtRev'],
                    $datos['redSubtSum'],
                    $datos['redSubtTub'],
                    $datos['redSubtUrg']
                ];
                $CA_Alc_redSubt = $base->nuevoRegistro("vol_CA_red", $keys,$valores);
                $salida['alcantarillado']['subt'] = $CA_Alc_redSubt;
            }
            //3.1.2.2 REDES AEREAS
            if (isset($datos['redAerCant']) && $datos['redAerCant'] >0){
                $keys = ['fk_CA_alc','tipo','arqSif','arqMue','cantidad','arqGra','redObs','rev','sum','tub','urg'];
                $valores =[
                    $CA_Alc,
                    'Aer',
                    $datos['redAerArqSif'],
                    $datos['redAerArqMue'],
                    $datos['redAerCant'],
                    $datos['redAerGra'],
                    $datos['redAerObs'],
                    $datos['redAerRev'],
                    $datos['redAerSum'],
                    $datos['redAerTub'],
                    $datos['redAerUrg']
                ];
                $CA_Alc_redAer = $base->nuevoRegistro("vol_CA_red", $keys,$valores);
                $salida['alcantarillado']['aer'] = $CA_Alc_redAer;
            }
             //3.1.2.3 REDES BOMBEO
             if (isset($datos['redBomCant']) && $datos['redBomCant'] >0){
                $keys = ['fk_CA_alc','tipo','arqBom','arqMue','cantidad','arqGra','redObs','arqRej','rev','sum','tub','urg'];
                $valores =[
                    $CA_Alc,
                    'Bom',
                    $datos['redBomArqBom'],
                    $datos['redBomArqPas'],
                    $datos['redBomCant'],
                    $datos['redBomArqDec'],
                    $datos['redBomObs'],
                    $datos['redBomArqRej'],
                    $datos['redBomRev'],
                    $datos['redBomSum'],
                    $datos['redBomTub'],
                    $datos['redBomUrg']
                ];
                $CA_Alc_redBom = $base->nuevoRegistro("vol_CA_red", $keys,$valores);
                $salida['alcantarillado']['bom'] = $CA_Alc_redBom;
            }
    //3.2 PLAGAS
       //3.2.1 DESINSECTACIÓN - CREAR PLAGAS
       if (
        (isset($datos['alemanaCant']) && $datos['alemanaCant'] !=0) || 
        (isset($datos['americanaCant']) && $datos['americanaCant'] !=0) || 
        (isset($datos['faraonCant']) && $datos['faraonCant'] !=0) ||
        (isset($datos['argentinaCant']) && $datos['argentinaCant'] !=0) ||
        (isset($datos['jardinCant']) && $datos['jardinCant'] !=0) ||
        (isset($datos['insecOtrosCant']) && $datos['insecOtrosCant'] !=0)
        ){
            $keys = ['fk_CA','alcance','obs','otrosTratamiento','tipo'];
            $valores = [
                $CA,
                $datos['desinAlc'],
                $datos['desinObs'],
                "",
                "desinsectacion"
                ];
            $CA_Plaga = $base->nuevoRegistro("vol_CA_plaga", $keys,$valores);
            $salida['plaga']['desin'] = $CA_Plaga;
        }
       //3.2.2 DESINSECTACION - CREAR ESPECIE
        //3.2.2.1 ALEMANA
        if (isset($datos['alemanaCant']) && $datos['alemanaCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['alemanaCant'],
                'alemana',
                $datos['alemanaRev'],
                $datos['alemanaReinf'],
                ""
            ];
            $alemana_desin = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desin']['alemana'] = $alemana_desin;
        }

        //3.2.2.2 AMERICANA
        if (isset($datos['americanaCant']) && $datos['americanaCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['americanaCant'],
                'americana',
                $datos['americanaRev'],
                $datos['americanaReinf'],
                ""
            ];
            $americana_desin = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desin']['americana'] = $americana_desin;
        }
        //3.2.2.3 faraon
        if (isset($datos['faraonCant']) && $datos['faraonCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['faraonCant'],
                'faraon',
                $datos['faraonRev'],
                $datos['faraonReinf'],
                ""
            ];
            $faraon_desin = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desin']['faraon'] = $faraon_desin;
        }
        //3.2.2.4 jardin
        if (isset($datos['jardinCant']) && $datos['jardinCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['jardinCant'],
                'jardin',
                $datos['jardinRev'],
                $datos['jardinReinf'],
                ""
            ];
            $jardin_desin = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desin']['jardin'] = $jardin_desin;
        }
        //3.2.2.5 argentina
        if (isset($datos['argentinaCant']) && $datos['argentinaCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['argentinaCant'],
                'argentina',
                $datos['argentinaRev'],
                $datos['argentinaReinf'],
                ""
            ];
            $argentina_desin = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desin']['argentina'] = $argentina_desin;
        }

         //3.2.2.6 insecOtros
         if (isset($datos['insecOtrosCant']) && $datos['insecOtrosCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['insecOtrosCant'],
                'insecOtros',
                $datos['insecOtrosRev'],
                $datos['insecOtrosReinf'],
                ""
            ];
            $insecOtros_desin = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desin']['otros'] = $otros_desin;
        }
        
        //3.2.3 DESRATIZACIÓN - CREAR PLAGAS
       if (
        (isset($datos['rattusCant']) && $datos['rattusCant'] !=0) || 
        (isset($datos['comunCant']) && $datos['comunCant'] !=0) || 
        (isset($datos['musculusCant']) && $datos['musculusCant'] !=0)
        ){
            $keys = ['fk_CA','alcance','obs','otrosTratamiento','tipo'];
            $valores = [
                $CA,
                $datos['desraAct'],
                $datos['desraObs'],
                "",
                "desratizacion"
                ];
            $CA_Plaga = $base->nuevoRegistro("vol_CA_plaga", $keys,$valores);
            $salida['plaga']['desra'] = $CA_Plaga;
        }
       //3.2.4 DESRATIZACIÓN - CREAR ESPECIE
        //3.2.4.1 rattus
        if (isset($datos['rattusCant']) && $datos['rattusCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['rattusCant'],
                'rattus',
                $datos['rattusRev'],
                $datos['rattusReinf'],
                ""
            ];
            $rattus_desra = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desra']['rattus'] = $rattus_desra;
        }

        //3.2.4.2 comun
        if (isset($datos['comunCant']) && $datos['comunCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['comunCant'],
                'comun',
                $datos['comunRev'],
                $datos['comunReinf'],
                ""
            ];
            $comun_desra = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desra']['comun'] = $comun_desra;
        }
        //3.2.4.3 musculus
        if (isset($datos['musculusCant']) && $datos['musculusCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['musculusCant'],
                'musculus',
                $datos['musculusRev'],
                $datos['musculusReinf'],
                ""
            ];
            $musculus_desra = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desra']['musculus'] = $musculus_desra;
        }

         //3.2.5 DESinfeccion - CREAR PLAGAS
       if (
        (isset($datos['virusCant']) && $datos['virusCant'] !=0) || 
        (isset($datos['germenesCant']) && $datos['germenesCant'] !=0) || 
        (isset($datos['bacteriaCant']) && $datos['bacteriaCant'] !=0)
        ){
            $keys = ['fk_CA','alcance','obs','otrosTratamiento','tipo'];
            $valores = [
                $CA,
                $datos['desinfAlc'],
                $datos['desinfObs'],
                "",
                "desinfeccion"
                ];
            $CA_Plaga = $base->nuevoRegistro("vol_CA_plaga", $keys,$valores);
            $salida['plaga']['desinf']['id'] = $CA_Plaga;
        }
       //3.2.6 DESinfeccion - CREAR ESPECIE
        //3.2.6.1 virus
        if (isset($datos['virusCant']) && $datos['virusCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['virusCant'],
                'virus',
                $datos['virusRev'],
                $datos['virusReinf'],
                ""
            ];
            $virus_desinf= $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desinf']['virus'] = $virus_desinf;
        }

        //3.2.6.2 germenes
        if (isset($datos['germenesCant']) && $datos['germenesCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['germenesCant'],
                'germenes',
                $datos['germenesRev'],
                $datos['germenesReinf'],
                ""
            ];
            $germenes_desinf = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desinf']['germenes'] = $germenes_desinf;
        }
        //3.2.6.3 bacteria
        if (isset($datos['bacteriaCant']) && $datos['bacteriaCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['bacteriaCant'],
                'bacteria',
                $datos['bacteriaRev'],
                $datos['bacteriaReinf'],
                ""
            ];
            $bacteria_desinf = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['desinf']['bacteria'] = $bacteria_desinf;
        }

        //3.2.7 OTRAS PLAGAS - CREAR PLAGAS
       if (
        (isset($datos['nuevaPlagaCant']) && $datos['nuevaPlagaCant'] !=0)){
            $keys = ['fk_CA','alcance','obs','otrosTratamiento','tipo'];
            $valores = [
                $CA,
                $datos['otrosAlc'],
                $datos['otrosObs'],
                $datos['tipoDeTratamiento'],
                "otros"
                ];
            $CA_Plaga = $base->nuevoRegistro("vol_CA_plaga", $keys,$valores);
            $salida['plaga']['otras'] = $CA_plaga;
        }
       //3.2.8 OTRAS PLAGAS - CREAR ESPECIE
        //3.2.8.1 Nueva Plaga
        if (isset($datos['nuevaPlagaCant']) && $datos['nuevaPlagaCant'] >0){
            $keys = ['fk_plaga','cantidad','tipo','rev','reinf','otrosNombre'];
            $valores = [
                $CA_Plaga,
                $datos['nuevaPlagaCant'],
                'otros',
                $datos['nuevaPlagaRev'],
                $datos['nuevaPlagaReinf'],
                $datos['nuevaPlaga'],
            ];
            $nuevaPlaga_otros = $base->nuevoRegistro("vol_CA_especie", $keys,$valores);
            $salida['plaga']['otras']['otras'] = $nuevaPlaga_otros;
        }
        
    return $salida;
     
   }


function crearPedidos($partes,$CA){
        $base = new BD();
        $usuario = $_POST['login'];
        $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
        $keyAPI = $key[0][0];
        $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
            //CREAR PEDIDOS
        $i = 0;
        $base = new BD();
        $sociedades = $base->query("SELECT fk_soc FROM vol_CA_soc where fk_CA = '".$CA."'");
        $soc = $sociedades[0]['fk_soc'];
        foreach($partes as $parte){
            $hoy = date ('Y-m-d');
            $ano = date ('Y');
            $mes = date ('m');
            //FECHAS PREVISTAS
            switch($parte['fecha']){
                case 'ENERO':
                    if ($mes >1){
                        $fecha = ($ano+1)."-01-01";
                    }else{
                        $fecha = ($ano)."-01-01";
                    }    
                break;
                case 'FEBRERO':
                    if ($mes >2){
                        $fecha = ($ano+1)."-02-01";
                    }else{
                        $fecha = ($ano)."-02-01";
                    }
                break;
                case 'MARZO':
                    if ($mes >3){
                        $fecha = ($ano+1)."-03-01";
                    }else{
                        $fecha = ($ano)."-03-01";
                    }
                break;
                case 'ABRIL':
                    if ($mes >4){
                        $fecha = ($ano+1)."-04-01";
                    }else{
                        $fecha = ($ano)."-04-01";
                    }
                break;
                case 'MAYO':
                    if ($mes >5){
                        $fecha = ($ano+1)."-05-01";
                    }else{
                        $fecha = ($ano)."-05-01";
                    }
                break;
                case 'JUNIO':
                    if ($mes >6){
                        $fecha = ($ano+1)."-06-01";
                    }else{
                        $fecha = ($ano)."-06-01";
                    }
                break;
                case 'JULIO':
                    if ($mes >7){
                        $fecha = ($ano+1)."-07-01";
                    }else{
                        $fecha = ($ano)."-07-01";
                    }
                break;
                case 'AGOSTO':
                    if ($mes >8){
                        $fecha = ($ano+1)."-08-01";
                    }else{
                        $fecha = ($ano)."-08-01";
                    }
                break;
                case 'SEPTIEMBRE':
                    if ($mes >9){
                        $fecha = ($ano+1)."-09-01";
                    }else{
                        $fecha = ($ano)."-09-01";
                    }
                break;
                case 'OCTUBRE':
                    if ($mes >10){
                        $fecha = ($ano+1)."-10-01";
                    }else{
                        $fecha = ($ano)."-10-01";
                    }
                break;
                case 'NOVIEMBRE':
                    if ($mes >11){
                        $fecha = ($ano+1)."-11-01";
                    }else{
                        $fecha = ($ano)."-11-01";
                    }
                break;
                case 'DICIEMBRE':
                    if ($mes >12){
                        $fecha = ($ano+1)."-12-01";
                    }else{
                        $fecha = ($ano)."-12-01";
                    }
                break;
            }
            //TIPO
            switch($parte['tipo']){
                case 'ALCANTARILLADO':
                    $serie = '5';
                    $tipo = '8';
                break;
                case 'CONJUNTA':
                    $serie = '5';
                    $tipo = '10';
                    break;
                case 'PLAGAS':
                    $serie = '6';
                    $tipo = '9';
                break;
            }
            $api['date'] = strtotime($fecha);
            $api['socid'] = $soc;
            $api['lines'][0]['qty'] = '1';
            $api['lines'][0]['price'] = '0';
            $api['lines'][0]['description'] = $parte['descripcion'];
            $api['lines'][0]['desc'] = $parte['descripcion'];
            $api['array_options']['options_serie'] = $serie;
            $api['array_options']['options_tipserv'] = $tipo;
            $api['array_options']['options_obs'] = $parte['observaciones'];
            //INSERTAMOS EN DOLIBARR
            $pedidoDoli[$i] = callAPI('POST', $keyAPI, $urlAPI.'orders', json_encode($api));

            $i++;
        }

        return $pedidoDoli;

   }

function crearFacturas($soc,$pagoBanco,$pagoEfectivo,$pagoTransferencia){
    $salida = array();
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
         //CREAR FACTURAS
    // Domiciliacion
    $concepto = "Factura por los servicios contratados, conforme al contrato realizado y estipulado en la sección de la forma de Pago.";
    if($pagoBanco !=''){
        $hoy = date ('Y-m-d');
        $ano = date ('Y');
        $mes = date ('m');
        $pago = $base->query('SELECT * FROM  vol_CA_pago WHERE rowid ='.$pagoBanco);
        $apiFactura['socid'] = $soc;
        $apiFactura['lines'][0]['desc'] = $concepto;
        $apiFactura['lines'][0]['description'] = $concepto;
        $apiFactura['lines'][0]['qty'] = '1';
        $importe = floatval($pago[0]['importe']);
        $facturaImporte = 0;
        //PERIODO
        switch($pago[0]['formaPago']){
        case "Anual":
            $facturaImporte =$importe/1;
        break;
        case "Semestral":
            $facturaImporte = $importe/2;
        break;
        case "Trimestral":
            $facturaImporte = $importe/4;
        break;
        case "Bimensual":
            $facturaImporte = $importe/6;
        break;
        case "Mensual":
            $facturaImporte = $importe/12;
        break;    
        }
        $apiFactura['lines'][0]['subprice'] = $facturaImporte;
            $facturasBanco = array();
        //FECHAS PREVISTAS Y CREACIÓN FACTURA
                if ($pago[0]['enero'] == 1){
                    if($mes>1){
                        $fecha = ($ano+1)."-01-01";
                    }else{
                        $fecha = ($ano)."-01-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['febrero'] == 1){
                    if($mes>2){
                        $fecha = ($ano+1)."-02-01";
                    }else{
                        $fecha = ($ano)."-02-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura))));
                }
                if ($pago[0]['marzo'] == 1){
                    if($mes>3){
                        $fecha = ($ano+1)."-03-01";
                    }else{
                        $fecha = ($ano)."-03-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura))));
                }
                if ($pago[0]['abril'] == 1){
                    if($mes>4){
                        $fecha = ($ano+1)."-04-01";
                    }else{
                        $fecha = ($ano)."-04-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['mayo'] == 1){
                    if($mes>5){
                        $fecha = ($ano+1)."-05-01";
                    }else{
                        $fecha = ($ano)."-05-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['junio'] == 1){
                    if($mes>6){
                        $fecha = ($ano+1)."-06-01";
                    }else{
                        $fecha = ($ano)."-06-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['julio'] == 1){
                    if($mes>7){
                        $fecha = ($ano+1)."-07-01";
                    }else{
                        $fecha = ($ano)."-07-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['agosto'] == 1){
                    if($mes>8){
                        $fecha = ($ano+1)."-08-01";
                    }else{
                        $fecha = ($ano)."-08-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['septiembre'] == 1){
                    if($mes>9){
                        $fecha = ($ano+1)."-09-01";
                    }else{
                        $fecha = ($ano)."-09-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['octubre'] == 1){
                    if($mes>10){
                        $fecha = ($ano+1)."-10-01";
                    }else{
                        $fecha = ($ano)."-10-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['noviembre'] == 1){
                    if($mes>11){
                        $fecha = ($ano+1)."-11-01";
                    }else{
                        $fecha = ($ano)."-11-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['diciembre'] == 1){
                    if($mes>12){
                        $fecha = ($ano+1)."-12-01";
                    }else{
                        $fecha = ($ano)."-12-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                            
    }      
    // Efectivo
    if($pagoEfectivo !=''){
        $hoy = date ('Y-m-d');
        $ano = date ('Y');
        $mes = date ('m');
        $pago = $base->query('SELECT * FROM  vol_CA_pago WHERE rowid ='.$pagoEfectivo);
        $apiFactura['socid'] = $soc;
        $apiFactura['lines'][0]['desc'] = $concepto;
        $apiFactura['lines'][0]['description'] = $concepto;
        $apiFactura['lines'][0]['qty'] = '1';
        $importe = floatval($pago[0]['importe']);
        //PERIODO
        switch($pago[0]['formaPago']){
        case "Anual":
            $facturaImporte = $importe /1;
        break;
        case "Semestral":
            $facturaImporte = $importe /2;
        break;
        case "Trimestral":
            $facturaImporte = $importe /4;
        break;
        case "Bimensual":
            $facturaImporte = $importe/6;
        break;
        case "Mensual":
            $facturaImporte = $importe /12;
        break;    
        }
        $apiFactura['lines'][0]['subprice'] = $facturaImporte;
            $facturasEfectivo = array();
    //FECHAS PREVISTAS Y CREACIÓN FACTURA)
            if ($pago[0]['enero']){
                    if($mes>1){
                        $fecha = ($ano+1)."-01-01";
                    }else{
                        $fecha = ($ano)."-01-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['febrero']){
                    if($mes>2){
                        $fecha = ($ano+1)."-02-01";
                    }else{
                        $fecha = ($ano)."-02-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['marzo']){
                    if($mes>3){
                        $fecha = ($ano+1)."-03-01";
                    }else{
                        $fecha = ($ano)."-03-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['abril']){
                    if($mes>4){
                        $fecha = ($ano+1)."-04-01";
                    }else{
                        $fecha = ($ano)."-04-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['mayo']){
                    if($mes>5){
                        $fecha = ($ano+1)."-05-01";
                    }else{
                        $fecha = ($ano)."-05-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['junio']){
                    if($mes>6){
                        $fecha = ($ano+1)."-06-01";
                    }else{
                        $fecha = ($ano)."-06-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['julio']){
                    if($mes>7){
                        $fecha = ($ano+1)."-07-01";
                    }else{
                        $fecha = ($ano)."-07-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['agosto']){
                    if($mes>8){
                        $fecha = ($ano+1)."-08-01";
                    }else{
                        $fecha = ($ano)."-08-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['septiembre']){
                    if($mes>9){
                        $fecha = ($ano+1)."-09-01";
                    }else{
                        $fecha = ($ano)."-09-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['octubre']){
                    if($mes>10){
                        $fecha = ($ano+1)."-10-01";
                    }else{
                        $fecha = ($ano)."-10-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['noviembre']){
                    if($mes>11){
                        $fecha = ($ano+1)."-11-01";
                    }else{
                        $fecha = ($ano)."-11-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['diciembre']){
                    if($mes>12){
                        $fecha = ($ano+1)."-12-01";
                    }else{
                        $fecha = ($ano)."-12-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                } 
            }   
    // Transferencia
    if($pagoTransferencia !=''){
        $hoy = date ('Y-m-d');
    $ano = date ('Y');
    $mes = date ('m');
    $pago = $base->query('SELECT * FROM  vol_CA_pago WHERE rowid ='.$pagoTransferencia);
    $apiFactura['socid'] = $soc;
    $apiFactura['lines'][0]['desc'] = $concepto;
    $apiFactura['lines'][0]['description'] = $concepto;
    $apiFactura['lines'][0]['qty'] = '1';
    $importe = floatval($pago[0]['importe']);
    //PERIODO
    switch($pago[0]['formaPago']){
    case "Anual":
        $facturaImporte = $importe /1;
    break;
    case "Semestral":
        $facturaImporte = $importe /2;
    break;
    case "Trimestral":
        $facturaImporte = $importe /4;
    break;
    case "Bimensual":
        $facturaImporte = $importe/6;
    break;
    case "Mensual":
        $facturaImporte = $importe /12;
    break;    
    }
    $apiFactura['lines'][0]['subprice'] = $facturaImporte;
        $facturasTransferencia = array();
    //FECHAS PREVISTAS Y CREACIÓN FACTURA
            if ($pago[0]['enero']){
                if($mes>1){
                    $fecha = ($ano+1)."-01-01";
                }else{
                    $fecha = ($ano)."-01-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['febrero']){
                if($mes>2){
                    $fecha = ($ano+1)."-02-01";
                }else{
                    $fecha = ($ano)."-02-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['marzo']){
                if($mes>3){
                    $fecha = ($ano+1)."-03-01";
                }else{
                    $fecha = ($ano)."-03-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['abril']){
                if($mes>4){
                    $fecha = ($ano+1)."-04-01";
                }else{
                    $fecha = ($ano)."-04-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['mayo']){
                if($mes>5){
                    $fecha = ($ano+1)."-05-01";
                }else{
                    $fecha = ($ano)."-05-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['junio']){
                if($mes>6){
                    $fecha = ($ano+1)."-06-01";
                }else{
                    $fecha = ($ano)."-06-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['julio']){
                if($mes>7){
                    $fecha = ($ano+1)."-07-01";
                }else{
                    $fecha = ($ano)."-07-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['agosto']){
                if($mes>8){
                    $fecha = ($ano+1)."-08-01";
                }else{
                    $fecha = ($ano)."-08-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['septiembre']){
                if($mes>9){
                    $fecha = ($ano+1)."-09-01";
                }else{
                    $fecha = ($ano)."-09-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['octubre']){
                if($mes>10){
                    $fecha = ($ano+1)."-10-01";
                }else{
                    $fecha = ($ano)."-10-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['noviembre']){
                if($mes>11){
                    $fecha = ($ano+1)."-11-01";
                }else{
                    $fecha = ($ano)."-11-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['diciembre']){
                if($mes>12){
                    $fecha = ($ano+1)."-12-01";
                }else{
                    $fecha = ($ano)."-12-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }      
        }
       
        $salida['banco'] = $facturasBanco;
        $salida['efectivo'] = $facturasEfectivo;
        $salida['transferencia'] = $facturasTransferencia;
        return $salida;
   };


   function agregaContacto($tipo,$id,$idContacto){
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $i = 0;
    
    if ($tipo == "pedido"){
        $api['fk_socpeople'] = $idContacto;
        $api['type_contact'] = 'SHIPPING';
        $api['source'] = 'external';
        return callAPI('POST', $keyAPI, $urlAPI.'orders/'.$id."/contact/".$idContacto."/SHIPPING");   
    }
    
}

function crearPDF($CAid,$destino){
    //DATOS CA
    $CA = verCA($CAid);
    // INSERTAMOS CABECERA   
        $cabecera = file_get_contents("plantilla/cabecera.html");
       //Modificamos
       $base = new BD();
       $datosCA = $base->query("SELECT * from vol_CA where rowid = '".$CA['id']."'");
       $mesEscrito = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
       $fechaArray =  explode("-",$datosCA[0]['fecha']);
       $fechaEscrita = $fechaArray[2]." de ".$mesEscrito[$fechaArray[1]-1]." de ".$fechaArray[0];
       //$fechaEscrita = date("d M Y",$fecha);
       $cabecera = str_replace("{{referencia}}",$datosCA[0]['referencia'],$cabecera); 
       $cabecera = str_replace("{{fechaEscrita}}",$fechaEscrita,$cabecera);   
    //INSERTAMOS PIE
        $pie =  file_get_contents("plantilla/pie.html");
        $empresasPie = "";

    // CUERPO
        $cuerpo = "";
        //CLIENTE
        $cuentaCliente = 0;
        foreach ($CA['soc'] as $idSoc => $datosCliente){
            if($cuentaCliente > 0){
                $cuerpo.="<p style=\"width: 100%;font-family: 'Courier New', Courier, monospace;font-size: 12px;\">Por otra parte,</p>";
            }
            //Datos generales
            $clienteFormato =  file_get_contents("plantilla/cliente.html"); 
            $clienteFicha = $base->registroRowid("vol_societe", $idSoc);
            $clienteFormato = str_replace("{{nom}}",$clienteFicha['nom'],$clienteFormato);
            $clienteFormato = str_replace("{{address}}",$clienteFicha['address'],$clienteFormato);
            $clienteFormato = str_replace("{{town}}",$clienteFicha['town'],$clienteFormato);
            $empresasPie .= "<td><p>Firma Cliente:</p><p>DNI/CIF:</p></td>";
            $cuerpo.=$clienteFormato;
            //Pagos - Domiciliacion
            if(!empty($datosCliente['banco'])){
                $pagoFormato = file_get_contents("plantilla/banco.html"); 
                $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['banco']);
                $iban = $base->registroRowid("vol_societe_rib", $datosPago['fk_iban_emp'])['iban_prefix'];
                //meses
                $datosMes = "";
                foreach ($mesEscrito as $mesFijo){
                    if ($datosPago[$mesFijo] == '1'){
                        if(!empty($datosMes)){
                            $datosMes .=" - ";
                        }
                        $datosMes.=$mesFijo;
                    }
                }
                //incluimos datos
                $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                $pagoFormato = str_replace("{{iban}}",$iban,$pagoFormato);
                $cuerpo.=$pagoFormato;
            }
            //Pagos - efectivo
            if(!empty($datosCliente['efectivo'])){
                $pagoFormato = file_get_contents("plantilla/efectivo.html"); 
                $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['efectivo']);
                //meses
                $datosMes = "";
                foreach ($mesEscrito as $mesFijo){
                    if ($datosPago[$mesFijo] == '1'){
                        if(!empty($datosMes)){
                            $datosMes .=" - ";
                        }
                        $datosMes.=$mesFijo;
                    }
                }
                //incluimos datos
                $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                $cuerpo.=$pagoFormato;
            }

            //Pagos - Transferencia
            if(!empty($datosCliente['transferencia'])){
                $pagoFormato = file_get_contents("plantilla/transferencia.html"); 
                $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['transferencia']);
                $iban = $base->registroRowid("vol_bank_account", $datosPago['fk_iban_soc'])['iban_prefix'];
                //meses
                $datosMes = "";
                foreach ($mesEscrito as $mesFijo){
                    if ($datosPago[$mesFijo] == '1'){
                        if(!empty($datosMes)){
                            $datosMes .=" - ";
                        }
                        $datosMes.=$mesFijo;
                    }
                }
                //incluimos datos
                $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                $pagoFormato = str_replace("{{iban}}",$iban,$pagoFormato);
                $cuerpo.=$pagoFormato;
            }

            $cuentaCliente++;
        };
        //DIRECCION
        $direccionFormato = file_get_contents('plantilla/direccion.html');
        $direccionServicio = (!empty($datosCA[0]['contacto'])?($base->registroRowid("vol_socpeople", $datosCA[0]['contacto'])['address'])." ".($base->registroRowid("vol_socpeople", $datosCA[0]['contacto'])['town']):$clienteFicha['address']);
        $direccionFormato = str_replace("{{direccion}}",$direccionServicio,$direccionFormato);
        $cuerpo.=$direccionFormato;
        //ALCANCE
        $cuerpo .= file_get_contents('plantilla/alcance.html');
        //ALCANTARILLADO
        if(!is_null(($CA['alcantarillado']['id']))){
            $datosAlc=  $base->registroRowid("vol_CA_alc",$CA['alcantarillado']['id']);
            $alcantarillado = file_get_contents('plantilla/alcantarillado.html');
            //SUBTERRANEA
            if(!is_null($CA['alcantarillado']['Subt'])){
                $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Subt']);
                $lineaRed = '<tr><td>Redes Subterráneas horizontales</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td></tr>";
                $alcantarillado = str_replace('{{alcSub}}',$lineaRed,$alcantarillado);
                $redFormato = file_get_contents('plantilla/sub.html');
                $arquetas = "";
                if($datosRed['arqSif']>0){
                    $lineaArq="<tr><td>Arquetas sifónicas</td><td>".$datosRed['arqSif']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['arqMue']>0){
                    $lineaArq="<tr><td>Arquetas de tomas de muestras</td><td>".$datosRed['arqMue']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['arqGra']>0){
                    $lineaArq="<tr><td>Arquetas separadoras de grasas</td><td>".$datosRed['arqGra']."</td></tr>";
                    $arquetas .=$lineaArq;
                } 
                if($datosRed['arqReg']>0){
                    $lineaArq="<tr><td>Arquetas/registros de paso</td><td>".$datosRed['arqReg']."</td></tr>";
                    $arquetas .=$lineaArq;
                } 
                if($datosRed['sum']>0){
                    $lineaArq="<tr><td>Husillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td></tr>";
                    $arquetas .=$lineaArq;
                } 
                if($datosRed['tub']>0){
                    $lineaArq="<tr><td>uberías horizontales de conexión</td><td>".$datosRed['tub']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                $redFormato = str_replace('{{arqSub}}',$arquetas,$redFormato);
                $redFormato = str_replace('{{obsSub}}',$datosRed['redObs'],$redFormato);
                $alcantarillado = str_replace('{{sub}}',$redFormato,$alcantarillado);
            }else{
                $alcantarillado = str_replace('{{alcSub}}',"",$alcantarillado);
                $alcantarillado = str_replace('{{sub}}',"",$alcantarillado);
            }
            //AER
            if(!is_null($CA['alcantarillado']['Aer'])){
                $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Aer']);
                $lineaRed = '<tr><td>Redes Aéreas horizontales</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td></tr>";
                $alcantarillado = str_replace('{{alcAer}}',$lineaRed,$alcantarillado);
                $redFormato = file_get_contents('plantilla/aer.html');
                $arquetas = "";
                if($datosRed['arqSif']>0){
                    $lineaArq="<tr><td>Arquetas sifónicas</td><td>".$datosRed['arqSif']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['arqMue']>0){
                    $lineaArq="<tr><td>Arquetas de tomas de muestras</td><td>".$datosRed['arqMue']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['arqGra']>0){
                    $lineaArq="<tr><td>Arquetas separadoras de grasas</td><td>".$datosRed['arqGra']."</td></tr>";
                    $arquetas .=$lineaArq;
                } 
                if($datosRed['sum']>0){
                    $lineaArq="<tr><td>Husillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td></tr>";
                    $arquetas .=$lineaArq;
                } 
                if($datosRed['tub']>0){
                    $lineaArq="<tr><td>Tuberías horizontales de conexión</td><td>".$datosRed['tub']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                $redFormato = str_replace('{{arqAer}}',$arquetas,$redFormato);
                $redFormato = str_replace('{{obsAer}}',$datosRed['redObs'],$redFormato);
                $alcantarillado = str_replace('{{aer}}',$redFormato,$alcantarillado);
            }else{
                $alcantarillado = str_replace('{{alcAer}}',"",$alcantarillado);
                $alcantarillado = str_replace('{{aer}}',"",$alcantarillado);
            }
             //BOM
             if(!is_null($CA['alcantarillado']['Bom'])){
                $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Bom']);
                $lineaRed = '<tr><td>Redes de suelo de garajes/sótanos (BOMBEO)</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td></tr>";
                $alcantarillado = str_replace('{{alcBom}}',$lineaRed,$alcantarillado);
                $redFormato = file_get_contents('plantilla/bom.html');
                $arquetas = "";
                if($datosRed['arqBom']>0){
                    $lineaArq="<tr><td>Arqueta de bombeo</td><td>".$datosRed['arqBom']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['arqGra']>0){
                    $lineaArq="<tr><td>Arquetas decantatadora/separadoras de grasas</td><td>".$datosRed['arqGra']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['sum']>0){
                    $lineaArq="<tr><td>Husillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                if($datosRed['arqRej']>0){
                    $lineaArq="<tr><td>Rejillas rampa Garaje</td><td>".$datosRed['arqRej']."</td></tr>";
                    $arquetas .=$lineaArq;
                }   
                if($datosRed['tub']>0){
                    $lineaArq="<tr><td>Tuberías horizontales de conexión</td><td>".$datosRed['tub']."</td></tr>";
                    $arquetas .=$lineaArq;
                }
                $redFormato = str_replace('{{arqBom}}',$arquetas,$redFormato);
                $redFormato = str_replace('{{obsBom}}',$datosRed['redObs'],$redFormato);
                $alcantarillado = str_replace('{{bom}}',$redFormato,$alcantarillado);
            }else{
                $alcantarillado = str_replace('{{alcBom}}',"",$alcantarillado);
                $alcantarillado = str_replace('{{bom}}',"",$alcantarillado);
            }

            $alcantarillado = str_replace('{{furgon}}',$datosAlc['furgon'],$alcantarillado);
            $alcantarillado = str_replace('{{camion}}',$datosAlc['camion'],$alcantarillado);
            $alcantarillado = str_replace('{{obsAlc}}',$datosAlc['obsAlc'],$alcantarillado);
            $cuerpo .= $alcantarillado;
        }
        $condPlagas = 0;
        //DESINSECTACION
        if(!is_null($CA['plaga']['desinsectacion']['id'])){
            $condPlagas = 1;
            $plagaFormato = file_get_contents('plantilla/plaga.html');
            $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desinsectacion']['id']);
            //Datos plaga
            $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
            $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
            $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
            $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
            //especies
            $especies = "";
            if(!is_null($CA['plaga']['desinsectacion']['alemana'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['alemana']);
                $especies.="<tr><td>Blatella germánica</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinsectacion']['americana'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['americana']);
                $especies.="<tr><td> Periplaneta americana (Cucaracha americana) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinsectacion']['jardin'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['jardin']);
                $especies.="<tr><td>Lasus niger(Hormiga negra jardín)</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinsectacion']['faraon'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['faraon']);
                $especies.="<tr><td>Monomorium pharaonis (Hormigas faraón) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinsectacion']['argentina'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['argentina']);
                $especies.="<tr><td>Linepithema humile(Hormiga argentina)</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinsectacion']['insecOtros'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['insecOtros']);
                $especies.="<tr><td>Otro tipo de insectos (definido en Observaciones) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
            $cuerpo .=$plagaFormato;
        }
        //DESRATIZACION
        if(!is_null($CA['plaga']['desratizacion']['id'])){
            $condPlagas = 1;
            $plagaFormato = file_get_contents('plantilla/plaga.html');
            $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desratizacion']['id']);
            //Datos plaga
            $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
            $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
            $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
            $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
            //especies
            $especies = "";
            if(!is_null($CA['plaga']['desratizacion']['rattus'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['rattus']);
                $especies.="<tr><td> Rattus rattus </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desratizacion']['comun'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['comun']);
                $especies.="<tr><td> Rattus norvegicus </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desratizacion']['musculus'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['musculus']);
                $especies.="<tr><td>Mus musculus</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
            $cuerpo .=$plagaFormato;
        }
        //DESINFECCION
        if(!is_null($CA['plaga']['desinfeccion']['id'])){
            $condPlagas = 1;
            $plagaFormato = file_get_contents('plantilla/plaga.html');
            $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desinfeccion']['id']);
            //Datos plaga
            $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
            $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
            $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
            $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
            //especies
            $especies = "";
            if(!is_null($CA['plaga']['desinfeccion']['virus'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['virus']);
                $especies.="<tr><td>Virus</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinfeccion']['bacteria'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['bacteria']);
                $especies.="<tr><td>Bacterias</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            if(!is_null($CA['plaga']['desinfeccion']['germenes'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['germenes']);
                $especies.="<tr><td>Germenes</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
            $cuerpo .=$plagaFormato;
        }
        //OTRAS ESPECIES
        if(!is_null($CA['plaga']['otras']['id'])){
            $condPlagas = 1;
            $plagaFormato = file_get_contents('plantilla/plaga.html');
            $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['otras']['id']);
            //Datos plaga
            $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
            $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
            $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
            $plagaFormato = str_replace('{{tratamiento}}',$datosPlaga['otrosTratamiento'],$plagaFormato);
            //especies
            $especies = "";
            if(!is_null($CA['plaga']['otras']['otro'])){
                $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['otras']['otro']);
                $especies.="<tr><td>".$datosPlaga['otrosNombre']."</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
            }
            $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
            $cuerpo .=$plagaFormato;
        }

        //CONDICIONES
        $condicionesFormato = file_get_contents('plantilla/condiciones.html');
        $cuerpo .= $condicionesFormato;


        //COND. ALCANTIRALLADO
        if(!is_null(($CA['alcantarillado']['id']))){
            $condicionesFormato = file_get_contents('plantilla/condicionesDesatascos.html');
            $cuerpo .= $condicionesFormato;
        }
        //COND. PLAGAS
        if($condPlagas > 0){
            $condicionesFormato = file_get_contents('plantilla/condicionesPlagas.html');
            $cuerpo .= $condicionesFormato;
        }
        //COND. LOPD
        $condicionesFormato = file_get_contents('plantilla/lopd.html');
        $cuerpo .= $condicionesFormato;

        //CONDICIONES GENERALES
        $condicionesGenerales = $datosCA[0]['clausulasAdicionales'];
        $condicionesFormato = file_get_contents('plantilla/condicionesAdic.html');
        $condicionesFormato = str_replace('{{particulares}}',$condicionesGenerales,$condicionesFormato);
        $cuerpo .= $condicionesFormato;
        $pie = str_replace("{{firmante}}",$empresasPie,$pie);

    //OBJETO MPDF
        $config['format'] = 'A4';
        $config['margin_top'] = 58;
        $config['margin_header'] = 5;
        $config['margin_bottom'] = 50;
        $config['margin_footer'] = 2;
        $mipdf = new \Mpdf\Mpdf($config);
        //Insertamos Cabecera y Pie
        $mipdf->SetHTMLHeader($cabecera);
        $mipdf->SetHTMLFooter($pie);
        //Insertamos cuerpo
        $mipdf->WriteHTML($cuerpo);
        
        //Generamos el PDF
        $mipdf->Output($destino);
        //$mipdf->Output($destino); 
    };

function crearPresPDF($CAid,$destino){
        //DATOS CA
        $CA = verCA($CAid);
        // INSERTAMOS CABECERA   
            $cabecera = file_get_contents("plantilla/cabecerapr.html");
           //Modificamos
           $base = new BD();
           $datosCA = $base->query("SELECT * from vol_CA where rowid = '".$CA['id']."'");
           $mesEscrito = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
           $fechaArray =  explode("-",$datosCA[0]['fecha']);
           $fechaEscrita = $fechaArray[2]." de ".$mesEscrito[$fechaArray[1]-1]." de ".$fechaArray[0];
           //$fechaEscrita = date("d M Y",$fecha);
           $cabecera = str_replace("{{referencia}}",$datosCA[0]['referencia'],$cabecera); 
           $cabecera = str_replace("{{fechaEscrita}}",$fechaEscrita,$cabecera);   
        //INSERTAMOS PIE
            $pie =  file_get_contents("plantilla/piePr.html");
    
        // CUERPO
            $cuerpo = "";
            //CLIENTE
            $cuentaCliente = 0;
            foreach ($CA['soc'] as $idSoc => $datosCliente){
                if($cuentaCliente > 0){
                    $cuerpo.="<p style=\"width: 100%;font-family: 'Courier New', Courier, monospace;font-size: 12px;\">Por otra parte,</p>";
                }
                //Datos generales
                $clienteFormato =  file_get_contents("plantilla/cliente.html"); 
                $clienteFicha = $base->registroRowid("vol_societe", $idSoc);
                $clienteFormato = str_replace("{{nom}}",$clienteFicha['nom'],$clienteFormato);
                $clienteFormato = str_replace("{{address}}",$clienteFicha['address'],$clienteFormato);
                $clienteFormato = str_replace("{{town}}",$clienteFicha['town'],$clienteFormato);
                $cuerpo.=$clienteFormato;
                //Pagos - Domiciliacion
                if(!empty($datosCliente['banco'])){
                    $pagoFormato = file_get_contents("plantilla/banco.html"); 
                    $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['banco']);
                    $iban = $base->registroRowid("vol_societe_rib", $datosPago['fk_iban_emp'])['iban_prefix'];
                    //meses
                    $datosMes = "";
                    foreach ($mesEscrito as $mesFijo){
                        if ($datosPago[$mesFijo] == '1'){
                            if(!empty($datosMes)){
                                $datosMes .=" - ";
                            }
                            $datosMes.=$mesFijo;
                        }
                    }
                    //incluimos datos
                    $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                    $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                    $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                    $pagoFormato = str_replace("{{iban}}",$iban,$pagoFormato);
                    $cuerpo.=$pagoFormato;
                }
                //Pagos - efectivo
                if(!empty($datosCliente['efectivo'])){
                    $pagoFormato = file_get_contents("plantilla/efectivo.html"); 
                    $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['efectivo']);
                    //meses
                    $datosMes = "";
                    foreach ($mesEscrito as $mesFijo){
                        if ($datosPago[$mesFijo] == '1'){
                            if(!empty($datosMes)){
                                $datosMes .=" - ";
                            }
                            $datosMes.=$mesFijo;
                        }
                    }
                    //incluimos datos
                    $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                    $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                    $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                    $cuerpo.=$pagoFormato;
                }
    
                //Pagos - Transferencia
                if(!empty($datosCliente['transferencia'])){
                    $pagoFormato = file_get_contents("plantilla/transferencia.html"); 
                    $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['transferencia']);
                    $iban = $base->registroRowid("vol_bank_account", $datosPago['fk_iban_soc'])['iban_prefix'];
                    //meses
                    $datosMes = "";
                    foreach ($mesEscrito as $mesFijo){
                        if ($datosPago[$mesFijo] == '1'){
                            if(!empty($datosMes)){
                                $datosMes .=" - ";
                            }
                            $datosMes.=$mesFijo;
                        }
                    }
                    //incluimos datos
                    $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                    $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                    $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                    $pagoFormato = str_replace("{{iban}}",$iban,$pagoFormato);
                    $cuerpo.=$pagoFormato;
                }
    
                $cuentaCliente++;
            };
            //DIRECCION
            $direccionFormato = file_get_contents('plantilla/direccion.html');
            $direccionServicio = (!empty($datosCA[0]['contacto'])?($base->registroRowid("vol_socpeople", $datosCA[0]['contacto'])['address'])." ".($base->registroRowid("vol_socpeople", $datosCA[0]['contacto'])['town']):$clienteFicha['address']);
            $direccionFormato = str_replace("{{direccion}}",$direccionServicio,$direccionFormato);
            $cuerpo.=$direccionFormato;
            //ALCANCE
            $cuerpo .= file_get_contents('plantilla/alcance.html');
            //ALCANTARILLADO
            if(!is_null(($CA['alcantarillado']['id']))){
                $datosAlc=  $base->registroRowid("vol_CA_alc",$CA['alcantarillado']['id']);
                $alcantarillado = file_get_contents('plantilla/alcantarillado.html');
                //SUBTERRANEA
                if(!is_null($CA['alcantarillado']['Subt'])){
                    $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Subt']);
                    $lineaRed = '<td>Redes Subterráneas horizontales</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td>";
                    $alcantarillado = str_replace('{{alcSub}}',$lineaRed,$alcantarillado);
                    $redFormato = file_get_contents('plantilla/sub.html');
                    $arquetas = "";
                    if($datosRed['arqSif']>0){
                        $lineaArq="<tr><td>Arquetas sifónicas</td><td>".$datosRed['arqSif']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqMue']>0){
                        $lineaArq="<tr><td>Arquetas de tomas de muestras</td><td>".$datosRed['arqMue']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqGra']>0){
                        $lineaArq="<tr><td>Arquetas separadoras de grasas</td><td>".$datosRed['arqGra']."</td></tr>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['arqReg']>0){
                        $lineaArq="<tr><td>Arquetas/registros de paso</td><td>".$datosRed['arqReg']."</td></tr>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['sum']>0){
                        $lineaArq="<tr><td>Husillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td></tr>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['tub']>0){
                        $lineaArq="<tr><td>uberías horizontales de conexión</td><td>".$datosRed['tub']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    $redFormato = str_replace('{{arqSub}}',$arquetas,$redFormato);
                    $redFormato = str_replace('{{obsSub}}',$datosRed['redObs'],$redFormato);
                    $alcantarillado = str_replace('{{sub}}',$redFormato,$alcantarillado);
                }else{
                    $alcantarillado = str_replace('{{alcSub}}',"",$alcantarillado);
                    $alcantarillado = str_replace('{{sub}}',"",$alcantarillado);
                }
                //AER
                if(!is_null($CA['alcantarillado']['Aer'])){
                    $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Aer']);
                    $lineaRed = '<tr><td>Redes Aéreas horizontales</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td></tr>";
                    $alcantarillado = str_replace('{{alcAer}}',$lineaRed,$alcantarillado);
                    $redFormato = file_get_contents('plantilla/aer.html');
                    $arquetas = "";
                    if($datosRed['arqSif']>0){
                        $lineaArq="<tr><td>Arquetas sifónicas</td><td>".$datosRed['arqSif']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqMue']>0){
                        $lineaArq="<tr><td>Arquetas de tomas de muestras</td><td>".$datosRed['arqMue']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqGra']>0){
                        $lineaArq="<tr><td>Arquetas separadoras de grasas</td><td>".$datosRed['arqGra']."</td></tr>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['sum']>0){
                        $lineaArq="<tr><td>Husillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td></tr>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['tub']>0){
                        $lineaArq="<tr><td>Tuberías horizontales de conexión</td><td>".$datosRed['tub']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    $redFormato = str_replace('{{arqAer}}',$arquetas,$redFormato);
                    $redFormato = str_replace('{{obsAer}}',$datosRed['redObs'],$redFormato);
                    $alcantarillado = str_replace('{{aer}}',$redFormato,$alcantarillado);
                }else{
                    $alcantarillado = str_replace('{{alcAer}}',"",$alcantarillado);
                    $alcantarillado = str_replace('{{aer}}',"",$alcantarillado);
                }
                 //BOM
                 if(!is_null($CA['alcantarillado']['Bom'])){
                    $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Bom']);
                    $lineaRed = '<tr><td>Redes de suelo de garajes/sótanos (BOMBEO)</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td></tr>";
                    $alcantarillado = str_replace('{{alcBom}}',$lineaRed,$alcantarillado);
                    $redFormato = file_get_contents('plantilla/bom.html');
                    $arquetas = "";
                    if($datosRed['arqBom']>0){
                        $lineaArq="<tr><td>Arqueta de bombeo</td><td>".$datosRed['arqBom']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqGra']>0){
                        $lineaArq="<tr><td>Arquetas decantatadora/separadoras de grasas</td><td>".$datosRed['arqGra']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['sum']>0){
                        $lineaArq="<tr><td>Husillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqRej']>0){
                        $lineaArq="<tr><td>Rejillas rampa Garaje</td><td>".$datosRed['arqRej']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }   
                    if($datosRed['tub']>0){
                        $lineaArq="<tr><td>Tuberías horizontales de conexión</td><td>".$datosRed['tub']."</td></tr>";
                        $arquetas .=$lineaArq;
                    }
                    $redFormato = str_replace('{{arqBom}}',$arquetas,$redFormato);
                    $redFormato = str_replace('{{obsBom}}',$datosRed['redObs'],$redFormato);
                    $alcantarillado = str_replace('{{bom}}',$redFormato,$alcantarillado);
                }else{
                    $alcantarillado = str_replace('{{alcBom}}',"",$alcantarillado);
                    $alcantarillado = str_replace('{{bom}}',"",$alcantarillado);
                }
    
                $alcantarillado = str_replace('{{furgon}}',$datosAlc['furgon'],$alcantarillado);
                $alcantarillado = str_replace('{{camion}}',$datosAlc['camion'],$alcantarillado);
                $alcantarillado = str_replace('{{obsAlc}}',$datosAlc['obsAlc'],$alcantarillado);
                $cuerpo .= $alcantarillado;
            }
    
            //DESINSECTACION
            if(!is_null($CA['plaga']['desinsectacion']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desinsectacion']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['desinsectacion']['alemana'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['alemana']);
                    $especies.="<tr><td>Blatella germánica</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['americana'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['americana']);
                    $especies.="<tr><td> Periplaneta americana (Cucaracha americana) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['jardin'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['jardin']);
                    $especies.="<tr><td>Lasus niger(Hormiga negra jardín)</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['faraon'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['faraon']);
                    $especies.="<tr><td>Monomorium pharaonis (Hormigas faraón) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['argentina'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['argentina']);
                    $especies.="<tr><td>Linepithema humile(Hormiga argentina)</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['insecOtros'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['insecOtros']);
                    $especies.="<tr><td>Otro tipo de insectos (definido en Observaciones) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //DESRATIZACION
            if(!is_null($CA['plaga']['desratizacion']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desratizacion']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['desratizacion']['rattus'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['rattus']);
                    $especies.="<tr><td> Rattus rattus </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desratizacion']['comun'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['comun']);
                    $especies.="<tr><td> Rattus norvegicus </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desratizacion']['musculus'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['musculus']);
                    $especies.="<tr><td>Mus musculus</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //DESINFECCION
            if(!is_null($CA['plaga']['desinfeccion']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desinfeccion']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['desinfeccion']['virus'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['virus']);
                    $especies.="<tr><td>Virus</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinfeccion']['bacteria'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['bacteria']);
                    $especies.="<tr><td>Bacterias</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinfeccion']['germenes'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['germenes']);
                    $especies.="<tr><td>Germenes</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //OTRAS ESPECIES
            if(!is_null($CA['plaga']['otras']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['otras']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',$datosPlaga['otrosTratamiento'],$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['otras']['otro'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['otras']['otro']);
                    $especies.="<tr><td>".$datosPlaga['otrosNombre']."</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //CONDICIONES GENERALES
            $condicionesGenerales = $datosCA[0]['clausulasAdicionales'];
            $condicionesFormato = file_get_contents('plantilla/condicionesAdic.html');
            $condicionesFormato = str_replace('{{particulares}}',$condicionesGenerales,$condicionesFormato);
            $cuerpo .= $condicionesFormato;
    
        //OBJETO MPDF
            $config['format'] = 'A4';
            $config['margin_top'] = 58;
            $config['margin_header'] = 5;
            $config['margin_bottom'] = 50;
            $config['margin_footer'] = 2;
            $mipdf = new \Mpdf\Mpdf($config);
            //Insertamos Cabecera y Pie
            $mipdf->SetHTMLHeader($cabecera);
            $mipdf->SetHTMLFooter($pie);
            //Insertamos cuerpo
            $mipdf->WriteHTML($cuerpo);
            
            //Generamos el PDF
            $mipdf->Output($destino);
            //$mipdf->Output($destino); 
        };
    

function crearWord($CAid,$destino){
        //DATOS CA
        $CA = verCA($CAid);
        // INSERTAMOS CABECERA   
            $cabecera = file_get_contents("plantilla/cabecera.html");
           //Modificamos
           $base = new BD();
           $datosCA = $base->query("SELECT * from vol_CA where rowid = '".$CA['id']."'");
           $mesEscrito = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
           $fechaArray =  explode("-",$datosCA[0]['fecha']);
           $fechaEscrita = $fechaArray[2]." de ".$mesEscrito[$fechaArray[1]-1]." de ".$fechaArray[0];
           //$fechaEscrita = date("d M Y",$fecha);
           $cabecera = str_replace("{{referencia}}",$datosCA[0]['referencia'],$cabecera); 
           $cabecera = str_replace("{{fechaEscrita}}",$fechaEscrita,$cabecera);   
        //INSERTAMOS PIE
            $pie =  file_get_contents("plantilla/pie.html");
    
        // CUERPO
            $cuerpo = "";
            //CLIENTE
            $cuentaCliente = 0;
            foreach ($CA['soc'] as $idSoc => $datosCliente){
                if($cuentaCliente > 0){
                    $cuerpo.="<p style=\"width: 100%;font-family: 'Courier New', Courier, monospace;font-size: 12px;\">Por otra parte,</p>";
                }
                //Datos generales
                $clienteFormato =  file_get_contents("plantilla/cliente.html"); 
                $clienteFicha = $base->registroRowid("vol_societe", $idSoc);
                $clienteFormato = str_replace("{{nom}}",$clienteFicha['nom'],$clienteFormato);
                $clienteFormato = str_replace("{{address}}",$clienteFicha['address'],$clienteFormato);
                $clienteFormato = str_replace("{{town}}",$clienteFicha['town'],$clienteFormato);
                $cuerpo.=$clienteFormato;
                //Pagos - Domiciliacion
                if(!empty($datosCliente['banco'])){
                    $pagoFormato = file_get_contents("plantilla/banco.html"); 
                    $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['banco']);
                    $iban = $base->registroRowid("vol_societe_rib", $datosPago['fk_iban_emp'])['iban_prefix'];
                    //meses
                    $datosMes = "";
                    foreach ($mesEscrito as $mesFijo){
                        if ($datosPago[$mesFijo] == '1'){
                            if(!empty($datosMes)){
                                $datosMes .=" - ";
                            }
                            $datosMes.=$mesFijo;
                        }
                    }
                    //incluimos datos
                    $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                    $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                    $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                    $pagoFormato = str_replace("{{iban}}",$iban,$pagoFormato);
                    $cuerpo.=$pagoFormato;
                }
                //Pagos - efectivo
                if(!empty($datosCliente['efectivo'])){
                    $pagoFormato = file_get_contents("plantilla/efectivo.html"); 
                    $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['efectivo']);
                    //meses
                    $datosMes = "";
                    foreach ($mesEscrito as $mesFijo){
                        if ($datosPago[$mesFijo] == '1'){
                            if(!empty($datosMes)){
                                $datosMes .=" - ";
                            }
                            $datosMes.=$mesFijo;
                        }
                    }
                    //incluimos datos
                    $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                    $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                    $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                    $cuerpo.=$pagoFormato;
                }
    
                //Pagos - Transferencia
                if(!empty($datosCliente['transferencia'])){
                    $pagoFormato = file_get_contents("plantilla/transferencia.html"); 
                    $datosPago = $base->registroRowid("vol_CA_pago", $datosCliente['transferencia']);
                    $iban = $base->registroRowid("vol_bank_account", $datosPago['fk_iban_soc'])['iban_prefix'];
                    //meses
                    $datosMes = "";
                    foreach ($mesEscrito as $mesFijo){
                        if ($datosPago[$mesFijo] == '1'){
                            if(!empty($datosMes)){
                                $datosMes .=" - ";
                            }
                            $datosMes.=$mesFijo;
                        }
                    }
                    //incluimos datos
                    $pagoFormato = str_replace("{{importe}}",$datosPago['importe'],$pagoFormato);
                    $pagoFormato = str_replace("{{forma}}",$datosPago['formaPago'],$pagoFormato);
                    $pagoFormato = str_replace("{{periodo}}",$datosMes,$pagoFormato);
                    $pagoFormato = str_replace("{{iban}}",$iban,$pagoFormato);
                    $cuerpo.=$pagoFormato;
                }
    
                $cuentaCliente++;
            };
            //DIRECCION
            $direccionFormato = file_get_contents('plantilla/direccion.html');
            $direccionServicio = (!empty($datosCA[0]['contacto'])?($base->registroRowid("vol_socpeople", $datosCA[0]['contacto'])['address'])." ".($base->registroRowid("vol_socpeople", $datosCA[0]['contacto'])['town']):$clienteFicha['address']);
            $direccionFormato = str_replace("{{direccion}}",$direccionServicio,$direccionFormato);
            $cuerpo.=$direccionFormato;
            //ALCANCE
            $cuerpo .= file_get_contents('plantilla/alcance.html');
            //ALCANTARILLADO
            if(!is_null(($CA['alcantarillado']['id']))){
                $datosAlc=  $base->registroRowid("vol_CA_alc",$CA['alcantarillado']['id']);
                $alcantarillado = file_get_contents('plantilla/alcantarillado.html');
                //SUBTERRANEA
                if(!is_null($CA['alcantarillado']['Subt'])){
                    $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Subt']);
                    $lineaRed = '<td>Redes Subterráneas horizontales</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td>";
                    $alcantarillado = str_replace('{{alcSub}}',$lineaRed,$alcantarillado);
                    $redFormato = file_get_contents('plantilla/sub.html');
                    $arquetas = "";
                    if($datosRed['arqSif']>0){
                        $lineaArq="<td>Arquetas sifónicas</td><td>".$datosRed['arqSif']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqMue']>0){
                        $lineaArq="<td>Arquetas de tomas de muestras</td><td>".$datosRed['arqMue']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqGra']>0){
                        $lineaArq="<td>Arquetas separadoras de grasas</td><td>".$datosRed['arqGra']."</td>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['arqReg']>0){
                        $lineaArq="<td>Arquetas/registros de paso</td><td>".$datosRed['arqReg']."</td>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['sum']>0){
                        $lineaArq="<tdHusillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['tub']>0){
                        $lineaArq="<tdTuberías horizontales de conexión</td><td>".$datosRed['tub']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    $redFormato = str_replace('{{arqSub}}',$arquetas,$redFormato);
                    $redFormato = str_replace('{{obsSub}}',$datosRed['redObs'],$redFormato);
                    $alcantarillado = str_replace('{{sub}}',$redFormato,$alcantarillado);
                }else{
                    $alcantarillado = str_replace('{{alcSub}}',"",$alcantarillado);
                    $alcantarillado = str_replace('{{sub}}',"",$alcantarillado);
                }
                //AER
                if(!is_null($CA['alcantarillado']['Aer'])){
                    $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Aer']);
                    $lineaRed = '<td>Redes Aéreas horizontales</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td>";
                    $alcantarillado = str_replace('{{alcAer}}',$lineaRed,$alcantarillado);
                    $redFormato = file_get_contents('plantilla/aer.html');
                    $arquetas = "";
                    if($datosRed['arqSif']>0){
                        $lineaArq="<td>Arquetas sifónicas</td><td>".$datosRed['arqSif']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqMue']>0){
                        $lineaArq="<td>Arquetas de tomas de muestras</td><td>".$datosRed['arqMue']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqGra']>0){
                        $lineaArq="<td>Arquetas separadoras de grasas</td><td>".$datosRed['arqGra']."</td>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['sum']>0){
                        $lineaArq="<tdHusillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td>";
                        $arquetas .=$lineaArq;
                    } 
                    if($datosRed['tub']>0){
                        $lineaArq="<tdTuberías horizontales de conexión</td><td>".$datosRed['tub']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    $redFormato = str_replace('{{arqAer}}',$arquetas,$redFormato);
                    $redFormato = str_replace('{{obsAer}}',$datosRed['redObs'],$redFormato);
                    $alcantarillado = str_replace('{{aer}}',$arquetas,$alcantarillado);
                }else{
                    $alcantarillado = str_replace('{{alcAer}}',"",$alcantarillado);
                    $alcantarillado = str_replace('{{aer}}',"",$alcantarillado);
                }
                 //BOM
                 if(!is_null($CA['alcantarillado']['Bom'])){
                    $datosRed=  $base->registroRowid("vol_CA_red",$CA['alcantarillado']['Bom']);
                    $lineaRed = '<td>Redes de suelo de garajes/sótanos (BOMBEO)</td><td>'.$datosRed['cantidad']."</td><td>".$datosRed['rev']."</td><td>".$datosRed['urg']."</td>";
                    $alcantarillado = str_replace('{{alcBom}}',$lineaRed,$alcantarillado);
                    $redFormato = file_get_contents('plantilla/bom.html');
                    $arquetas = "";
                    if($datosRed['arqBom']>0){
                        $lineaArq="<td>Arqueta de bombeo</td><td>".$datosRed['arqBom']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqGra']>0){
                        $lineaArq="<td>Arquetas decantatadora/separadoras de grasas</td><td>".$datosRed['arqGra']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['sum']>0){
                        $lineaArq="<tdHusillos/imbornales/sumideros</td><td>".$datosRed['sum']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    if($datosRed['arqRej']>0){
                        $lineaArq="<td>Rejillas rampa Garaje</td><td>".$datosRed['arqRej']."</td>";
                        $arquetas .=$lineaArq;
                    }   
                    if($datosRed['tub']>0){
                        $lineaArq="<tdTuberías horizontales de conexión</td><td>".$datosRed['tub']."</td>";
                        $arquetas .=$lineaArq;
                    }
                    $redFormato = str_replace('{{arqBom}}',$arquetas,$redFormato);
                    $redFormato = str_replace('{{obsBom}}',$datosRed['redObs'],$redFormato);
                    $alcantarillado = str_replace('{{bom}}',$arquetas,$alcantarillado);
                }else{
                    $alcantarillado = str_replace('{{alcBom}}',"",$alcantarillado);
                    $alcantarillado = str_replace('{{bom}}',"",$alcantarillado);
                }
    
                $alcantarillado = str_replace('{{furgon}}',$datosAlc['furgon'],$alcantarillado);
                $alcantarillado = str_replace('{{camion}}',$datosAlc['camion'],$alcantarillado);
                $alcantarillado = str_replace('{{obsAlc}}',$datosAlc['obsAlc'],$alcantarillado);
                $cuerpo .= $alcantarillado;
            }
    
            //DESINSECTACION
            if(!is_null($CA['plaga']['desinsectacion']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desinsectacion']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['desinsectacion']['alemana'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['alemana']);
                    $especies.="<tr><td>Blatella germánica</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['americana'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['americana']);
                    $especies.="<tr><td> Periplaneta americana (Cucaracha americana) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['jardin'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['jardin']);
                    $especies.="<tr><td>Lasus niger(Hormiga negra jardín)</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['faraon'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['faraon']);
                    $especies.="<tr><td>Monomorium pharaonis (Hormigas faraón) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['argentina'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['argentina']);
                    $especies.="<tr><td>Linepithema humile(Hormiga argentina)</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinsectacion']['insecOtros'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinsectacion']['insecOtros']);
                    $especies.="<tr><td>Otro tipo de insectos (definido en Observaciones) </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //DESRATIZACION
            if(!is_null($CA['plaga']['desratizacion']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desratizacion']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['desratizacion']['rattus'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['rattus']);
                    $especies.="<tr><td> Rattus rattus </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desratizacion']['comun'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['comun']);
                    $especies.="<tr><td> Rattus norvegicus </td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desratizacion']['musculus'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desratizacion']['musculus']);
                    $especies.="<tr><td>Mus musculus</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //DESINFECCION
            if(!is_null($CA['plaga']['desinfeccion']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['desinfeccion']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',"",$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['desinfeccion']['virus'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['virus']);
                    $especies.="<tr><td>Virus</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinfeccion']['bacteria'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['bacteria']);
                    $especies.="<tr><td>Bacterias</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                if(!is_null($CA['plaga']['desinfeccion']['germenes'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['desinfeccion']['germenes']);
                    $especies.="<tr><td>Germenes</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
            //OTRAS ESPECIES
            if(!is_null($CA['plaga']['otras']['id'])){
                $plagaFormato = file_get_contents('plantilla/plaga.html');
                $datosPlaga =  $base->registroRowid("vol_CA_plaga",$CA['plaga']['otras']['id']);
                //Datos plaga
                $plagaFormato = str_replace('{{zona}}',$datosPlaga['alcance'],$plagaFormato);
                $plagaFormato = str_replace('{{obs}}',$datosPlaga['obs'],$plagaFormato);
                $plagaFormato = str_replace('{{tipo}}',$datosPlaga['tipo'],$plagaFormato);
                $plagaFormato = str_replace('{{tratamiento}}',$datosPlaga['otrosTratamiento'],$plagaFormato);
                //especies
                $especies = "";
                if(!is_null($CA['plaga']['otras']['otro'])){
                    $datosPlaga = $base->registroRowid("vol_CA_especie",$CA['plaga']['otras']['otro']);
                    $especies.="<tr><td>".$datosPlaga['otrosNombre']."</td><td>".$datosPlaga['cantidad']."</td><td>".$datosPlaga['rev']."</td><td>".$datosPlaga['reinf']."</td></tr>";
                }
                $plagaFormato = str_replace('{{plaga}}',$especies,$plagaFormato);
                $cuerpo .=$plagaFormato;
            }
    
            //CONDICIONES
            $condicionesFormato = file_get_contents('plantilla/condiciones.html');
            $cuerpo .= $condicionesFormato;
            //CONDICIONES GENERALES
            $condicionesGenerales = $datosCA[0]['clausulasAdicionales'];
            $condicionesFormato = file_get_contents('plantilla/condicionesAdic.html');
            $condicionesFormato = str_replace('{{particulares}}',$condicionesGenerales,$condicionesFormato);
            $cuerpo .= $condicionesFormato;
    
        //OBJETO WORD
            $config['format'] = 'A4';
            $config['margin_top'] = 58;
            $config['margin_header'] = 5;
            $config['margin_bottom'] = 50;
            $config['margin_footer'] = 2;

            $ficheroWord = new HTML_TO_MSDOC();

            //Insertamos Cabecera y Pie
            //$ficheroWord->getHeader($cabecera);
            //$ficheroWord->getFooter($pie);
            //Insertamos cuerpo
            $ficheroWord->WriteHTML($cuerpo);
            
            //Generamos el PDF
            $ficheroWord->createDoc($cuerpo, "word");
            
        };



         function verCA($id){
    $base = new BD();
     //SOCIEDADES Y PAGO
     $salida['id'] = $id;
     $CAsoc = $base->query("SELECT * FROM vol_CA_soc WHERE fk_CA ='".$id."'");
     foreach($CAsoc as $soc){
         $socRowid = $soc['fk_soc'];
         //crear id soc
         $salida['soc'][$socRowid]['id'] = $soc['rowid'];
         //Pago banco
         $salida['soc'][$socRowid]['banco'] = ($base->query("SELECT rowid from vol_CA_pago where tipo = 'banco' and fk_CA = '".$soc['rowid']."'")[0][0]);
         //Pago efectivo
         $salida['soc'][$socRowid]['efectivo'] = ($base->query("SELECT rowid from vol_CA_pago where tipo = 'efectivo' and fk_CA = '".$soc['rowid']."'")[0][0]);
         //Pago transferencia
         $salida['soc'][$socRowid]['transferencia'] = ($base->query("SELECT rowid from vol_CA_pago where tipo = 'transferencia' and fk_CA = '".$soc['rowid']."'")[0][0]);
     }

     //REDES
     $CAalc = $base->query("SELECT vol_CA_alc.rowid AS 'id', 
     (SELECT vol_CA_red.rowid FROM vol_CA_red WHERE tipo = 'Subt' AND fk_CA_alc = vol_CA_alc.rowid) AS 'Subt', 
     (SELECT vol_CA_red.rowid FROM vol_CA_red WHERE tipo = 'Aer' AND fk_CA_alc = vol_CA_alc.rowid) AS 'Aer',
     (SELECT vol_CA_red.rowid FROM vol_CA_red WHERE tipo = 'Bom' AND fk_CA_alc = vol_CA_alc.rowid) AS 'Bom'  
      FROM vol_CA_alc WHERE fk_CA =".$id);
     $salida['alcantarillado']['id'] = $CAalc[0]['id']; 
     $salida['alcantarillado']['Subt'] = $CAalc[0]['Subt'];
     $salida['alcantarillado']['Aer'] = $CAalc[0]['Aer'];
     $salida['alcantarillado']['Bom'] = $CAalc[0]['Bom'];    
     //PLAGAS
     $CAPlag = $base->query("SELECT vol_CA.rowid AS 'id', 
     (SELECT vol_CA_plaga.rowid FROM vol_CA_plaga WHERE tipo = 'desinsectacion' AND vol_CA_plaga.fk_CA = vol_CA.rowid) AS 'desinsectacion', 
     (SELECT vol_CA_plaga.rowid FROM vol_CA_plaga WHERE tipo = 'desratizacion' AND vol_CA_plaga.fk_CA = vol_CA.rowid) AS 'desratizacion',
     (SELECT vol_CA_plaga.rowid FROM vol_CA_plaga WHERE tipo = 'desinfeccion' AND vol_CA_plaga.fk_CA = vol_CA.rowid) AS 'desinfeccion',
     (SELECT vol_CA_plaga.rowid FROM vol_CA_plaga WHERE tipo = 'otros' AND vol_CA_plaga.fk_CA = vol_CA.rowid) AS 'otras'
      FROM vol_CA WHERE vol_CA.rowid =". $salida['id']); 
     $salida['plaga']['desinsectacion']['id'] = $CAPlag[0]['desinsectacion'];
     $salida['plaga']['desratizacion']['id'] = $CAPlag[0]['desratizacion'];
     $salida['plaga']['desinfeccion']['id'] = $CAPlag[0]['desinfeccion'];
     $salida['plaga']['otras']['id'] = $CAPlag[0]['otras'];

    //ESPECIES
    //DESIN
      $CAinsec = $base->query("SELECT vol_CA_plaga.rowid AS 'id',
      (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'americana') AS 'americana',
      (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'alemana') AS 'alemana',
      (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'argentina') AS 'argentina',
      (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'faraon') AS 'faraon',
      (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'jardin') AS 'jardin',
      (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'insecOtros') AS 'insecOtros'
      
       FROM vol_CA_plaga  WHERE vol_CA_plaga.rowid =".$salida['plaga']['desinsectacion']['id']);
       $salida['plaga']['desinsectacion']['americana'] = $CAinsec[0]['americana'];
       $salida['plaga']['desinsectacion']['alemana'] = $CAinsec[0]['alemana'];
       $salida['plaga']['desinsectacion']['argentina'] = $CAinsec[0]['argentina'];
       $salida['plaga']['desinsectacion']['faraon'] = $CAinsec[0]['faraon'];
       $salida['plaga']['desinsectacion']['jardin'] = $CAinsec[0]['jardin'];
       $salida['plaga']['desinsectacion']['insecOtros'] = $CAinsec[0]['insecOtros'];
    
    //DESRA
        $CAdesra = $base->query("SELECT vol_CA_plaga.rowid AS 'id',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'rattus') AS 'rattus',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'comun') AS 'comun',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'musculus') AS 'musculus'
        
         FROM vol_CA_plaga  WHERE vol_CA_plaga.rowid =".$salida['plaga']['desratizacion']['id']);
        
        $salida['plaga']['desratizacion']['rattus'] = $CAdesra[0]['rattus'];
        $salida['plaga']['desratizacion']['comun'] = $CAdesra[0]['comun'];
        $salida['plaga']['desratizacion']['musculus'] = $CAdesra[0]['musculus'];
    
    //DESIN
        $CADesin = $base->query("SELECT vol_CA_plaga.rowid AS 'id',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'virus') AS 'virus',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'bacteria') AS 'bacteria',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'germenes') AS 'germenes'
         FROM vol_CA_plaga  WHERE vol_CA_plaga.rowid =".$salida['plaga']['desinfeccion']['id']);
            $salida['plaga']['desinfeccion']['virus'] = $CADesin[0]['virus'];
            $salida['plaga']['desinfeccion']['bacteria'] = $CADesin[0]['bacteria'];
            $salida['plaga']['desinfeccion']['germenes'] = $CADesin[0]['germenes'];

    //OTROS
        $CAOtros = $base->query("SELECT vol_CA_plaga.rowid AS 'id',
        (SELECT vol_CA_especie.rowid FROM vol_CA_especie WHERE fk_plaga = vol_CA_plaga.rowid AND tipo = 'otros') AS 'otros'
        FROM vol_CA_plaga  WHERE vol_CA_plaga.rowid =".$salida['plaga']['otras']['id']);
            $salida['plaga']['otras']['otro'] = $CAOtros[0]['otros'];

     return $salida;
    
}

function crearPedidos2021($partes,$CA){
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
        //CREAR PEDIDOS
    $i = 0;
    $base = new BD();
    $datosCA = $base->query("select * from vol_CA where rowid = ".$CA);
    $mesContrato = date ('m',strtotime($datosCA[0]['fechaActivo'])); 
    $sociedades = $base->query("SELECT fk_soc FROM vol_CA_soc where fk_CA = '".$CA."'");
    $soc = $sociedades[0]['fk_soc'];
    foreach($partes as $parte){
        $hoy = date ('Y-m-d');
        $ano = date ('Y');
        $mes = date ('m');
            //FECHAS PREVISTAS
        switch($parte['fecha']){
            case 'ENERO':
                if ($mes >1){
                    $fecha = ($ano+1)."-01-01";
                }else{
                    $fecha = ($ano)."-01-01";
                }    
            break;
            case 'FEBRERO':
                if ($mes >2){
                    $fecha = ($ano+1)."-02-01";
                }else{
                    $fecha = ($ano)."-02-01";
                }
            break;
            case 'MARZO':
                if ($mes >3){
                    $fecha = ($ano+1)."-03-01";
                }else{
                    $fecha = ($ano)."-03-01";
                }
            break;
            case 'ABRIL':
                if ($mes >4){
                    $fecha = ($ano+1)."-04-01";
                }else{
                    $fecha = ($ano)."-04-01";
                }
            break;
            case 'MAYO':
                if ($mes >5){
                    $fecha = ($ano+1)."-05-01";
                }else{
                    $fecha = ($ano)."-05-01";
                }
            break;
            case 'JUNIO':
                if ($mes >6){
                    $fecha = ($ano+1)."-06-01";
                }else{
                    $fecha = ($ano)."-06-01";
                }
            break;
            case 'JULIO':
                if ($mes >7){
                    $fecha = ($ano+1)."-07-01";
                }else{
                    $fecha = ($ano)."-07-01";
                }
            break;
            case 'AGOSTO':
                if ($mes >8){
                    $fecha = ($ano+1)."-08-01";
                }else{
                    $fecha = ($ano)."-08-01";
                }
            break;
            case 'SEPTIEMBRE':
                if ($mes >9){
                    $fecha = ($ano+1)."-09-01";
                }else{
                    $fecha = ($ano)."-09-01";
                }
            break;
            case 'OCTUBRE':
                if ($mes >10){
                    $fecha = ($ano+1)."-10-01";
                }else{
                    $fecha = ($ano)."-10-01";
                }
            break;
            case 'NOVIEMBRE':
                if ($mes >11){
                    $fecha = ($ano+1)."-11-01";
                }else{
                    $fecha = ($ano)."-11-01";
                }
            break;
            case 'DICIEMBRE':
                if ($mes >12){
                    $fecha = ($ano+1)."-12-01";
                }else{
                    $fecha = ($ano)."-12-01";
                }
            break;
        }
        //TIPO
        switch($parte['tipo']){
            case 'ALCANTARILLADO':
                $serie = '5';
                $tipo = '8';
            break;
            case 'CONJUNTA':
                $serie = '5';
                $tipo = '10';
                break;
            case 'PLAGAS':
                $serie = '6';
                $tipo = '9';
            break;
        }
        $api['date'] = strtotime($fecha);
        $api['socid'] = $soc;
        $api['lines'][0]['qty'] = '1';
        $api['lines'][0]['price'] = '0';
        $api['lines'][0]['description'] = $parte['descripcion'];
        $api['lines'][0]['desc'] = $parte['descripcion'];
        $api['array_options']['options_serie'] = $serie;
        $api['array_options']['options_tipserv'] = $tipo;
        $api['array_options']['options_obs'] = $parte['observaciones'];
        //INSERTAMOS EN DOLIBARR
        $mesPedido =  date ('m',strtotime($fecha)); 
        if ($datosCA[0]['fechaActivo'] > $fecha){

        }else{
            $pedidoDoli[$i] = callAPI('POST', $keyAPI, $urlAPI.'orders', json_encode($api));
        }
       
        $i++;

        
    }

    return $pedidoDoli;

}


function crearFacturas2021($soc,$pagoBanco,$pagoEfectivo,$pagoTransferencia){
    $salida = array();
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    //CREAR FACTURAS
    // Domiciliacion
    $concepto = "Factura por los servicios contratados, conforme al contrato realizado y estipulado en la sección de la forma de Pago.";
    if($pagoBanco !=''){
        $hoy = date ('Y-m-d');
        $ano = date ('Y');
        $mes = date ('m');
        $pago = $base->query('SELECT * FROM  vol_CA_pago WHERE rowid ='.$pagoBanco);
        $apiFactura['socid'] = $soc;
        $apiFactura['lines'][0]['desc'] = $concepto;
        $apiFactura['lines'][0]['description'] = $concepto;
        $apiFactura['lines'][0]['qty'] = '1';
        $importe = floatval($pago[0]['importe']);
        $facturaImporte = 0;
        //PERIODO
        switch($pago[0]['formaPago']){
        case "Anual":
            $facturaImporte =$importe/1;
        break;
        case "Semestral":
            $facturaImporte = $importe/2;
        break;
        case "Trimestral":
            $facturaImporte = $importe/4;
        break;
        case "Bimensual":
            $facturaImporte = $importe/6;
        break;
        case "Mensual":
            $facturaImporte = $importe/12;
        break;    
        }
        $apiFactura['lines'][0]['subprice'] = $facturaImporte;
            $facturasBanco = array();
        //FECHAS PREVISTAS Y CREACIÓN FACTURA
                if ($pago[0]['enero'] == 1){
                    if($mes>1){
                        $fecha = ($ano+1)."-01-01";
                    }else{
                        $fecha = ($ano)."-01-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['febrero'] == 1){
                    if($mes>2){
                        $fecha = ($ano+1)."-02-01";
                    }else{
                        $fecha = ($ano)."-02-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura))));
                }
                if ($pago[0]['marzo'] == 1){
                    if($mes>3){
                        $fecha = ($ano+1)."-03-01";
                    }else{
                        $fecha = ($ano)."-03-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura))));
                }
                if ($pago[0]['abril'] == 1){
                    if($mes>4){
                        $fecha = ($ano+1)."-04-01";
                    }else{
                        $fecha = ($ano)."-04-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['mayo'] == 1){
                    if($mes>5){
                        $fecha = ($ano+1)."-05-01";
                    }else{
                        $fecha = ($ano)."-05-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['junio'] == 1){
                    if($mes>6){
                        $fecha = ($ano+1)."-06-01";
                    }else{
                        $fecha = ($ano)."-06-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['julio'] == 1){
                    if($mes>7){
                        $fecha = ($ano+1)."-07-01";
                    }else{
                        $fecha = ($ano)."-07-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['agosto'] == 1){
                    if($mes>8){
                        $fecha = ($ano+1)."-08-01";
                    }else{
                        $fecha = ($ano)."-08-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['septiembre'] == 1){
                    if($mes>9){
                        $fecha = ($ano+1)."-09-01";
                    }else{
                        $fecha = ($ano)."-09-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['octubre'] == 1){
                    if($mes>10){
                        $fecha = ($ano+1)."-10-01";
                    }else{
                        $fecha = ($ano)."-10-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['noviembre'] == 1){
                    if($mes>11){
                        $fecha = ($ano+1)."-11-01";
                    }else{
                        $fecha = ($ano)."-11-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                if ($pago[0]['diciembre'] == 1){
                    if($mes>12){
                        $fecha = ($ano+1)."-12-01";
                    }else{
                        $fecha = ($ano)."-12-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasBanco,callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)));
                }
                            
    }      
    // Efectivo
    if($pagoEfectivo !=''){
        $hoy = date ('Y-m-d');
        $ano = date ('Y');
        $mes = date ('m');
        $pago = $base->query('SELECT * FROM  vol_CA_pago WHERE rowid ='.$pagoEfectivo);
        $apiFactura['socid'] = $soc;
        $apiFactura['lines'][0]['desc'] = $concepto;
        $apiFactura['lines'][0]['description'] = $concepto;
        $apiFactura['lines'][0]['qty'] = '1';
        $importe = floatval($pago[0]['importe']);
        //PERIODO
        switch($pago[0]['formaPago']){
        case "Anual":
            $facturaImporte = $importe /1;
        break;
        case "Semestral":
            $facturaImporte = $importe /2;
        break;
        case "Trimestral":
            $facturaImporte = $importe /4;
        break;
        case "Bimensual":
            $facturaImporte = $importe/6;
        break;
        case "Mensual":
            $facturaImporte = $importe /12;
        break;    
        }
        $apiFactura['lines'][0]['subprice'] = $facturaImporte;
            $facturasEfectivo = array();
        //FECHAS PREVISTAS Y CREACIÓN FACTURA)
            if ($pago[0]['enero']){
                    if($mes>1){
                        $fecha = ($ano+1)."-01-01";
                    }else{
                        $fecha = ($ano)."-01-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['febrero']){
                    if($mes>2){
                        $fecha = ($ano+1)."-02-01";
                    }else{
                        $fecha = ($ano)."-02-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['marzo']){
                    if($mes>3){
                        $fecha = ($ano+1)."-03-01";
                    }else{
                        $fecha = ($ano)."-03-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['abril']){
                    if($mes>4){
                        $fecha = ($ano+1)."-04-01";
                    }else{
                        $fecha = ($ano)."-04-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['mayo']){
                    if($mes>5){
                        $fecha = ($ano+1)."-05-01";
                    }else{
                        $fecha = ($ano)."-05-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['junio']){
                    if($mes>6){
                        $fecha = ($ano+1)."-06-01";
                    }else{
                        $fecha = ($ano)."-06-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['julio']){
                    if($mes>7){
                        $fecha = ($ano+1)."-07-01";
                    }else{
                        $fecha = ($ano)."-07-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['agosto']){
                    if($mes>8){
                        $fecha = ($ano+1)."-08-01";
                    }else{
                        $fecha = ($ano)."-08-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['septiembre']){
                    if($mes>9){
                        $fecha = ($ano+1)."-09-01";
                    }else{
                        $fecha = ($ano)."-09-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['octubre']){
                    if($mes>10){
                        $fecha = ($ano+1)."-10-01";
                    }else{
                        $fecha = ($ano)."-10-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['noviembre']){
                    if($mes>11){
                        $fecha = ($ano+1)."-11-01";
                    }else{
                        $fecha = ($ano)."-11-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                }
                if ($pago[0]['diciembre']){
                    if($mes>12){
                        $fecha = ($ano+1)."-12-01";
                    }else{
                        $fecha = ($ano)."-12-01";
                    }
                    $apiFactura['date'] = strtotime($fecha);
                    array_push($facturasEfectivo,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
                } 
            }   
    // Transferencia
    if($pagoTransferencia !=''){
        $hoy = date ('Y-m-d');
    $ano = date ('Y');
    $mes = date ('m');
    $pago = $base->query('SELECT * FROM  vol_CA_pago WHERE rowid ='.$pagoTransferencia);
    $apiFactura['socid'] = $soc;
    $apiFactura['lines'][0]['desc'] = $concepto;
    $apiFactura['lines'][0]['description'] = $concepto;
    $apiFactura['lines'][0]['qty'] = '1';
    $importe = floatval($pago[0]['importe']);
    //PERIODO
    switch($pago[0]['formaPago']){
    case "Anual":
        $facturaImporte = $importe /1;
    break;
    case "Semestral":
        $facturaImporte = $importe /2;
    break;
    case "Trimestral":
        $facturaImporte = $importe /4;
    break;
    case "Bimensual":
        $facturaImporte = $importe/6;
    break;
    case "Mensual":
        $facturaImporte = $importe /12;
    break;    
    }
    $apiFactura['lines'][0]['subprice'] = $facturaImporte;
        $facturasTransferencia = array();
    //FECHAS PREVISTAS Y CREACIÓN FACTURA
            if ($pago[0]['enero']){
                if($mes>1){
                    $fecha = ($ano+1)."-01-01";
                }else{
                    $fecha = ($ano)."-01-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['febrero']){
                if($mes>2){
                    $fecha = ($ano+1)."-02-01";
                }else{
                    $fecha = ($ano)."-02-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['marzo']){
                if($mes>3){
                    $fecha = ($ano+1)."-03-01";
                }else{
                    $fecha = ($ano)."-03-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['abril']){
                if($mes>4){
                    $fecha = ($ano+1)."-04-01";
                }else{
                    $fecha = ($ano)."-04-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['mayo']){
                if($mes>5){
                    $fecha = ($ano+1)."-05-01";
                }else{
                    $fecha = ($ano)."-05-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['junio']){
                if($mes>6){
                    $fecha = ($ano+1)."-06-01";
                }else{
                    $fecha = ($ano)."-06-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['julio']){
                if($mes>7){
                    $fecha = ($ano+1)."-07-01";
                }else{
                    $fecha = ($ano)."-07-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['agosto']){
                if($mes>8){
                    $fecha = ($ano+1)."-08-01";
                }else{
                    $fecha = ($ano)."-08-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['septiembre']){
                if($mes>9){
                    $fecha = ($ano+1)."-09-01";
                }else{
                    $fecha = ($ano)."-09-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['octubre']){
                if($mes>10){
                    $fecha = ($ano+1)."-10-01";
                }else{
                    $fecha = ($ano)."-10-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['noviembre']){
                if($mes>11){
                    $fecha = ($ano+1)."-11-01";
                }else{
                    $fecha = ($ano)."-11-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }
            if ($pago[0]['diciembre']){
                if($mes>12){
                    $fecha = ($ano+1)."-12-01";
                }else{
                    $fecha = ($ano)."-12-01";
                }
                $apiFactura['date'] = strtotime($fecha);
                array_push($facturasTransferencia,json_decode(callAPI('POST', $keyAPI, $urlAPI.'invoices', json_encode($apiFactura)),true));
            }      
        }

       
        $salida['banco'] = $facturasBanco;
        $salida['efectivo'] = $facturasEfectivo;
        $salida['transferencia'] = $facturasTransferencia;
        return $salida;

}


//CALLBACKS APIS

if(isset($_POST['datosCliente'])){
    $base = new BD();
    $cliente = $base->query("SELECT * from vol_societe WHERE rowid = '".$_POST['clienteId']."'");
    echo json_encode($cliente[0]);
}


if(isset($_POST['direccionesCliente'])){
    $base = new BD();
    $cliente = $base->query("SELECT * from vol_socpeople WHERE fk_soc = '".$_POST['clienteId']."'");
    echo json_encode($cliente); 
}


if(isset($_POST['cuentasCliente'])){
    $base = new BD();
    $cliente = $base->query("SELECT rowid,iban_prefix from vol_societe_rib WHERE fk_soc ='".$_POST['clienteId']."'");
    echo json_encode($cliente); 
}

if(isset($_POST['cuentasCastillo'])){
    $base = new BD();
    $cliente = $base->query("SELECT * from vol_bank_account WHERE iban_prefix != ''");
    echo json_encode($cliente); 
}

if(isset($_POST['clienteBusqueda'])){
    $base = new BD();
    $cliente = $base->query("SELECT rowid as 'soc' FROM vol_societe WHERE nom LIKE '%".$_POST['busqueda']."%' OR code_client LIKE '%".$_POST['busqueda']."%' OR address LIKE '%".$_POST['busqueda']."%' OR phone LIKE '%".$_POST['busqueda']."%' OR email LIKE '%".$_POST['busqueda']."%' OR tva_intra LIKE '%".$_POST['busqueda']."%' GROUP BY rowid");

    $contacto = $base->query("SELECT fk_soc as 'soc' FROM vol_socpeople WHERE lastname LIKE '%".$_POST['busqueda']."%' OR address LIKE '%".$_POST['busqueda']."%' OR phone LIKE '%".$_POST['busqueda']."%' OR email LIKE '%".$_POST['busqueda']."%' GROUP BY fk_soc");

    $contratobusq = $base->query("SELECT fk_soc as 'soc' FROM vol_contrat WHERE ref_customer LIKE '%".$_POST['busqueda']."%' OR ref LIKE '%".$_POST['busqueda']."%' GROUP BY fk_soc");

    $busquedaSoc = array_merge($cliente, $contacto,$contratobusq);
    $i = 0;
     foreach($busquedaSoc as $soc){
        $sociedad = $base->query("SELECT vol_societe.rowid,code_client,nom,address,phone,email,vol_contrat.rowid as 'contrat', vol_contrat.ref_customer as 'contrato' from vol_societe left join vol_contrat on vol_contrat.fk_soc = vol_societe.rowid where vol_societe.rowid ='".$soc[0]."'");
        $salida[$i] = $sociedad[0];
        $i++;
    }
    echo json_encode($salida); 
}


if(isset($_POST['referenciaCont'])){
    $base = new BD();
    $cliente = $base->query("SELECT MAX(LEFT(ref_customer,4)) FROM vol_contrat WHERE ref_customer LIKE '%".$_POST['prefijo']."'");
    if(is_null($cliente[0][0])){
        echo 1;
    }else{
        echo $cliente[0][0]+1;
    }
}


if(isset($_POST['referenciaPres'])){
    $base = new BD();
    $anio = date("Y");
    $cliente = $base->query("SELECT MAX(LEFT(ref_customer,4)) FROM vol_contrat WHERE ref_customer LIKE '%-Pr%' and YEAR(date_contrat) = ".$anio);
    if(is_null($cliente[0][0])){
        echo 1;
    }else{
        echo $cliente[0][0]+1;
    }
    
}

if(isset($_POST['envioForm'])){

    echo json_encode($_POST); 
}

if(isset($_POST['datosCA'])){
    $CA = verCA($_POST['id']);
    echo json_encode($CA); 
}


if(isset($_POST['nuevoContrato'])){
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];   
    //RECOGE DATOS
    $datos = $_POST;
    //REALIZA CA
    $envio = creaCA($datos);
    $cliente = $datos['clienteId'];
    $sociedades = json_decode($datos['sociedades'],true);
    //REALIZA PARTES
    $partes = json_decode($_POST['partes'],true);
    $pedidosCreados = crearPedidos($partes,$envio['id']);
    $i = 0;
    //AÑADE DIRECCION direccionNew
    if ($datos['direccionNew'] != ""){
        //agregamos dirección
        $api['address'] = $datos['direccionNew'];
        $api['zip'] = $datos['cpNew'];
        $api['lastname'] = $api['address'];
        $api['town'] = $datos['localidadNew'];
        $api['phone_mobile'] = $datos['telefonoNew'];
        $api['firstname'] = $datos['contactoNew'];
        $api['socid'] = $datos['clienteId'];
        $idContacto = callAPI('POST', $keyAPI, $urlAPI.'contacts/',json_encode($api));
    }else if ($datos['direccionServicio'] != "ficha"){
        $idContacto = $datos['direccionServicio'];
    }else {
        $idContacto = "NO";
    }
    //AÑADE DIRECCION PARTE
    if ($idContacto !="NO"){
        foreach ($pedidosCreados as $pedido){
           agregaContacto('pedido',$pedido,$idContacto);
        }
        //AÑADE DIRECCION CA
        $base->modificaRowid("vol_CA", $envio['id'],"contacto",$idContacto);
    }


    //AÑADE FACTURAS PPAL
    $pagoBanco = (is_null($envio['soc'][$cliente]['pago']['banco']))?"":$envio['soc'][$cliente]['pago']['banco'];
    $pagoEfectivo = (is_null($envio['soc'][$cliente]['pago']['efectivo']))?"":$envio['soc'][$cliente]['pago']['efectivo'];
    $pagoTransferencia = (is_null($envio['soc'][$cliente]['pago']['transferencia']))?"":$envio['soc'][$cliente]['pago']['transferencia'];
    $facturasCreadas[$cliente] = crearFacturas($cliente,$pagoBanco,$pagoEfectivo,$pagoTransferencia);


    //AÑADE FACTURAS OTROS
    foreach ($sociedades as $socOtro){
        $pagoBanco = (is_null($envio['soc'][$socOtro]['pago']['banco']))?"":$envio['soc'][$socOtro]['pago']['banco'];
        $pagoEfectivo = (is_null($envio['soc'][$socOtro]['pago']['efectivo']))?"":$envio['soc'][$socOtro]['pago']['efectivo'];
        $pagoTransferencia = (is_null($envio['soc'][$socOtro]['pago']['transferencia']))?"":$envio['soc'][$socOtro]['pago']['transferencia'];
        $facturasCreadas[$socOtro] = crearFacturas($socOtro,$pagoBanco,$pagoEfectivo,$pagoTransferencia);
    }

    //CREAR CONTRATO PPAL
    $contrato['socid'] = $cliente;
    $contrato['date_contrat'] = strtotime($datos['fecha']);
    $contrato['ref_customer'] = $datos['referencia'];
    $contrato['commercial_signature_id'] = '5';
    $contrato['commercial_suivi_id'] = '5';
    $contLineas['statut'] = '4';
    $contLineas['date_start'] = strtotime($datos['fecha']);
    $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['qty'] = '1';
    $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
    $contratoId = callAPI('POST', $keyAPI, $urlAPI."contracts",json_encode($contrato));
    //INSERTA LÍNEA
    $servicioActivo = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/lines",json_encode($contLineas));
    //CONFIRMAR CONTRATO
    $confirma = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/validate",'1');
    //ACTIVAR LINEA
    $activa['datestart'] = strtotime($datos['fecha']);
    callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$servicioActivo."/activate",json_encode($activa));
    $salidaN = $contratoId; 
    
    //AÑADE DIRECCION CONTRATO
    if($idContacto != 'NO'){
        $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
        $valores = ['4',$contratoId,'21',$idContacto];
        $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
    }

    //AÑADE OBJETOS A CONTRATO
    //pedidos
    foreach($pedidosCreados as $pedido){
        $keys = ['fk_source','sourcetype','fk_target','targettype'];
        $valores = [$pedido,'commande',$contratoId,'contrat'];
        $pedidosConfirma = $base->nuevoRegistro("vol_element_element", $keys,$valores);

    }
    //facturas ppal
    foreach($facturasCreadas[$cliente] as $facTipo){
        foreach ($facTipo as $id){
            if (!is_null($id)){
                $keys = ['fk_source','sourcetype','fk_target','targettype'];
                $valores = [$id,'facture',$contratoId,'contrat'];
                $facturaConfirma  = $base->nuevoRegistro("vol_element_element", $keys,$valores);
            }
        }
            
        
    }

    //AÑADIR CONTRATO A vol_CA_soc
    $base = new BD();
    $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$cliente."'");
    $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId);

    //CREAR CONTRATO con otros
    foreach ($sociedades as $socOtro){
                //CREAR CONTRATO
            $contrato['socid'] = $socOtro;
            $contrato['date_contrat'] = strtotime($datos['fecha']);
            $contrato['ref_customer'] = $datos['referencia'];
            $contrato['commercial_signature_id'] = '5';
            $contrato['commercial_suivi_id'] = '5';
            $contLineas['statut'] = '4';
            $contLineas['date_start'] = strtotime($datos['fecha']);
            $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
            $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
            $contLineas['qty'] = '1';
            $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
            $contratoId = callAPI('POST', $keyAPI, $urlAPI."contracts",json_encode($contrato));
            //INSERTA LÍNEA
            $servicioActivo = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/lines",json_encode($contLineas));
            //CONFIRMAR CONTRATO
            $confirma = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/validate",'1');
            //ACTIVAR LINEA
            $activa['datestart'] = strtotime($datos['fecha']);
            callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$servicioActivo."/activate",json_encode($activa));
            //AÑADE DIRECCION CONTRATO
            if($idContacto != 'NO'){
                $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
                $valores = ['4',$contratoId,'21',$idContacto];
                $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
            }

            //AÑADE OBJETOS A CONTRATO
            
            //pedidos
            foreach($pedidosCreados as $pedido){
                $keys = ['fk_source','sourcetype','fk_target','targettype'];
                $valores = [$pedido,'commande',$contratoId,'contrat'];
                $pedidosConfirma = $base->nuevoRegistro("vol_element_element", $keys,$valores);
             }

            //facturas ppal
            foreach($facturasCreadas[$socOtro] as $tipoFact){
                   foreach($tipoFact as $id){
                    if (!is_null($id)){
                        $keys = ['fk_source','sourcetype','fk_target','targettype'];
                        $valores = [$id,'facture',$contratoId,'contrat'];
                        $facturaConfirma  = $base->nuevoRegistro("vol_element_element", $keys,$valores);
                    }

                   }
                    
            }
            
           //AÑADIR CONTRATO A vol_CA_soc
            $base = new BD();
            $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$socOtro."'");
            $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId); 
    }

    //CREAR LOG
    //Nuevo
    $keys = ['fk_CA','log','situacion'];
    $valores = [$envio['id'],'nuevo',"Sit-1"];
    $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);
    //Activo
    $keys = ['fk_CA','log','situacion'];
    $valores = [$envio['id'],'activo',"Sit-1"];
    $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);

    //CREA DOCUMENTOS
     //CREA PDF
     $contratoRef = $base->registroRowid("vol_contrat", $contratoId);
     //CREA CARPETA SI NO EXISTE
     if (!file_exists("../../../documents/contract/".$contratoRef['ref'])){
         mkdir("../../../documents/contract/".$contratoRef['ref'],0777);
     }
     crearPDF($envio['id'],("../../../documents/contract/".$contratoRef['ref']."/".$contratoRef['ref']."-".$envio['id'].".pdf"));

    echo $salidaN ;
}

if(isset($_POST['nuevoPresupuesto'])){
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];   
    //RECOGE DATOS
    $datos = $_POST;
    //REALIZA CA
    $envio = creaCA($datos);
    $cliente = $datos['clienteId'];
    $sociedades = json_decode($datos['sociedades'],true);
    $i = 0;
    //AÑADE DIRECCION direccionNew
    if ($datos['direccionNew'] != ""){
        //agregamos dirección
        $api['address'] = $datos['direccionNew'];
        $api['zip'] = $datos['cpNew'];
        $api['lastname'] = $api['address'];
        $api['town'] = $datos['localidadNew'];
        $api['phone_mobile'] = $datos['telefonoNew'];
        $api['firstname'] = $datos['contactoNew'];
        $api['socid'] = $datos['clienteId'];
        $idContacto = callAPI('POST', $keyAPI, $urlAPI.'contacts/',json_encode($api));
    }else if ($datos['direccionServicio'] != "ficha"){
        $idContacto = $datos['direccionServicio'];
    }else {
        $idContacto = "NO";
    }
    //AÑADE DIRECCION CA    
    if ($idContacto !="NO"){ 
        $base->modificaRowid("vol_CA", $envio['id'],"contacto",$idContacto);
    }


    //CREAR CONTRATO PPAL
    $contrato['socid'] = $cliente;
    $contrato['date_contrat'] = strtotime($datos['fecha']);
    $contrato['ref_customer'] = $datos['referencia'];
    $contrato['commercial_signature_id'] = '5';
    $contrato['commercial_suivi_id'] = '5';
    $contLineas['statut'] = '4';
    $contLineas['date_start'] = strtotime($datos['fecha']);
    $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['qty'] = '1';
    $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
    $contratoId = callAPI('POST', $keyAPI, $urlAPI."contracts",json_encode($contrato));
    //INSERTA LÍNEA
    $servicioActivo = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/lines",json_encode($contLineas));
    //AÑADE DIRECCION CONTRATO
    if($idContacto != 'NO'){
        $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
        $valores = ['4',$contratoId,'21',$idContacto];
        $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
    }

    //AÑADIR CONTRATO A vol_CA_soc
    $base = new BD();
    $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$cliente."'");
    $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId);

    //CREAR CONTRATO con otros
    foreach ($sociedades as $socOtro){
                //CREAR CONTRATO
            $contrato['socid'] = $socOtro;
            $contrato['date_contrat'] = strtotime($datos['fecha']);
            $contrato['ref_customer'] = $datos['referencia'];
            $contrato['commercial_signature_id'] = '5';
            $contrato['commercial_suivi_id'] = '5';
            $contLineas['statut'] = '4';
            $contLineas['date_start'] = strtotime($datos['fecha']);
            $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
            $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
            $contLineas['qty'] = '1';
            $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
            $contratoId = callAPI('POST', $keyAPI, $urlAPI."contracts",json_encode($contrato));
            //INSERTA LÍNEA
            $servicioActivo = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/lines",json_encode($contLineas));
            //AÑADE DIRECCION CONTRATO
            if($idContacto != 'NO'){
                $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
                $valores = ['4',$contratoId,'21',$idContacto];
                $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
            }

            
           //AÑADIR CONTRATO A vol_CA_soc
            $base = new BD();
            $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$socOtro."'");
            $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId); 
    }

    //CREAR LOG
    //Nuevo
    $keys = ['fk_CA','log','situacion'];
    $valores = [$envio['id'],'presupuesto','Sit-0'];
    $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);

    //CREA DOCUMENTOS
     //CREA PDF
     $contratoRef = $base->registroRowid("vol_contrat", $contratoId);
     //CREA CARPETA SI NO EXISTE
     if (!file_exists("../../../documents/contract/".$contratoRef['ref'])){
         mkdir("../../../documents/contract/".$contratoRef['ref'],0777);
     }
     crearPresPDF($envio['id'],("../../../documents/contract/".$contratoRef['ref']."/".$contratoId.".pdf"));

    echo $contratoId ;
}


if(isset($_POST['existeCIF'])){
    $base = new BD();
    $cliente = $base->query("SELECT COUNT(tva_intra) FROM vol_societe WHERE tva_intra = '".$_POST['CIF']."'");
    echo $cliente[0][0];
}

if(isset($_POST['creaCliente'])){

    $cliente = callAPI("POST", $keyAPI, "https://biop.voluta.studio/htdocs/api/index.php/thirdparties", $_POST['datosClienteNuevo']);
    echo $cliente;
    
    
        
}

if(isset($_POST['CA_activo'])){
    $base = new BD();
    $rowid = $base->query("SELECT vol_CA_soc.fk_CA FROM vol_CA_soc LEFT JOIN vol_CA_log on vol_CA_log.fk_CA = vol_CA_soc.fk_CA WHERE LOG = 'activo' AND fk_contrat = '".$_POST['contrato']."'");
    echo $rowid[0][0];
}

if(isset($_POST['clienteContrat'])){
    $base = new BD();
    $rowid = $base->query("SELECT fk_soc FROM vol_contrat WHERE rowid ='".$_POST['contrato']."'");
    echo $rowid[0][0];
}

if(isset($_POST['registroTabla'])){
    $base = new BD();
    $rowid = $base->query("SELECT * FROM ".$_POST['tabla']." WHERE rowid ='".$_POST['id']."'");
    echo json_encode($rowid[0]);
}

if(isset($_POST['facturasProv'])){
    $base = new BD();
    $listado1 = $base->query ("SELECT vol_facture.rowid as 'parte' , vol_facture.ref AS 'ref', vol_facture.total_ttc AS 'importe'
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_target
    LEFT JOIN vol_facture ON vol_element_element.fk_source = vol_facture.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'contrat' AND vol_element_element.sourcetype = 'facture'
    AND vol_element_element.fk_source = vol_facture.rowid AND vol_element_element.fk_target = vol_contrat.rowid
    AND vol_facture.fk_statut = 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_facture.ref");

    $listado2 = $base->query ("SELECT vol_facture.rowid as 'parte' , vol_facture.ref AS 'ref', vol_facture.total_ttc AS 'importe'
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_source
    LEFT JOIN vol_facture ON vol_element_element.fk_target = vol_facture.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'facture' AND vol_element_element.sourcetype = 'contrat'
    AND vol_element_element.fk_target = vol_facture.rowid AND vol_element_element.fk_source = vol_contrat.rowid
    AND vol_facture.fk_statut = 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    
    GROUP BY vol_facture.ref");

    $salida = array_merge($listado1,$listado2);
    echo json_encode($salida);

}

if(isset($_POST['facturasCont'])){
    $base = new BD();
    $listado1 = $base->query ("SELECT vol_facture.rowid as 'parte' , vol_facture.ref AS 'ref', vol_facture.total_ttc AS 'importe'
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_target
    LEFT JOIN vol_facture ON vol_element_element.fk_source = vol_facture.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'contrat' AND vol_element_element.sourcetype = 'facture'
    AND vol_element_element.fk_source = vol_facture.rowid AND vol_element_element.fk_target = vol_contrat.rowid
    AND vol_facture.fk_statut > 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_facture.ref");

    $listado2 = $base->query ("SELECT vol_facture.rowid as 'parte' , vol_facture.ref AS 'ref', vol_facture.total_ttc AS 'importe'
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_source
    LEFT JOIN vol_facture ON vol_element_element.fk_target = vol_facture.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'facture' AND vol_element_element.sourcetype = 'contrat'
    AND vol_element_element.fk_target = vol_facture.rowid AND vol_element_element.fk_source = vol_contrat.rowid
    AND vol_facture.fk_statut > 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_facture.ref");

    $salida = array_merge($listado1,$listado2);
    echo json_encode($salida);

}

if(isset($_POST['partesPrev'])){
    $base = new BD();
    $listado = $base->query("SELECT vol_commande.rowid as 'parte' , vol_commande.ref AS 'ref', vol_commande.date_commande, vol_contrat.rowid
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_source
    LEFT JOIN vol_commande ON vol_element_element.fk_target = vol_commande.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'commande' AND vol_element_element.sourcetype = 'contrat'
    AND vol_element_element.fk_target = vol_commande.rowid AND vol_element_element.fk_source = vol_contrat.rowid
    AND vol_commande.fk_statut = 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_commande.rowid");

    $listado2 = $base->query("SELECT vol_commande.rowid as 'parte' , vol_commande.ref AS 'ref', vol_commande.date_commande, vol_contrat.rowid
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_target
    LEFT JOIN vol_commande ON vol_element_element.fk_source = vol_commande.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'contrat' AND vol_element_element.sourcetype = 'commande'
    AND vol_element_element.fk_source = vol_commande.rowid AND vol_element_element.fk_target = vol_contrat.rowid
    AND vol_commande.fk_statut = 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_commande.rowid");
    $salida = array_merge($listado,$listado2);
    echo json_encode($salida);
}

if(isset($_POST['partesCont'])){
    $base = new BD();
    $listado = $base->query("SELECT vol_commande.rowid as 'parte' , vol_commande.ref AS 'ref', vol_commande.date_commande, vol_contrat.rowid
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_source
    LEFT JOIN vol_commande ON vol_element_element.fk_target = vol_commande.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'commande' AND vol_element_element.sourcetype = 'contrat'
    AND vol_element_element.fk_target = vol_commande.rowid AND vol_element_element.fk_source = vol_contrat.rowid
    AND vol_commande.fk_statut > 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_commande.rowid");

    $listado2 = $base->query("SELECT vol_commande.rowid as 'parte' , vol_commande.ref AS 'ref', vol_commande.date_commande, vol_contrat.rowid
    FROM vol_contrat
    LEFT JOIN vol_element_element ON vol_contrat.rowid = vol_element_element.fk_target
    LEFT JOIN vol_commande ON vol_element_element.fk_source = vol_commande.rowid
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_contrat.fk_soc
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_element_element.targettype = 'contrat' AND vol_element_element.sourcetype = 'commande'
    AND vol_element_element.fk_source = vol_commande.rowid AND vol_element_element.fk_target = vol_contrat.rowid
    AND vol_commande.fk_statut > 0
    AND vol_CA_soc.fk_contrat = vol_contrat.rowid
    and vol_CA_soc.fk_CA = ".$_POST['CA']."
    GROUP BY vol_commande.rowid");
    $salida = array_merge($listado,$listado2);
    echo json_encode($salida);
    //echo json_encode($_POST);
}


if(isset($_POST['log'])){
    $base = new BD();
    $listado = $base->query ("SELECT * FROM vol_CA_log LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA WHERE vol_CA_soc.fk_contrat = '".$_POST['contrat']."'");
    echo json_encode($listado);
    
}


if(isset($_POST['modificaContrato'])){
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];   
    //RECOGE DATOS
    $datos = $_POST;
    //REALIZA CA
    $envio = creaCA($datos);
    $cliente = $datos['clienteId'];
    $contratoId = $datos['contrat'];
    $sociedades = json_decode($datos['sociedades'],true);
    //AÑADE DIRECCION direccionNew
    if ($datos['direccionNew'] != ""){
        //agregamos dirección
        $api['address'] = $datos['direccionNew'];
        $api['zip'] = $datos['cpNew'];
        $api['lastname'] = $api['address'];
        $api['town'] = $datos['localidadNew'];
        $api['phone_mobile'] = $datos['telefonoNew'];
        $api['firstname'] = $datos['contactoNew'];
        $api['socid'] = $datos['clienteId'];
        $idContacto = callAPI('POST', $keyAPI, $urlAPI.'contacts/',json_encode($api));
    }else if ($datos['direccionServicio'] != "ficha"){
        $idContacto = $datos['direccionServicio'];
    }else {
        $idContacto = "NO";
    }
    //AÑADE DIRECCION CA
    if ($idContacto !="NO"){
        //AÑADE DIRECCION CA
        $base->modificaRowid("vol_CA", $envio['id'],"contacto",$idContacto);
    }


    //AÑADE DIRECCION CONTRATO
    if($idContacto != 'NO'){
        $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
        $valores = ['4',$contratoId,'21',$idContacto];
        $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
    }

    //MODIFICAR CONTRATO
    $contrato['socid'] = $cliente;
    $contrato['date_contrat'] = strtotime($datos['fecha']);
    $contrato['ref_customer'] = $datos['referencia'];
    $contrato['commercial_signature_id'] = '5';
    $contrato['commercial_suivi_id'] = '5';
    $contLineas['statut'] = '4';
    $contLineas['date_start'] = strtotime($datos['fecha']);
    $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['qty'] = '1';
    $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
    $contratoMod = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId,json_encode($contrato));
    //LINEAS CONTRATO
    $lineas = json_decode(callAPI('GET', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"),true);
    //MODIFICAR LÍNEA
    $servicioActivo = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$lineas[0]['id'],json_encode($contLineas));
    $salidaN = $contratoId; 
    
    //AÑADE DIRECCION CONTRATO
    if($idContacto != 'NO'){
        $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
        $valores = ['4',$contratoId,'21',$idContacto];
        $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
    }

    //AÑADIR CONTRATO A vol_CA_soc
    $base = new BD();
    $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$cliente."'");
    $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId);
    
    //CREAR LOG
    $CAanterior = $datos['CA'];
    //Modificar anterior LOG
    $base = new BD();
    $anterior = $base->query("SELECT rowid FROM vol_CA_log WHERE log = 'activo' AND fk_CA=".$CAanterior);
    $keys = ['fk_CA','log'];
    $valores = [$envio['id'],'nuevo'];
    $modificaLog  = $base->modificaRowid("vol_CA_log", $anterior[0][0],"log","modificado");
    //Activo
    $keys = ['fk_CA','log'];
    $valores = [$envio['id'],'activo'];
    $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);

    //CREA PDF
    $contratoRef = $base->registroRowid("vol_contrat", $contratoId);
    //CREA CARPETA SI NO EXISTE
    if (!file_exists("../../../documents/contract/".$contratoRef['ref'])){
        mkdir("../../../documents/contract/".$contratoRef['ref'],0777);
    }
    crearPDF($envio['id'],("../../../documents/contract/".$contratoRef['ref']."/".$contratoRef['ref']."-".$envio['id'].".pdf"));

    echo $contratoId; 
}

if(isset($_POST['renuevaContrato'])){
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];

    //RECOGE DATOS
    $datos = $_POST;
    $contratoId = $datos['contrat'];
    $CAanterior = $datos['CA'];
    //REALIZA CA
    $envio = creaCA($datos);
    $cliente = $datos['clienteId'];
    $sociedades = json_decode($datos['sociedades'],true);
    //REALIZA PARTES
    $partes = json_decode($_POST['partes'],true);
    $pedidosCreados = crearPedidos($partes,$envio['id']);
    $i = 0;
    //AÑADE DIRECCION direccionNew
    if ($datos['direccionNew'] != ""){
        //agregamos dirección
        $api['address'] = $datos['direccionNew'];
        $api['zip'] = $datos['cpNew'];
        $api['lastname'] = $api['address'];
        $api['town'] = $datos['localidadNew'];
        $api['phone_mobile'] = $datos['telefonoNew'];
        $api['firstname'] = $datos['contactoNew'];
        $api['socid'] = $datos['clienteId'];
        $idContacto = callAPI('POST', $keyAPI, $urlAPI.'contacts/',json_encode($api));
    }else if ($datos['direccionServicio'] != "ficha"){
        $idContacto = $datos['direccionServicio'];
    }else {
        $idContacto = "NO";
    }
    //AÑADE DIRECCION PARTE
    if ($idContacto !="NO"){
        foreach ($pedidosCreados as $pedido){
           agregaContacto('pedido',$pedido,$idContacto);
        }
        //AÑADE DIRECCION CA
        $base->modificaRowid("vol_CA", $envio['id'],"contacto",$idContacto);
    }



    //AÑADE FACTURAS PPAL
    $pagoBanco = (is_null($envio['soc'][$cliente]['pago']['banco']))?"":$envio['soc'][$cliente]['pago']['banco'];
    $pagoEfectivo = (is_null($envio['soc'][$cliente]['pago']['efectivo']))?"":$envio['soc'][$cliente]['pago']['efectivo'];
    $pagoTransferencia = (is_null($envio['soc'][$cliente]['pago']['transferencia']))?"":$envio['soc'][$cliente]['pago']['transferencia'];
    $facturasCreadas[$cliente] = crearFacturas($cliente,$pagoBanco,$pagoEfectivo,$pagoTransferencia);

    //AÑADE FACTURAS OTROS
    foreach ($sociedades as $socOtro){
        $pagoBanco = (is_null($envio['soc'][$socOtro]['pago']['banco']))?"":$envio['soc'][$socOtro]['pago']['banco'];
        $pagoEfectivo = (is_null($envio['soc'][$socOtro]['pago']['efectivo']))?"":$envio['soc'][$socOtro]['pago']['efectivo'];
        $pagoTransferencia = (is_null($envio['soc'][$socOtro]['pago']['transferencia']))?"":$envio['soc'][$socOtro]['pago']['transferencia'];
        $facturasCreadas[$socOtro] = crearFacturas($socOtro,$pagoBanco,$pagoEfectivo,$pagoTransferencia);
    }

    

    //MODIFICAR CONTRATO
    $contrato['socid'] = $cliente;
    $contrato['date_contrat'] = strtotime($datos['fecha']);
    $contrato['ref_customer'] = $datos['referencia'];
    $contrato['commercial_signature_id'] = '5';
    $contrato['commercial_suivi_id'] = '5';
    $contLineas['statut'] = '4';
    $contLineas['date_start'] = strtotime($datos['fecha']);
    $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['qty'] = '1';
    $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
    $contratoMod = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId,json_encode($contrato));
    //LINEAS CONTRATO
    $lineas = json_decode(callAPI('GET', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"),true);
    //MODIFICAR LÍNEA
    $servicioActivo = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$lineas[0]['id'],json_encode($contLineas));
    $salidaN = $contratoId; 
    
    //AÑADE DIRECCION CONTRATO
    if($idContacto != 'NO'){
        $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
        $valores = ['4',$contratoId,'21',$idContacto];
        $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
    }

    //AÑADE OBJETOS A CONTRATO
    //pedidos
    foreach($pedidosCreados as $pedido){
        $keys = ['fk_source','sourcetype','fk_target','targettype'];
        $valores = [$pedido,'commande',$contratoId,'contrat'];
        $pedidosConfirma = $base->nuevoRegistro("vol_element_element", $keys,$valores);

    }
    //facturas ppal
    foreach($facturasCreadas[$cliente] as $facTipo){
        foreach ($facTipo as $id){
            if (!is_null($id)){
                $keys = ['fk_source','sourcetype','fk_target','targettype'];
                $valores = [$id,'facture',$contratoId,'contrat'];
                $facturaConfirma  = $base->nuevoRegistro("vol_element_element", $keys,$valores);
            }
        }
            
        
    }

    //AÑADIR CONTRATO A vol_CA_soc
    $base = new BD();
    $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$cliente."'");
    $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId);

    //CREAR CONTRATO con otros
    foreach ($sociedades as $socOtro){
               //MODIFICAR CONTRATO
    $contrato['socid'] = $cliente;
    $contrato['date_contrat'] = strtotime($datos['fecha']);
    $contrato['ref_customer'] = $datos['referencia'];
    $contrato['commercial_signature_id'] = '5';
    $contrato['commercial_suivi_id'] = '5';
    $contLineas['statut'] = '4';
    $contLineas['date_start'] = strtotime($datos['fecha']);
    $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
    $contLineas['qty'] = '1';
    $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
    $contratoMod = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId,json_encode($contrato));
    //LINEAS CONTRATO
    $lineas = json_decode(callAPI('GET', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"),true);
    //MODIFICAR LÍNEA
    $servicioActivo = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$lineas[0]['id'],json_encode($contLineas));

            //AÑADE DIRECCION CONTRATO
            if($idContacto != 'NO'){
                $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
                $valores = ['4',$contratoId,'21',$idContacto];
                $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
            }

            //AÑADE OBJETOS A CONTRATO
            
            //pedidos
            foreach($pedidosCreados as $pedido){
                $keys = ['fk_source','sourcetype','fk_target','targettype'];
                $valores = [$pedido,'commande',$contratoId,'contrat'];
                $pedidosConfirma = $base->nuevoRegistro("vol_element_element", $keys,$valores);
             }

            //facturas ppal
            foreach($facturasCreadas[$socOtro] as $tipoFact){
                   foreach($tipoFact as $id){
                    if (!is_null($id)){
                        $keys = ['fk_source','sourcetype','fk_target','targettype'];
                        $valores = [$id,'facture',$contratoId,'contrat'];
                        $facturaConfirma  = $base->nuevoRegistro("vol_element_element", $keys,$valores);
                    }

                   }
                    
            }
            
           //AÑADIR CONTRATO A vol_CA_soc
            $base = new BD();
            $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$socOtro."'");
            $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId); 
    }

    //Modificar anterior LOG
    $base = new BD();
    $anterior = $base->query("SELECT rowid FROM vol_CA_log WHERE log = 'activo' AND fk_CA=".$CAanterior);
    $keys = ['fk_CA','log'];
    $valores = [$envio['id'],'nuevo'];
    $modificaLog  = $base->modificaRowid("vol_CA_log", $anterior[0][0],"log","Renovado");
    //Activo
    $keys = ['fk_CA','log'];
    $valores = [$envio['id'],'activo'];
    $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);

    echo $contratoId;
}

if(isset($_POST['cancelaContrato'])){
    $CA = $_POST['CA'];
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $contratosCA = $base->query("SELECT fk_contrat FROM vol_CA_soc WHERE fk_CA = '".$CA."'");
    //MODIFICAR LOS CONTRATOS
    foreach($contratosCA as $contrato){
        $contratoId = $contrato['fk_contrat'];
        $servicios = json_decode(callAPI('GET', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"),true);
        foreach($servicios as $servicio){
            $contLineas['datestart'] = strtotime($_POST['fecha']);
            $contLineas['comment'] = $_POST['comentario'];
            $respuesta = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$servicio['id']."/unactivate",json_encode($contLineas));
        }
    }
    //MODIFICA EL LOG
      //Modificar anterior LOG
    $base = new BD();
    $anterior = $base->query("SELECT rowid FROM vol_CA_log WHERE log = 'activo' AND fk_CA=".$CA);
    $keys = ['fk_CA','log'];
    $valores = [$CA,'cancelado'];
    $modificaLog  = $base->modificaRowid("vol_CA_log", $anterior[0][0],"log","cancelado");
    echo $contratoId;
}


if(isset($_POST['CA_desactivo'])){
    $base = new BD();
    $rowid = $base->query("SELECT vol_CA_soc.fk_CA FROM vol_CA_soc LEFT JOIN vol_CA_log on vol_CA_log.fk_CA = vol_CA_soc.fk_CA WHERE LOG = 'cancelado' AND fk_contrat = '".$_POST['contrato']."'");
    echo $rowid[0][0];
}

if(isset($_POST['CA_presupuesto'])){
    $base = new BD();
    $rowid = $base->query("SELECT vol_CA_soc.fk_CA FROM vol_CA_soc LEFT JOIN vol_CA_log on vol_CA_log.fk_CA = vol_CA_soc.fk_CA WHERE LOG = 'presupuesto' AND fk_contrat = '".$_POST['contrato']."'");
    echo $rowid[0][0];
}

if(isset($_POST['reactivaContrato'])){
    $CA = $_POST['CA'];
    $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
    $base = new BD();
    $usuario = $_POST['login'];
    $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
    $keyAPI = $key[0][0];
    $contratosCA = $base->query("SELECT fk_contrat FROM vol_CA_soc WHERE fk_CA = '".$CA."'");
    //MODIFICAR LOS CONTRATOS
    foreach($contratosCA as $contrato){
        $contratoId = $contrato['fk_contrat'];
        //echo $contratoId;
        $servicios = json_decode(callAPI('GET', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"),true);
        foreach($servicios as $servicio){
            $contLineas['datestart'] = strtotime($_POST['fecha']);
            $contLineas['comment'] = $_POST['comentario'];
            $respuesta = callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$servicio['id']."/activate",json_encode($contLineas));
        }
    }
    //MODIFICA EL LOG
      //Modificar anterior LOG
    $base = new BD();
    $anterior = $base->query("SELECT rowid FROM vol_CA_log WHERE log = 'cancelado' AND fk_CA=".$CA);
    $keys = ['fk_CA','log'];
    $valores = [$CA,'reactivado'];
    $modificaLog  = $base->modificaRowid("vol_CA_log", $anterior[0][0],"log","reactivado");


    //Activo
    $keys = ['fk_CA','log'];
    $valores = [$CA,'activo'];
    $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);

    echo $contratoId;
}

if(isset($_POST['listadoContratos'])){
    $base = new BD();
    $contratos = $base->query("SELECT vol_CA_log.log, vol_contrat.ref_customer AS 'ref',vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.fechaActivo,
    (SELECT MIN(vol_facture.datef) FROM vol_facture where vol_facture.fk_soc = vol_CA_soc.fk_soc AND vol_facture.fk_statut = 0) AS 'factura',
    (SELECT MIN(vol_commande.date_commande) FROM vol_commande where vol_commande.fk_soc = vol_CA_soc.fk_soc AND vol_commande.fk_statut = 0) AS 'pedido'
    FROM vol_CA_log
    LEFT JOIN vol_CA ON vol_CA.rowid = vol_CA_log.fk_CA
    LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
    LEFT JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
    LEFT JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_CA_log.log = 'Activo' GROUP BY vol_contrat.ref_customer" );
    $hoy = date("Y-m-d");
    $siguiente = date("Y-m-d",strtotime($hoy."+15 days"));
    foreach($contratos as $key=>$contrato){
        foreach($contrato as $nombre=>$dato){
            switch($nombre){
                case "fechaActivo":
                    if (date("Y-m-d",strtotime($dato."+1 years"))<$hoy){
                        $salida[$key][$nombre] = "fuera de plazo";
                    }else if(date("Y-m-d",strtotime($dato))>=$hoy && date("Y-m-d",strtotime($dato))<$siguiente){
                        $salida[$key][$nombre] = "próximo";
                    }else{
                        $salida[$key][$nombre] = "vigente";
                    }
                break;
                case "factura":
                    if (date("Y-m-d",strtotime($dato))<$hoy){
                        $salida[$key][$nombre] = "fuera de plazo";
                    }else if(date("Y-m-d",strtotime($dato))>=$hoy && date("Y-m-d",strtotime($dato))<$siguiente){
                        $salida[$key][$nombre] = "próximo";
                    }else{
                        $salida[$key][$nombre] = "vigente";
                    }
                break;
                case "pedido":
                    if (date("Y-m-d",strtotime($dato))<$hoy){
                        $salida[$key][$nombre] = "fuera de plazo";
                    }else if(date("Y-m-d",strtotime($dato))>=$hoy && date("Y-m-d",strtotime($dato))<$siguiente){
                        $salida[$key][$nombre] = "próximo";
                    }else{
                        $salida[$key][$nombre] = "vigente";
                    }
                break;
                default:
                $salida[$key][$nombre] = $dato;
                break;

            }
        }

    }

    echo json_encode($salida);
}

if(isset($_POST['listadoPresupuestos'])){
    $base = new BD();
    $contratos = $base->query("SELECT vol_CA_log.log, vol_contrat.ref_customer AS 'ref',
    vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.fechaActivo
        FROM vol_CA_log
        LEFT JOIN vol_CA ON vol_CA.rowid = vol_CA_log.fk_CA
        LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
        LEFT JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
        LEFT JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
    WHERE vol_CA_log.log = 'Presupuesto'" );
    

    echo json_encode($contratos);
}


if (isset($_POST['documentos'])){
        $base = new BD();
        $carpetas = $base->query("SELECT ref FROM vol_contrat LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_contrat = vol_contrat.rowid WHERE vol_CA_soc.fk_CA = '".$_POST['CA']."'");
        $listado = [];
        foreach ($carpetas as $carpeta){
            foreach($carpeta as $key=>$valor){      
                if($key === "ref"){
                    $listaCarpeta = scandir("../../../documents/contract/".$valor,1);
                    if($listaCarpeta != false){
                        array_pop($listaCarpeta);
                        array_pop($listaCarpeta);
                        foreach($listaCarpeta as $documento){
                            $salida['dir'] = $valor;
                            $salida['fichero'] = $documento;
                            array_push($listado,$salida);
                        }
                       
                    }

                }
            }
        }
        echo json_encode($listado);
    }




if(isset($_REQUEST['creaWord'])){
        echo "Word!";
        $CAid = '267';
        crearWord($CAid,"");


    }


if(isset($_POST['nuevoContrato2021'])){
        $urlAPI = 'https://biop.voluta.studio/htdocs/api/index.php/';
        $base = new BD();
        $usuario = $_POST['login'];
        $key = ($base->query("SELECT api_key from vol_user WHERE login= '".$usuario."'"));
        $keyAPI = $key[0][0];   
        //RECOGE DATOS
        $datos = $_POST;
        //REALIZA CA
        $envio = creaCA($datos);
        $cliente = $datos['clienteId'];
        $sociedades = json_decode($datos['sociedades'],true);
        //REALIZA PARTES
        $partes = json_decode($_POST['partes'],true);
        $pedidosCreados = crearPedidos2021($partes,$envio['id']);
        $i = 0;
        //AÑADE DIRECCION direccionNew
        if ($datos['direccionNew'] != ""){
            //agregamos dirección
            $api['address'] = $datos['direccionNew'];
            $api['zip'] = $datos['cpNew'];
            $api['lastname'] = $api['address'];
            $api['town'] = $datos['localidadNew'];
            $api['phone_mobile'] = $datos['telefonoNew'];
            $api['firstname'] = $datos['contactoNew'];
            $api['socid'] = $datos['clienteId'];
            $idContacto = callAPI('POST', $keyAPI, $urlAPI.'contacts/',json_encode($api));
        }else if ($datos['direccionServicio'] != "ficha"){
            $idContacto = $datos['direccionServicio'];
        }else {
            $idContacto = "NO";
        }
        //AÑADE DIRECCION PARTE
        if ($idContacto !="NO"){
            foreach ($pedidosCreados as $pedido){
               agregaContacto('pedido',$pedido,$idContacto);
            }
            //AÑADE DIRECCION CA
            $base->modificaRowid("vol_CA", $envio['id'],"contacto",$idContacto);
        }
    
    
        //AÑADE FACTURAS PPAL
        $pagoBanco = (is_null($envio['soc'][$cliente]['pago']['banco']))?"":$envio['soc'][$cliente]['pago']['banco'];
        $pagoEfectivo = (is_null($envio['soc'][$cliente]['pago']['efectivo']))?"":$envio['soc'][$cliente]['pago']['efectivo'];
        $pagoTransferencia = (is_null($envio['soc'][$cliente]['pago']['transferencia']))?"":$envio['soc'][$cliente]['pago']['transferencia'];
        $facturasCreadas[$cliente] = crearFacturas2021($cliente,$pagoBanco,$pagoEfectivo,$pagoTransferencia);
    
    
        //AÑADE FACTURAS OTROS
        foreach ($sociedades as $socOtro){
            $pagoBanco = (is_null($envio['soc'][$socOtro]['pago']['banco']))?"":$envio['soc'][$socOtro]['pago']['banco'];
            $pagoEfectivo = (is_null($envio['soc'][$socOtro]['pago']['efectivo']))?"":$envio['soc'][$socOtro]['pago']['efectivo'];
            $pagoTransferencia = (is_null($envio['soc'][$socOtro]['pago']['transferencia']))?"":$envio['soc'][$socOtro]['pago']['transferencia'];
            $facturasCreadas[$socOtro] = crearFacturas2021($socOtro,$pagoBanco,$pagoEfectivo,$pagoTransferencia);
        }
    
        //CREAR CONTRATO PPAL
        $contrato['socid'] = $cliente;
        $contrato['date_contrat'] = strtotime($datos['fecha']);
        $contrato['ref_customer'] = $datos['referencia'];
        $contrato['commercial_signature_id'] = '5';
        $contrato['commercial_suivi_id'] = '5';
        $contLineas['statut'] = '4';
        $contLineas['date_start'] = strtotime($datos['fecha']);
        $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
        $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
        $contLineas['qty'] = '1';
        $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
        $contratoId = callAPI('POST', $keyAPI, $urlAPI."contracts",json_encode($contrato));
        //INSERTA LÍNEA
        $servicioActivo = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/lines",json_encode($contLineas));
        //CONFIRMAR CONTRATO
        $confirma = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/validate",'1');
        //ACTIVAR LINEA
        $activa['datestart'] = strtotime($datos['fecha']);
        callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$servicioActivo."/activate",json_encode($activa));
        $salidaN = $contratoId; 
        
        //AÑADE DIRECCION CONTRATO
        if($idContacto != 'NO'){
            $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
            $valores = ['4',$contratoId,'21',$idContacto];
            $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
        }
    
        //AÑADE OBJETOS A CONTRATO
        //pedidos
        foreach($pedidosCreados as $pedido){
            $keys = ['fk_source','sourcetype','fk_target','targettype'];
            $valores = [$pedido,'commande',$contratoId,'contrat'];
            $pedidosConfirma = $base->nuevoRegistro("vol_element_element", $keys,$valores);
    
        }
        //facturas ppal
        foreach($facturasCreadas[$cliente] as $facTipo){
            foreach ($facTipo as $id){
                if (!is_null($id)){
                    $keys = ['fk_source','sourcetype','fk_target','targettype'];
                    $valores = [$id,'facture',$contratoId,'contrat'];
                    $facturaConfirma  = $base->nuevoRegistro("vol_element_element", $keys,$valores);
                }
            }
                
            
        }
    
        //AÑADIR CONTRATO A vol_CA_soc
        $base = new BD();
        $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$cliente."'");
        $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId);
    
        //CREAR CONTRATO con otros
        foreach ($sociedades as $socOtro){
                    //CREAR CONTRATO
                $contrato['socid'] = $socOtro;
                $contrato['date_contrat'] = strtotime($datos['fecha']);
                $contrato['ref_customer'] = $datos['referencia'];
                $contrato['commercial_signature_id'] = '5';
                $contrato['commercial_suivi_id'] = '5';
                $contLineas['statut'] = '4';
                $contLineas['date_start'] = strtotime($datos['fecha']);
                $contLineas['description'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
                $contLineas['desc'] = 'Contrato realizado por módulo CONTRATO ABONADOS';
                $contLineas['qty'] = '1';
                $contLineas['subprice'] = floatval($datos['importeBanco']) + floatval($datos['importeEfectivo']) + floatval($datos['importeTransferencia']);
                $contratoId = callAPI('POST', $keyAPI, $urlAPI."contracts",json_encode($contrato));
                //INSERTA LÍNEA
                $servicioActivo = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/lines",json_encode($contLineas));
                //CONFIRMAR CONTRATO
                $confirma = callAPI('POST', $keyAPI, $urlAPI."contracts/".$contratoId."/validate",'1');
                //ACTIVAR LINEA
                $activa['datestart'] = strtotime($datos['fecha']);
                callAPI('PUT', $keyAPI, $urlAPI."contracts/".$contratoId."/lines"."/".$servicioActivo."/activate",json_encode($activa));
                //AÑADE DIRECCION CONTRATO
                if($idContacto != 'NO'){
                    $keys = ['statut','element_id','fk_c_type_contact','fk_socpeople'];
                    $valores = ['4',$contratoId,'21',$idContacto];
                    $contacto  = $base->nuevoRegistro("vol_element_contact", $keys,$valores);
                }
    
                //AÑADE OBJETOS A CONTRATO
                
                //pedidos
                foreach($pedidosCreados as $pedido){
                    $keys = ['fk_source','sourcetype','fk_target','targettype'];
                    $valores = [$pedido,'commande',$contratoId,'contrat'];
                    $pedidosConfirma = $base->nuevoRegistro("vol_element_element", $keys,$valores);
                 }
    
                //facturas ppal
                foreach($facturasCreadas[$socOtro] as $tipoFact){
                       foreach($tipoFact as $id){
                        if (!is_null($id)){
                            $keys = ['fk_source','sourcetype','fk_target','targettype'];
                            $valores = [$id,'facture',$contratoId,'contrat'];
                            $facturaConfirma  = $base->nuevoRegistro("vol_element_element", $keys,$valores);
                        }
    
                       }
                        
                }
                
               //AÑADIR CONTRATO A vol_CA_soc
                $base = new BD();
                $rowid = $base->query("SELECT rowid from vol_CA_soc where fk_CA = '".$envio['id']."' AND fk_soc='".$socOtro."'");
                $addCont = $base->modificaRowid("vol_CA_soc", $rowid[0][0],"fk_contrat",$contratoId); 
        }
    
        //CREAR LOG
        //Nuevo
        $keys = ['fk_CA','log','situacion'];
        $valores = [$envio['id'],'nuevo',"Sit-1"];
        $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);
        //Activo
        $keys = ['fk_CA','log','situacion'];
        $valores = [$envio['id'],'activo',"Sit-1"];
        $facturaConfirma  = $base->nuevoRegistro("vol_CA_log", $keys,$valores);
    
        //CREA DOCUMENTOS
         //CREA PDF
         $contratoRef = $base->registroRowid("vol_contrat", $contratoId);
         //CREA CARPETA SI NO EXISTE
         if (!file_exists("../../../documents/contract/".$contratoRef['ref'])){
             mkdir("../../../documents/contract/".$contratoRef['ref'],0777);
         }
         crearPDF($envio['id'],("../../../documents/contract/".$contratoRef['ref']."/".$contratoRef['ref']."-".$envio['id'].".pdf"));
    
        echo $salidaN ;
    }

if(isset($_REQUEST['listadoContratosNuevo'])){
        //EMPEZAMOS A CREAR EL ARRAY DE SALIDA
        $return = array();
        $arrayComp = array();
        $base = new BD();
        //LISTADO COMPLETO
        // $contratos = $base->query("SELECT vol_CA_log.log, vol_CA_log.fk_CA AS 'CA', vol_contrat.ref_customer AS 'ref',vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.fechaActivo, vol_CA.contacto,

        // vol_facture.datef AS 'fechaFactura', vol_facture.fk_statut AS 'estadoFactura', vol_facture.ref AS 'referenciaFactura',
        
        // vol_commande.date_commande AS 'fechaParte',vol_commande.fk_statut AS 'estadoPedido', vol_commande.ref AS 'referenciaPedido'
        
        //     FROM vol_CA_log
        
        //     LEFT JOIN vol_CA ON vol_CA.rowid = vol_CA_log.fk_CA
        
        //     LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
        
        //     left JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
        
        //     left JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
            
        //     left JOIN vol_facture ON vol_CA_soc.fk_soc = vol_facture.fk_soc
            
        //     LEFT JOIN vol_commande ON vol_CA_soc.fk_soc = vol_commande.fk_soc
        
        //     WHERE vol_CA_log.log = 'Activo'" );

        $contratosComunes = $base->query("SELECT vol_CA_log.log, vol_CA_log.fk_CA AS 'CA', vol_contrat.ref_customer AS 'ref',vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.fechaActivo, vol_CA.contacto,
        vol_facture.datef AS 'fechaFactura', vol_facture.fk_statut AS 'estadoFactura', vol_facture.ref AS 'referenciaFactura',
        vol_commande.date_commande AS 'fechaParte',vol_commande.fk_statut AS 'estadoPedido', vol_commande.ref AS 'referenciaPedido', vol_commande.rowid AS 'idPedido', vol_COMPROMETIDOS.cita  as 'cita'
        FROM vol_CA
        LEFT JOIN vol_CA_log ON vol_CA.rowid = vol_CA_log.fk_CA
        LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
        LEFT JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
        LEFT JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
        LEFT JOIN vol_facture ON vol_CA_soc.fk_soc = vol_facture.fk_soc
        LEFT JOIN vol_commande ON vol_CA_soc.fk_soc = vol_commande.fk_soc
        LEFT JOIN vol_COMPROMETIDOS on vol_COMPROMETIDOS.fk_commande = vol_commande.rowid
        WHERE vol_CA_log.log = 'activo' AND vol_commande.date_commande = vol_facture.datef ");

        $contratosPedidos = $base->query("SELECT vol_CA_log.log, vol_CA_log.fk_CA AS 'CA', vol_contrat.ref_customer AS 'ref',vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.fechaActivo, vol_CA.contacto,
        vol_facture.datef AS 'fechaFactura', vol_facture.fk_statut AS 'estadoFactura', vol_facture.ref AS 'referenciaFactura',
        vol_commande.date_commande AS 'fechaParte',vol_commande.fk_statut AS 'estadoPedido', vol_commande.ref AS 'referenciaPedido', vol_commande.rowid AS 'idPedido', vol_COMPROMETIDOS.cita  as 'cita'
        FROM vol_CA
        LEFT JOIN vol_CA_log ON vol_CA.rowid = vol_CA_log.fk_CA
        LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
        LEFT JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
        LEFT JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
        LEFT JOIN vol_facture ON vol_CA_soc.fk_soc = vol_facture.fk_soc
        LEFT JOIN vol_commande ON vol_CA_soc.fk_soc = vol_commande.fk_soc
        LEFT JOIN vol_COMPROMETIDOS on vol_COMPROMETIDOS.fk_commande = vol_commande.rowid
        WHERE vol_CA_log.log = 'activo' AND vol_commande.date_commande != vol_facture.datef GROUP BY  vol_commande.date_commande");

        $contratosFacturas = $base->query("SELECT vol_CA_log.log, vol_CA_log.fk_CA AS 'CA', vol_contrat.ref_customer AS 'ref',vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.fechaActivo, vol_CA.contacto,
        vol_facture.datef AS 'fechaFactura', vol_facture.fk_statut AS 'estadoFactura', vol_facture.ref AS 'referenciaFactura',
        vol_commande.date_commande AS 'fechaParte',vol_commande.fk_statut AS 'estadoPedido', vol_commande.ref AS 'referenciaPedido', vol_commande.rowid AS 'idPedido', vol_COMPROMETIDOS.cita  as 'cita'
        FROM vol_CA
        LEFT JOIN vol_CA_log ON vol_CA.rowid = vol_CA_log.fk_CA
        LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
        LEFT JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
        LEFT JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
        LEFT JOIN vol_facture ON vol_CA_soc.fk_soc = vol_facture.fk_soc
        LEFT JOIN vol_commande ON vol_CA_soc.fk_soc = vol_commande.fk_soc
        LEFT JOIN vol_COMPROMETIDOS on vol_COMPROMETIDOS.fk_commande = vol_commande.rowid
        WHERE vol_CA_log.log = 'activo' AND vol_commande.date_commande != vol_facture.datef GROUP BY  vol_facture.datef");

        $consultaContrato = "SELECT vol_commande.ref,
        (
            SELECT vol_element_element.fk_target FROM vol_element_element 
            WHERE vol_element_element.sourcetype = 'commande' AND vol_element_element.fk_source = vol_commande.rowid AND vol_element_element.targettype = 'contrat' AND vol_element_element.fk_target IS NOT null
            GROUP BY vol_element_element.fk_source
        
        ) AS 'contrato',
        (
            SELECT vol_element_element.fk_source FROM vol_element_element 
            WHERE vol_element_element.targettype = 'commande' AND vol_element_element.fk_target = vol_commande.rowid AND vol_element_element.sourcetype = 'contrat' AND vol_element_element.fk_source IS NOT null
            GROUP BY vol_element_element.fk_source
        
        ) AS 'contrato2'
        
        FROM vol_commande ";

        $consultaFactura = "SELECT vol_facture.ref,
        (
            SELECT vol_element_element.fk_target FROM vol_element_element 
            WHERE vol_element_element.sourcetype = 'facture' AND vol_element_element.fk_source = vol_facture.rowid AND vol_element_element.targettype = 'contrat' AND vol_element_element.fk_target IS NOT NULL
            GROUP BY vol_element_element.fk_source
        
        ) AS 'contrato',
        (
            SELECT vol_element_element.fk_source FROM vol_element_element 
            WHERE vol_element_element.targettype = 'facture' AND vol_element_element.fk_target = vol_facture.rowid AND vol_element_element.sourcetype = 'contrat' AND vol_element_element.fk_source IS NOT NULL
            GROUP BY vol_element_element.fk_source
        
        ) AS 'contrato2'
        
        FROM vol_facture ";

        $hoy = date("Y-m-d");
        $siguiente = date("Y-m-d",strtotime($hoy."+15 days"));
        $idLinea = 0;
        //COMUNES
        foreach($contratosComunes as $keys=>$contrato){
            //DIRECCION
            if(empty($contrato['contacto'])){
                $base = new BD();
                $direccion = $base->query("SELECT CONCAT(vol_societe.address,', ',vol_societe.zip,' ',vol_societe.town) AS 'direccion' FROM vol_societe WHERE vol_societe.nom = '".$contrato['nom']."'");
                $contrato['direccion'] = $direccion[0]['direccion'];
            }else{
                $base = new BD();
                $direccion = $base->query("SELECT CONCAT(vol_socpeople.address,', ',vol_socpeople.zip,' ',vol_socpeople.town) AS 'direccion' FROM vol_socpeople WHERE vol_socpeople.rowid = '".$contrato['contacto']."'");
                $contrato['direccion'] = $direccion[0]['direccion'];
            }


            $fechas = [$contrato['fechaActivo'],$contrato['fechaFactura'],$contrato['fechaParte']];
            //BUSCO LA FECHA MAYOR PARA CRUCE
            $fechaListado = max($fechas);
            $salida[$key]['fechaListado'] = $fechaListado;
            //COMPARO FECHAS
            $fechaContrato = date("Y-m-d",strtotime($contrato['fechaActivo']));
            $fechaContArray = explode("-",$fechaContrato);
            $fechaRenov = date("Y-m-d",strtotime(($fechaContArray[0]+1)."-".$fechaContArray[1]."-01 +1 month"));

            //OPCION CONTRATOS ACTIVOS VS PARTES
            $base = new BD();
            $contratoPartesActivo = $base->query($consultaContrato." WHERE vol_commande.ref = '".$contrato['referenciaPedido']."'");
            if($contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato'] || $contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato2']){
                $contrato['parteActivo'] = 1;
            } 
            
            //OPCION CONTRATOS ACTIVOS VS FACTURA
            $base = new BD();
            $contratoPartesActivo = $base->query($consultaFactura." WHERE vol_facture.ref = '".$contrato['referenciaFactura']."'");
            if($contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato'] || $contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato2']){
                $contrato['facturaActivo'] = 1;
            } 

            //FACTURA + REVISION
            if($fechaListado == $contrato['fechaFactura'] && $fechaListado == $contrato['fechaParte'] && $contrato['facturaActivo'] == 1 && $contrato['parteActivo'] == 1){
                $linea= $contrato;
                $linea['fechaListado'] = $fechaListado;
                $linea['accion'] = "FR";
                $linea['renovar'] = 0;
                $linea['revision'] = 1;
                $linea['facturar'] = 1;
                $linea['cita'] = empty($contrato['cita'])?"":$contrato['cita'];
                $comp['fechaListado'] = $fechaListado;
                $comp['CA'] = $linea['CA'];
                if(!in_array($linea,$return)){ //ELIMINAR DUPLICADOS
                    $idLinea ++;
                    $return[] = $linea;
                    $arrayComp[] = $comp;

                }
            }
        }
        //SOLO FACTURAS
        foreach($contratosFacturas as $keys=>$contrato){
            //DIRECCION
            if(empty($contrato['contacto'])){
                $base = new BD();
                $direccion = $base->query("SELECT CONCAT(vol_societe.address,', ',vol_societe.zip,' ',vol_societe.town) AS 'direccion' FROM vol_societe WHERE vol_societe.nom = '".$contrato['nom']."'");
                $contrato['direccion'] = $direccion[0]['direccion'];
            }else{
                $base = new BD();
                $direccion = $base->query("SELECT CONCAT(vol_socpeople.address,', ',vol_socpeople.zip,' ',vol_socpeople.town) AS 'direccion' FROM vol_socpeople WHERE vol_socpeople.rowid = '".$contrato['contacto']."'");
                $contrato['direccion'] = $direccion[0]['direccion'];
            }


            $fechas = [$contrato['fechaActivo'],$contrato['fechaFactura'],$contrato['fechaParte']];
            //BUSCO LA FECHA MAYOR PARA CRUCE
            $fechaListado = max($fechas);
            $salida[$key]['fechaListado'] = $fechaListado;
            //COMPARO FECHAS
            $fechaContrato = date("Y-m-d",strtotime($contrato['fechaActivo']));
            $fechaContArray = explode("-",$fechaContrato);
            $fechaRenov = date("Y-m-d",strtotime(($fechaContArray[0]+1)."-".$fechaContArray[1]."-01 +1 month"));
            
            //OPCION CONTRATOS ACTIVOS VS FACTURA
            $base = new BD();
            $contratoPartesActivo = $base->query($consultaFactura." WHERE vol_facture.ref = '".$contrato['referenciaFactura']."'");
            if($contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato'] || $contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato2']){
                $contrato['facturaActivo'] = 1;
            } 

            if($fechaListado == $contrato['fechaFactura'] && $fechaListado != $contrato['fechaParte'] && $contrato['facturaActivo'] == 1){ //REVISION
                $linea= $contrato;
                $linea['fechaListado'] = $fechaListado;
                $linea['accion'] = "F";
                $linea['renovar'] = 0;
                $linea['revision'] = 0;
                $linea['facturar'] = 1;
                $linea['cita'] = empty($contrato['cita'])?"":$contrato['cita'];
                $comp['fechaListado'] = $fechaListado;
                $comp['CA'] = $linea['CA'];
                if(!in_array($comp,$arrayComp)){ //ELIMINAR DUPLICADOS
                    $idLinea ++;
                    $return[] = $linea;
                }else{
                    //PROBAR
                }
            }  
        }        
        
         //PARTES
         foreach($contratosPedidos as $keys=>$contrato){
            //DIRECCION
            if(empty($contrato['contacto'])){
                $base = new BD();
                $direccion = $base->query("SELECT CONCAT(vol_societe.address,', ',vol_societe.zip,' ',vol_societe.town) AS 'direccion' FROM vol_societe WHERE vol_societe.nom = '".$contrato['nom']."'");
                $contrato['direccion'] = $direccion[0]['direccion'];
            }else{
                $base = new BD();
                $direccion = $base->query("SELECT CONCAT(vol_socpeople.address,', ',vol_socpeople.zip,' ',vol_socpeople.town) AS 'direccion' FROM vol_socpeople WHERE vol_socpeople.rowid = '".$contrato['contacto']."'");
                $contrato['direccion'] = $direccion[0]['direccion'];
            }


            $fechas = [$contrato['fechaActivo'],$contrato['fechaFactura'],$contrato['fechaParte']];
            //BUSCO LA FECHA MAYOR PARA CRUCE
            $fechaListado = max($fechas);
            $salida[$key]['fechaListado'] = $fechaListado;
            //COMPARO FECHAS
            $fechaContrato = date("Y-m-d",strtotime($contrato['fechaActivo']));
            $fechaContArray = explode("-",$fechaContrato);
            $fechaRenov = date("Y-m-d",strtotime(($fechaContArray[0]+1)."-".$fechaContArray[1]."-01 +1 month"));

            //OPCION CONTRATOS ACTIVOS VS PARTES
            $base = new BD();
            $contratoPartesActivo = $base->query($consultaContrato." WHERE vol_commande.ref = '".$contrato['referenciaPedido']."'");
            if($contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato'] || $contrato['fk_contrat'] == $contratoPartesActivo[0]['contrato2']){
                $contrato['parteActivo'] = 1;
            } 
            
            if($fechaListado != $contrato['fechaFactura'] && $fechaListado == $contrato['fechaParte'] && $contrato['parteActivo'] == 1){ //REVISION
                $linea= $contrato;
                $linea['fechaListado'] = $fechaListado;
                $linea['accion'] = "R";
                $linea['renovar'] = 0;
                $linea['revision'] = 1;
                $linea['facturar'] = 0;
                $linea['cita'] = empty($contrato['cita'])?"":$contrato['cita'];
                $comp['fechaListado'] = $fechaListado;
                $comp['CA'] = $linea['CA'];
                if(!in_array($comp,$arrayComp)){ //ELIMINAR DUPLICADOS
                    $idLinea ++;
                    $return[] = $linea;
                }else{
                  //PROBAR
                }
            }  
        }
 
        //RENOVACION
        $queryRenov = "SELECT vol_CA_log.log, vol_CA_log.fk_CA AS 'CA', vol_contrat.ref_customer AS 'ref',vol_contrat.rowid AS 'fk_contrat', vol_societe.nom, vol_CA.tipo, vol_CA.contacto, vol_CA.fechaActivo
        FROM vol_CA
        LEFT JOIN vol_CA_log ON vol_CA.rowid = vol_CA_log.fk_CA
        LEFT JOIN vol_CA_soc ON vol_CA_soc.fk_CA = vol_CA_log.fk_CA
        LEFT JOIN vol_contrat ON vol_CA_soc.fk_contrat = vol_contrat.rowid
        LEFT JOIN vol_societe ON vol_CA_soc.fk_soc = vol_societe.rowid
        
        WHERE 
        vol_CA_log.log = 'activo'";
        $base = new BD();
        $renovar = $base->query($queryRenov);
        foreach($renovar as $key=>$contrato){
            //RENOVACION - TODOS
            $lineaRenov= $contrato;
            $lineaRenov['accion'] = "RE";
            $lineaRenov['fechaListado'] = $fechaRenov;
            $lineaRenov['direccion'] = "";
            $lineaRenov['renovar'] = 1;
            $lineaRenov['revision'] = 0;
            $lineaRenov['facturar'] = 0;
            $lineaRenov['fechaFactura'] = "";
            $lineaRenov['fechaParte'] = "";
            $lineaRenov['estadoFactura'] = "";
            $lineaRenov['estadoPedido'] = "";
            $lineaRenov['referenciaFactura'] = "";
            $lineaRenov['referenciaPedido'] = "";
            $lineaRenov['fechaActivo'] = "";
            if(!in_array($lineaRenov,$return)){ //ELIMINAR DUPLICADOS
                $idLinea ++;
                $return[] = $lineaRenov;
            }
        }
        
        echo json_encode($return);
        // echo "<br>";
        // echo "<h5>Listado Comparativo</h5>";
        // echo "<hr>";
        // var_dump($arrayComp);
    }



//CAMBIO DE CITA COMPROMETIDA

if(isset($_POST['cambiaFecha'])){
    $idParte = $_POST['idParte'];
    $fecha = $_POST['fecha'];
    $base = new BD();
    //VER SI EXISTE
    $idCOMPROMETIDO = $base->query("SELECT * from vol_COMPROMETIDOS where fk_commande = '".$idParte."'");
    if (empty($idCOMPROMETIDO)){
        $base = new BD();
        $keys = ['fk_commande','cita'];
        $valores = [$idParte,$fecha];
        $nuevoCompr = $base->nuevoRegistro("vol_COMPROMETIDOS",$keys,$valores);
        echo "fecha prefijada realizada";
    }else{
        $base = new BD();
        $consulta = $base->query("UPDATE vol_COMPROMETIDOS SET cita = '".$fecha."' WHERE fk_commande = '".$idParte."'");
        echo "fecha prefijada moficada";
    }
    
}
//AGENDAS
if(isset($_POST['agendas'])){
    $base = new BD();
    $operarios = $base->query("SELECT vol_user.rowid, CONCAT(vol_user.firstname,' ',vol_user.lastname) AS 'nombre' FROM vol_user WHERE vol_user.job = 'operario'");
    $salida = array();
    foreach($operarios as $operario){
        $linea['rowid'] = $operario['rowid'];
        $linea['nombre'] =  $operario['nombre'];
        $base = new  BD();
        $agenda = $base->query("SELECT vol_actioncomm_resources.fk_element AS 'idOperario', vol_actioncomm.datep AS 'start', vol_actioncomm.id AS 'idCita', vol_actioncomm.fk_soc AS 'idSoc', vol_actioncomm.fk_element AS 'idParte',vol_societe.nom as 'title'
        FROM vol_actioncomm
        LEFT JOIN vol_actioncomm_resources ON vol_actioncomm_resources.fk_actioncomm = vol_actioncomm.id
        LEFT JOIN vol_societe ON vol_societe.rowid = vol_actioncomm.fk_soc
        WHERE vol_actioncomm.elementtype='order' AND vol_actioncomm_resources.element_type='user' AND vol_actioncomm_resources.fk_element = '".$operario['rowid']."'");
        $linea['agenda'] = $agenda;
        $salida[] = $linea;
    }
    echo json_encode($salida);
}

if(isset($_REQUEST['listaComprometidos'])){
    $base = new BD();
    $citas = $base->query("SELECT vol_societe.rowid as 'idSoc', vol_COMPROMETIDOS.fk_commande AS 'idParte', vol_COMPROMETIDOS.cita AS 'start',vol_societe.nom AS 'title' 
    FROM vol_COMPROMETIDOS
    LEFT JOIN vol_commande ON vol_commande.rowid = vol_COMPROMETIDOS.fk_commande
    LEFT JOIN vol_societe ON vol_societe.rowid = vol_commande.fk_soc
    WHERE vol_COMPROMETIDOS.cita != 0");

    echo json_encode($citas);
}


if (isset($_POST['addCita'])){
    //Preparamos el envio por API
    $fecha = explode(" ",$_POST['datep']);
    $fechaSQL =$fecha[2]."/".$fecha[1]."/".$fecha[3].":".$fecha[4]." ".$fecha[5];
    $fechaUnix = strtotime($fechaSQL);  
    $envioCurl = array();
    $envioCurl['datep'] = $fechaUnix;
    $envioCurl['type_id'] = '50';
    $envioCurl['type_code'] = 'AC_OTH';
    $envioCurl['userassigned'][$_POST['idOperario']]['id'] = $_POST['idOperario'];
    $envioCurl['userownerid'] = $_POST['idOperario'];
    $envioCurl['socid'] = $_POST['idSoc'];
    $envioCurl['fk_element'] = $_POST['idParte'];
    $envioCurl['elementtype'] = 'order';
    $idCita = $_POST['idCita'];
    if(empty($idCita)){
        //Creamos cita nueva
        $salida = callAPI('POST', $keyAPI, $urlAPI."agendaevents/",json_encode($envioCurl));
        //eliminamos comprometido
        $eliminaComprometido = $base->query("UPDATE vol_COMPROMETIDOS SET cita = '' WHERE fk_commande = '".$_POST['idParte']."'");
    }else{
        //modificamos cita
        $agendaCambio = callAPI('PUT', $keyAPI, $urlAPI."agendaevents/".$idCita."/",json_encode($envioCurl));
        $salida = $idCita;
    }

    echo $salida;

}

if (isset($_POST['addCompromiso'])){
    //fecha
    $fecha = explode(" ",$_POST['datep']);
    $fechaSQL =$fecha[2]."/".$fecha[1]."/".$fecha[3].":".$fecha[4]." ".$fecha[5];
    $fechaISO = date("Y-m-d",strtotime($fechaSQL)); //1 hora más
    //Vemos si tiene operarioId
    if(!empty($_POST['idOperario'])){
        //Elimina Cita
        $eliminaCita = callAPI("DELETE", $keyAPI, $urlAPI."agendaevents/".$_POST['idCita'],false);
    }
    //MODIFICA BBDD COMPROMETIDOS
    $cambiaComprometido = $base->query("UPDATE vol_COMPROMETIDOS SET cita = '".$fechaISO."' WHERE fk_commande = '".$_POST['idParte']."'");
    echo $eliminaCita;

}








?>