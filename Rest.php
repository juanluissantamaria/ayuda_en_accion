<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Rest extends REST_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('rest_model');
	}

	public function index_post(){
        $this->response(array( "success" => true, "message" => "Entra a index post" , "data"=> ''));
    }

    public function index_get(){

	}

    public function nuevo_usuario_post()
    {

        $email = trim($this->input->post('Email'));
        $nombre_usuario = trim($this->input->post('Nombre'));
        $apellido_paterno = trim($this->input->post('ApellidoPaterno'));
        $apellido_materno = trim($this->input->post('ApellidoMaterno'));
        $fecha_nacimiento = trim($this->input->post('FechaNacimiento'));
        $celular = trim($this->input->post('celular'));
        $clave = trim($this->input->post('clave'));

        if(filter_var($email, FILTER_VALIDATE_EMAIL) && $nombre_usuario != '' && $celular != '' && $clave != ''){
            $respuesta_exist = $this->rest_model->existe_email_celular($email, $celular);
            if(!$respuesta_exist['success']){
                $clave = $this->encriptacion_clave($clave);
                $datos_registro = array(
                    "Celular"              => $celular,
                    "Correo"       => $email,
                    "Clave"            => $clave,
                    "Nombre"     => $nombre_usuario,
                    "ApellidoPaterno"     => $apellido_paterno,
                    "ApellidoMaterno"     => $apellido_materno,
                    "FechaNacimiento"     => $fecha_nacimiento,
                    "Tipo"                => "G",
                    "Status" => 3,
                    "Comentarios" => 'Pendiente verificación por sms',
                    "IdUsuarioModifico"     => 1,
                    "FechaModifico"     => date('Y-m-d H:i:s'));

                $respuesta = $this->rest_model->guardar_usuario($datos_registro);
                $this->response( $respuesta );  
            }else{
                $this->response(array( "success" => false, "message" => $respuesta_exist['message'] , "data"=> ''));  
            }
        }else{
            $msj_extra = '';
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $msj_extra .= ' Email no valido ('.$email.')';
            }
            if($nombre_usuario == ''){
                $msj_extra .= ', Nombre vacio ('.$nombre_usuario.')';
            }
            if($celular == ''){
                $msj_extra .= ', Celular vacio ('.$celular.')';
            }
            if($clave == ''){
                $msj_extra .= ', Clave vacia ('.$clave.')';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    public function actualizar_usuario_post()
    {
        $IdUsuario = trim($this->input->post('IdUsuario'));
        $email = trim($this->input->post('Email'));
        $nombre_usuario = trim($this->input->post('Nombre'));
        $apellido_paterno = trim($this->input->post('ApellidoPaterno'));
        $apellido_materno = trim($this->input->post('ApellidoMaterno'));
        $fecha_nacimiento = trim($this->input->post('FechaNacimiento'));
        $sexo = trim($this->input->post('Sexo'));
        $estado_civil = trim($this->input->post('EstadoCivil'));
        $celular = trim($this->input->post('celular'));

        if($IdUsuario > 0 && filter_var($email, FILTER_VALIDATE_EMAIL) && $nombre_usuario != '' && $celular != ''){
            $respuesta_exist = $this->rest_model->existe_email_celular($email, $celular, $IdUsuario);
            if(!$respuesta_exist['success']){
                $datos_registro = array(
                    "Celular"              => $celular,
                    "Correo"       => $email,
                    "Nombre"     => $nombre_usuario,
                    "ApellidoPaterno"     => $apellido_paterno,
                    "ApellidoMaterno"     => $apellido_materno,
                    "FechaNacimiento"     => $fecha_nacimiento,
                    "IdSexo"     => $sexo,
                    "IdEstadoCivil"     => $estado_civil,
                    "IdUsuarioModifico"     => 1,
                    "FechaModifico"     => date('Y-m-d H:i:s'));

                $respuesta = $this->rest_model->actualizar_usuario($datos_registro, $IdUsuario);
                $this->response( $respuesta );  
            }else{
                $this->response(array( "success" => false, "message" => $respuesta_exist['message'] , "data"=> ''));  
            }
        }else{
            $msj_extra = '';
            if($IdUsuario <= 0){
                $msj_extra .= ' No se ha indicado el identificador de usuario('.$IdUsuario.')';
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $msj_extra .= ' Email no valido ('.$email.')';
            }
            if($nombre_usuario == ''){
                $msj_extra .= ', Nombre vacio ('.$nombre_usuario.')';
            }
            if($celular == ''){
                $msj_extra .= ', Celular vacio ('.$celular.')';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    public function actualizar_usuario_direccion_post()
    {
        $IdUsuario = trim($this->input->post('IdUsuario'));
        $idMunicipio = trim($this->input->post('IdMunicipio'));

        if($IdUsuario > 0 && $idMunicipio > 0){
            $datos_registro = array(
                "IdMunicipio"       => $idMunicipio,
                "IdUsuarioModifico" => 1,
                "FechaModifico"     => date('Y-m-d H:i:s'));
            $respuesta = $this->rest_model->actualizar_usuario($datos_registro, $IdUsuario);
            $this->response( $respuesta );  
        }else{
            $msj_extra = '';
            if($IdUsuario <= 0){
                $msj_extra .= ' No se ha indicado el identificador de usuario('.$IdUsuario.')';
            }
            if($idMunicipio <= 0){
                $msj_extra .= ' Municipio no elegido ('.$idMunicipio.')';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    public function actualizar_usuario_laborales_post()
    {
        $IdUsuario = trim($this->input->post('IdUsuario'));
        $rfc = trim($this->input->post('RFC'));
        $razonSocial = trim($this->input->post('RazonSocial'));
        $idActividadEconomica = trim($this->input->post('IdActividadEconomica'));
        $idGradoEstudios = trim($this->input->post('IdGradoEstudios'));

        if($IdUsuario > 0 && $rfc != '' && $razonSocial != '' && $idActividadEconomica > 0 && $idGradoEstudios > 0){
            $datos_registro = array(
                "RFC"                   => $rfc,
                "RazonSocial"           => $razonSocial,
                "IdActividadEconomica"  => $idActividadEconomica,
                "IdGradoEstudios"       => $idGradoEstudios,
                "IdUsuarioModifico"     => 1,
                "FechaModifico"         => date('Y-m-d H:i:s'));
            $respuesta = $this->rest_model->actualizar_usuario($datos_registro, $IdUsuario);
            $this->response( $respuesta );  
        }else{
            $msj_extra = '';
            if($IdUsuario <= 0){
                $msj_extra .= ' No se ha indicado el identificador de usuario('.$IdUsuario.')';
            }
            if($rfc == ''){
                $msj_extra .= ' No se ha escrito su rfc ('.$rfc.')';
            }
            if($razonSocial == ''){
                $msj_extra .= ' No se ha escrito su razón social ('.$razonSocial.')';
            }
            if($idActividadEconomica <= 0){
                $msj_extra .= ' Actividad economica no valida ('.$idActividadEconomica.')';
            }
            if($idGradoEstudios <= 0){
                $msj_extra .= ' Grado de estudios no valido ('.$idGradoEstudios.')';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    public function actualizar_pwd_usuario_post()
    {
        $IdUsuario = trim($this->input->post('IdUsuario'));
        $ClaveActual = trim($this->input->post('ClaveActual'));
        $ClaveNueva = trim($this->input->post('ClaveNueva'));


        if($IdUsuario > 0 && $ClaveActual != '' && $ClaveActual != NULL && $ClaveNueva != '' && $ClaveNueva != NULL){
            if($this->rest_model->validarContraseñaUsuario($ClaveActual, $IdUsuario)){
                $ClaveNueva = $this->encriptacion_clave($ClaveNueva);
                $datos_registro = array(
                        "Clave"              => $ClaveNueva,
                        "IdUsuarioModifico"     => 1,
                        "FechaModifico"     => date('Y-m-d H:i:s'));

                $respuesta = $this->rest_model->actualizar_usuario($datos_registro, $IdUsuario);
                $this->response( $respuesta );  
            }else{
                $this->response(array( "success" => false, "message" => 'La contraseña actual no coincide, verifiquela e intente nuevamente' , "data"=> array()));
            }
        }else{
            $msj_extra = '';
            if($IdUsuario <= 0){
                $msj_extra .= ' No se ha indicado el identificador de usuario('.$IdUsuario.')';
            }
            if($ClaveActual == ''){
                $msj_extra .= ', Contraseña actual no indicada ('.$ClaveActual.')';
            }
            if($ClaveNueva == ''){
                $msj_extra .= ', Contraseña nueva no indicada ('.$celular.')';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    public function login_post(){
        $celular = $this->input->post('Celular');
        $password = $this->input->post('Contrasenia');

        if($celular != '' && $password != ''){
            $this->response($this->rest_model->login($celular,$password));
        }else{
            $msj_extra = '';
            if($celular == ''){
                $msj_extra .= ' Celular no valido ('.$celular.')';
            }
            if($password == ''){
                $msj_extra .= ', Contraseña vacia';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    public function activar_usuario_post()
    {
        $IdUsuario = trim($this->input->post('IdUsuario'));

        if($IdUsuario > 0){
            $datos_usuario = array(
                "Status"              => 1,
                "Comentarios" => 'Usuario verificado con sms',
                "IdUsuarioModifico"     => 1,
                "FechaModifico"     => date('Y-m-d H:i:s'));

            $respuesta = $this->rest_model->activar_usuario($datos_usuario, $IdUsuario);
            $this->response( $respuesta );  
        }else{
            $this->response(array( "success" => false, "message" => "Usuario no valido, inicia sesión nuevamente." , "data"=> ''));
        }
    }

    public function validar_usuario_celular_post()
    {
        $Celular = trim($this->input->post('Celular'));

        if($Celular != ''){
            $respuesta = $this->rest_model->validar_usuario_celular($Celular);
            $this->response( $respuesta );  
        }else{
            $this->response(array( "success" => false, "message" => "Número de celular no valido" , "data"=> ''));
        }
    }

    public function validar_celular_password_post()
    {
        $Celular = trim($this->input->post('Celular'));
        if($Celular != ''){
            $respuesta = $this->rest_model->validar_usuario_celular($Celular);
            if(!$respuesta['success'] && $respuesta['message'] == 'El usuario indicado ya verifico su número de celular.'){
                $respuesta = $this->rest_model->recuperarPassword($respuesta['data']['IdUsuario']);
            }else{
                $respuesta = $this->response(array( "success" => false, "message" => $respuesta->message , "data"=> ''));
            }
            $this->response( $respuesta );  
        }else{
            $this->response(array( "success" => false, "message" => "Número de celular no valido" , "data"=> ''));
        }
    }
	
	
	public function lista_colores_get()
    {
        $respuesta = $this->rest_model->obtener_colores();
        $this->response( $respuesta );       
    }

    public function lista_emociones_color_post()
    {
    	$color = $this->input->post("idColor");
        

        if($color != '' && $color > 0){
            $respuesta = $this->rest_model->obtener_emociones_color($color);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el color" , "data"=> array()));
        }

    }

    public function lista_podcast_emociones_post()
    {
        $emocion = $this->input->post("idEmocion");
        $tipo = trim($this->input->post("Tipo"));
        if($tipo == '' || $tipo == NULL){
            $tipo = 'G';
        }

        if($emocion != '' && $emocion > 0){
            $respuesta = $this->rest_model->obtener_podcast_emocion($emocion, $tipo);
            $this->response( $respuesta );            
        }else{
            $this->response(array( "success" => false, "message" => "No has indicado la emoción" , "data"=> array()));
        }

    }

    public function nueva_visita_post()
    {
        $idVisita = 0;
        if(trim($this->input->post('IdVisita')) != NULL){
            $idVisita = trim($this->input->post('IdVisita'));
        }
        $idColor = trim($this->input->post('IdColor'));
        $idEmocion = trim($this->input->post('IdEmocion'));
        $idPodcast = trim($this->input->post('IdPodcast'));
        $idUsuarioPodcast = trim($this->input->post('IdUsuarioPodcast'));
        $direccionIP = trim($this->input->post('DireccionIP'));
        $latitud = trim($this->input->post('Latitud'));
        $longitud = trim($this->input->post('Longitud'));

        $datos_registro = array(
                    "IdColor"              => $idColor,
                    "IdEmocion"              => $idEmocion,
                    "IdPodcast"       => $idPodcast,
                    "IdUsuarioPodcast"            => $idUsuarioPodcast,
                    "DireccionIP"     => $direccionIP,
                    "Latitud"     => $latitud,
                    "Longitud"     => $longitud,
                    "Fecha"     => date('Y-m-d'),
                    "Status" => 1,
                    "IdUsuarioModifico"     => 1,
                    "FechaModifico"     => date('Y-m-d H:i:s'));

        if(($idVisita == 0 || $idVisita == NUll) && $idColor > 0){
            $respuesta = $this->rest_model->guardar_visita($datos_registro);
        }else{
            $datosVisita = $this->rest_model->obtener_visita($idVisita);
            if($datosVisita[0]){
                if($datosVisita[0]->Fecha == date('Y-m-d')){
                    if($datosVisita[0]->IdEmocion == 0 && $idEmocion > 0 && $idPodcast == 0){
                        $datos_registro = array(
                            "IdEmocion"              => $idEmocion,
                            "DireccionIP"     => $direccionIP,
                            "Latitud"     => $latitud,
                            "Longitud"     => $longitud,
                            "Fecha"     => date('Y-m-d'),
                            "Status" => 1,
                            "IdUsuarioModifico"     => 1,
                            "FechaModifico"     => date('Y-m-d H:i:s'));
                        $respuesta = $this->rest_model->actualizar_visita($datos_registro, $idVisita);
                    }else if($datosVisita[0]->IdPodcast == 0 && $idPodcast > 0 && $idEmocion == 0){
                        $datos_registro = array(
                            "IdPodcast"              => $idPodcast,
                            "DireccionIP"     => $direccionIP,
                            "Latitud"     => $latitud,
                            "Longitud"     => $longitud,
                            "Fecha"     => date('Y-m-d'),
                            "Status" => 1,
                            "IdUsuarioModifico"     => 1,
                            "FechaModifico"     => date('Y-m-d H:i:s'));
                        $respuesta = $this->rest_model->actualizar_visita($datos_registro, $idVisita);
                    }else{
                        if($datosVisita[0]->IdEmocion > 0){
                            $datos_registro['IdColor'] = $datosVisita[0]->IdColor; 
                        }

                        if($datosVisita[0]->IdPodcast > 0){
                            $datos_registro['IdEmocion'] = $datosVisita[0]->IdEmocion; 
                        }

                        $respuesta = $this->rest_model->guardar_visita($datos_registro);
                    }
                }else{
                    $respuesta = $this->rest_model->guardar_visita($datos_registro);
                }
            }else{
                $respuesta = $this->response(array( "success" => false, "message" => "No se ha indicado la visita anterior." , "data"=> array()));
            }
        }
        $this->response( $respuesta );
    }

    public function configuracion_get()
    {
        $respuesta = $this->rest_model->obtener_configuracion();
        $this->response( $respuesta );            
    }

    public function sexo_get()
    {
        $respuesta = $this->rest_model->obtener_sexos();
        $this->response( $respuesta );       
    }

    public function estado_civil_get()
    {
        $respuesta = $this->rest_model->obtener_estado_civil();
        $this->response( $respuesta );       
    }

    public function obtener_sexo_post()
    {
        $sexo = $this->input->post("idSexo");
        

        if($sexo != '' && $sexo > 0){
            $respuesta = $this->rest_model->obtener_sexo($sexo);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el sexo" , "data"=> array()));
        }

    }

    public function obtener_estado_civil_post()
    {
        $estado_civil = $this->input->post("idEstadoCivil");
        

        if($estado_civil != '' && $estado_civil > 0){
            $respuesta = $this->rest_model->obtener_estado_civil_id($estado_civil);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el estado civil" , "data"=> array()));
        }

    }

    public function obtener_datos_usuario_post()
    {
        $idUsuarioPodcast = $this->input->post("idUsuario");
        

        if($idUsuarioPodcast > 0){
            $respuesta = $this->rest_model->obtener_usuario($idUsuarioPodcast);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el usuario" , "data"=> array()));
        }

    }

    public function introduccion_get()
    {
        $respuesta = $this->rest_model->obtener_introduccion();
        $this->response( $respuesta );            
    }

    public function datosAportaciones_get()
    {
        $respuesta = $this->rest_model->obtener_datos_aportaciones();
        $this->response( $respuesta );            
    }

    public function lista_estados_get()
    {
        $respuesta = $this->rest_model->obtener_estados();
        $this->response( $respuesta );       
    }

    public function obtener_estado_get()
    {
        $municipio = $this->input->get("idMunicipio");
        if($municipio != '' && $municipio > 0){
            $respuesta = $this->rest_model->obtener_estado_municipio($municipio);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el municipio" , "data"=> array()));
        }
    }

    public function lista_municipios_get()
    {
        $estado = $this->input->get("idEstado");
        if($estado != '' && $estado > 0){
            $respuesta = $this->rest_model->obtener_municipios_estado($estado);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el estado" , "data"=> array()));
        }       
    }

    public function lista_actividadesE_get()
    {
        $respuesta = $this->rest_model->obtener_actividadesE();
        $this->response( $respuesta );       
    }

    public function lista_gradosE_get()
    {
        $respuesta = $this->rest_model->obtener_gradosE();
        $this->response( $respuesta );       
    }


    public function cambiar_fotoP_post()
    {
    	error_log( 'IdUsuarioPodcast recibido: '.( $this->input->post('IdUsuarioPodcast') !== null ? $this->input->post('IdUsuarioPodcast') : 'No esta definido IdUsuarioPodcast' ).( isset($_FILES['file']) ? ' File definido: '.$_FILES['file']['name'] : ' No esta definido el file' ) , 0);
        $ruta = "usuariospodcast";
        $carpetaAdjunta = "assets/imagenes/".$ruta."/";    
        $idUsuarioPodcast = trim($this->input->post('IdUsuarioPodcast'));

        if($idUsuarioPodcast > 0){

            $ano=date("Y");
            $mes=date("m");
            $dia=date("d");
            $hora=date("H");
            $min=date("i");
            $seg=date("s");
            $fecha = $ano.$mes.$dia.$hora.$min.$seg;
            $nombre_imagen = 'Usuarios'.$fecha.'.jpg';

            // El nombre y nombre temporal del archivo que vamos para adjuntar
            $nombreArchivo=isset($_FILES['file']['name'])?$_FILES['file']['name']:null;
            $nombreTemporal=isset($_FILES['file']['tmp_name'])?$_FILES['file']['tmp_name']:null;
    
            $rutaArchivo=$carpetaAdjunta.$nombre_imagen;

            $move_upload = move_uploaded_file($nombreTemporal,$rutaArchivo);
            if($move_upload){
                $datosUsuario = $this->rest_model->obtener_usuario($idUsuarioPodcast);
                // var_dump($datosUsuario);
                if($datosUsuario['data']->Fotografia != ''){
                    unlink($carpetaAdjunta.$datosUsuario['data']->Fotografia);
                }
                $datos_registro       = array(
                    "Fotografia"          => $nombre_imagen,
                    "IdUsuarioModifico"     => 1,
                    "FechaModifico"     => date('Y-m-d H:i:s'));

                $respuesta = $this->rest_model->actualizar_usuario($datos_registro, $idUsuarioPodcast);
                $this->response( $respuesta );
            }else{
                $this->response(array( "success" => false, "message" => "Ocurrio un error al subir la imagen, detalles: ".$_FILES["file"]["error"].".", "data"=> ''));
            }
        }else{
            $msj_extra = '';
            if($idUsuarioPodcast <= 0){
                $msj_extra .= ' No se ha indicado el identificador de usuario('.$idUsuarioPodcast.')';
            }
            $this->response(array( "success" => false, "message" => "Faltan datos. Detalles: ".$msj_extra , "data"=> ''));
        }
    }

    function enviar_codigo_correo_post(){
        $correo = $this->input->post("Correo");
        if(filter_var($correo, FILTER_VALIDATE_EMAIL)){
            $respuesta = $this->rest_model->crear_codigo($correo);
            if($respuesta['success']){
                $cuerpo = "<h1>Ayuda en Acción</h1>";
                $cuerpo .= "<br/>";
                $cuerpo .= "<p>Tu código de verificación es ".$respuesta['datos']['Codigo']." , ingresalo en la app para validar tu correo.</p>";
                //mando el correo...
                $success = mail($correo, 'Ayuda en Acción', $cuerpo, "MIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\nFrom: Mensaje de: https://ayudaenaccion.detecsa-consultores.com");
                
                if (!$success) {
                    $this->response(array( "success" => false, "message" => "No se envio el correo." , "data"=> array()));
                } else {
                    return array( "success" => true, "message" => 'Código enviado correctamente.', 'data' => array() );
                }
            }else{
                $this->response( $respuesta );
            }
        }else{
            $this->response(array( "success" => false, "message" => "El correo no es valido." , "data"=> array()));
        }

        if($idUsuarioPodcast > 0){
            $respuesta = $this->rest_model->obtener_usuario($idUsuarioPodcast);
            $this->response( $respuesta );            
        } else{
            $this->response(array( "success" => false, "message" => "No has indicado el usuario" , "data"=> array()));
        }
    }

    /* ---------------------------------------------- */

    public function lista_clientes_get()
    {

        $respuesta = $this->rest_model->obtener_clientes();
        $this->response( $respuesta );            
    }

    public function lista_doctores_post()
    {
    	$userid = $this->input->post('userId');
    	$tipoUsuario = $this->input->post('tipoUsuario');
        $respuesta = $this->rest_model->obtener_doctores( $userid, $tipoUsuario );
        $this->response( $respuesta );          
    }

    public function lista_tiposconsulta_post()
    {
        $respuesta = $this->rest_model->obtener_tiposconsulta($this->input->post('iddoctor'),$this->input->post('idcliente'));
        $this->response( $respuesta );
    }

    public function guardar_registro_post()
	{
        $cliente = trim($this->input->post('IdCliente'));
        $doctor = trim($this->input->post('IdDoctor'));
        $convenio = trim($this->input->post('IdConvenio'));
        $tipoconsulta = trim($this->input->post('TipoConsulta'));
        $fecha = trim($this->input->post('Fecha'));
        $inicio = trim($this->input->post('HoraInicio'));
        $final = trim($this->input->post('HoraFinal'));

        $userId = trim($this->input->post('IdUsuarioModifico'));

        $datos_registro       = array(
            "IdCliente"       => $cliente,
            "IdDoctor"       => $doctor,
            "IdConvenio"            => $convenio,
            "TipoConsulta"            => $tipoconsulta,
            "Fecha"            => $fecha,
            "HoraInicio"            => $inicio,
            "HoraFinal"            => $final,
            "Status"            => 1,
            "IdUsuarioModifico" => $userId,
            "FechaModifico"     => date('Y-m-d H:i:s'));

        $respuesta = $this->rest_model->guardar_registro($datos_registro);
        
        $this->response( $respuesta );  
	}

	public function cambiar_contrasenia_post(){
		$id = $this->input->post('IdUsuario');
		$pwd = $this->input->post('pwd');
		$newpwd = $this->input->post('newpwd');

		$respuesta = $this->rest_model->cambiarContrasenia( $id, $pwd, $newpwd );
		$this->response( $respuesta );
	}

	public function obtener_citas_post(){
		$id = $this->input->post('IdUsuario');
		$respuesta = $this->rest_model->getEvents($id);
		$this->response( $respuesta );
	}

	public function grafica_post(){
		$id = $this->input->post('IdUsuario');
		$respuesta = $this->rest_model->listaIngresosEgresos($id);
		$this->response( $respuesta );
	}

	public function historial_post(){
		$iddoctor = $this->input->post("idDoctor");
		$idpaciente = $this->input->post("idPaciente");
		$this->response($this->rest_model->obtenerHistorialPorPacienteDoctor($iddoctor, $idpaciente));

	}

    public function encriptacion_clave($clave){
        $this->encryption->initialize(
            array(
                'driver' => 'openssl',
                'cipher' => 'aes-256',
                'mode' => 'ctr',
                'key' => 'j0v3n352024'
            )
        );
        $clave_encriptada = $this->encryption->encrypt($clave);
        return $clave_encriptada;
    }

    public function desencriptar_clave($clave){
        $this->encryption->initialize(
            array(
                'driver' => 'openssl',
                'cipher' => 'aes-256',
                'mode' => 'ctr',
                'key' => 'j0v3n352024'
            )
        );
        $clave_encriptada = $this->encryption->decrypt($clave);
        return $clave_encriptada;
    }

}