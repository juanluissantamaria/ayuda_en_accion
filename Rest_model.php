<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rest_model extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	function encriptacion_clave($clave){
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

    function desencriptacion_clave($clave){
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

	function login($usr,$pwd){
		$sql = "SELECT UsuariosPodcasts.* FROM UsuariosPodcasts 
				where UsuariosPodcasts.Celular = '$usr' AND UsuariosPodcasts.Status = 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		if( isset( $row ) ) {
				$password_db = $this->desencriptacion_clave($row->Clave);
				if( $password_db == $pwd ) {
					return array(   "success" => true, 
							"message" => "Acceso correcto",
							"data"=> $row
						);
				}else{
					return array(   "success" => false, 
						"message" => "Contraseña incorrecta",
						"data"=> null
					);
				}
		}
		return array(   "success" => false, 
						"message" => "Usuario y/o contraseña incorrectos",
						"data"=> null
					);
	}

	function recuperarPassword($idUsuarioPodcast){
		$sql = "SELECT UsuariosPodcasts.Clave FROM UsuariosPodcasts 
				where UsuariosPodcasts.IdUsuarioPodcast = $idUsuarioPodcast AND UsuariosPodcasts.Status = 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		if( isset( $row ) ) {
				$row->Clave = $this->desencriptacion_clave($row->Clave);
				return array(   "success" => true, 
							"message" => "Contraseña recuperada",
							"data"=> $row
						);
		}
		return array(   "success" => false, 
						"message" => "Usuario no encontrado",
						"data"=> null
					);
	}

	function obtener_usuario($idUsuario){
        $sql = "SELECT CONCAT(usu.Nombre, ' ', usu.ApellidoPaterno, ' ', usu.ApellidoMaterno) AS NombreCompleto, usu.Correo, usu.Celular, usu.RazonSocial, usu.RFC, usu.Fotografia, s.Sexo, ec.EstadoCivil, e.Estado, m.Municipio, ae.ActividadEconomica, ge.GradoEstudios
        	FROM UsuariosPodcasts AS usu
        	LEFT JOIN Sexos AS s ON usu.IdSexo = s.IdSexo
        	LEFT JOIN EstadosCiviles AS ec ON usu.IdEstadoCivil = ec.IdEstadoCivil
        	LEFT JOIN Municipios AS m ON usu.IdMunicipio = m.IdMunicipio
        	LEFT JOIN Estados AS e ON m.IdEstado = e.IdEstado
        	LEFT JOIN ActividadesEconomicas AS ae ON usu.IdActividadEconomica = ae.IdActividadEconomica
        	LEFT JOIN GradoEstudios AS ge ON usu.IdGradoEstudios = ge.IdGradoEstudios	
			WHERE usu.Status=1 AND usu.IdUsuarioPodcast=$idUsuario;";
		$query = $this->db->query($sql);
		$row = $query->row();

		if( isset( $row ) ) {
			return array(   "success" => true, 
							"message" => "Datos obtenidos correctamente",
							"data"=> $row
						);
		}
		return array(   "success" => false, 
						"message" => "No fue posible obtener los datos del usuario",
						"data"=> null
					);
		
	}

	function valida_codigo($codigo, $idUsuario, $correo){
		$sql = "SELECT COUNT(*) as cuenta
        	FROM Codigos	
			WHERE Status=1 AND IdUsuario=$idUsuario AND Correo = '$correo';";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($row && $row->cuenta != NULL && $row->cuenta > 0){
			return false;
		}else{
			return true;
		}
	}

	function guardar_codigo($correo, $idUsuario, $codigo){
		$datos = array(
			"Codigo" => $codigo,
			"IdUsuario" => $idUsuario,
			"Correo" => $correo,
			"Status" => 1,
			"IdUsuarioModifico" => 1,
			"FechaModifico" => date('Y-m-d H:i:s'))
		);

		$validation = $this->db->insert('Codigos', $datos);
		return $validation;
	}

	function generarCodigo($longitud) {
	    $key = '';
	    $pattern = '1234567890';
	    $max = strlen($pattern)-1;
	    for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
	    return $key;
	}   

	function crear_codigo($correo){
		$IdUsuario = $this->obtenerIdUsuarioCorreo($correo);
		if($IdUsuario > 0){
			$codigo = generarCodigo(6);
			while (!valida_codigo($codigo, $idUsuario, $correo)) {
				$codigo = generarCodigo(6);
			}
			$validation = $this->guardar_codigo($correo, $IdUsuario, $codigo);
			if( $validation){
				return array( "success" => true, "message" => 'Código creado correctamente.', 'data' => array('Codigo'=>$codigo) );
			}else{
				return array( "success" => false, "message" => "Ocurrió un error al guarda el código.", 'data' => array());
			}
		}else{
			return array( "success" => false, "message" => "No hay usuarios registrados con ese correo.", 'data' => array());
		}
		$validation = $this->generar_codigo($correo);

		if( $validation){
			$IdUsuario = $this->obtenerIdUsuarioCorreo($correo);
			$resoibse
			$datos['IdUsuario'] = $IdUsuario;
			return array( "success" => true, "message" => 'Usuario creado correctamente.', 'data' => $datos);
		}

		return array( "success" => false, "message" => "Ocurrió un error, por favor valide sus datos.", 'data' => $datos);
	}

	function obtenerIdUsuarioCorreo($correo){
		$IdUsuario = 0;
		$sql = "SELECT IdUsuarioPodcast as IdUsuario FROM UsuariosPodcasts WHERE Correo = '$correo';";
		$query = $this->db->query($sql);
		
		if ( $query->num_rows() < 1) {
			return $IdUsuario;
		}
		
		$row = $query->row();
		if($row->IdUsuario != NULL){
			$IdUsuario = $row->IdUsuario;
		}
		return $IdUsuario; 
	}

	function guardar_usuario( $datos ) {

    	$validation = $this->db->insert('UsuariosPodcasts', $datos);

		if( $validation){
			$IdUsuario = $this->obtenerIdUltimoUsuario();
			$datos['IdUsuario'] = $IdUsuario;
			return array( "success" => true, "message" => 'Usuario creado correctamente.', 'data' => $datos);
		}

		return array( "success" => false, "message" => "Ocurrió un error, por favor valide sus datos.", 'data' => $datos);
	}

	function actualizar_usuario( $datos, $id ) {

    	$this->db->where('IdUsuarioPodcast', $id);
        $validation =  $this->db->update('UsuariosPodcasts', $datos);	

        if( $validation ){
        	$sql = "SELECT UsuariosPodcasts.* FROM UsuariosPodcasts 
				where UsuariosPodcasts.IdUsuarioPodcast = $id";
			$query = $this->db->query($sql);
			$row = $query->row();
			if( isset( $row ) ) {
				$datos = $row;
			}
        	
			return array( "success" => true, "message" => 'Usuario actualizado correctamente.', 'data' => $datos);
		}

		return array( "success" => false, "message" => "Ocurrió un error, por favor valide sus datos.", 'data' => $datos);
	}

	function validarContraseñaUsuario($password, $id){

		$sql = "SELECT UsuariosPodcasts.Clave FROM UsuariosPodcasts 
				where UsuariosPodcasts.IdUsuarioPodcast = $id AND UsuariosPodcasts.Status = 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		if( isset( $row ) ) {
			$password_db = $this->desencriptacion_clave($row->Clave);
			if( $password_db == $password ) {
				return true;
			}else{
				return false;
			}
		}
		return false;
	}

	function obtenerIdUltimoUsuario(){
		$IdUsuario = 0;
		$sql = "SELECT MAX(IdUsuarioPodcast) as IdUsuario FROM UsuariosPodcasts;";
		$query = $this->db->query($sql);
		
		if ( $query->num_rows() < 1) {
			return $IdUsuario;
		}
		
		$row = $query->row();
		if($row->IdUsuario != NULL){
			$IdUsuario = $row->IdUsuario;
		}
		return $IdUsuario; 
	}

	function activar_usuario($datos, $id){
        $this->db->where('IdUsuarioPodcast', $id);
        $validation =  $this->db->update('UsuariosPodcasts', $datos);	

        if( $validation ){
			return array( "success" => true, "message" => 'Usuario activado correctamente.', 'data' => $datos);
		}

		return array( "success" => false, "message" => "Ocurrió un error, por favor valide sus datos.", 'data' => $datos);
	}

	function existe_email_celular($email, $celular, $idUsuario = 0){
		$sql = "SELECT Correo, Celular FROM UsuariosPodcasts 
				WHERE (Correo = '$email' OR Celular = '$celular') AND Status = 1";
		if($idUsuario > 0){
			$sql .= " AND IdUsuarioPodcast != $idUsuario";
		}

		$query = $this->db->query($sql);
		$row = $query->row();
		if( isset( $row ) ) {
			if( $email == $row->Correo ) {
				return array(   "success" => true, 
						"message" => "El correo ingresado ya se encuentra registrado.",
						"data"=> $row->Correo
					);
			}else if( $celular == $row->Celular ) {
				return array(   "success" => true, 
						"message" => "El número de celular ingresado ya se encuentra registrado.",
						"data"=> $row->Celular
					);
			}
		}

		return array(   "success" => false, 
						"message" => "Correo y celular no estan registrados.",
						"data"=> null
					);
	}

	function validar_usuario_celular($celular){
		$sql = "SELECT IdUsuarioPodcast as IdUsuario, Status, Celular FROM UsuariosPodcasts 
				WHERE Celular = '$celular';";
		$query = $this->db->query($sql);
		$row = $query->row();
		if( isset( $row ) ) {
			if($row->Status == 3){
				return array(   "success" => true, 
						"message" => "Usuario registrado sin verificar celular.",
						"data"=> array("IdUsuario"=>$row->IdUsuario, 'Celular'=>$row->Celular)
				);
			}else if($row->Status == 1){
				return array(   "success" => false, 
						"message" => "El usuario indicado ya verifico su número de celular.",
						"data"=> array("IdUsuario"=>$row->IdUsuario, 'Celular'=>$row->Celular)
					);
			}else{
				return array(   "success" => false, 
						"message" => "No se encontro un usuario registrado con el celular indicado.",
						"data"=> null
					);
			}
		}

		return array(   "success" => false, 
						"message" => "No se encontro un usuario registrado con el celular indicado.",
						"data"=> null
					);
	}

	public function obtener_colores()
    {
        $sql = "SELECT *, CONCAT('btn-', lower(Colores.Concepto)) as class

				FROM Colores

				where Status=1 AND Concepto <> 'VERDE'

				ORDER BY IdColor";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);		

    }

    public function obtener_emociones_color($idColor)
    {
        $sql = "SELECT e.*, CONCAT('https://ayudaenaccion.detecsa-consultores.com/assets/imagenes/emociones/', e.Imagen) as imagen
				FROM Emociones e
				JOIN Colores c
				ON e.IdColor=c.IdColor
				WHERE e.IdColor=$idColor
				AND e.Status=1
				ORDER BY e.Orden ASC, e.Emocion ASC;";

        $query = $this->db->query($sql);

        $data = $query->result();		
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    public function obtener_podcast_emocion($idEmocion, $Tipo)
    {
        $sql = "SELECT p.IdPodcast as id, p.Podcast as name, p.Descripcion as descripcion, CONCAT('https://ayudaenaccion.detecsa-consultores.com/assets/imagenes/podcasts/', p.Imagen) as imagen, CONCAT('https://ayudaenaccion.detecsa-consultores.com/assets/imagenes/podcasts/', p.AudioVideo) as url, p.AudioVideo as url_video
				FROM Podcasts p
				JOIN Emociones e
				ON p.IdEmocion=e.IdEmocion
				WHERE p.IdEmocion=$idEmocion
				AND p.Tipo = '$Tipo'
				AND p.Status=1 AND p.Activo=1
				ORDER BY p.Orden ASC, p.Podcast ASC;";

        $query = $this->db->query($sql);

        $data = $query->result();		
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    function guardar_visita( $datos ) {

    	$validation = $this->db->insert('Visitas', $datos);

		if( $validation){
			$datos['IdVisita'] = $this->db->insert_id();
			return array( "success" => true, "message" => 'Visita creada correctamente.', 'data' => $datos);
		}

		return array( "success" => false, "message" => "Ocurrió un error, por favor valide sus datos.", 'data' => $datos);
	}

	function actualizar_visita( $datos, $id ) {
		$this->db->where('IdVisita', $id);
        $validation =  $this->db->update('Visitas', $datos);	
        if( $validation ){
        	$datos['IdVisita'] = $id;	
			return array( "success" => true, "message" => 'Visita actualizada correctamente.', 'data' => $datos);
		}
		return array( "success" => false, "message" => "Ocurrió un error, por favor valide sus datos.", 'data' => $datos);
	}

	public function obtener_visita($idVisita)
    {
        $sql = "SELECT *
				FROM Visitas
				where IdVisita = $idVisita AND Status=1";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return $data;		

    }

	public function obtener_configuracion()
    {
        $sql = "SELECT AplicacionMovil as PiePagina, CONCAT('https://ayudaenaccion.detecsa-consultores.com/assets/imagenes/configuracion/', Logotipo) AS Logotipo
				FROM Configuracion
				where Status = 1
				LIMIT 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);		

    }

    public function obtener_sexos()
    {
        $sql = "SELECT *
				FROM Sexos
				WHERE Status = 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "Datos obtenidos correctamente" , "data"=> $data);		

    }

    public function obtener_estado_civil()
    {
        $sql = "SELECT *
				FROM EstadosCiviles
				WHERE Status = 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "Datos obtenidos correctamente" , "data"=> $data);		

    }

    public function obtener_sexo($idSexo)
    {
        $sql = "SELECT *
				FROM Sexos
				WHERE IdSexo = $idSexo AND Status = 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "Datos obtenidos correctamente" , "data"=> $data);		

    }

    public function obtener_estado_civil_id($idEstadoCivil)
    {
        $sql = "SELECT *
				FROM EstadosCiviles
				WHERE IdEstadoCivil = $idEstadoCivil AND Status = 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "Datos obtenidos correctamente" , "data"=> $data);		

    }

    public function obtener_introduccion()
    {
        $sql = "SELECT Introduccion, VideoIntroduccion, PoliticasPrivacidad
				FROM Parametros
				where Status = 1
				LIMIT 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "Datos de introduccion obtenidos correctamente" , "data"=> $data);		

    }

    public function obtener_datos_aportaciones(){
		$sql = "SELECT DonacionMinima, VigenciaDonacion, ClavePrivadaConekta, ClavePublicaConekta
				FROM Parametros
				where Status = 1
				LIMIT 1;";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "Datos de aportaciones obtenidos correctamente" , "data"=> $data);		    	
    }

    public function obtener_estados(){
        $sql = "SELECT *
				FROM Estados
				where Status=1
				ORDER BY IdEstado";
        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    public function obtener_estado_municipio($idMunicipio)
    {
        $sql = "SELECT IdEstado
				FROM Municipios
				WHERE IdMunicipio = $idMunicipio
				AND Status=1;";

        $query = $this->db->query($sql);

        $data = $query->result();		
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    public function obtener_municipios_estado($idEstado)
    {
        $sql = "SELECT *
				FROM Municipios
				WHERE IdEstado = $idEstado
				AND Status=1;";

        $query = $this->db->query($sql);

        $data = $query->result();		
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    public function obtener_actividadesE(){
        $sql = "SELECT *
				FROM ActividadesEconomicas
				where Status=1
				ORDER BY IdActividadEconomica";
        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    public function obtener_gradosE(){
        $sql = "SELECT *
				FROM GradoEstudios
				where Status=1
				ORDER BY IdGradoEstudios";
        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }


	/*Consultas para nueva cita*/
	
    public function obtener_convenios()
    {
        $sql = "SELECT *

				FROM Convenios

				where Status=1

				ORDER BY IdConvenio";

        $query = $this->db->query($sql);
        $data =  $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);		

    }

    public function obtener_clientes_post($iddoctor)
    {
        $sql = "SELECT c.*, CONCAT(c.Nombre, ' ', c.ApellidoPaterno, ' ', c.ApellidoMaterno) AS NombreCompleto
				FROM Clientes c
				JOIN Citas ct
				ON ct.IdCliente=c.IdCliente
				WHERE ct.IdDoctor=$iddoctor
				AND c.Status=1
				GROUP BY ct.IdCliente
				ORDER BY NombreCompleto ASC;";

        $query = $this->db->query($sql);

        $data = $query->result();		
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }

    public function obtener_doctores($IdUsuario, $tipo_usuario)
    {
		$idregistro = $IdUsuario;

		if( $tipo_usuario == '3' ) {
	        $sql = "SELECT *, CONCAT(doc.Nombre, ' ', doc.ApellidoPaterno, ' ', doc.ApellidoMaterno) AS NombreCompleto

				FROM Doctores doc

				inner join Usuarios usu
				on doc.IdDoctor=usu.IdDoctor

				where doc.Status=1 and  usu.IdUsuario=$idregistro

				ORDER BY doc.IdDoctor";
		} else {
	        $sql = "SELECT *, CONCAT(Nombre, ' ', ApellidoPaterno, ' ', ApellidoMaterno) AS NombreCompleto

				FROM Doctores

				where Status=1

				ORDER BY IdDoctor";
		}

        $query = $this->db->query($sql);

        $data = $query->result();

       	return array( "success" => true, "message" => "" , "data"=> $data);	

    }

    public function obtener_tiposconsulta($id,$cliente)
    {
    	$firstDateQuery = "SELECT * from Citas where IdCliente = $cliente ; ";

    	$ceroquery = "SELECT *	FROM Consultas
						where Status=1 and IdDoctor=$id and Principal= 0
						order by IdDoctor, Principal, IdConsulta;";


        $sql = "SELECT * FROM TiposConsultas
				where Status=1 and IdDoctor=$id and Principal = 1 
				order by IdDoctor, Principal, IdConsulta";

		$fistDateResult = $this->db->query($firstDateQuery);

		if( $fistDateResult->num_rows() == 0 ){
			//Primera cita
			$ceroresult = $this->db->query($ceroquery);
			if( $ceroresult->num_rows() > 0 ){
				//exite un cero
				return array( "success" => true, "message" => "" , "data"=> $ceroresult->result() );
			}
		}

		//no es primera cita o no hay cero
		$query = $this->db->query($sql);


        $data = $query->result();
        return array( "success" => true, "message" => "" , "data"=> $data);
    }

    function guardar_registro( $datos ) {
		
		$validation = $this->ValidateDate( $datos['IdDoctor'], $datos['IdCliente'], $datos['Fecha'],
											$datos['HoraInicio'], $datos['HoraFinal'] );

		if( !$validation['success'] ){
			return array( "success" => false, "message" => $validation['message']);
		}
		
		if( $this->db->insert('Citas', $datos) ) {
			return array( "success" => true, "message" => "Cita creada correctamente, espere la confirmación");
		}
		return array( "success" => false, "message" => "Ocurrió un error, por favor intenta más tarde");
	}


	function cambiarContrasenia( $id, $pwd, $newPwd ) {
		$sql = "SELECT Usuarios.*,Perfiles.IdPerfil FROM Usuarios 
				LEFT JOIN Perfiles 
				ON Perfiles.IdPerfil = Usuarios.IdPerfil
				where Usuarios.IdUsuario = $id and Usuarios.Status=1 ;";
		$query = $this->db->query($sql);
		
		if ( $query->num_rows() < 1) {
			return array( "success" => false, "message" => "Usuario inexistente");
		}
		
		$row = $query->row();
		$currentpwd = $this->desencriptacion_clave($row->Clave);
		
		if ( $currentpwd == $pwd ) {
			$bdpwd = $this->encriptacion_clave( $newPwd );
			$sqlcommand = "UPDATE Usuarios SET Clave = '$bdpwd' where IdUsuario = $id;";
			if ( $this->db->query( $sqlcommand ) ) {
				return array( "success" => true, "message" => "Contraseña actualizada correctamente, por favor vuelve a iniciar sesión");
			}
			return array( "success" => false, "message" => "Ocurrió un error, por favor intenta más tarde");

		}else{
			return array( "success" => false, "message" => "Contraseña incorrecta");
		}
	}

	function getEvents($user){

		$idregistro=$user;
		$hoy=date("Y-m-d");

		$sql="SELECT cit.IdCita as id, CONCAT(cli.Nombre, ' ', cli.ApellidoPaterno, ' ', cli.ApellidoMaterno) AS title, STR_TO_DATE (CONCAT(DATE(cit.Fecha),' ',cit.HoraInicio), '%Y-%m-%d %H:%i:%s') as start, STR_TO_DATE (CONCAT(DATE(cit.Fecha),' ',cit.HoraFinal), '%Y-%m-%d %H:%i:%s') as end, if(cit.Fecha<'$hoy','#ccc',doc.Color) as color

			from Citas cit

			inner join Clientes cli
			on cit.IdCliente=cli.IdCliente

			inner join Doctores doc
			on cit.IdDoctor=doc.IdDoctor

			inner join Usuarios usu
			on doc.IdDoctor=usu.IdDoctor

			where usu.IdUsuario=$idregistro and cit.Status=2";

		$query = $this->db->query($sql);
	    $data = $query->result();

	    return array( "success" => true, 
	    			  "message" => "Citas cargadas correctamente",
	    			  "data" => $data);
	}

	function listaIngresosEgresos($userId){
		$ingresos = $this->contar_ingresos_anual($userId)[0];
		$egresos = $this->contar_egresos_anual($userId)[0];
		$gastos = $this->contar_gastosfijos_anual($userId)[0];
		
		$anual_data = $arrayName = array(
			array( 'Mes' => 'Enero',
				   'Data' => array('Ingresos' =>$ingresos->Enero ,'Egresos' =>$egresos->Enero,'Gastos' =>$gastos->Enero ) 
				),
			array('Mes' => 'Febrero', 
				  'Data' => array('Ingresos' =>$ingresos->Febrero ,'Egresos' =>$egresos->Febrero,'Gastos' =>$gastos->Febrero )
				),
			array('Mes' => 'Marzo', 
				  'Data' => array('Ingresos' =>$ingresos->Marzo ,'Egresos' =>$egresos->Marzo,'Gastos' =>$gastos->Marzo )
				),
			array('Mes' => 'Abril', 
				  'Data' => array('Ingresos' =>$ingresos->Abril ,'Egresos' =>$egresos->Abril,'Gastos' =>$gastos->Abril )
				),
			array('Mes' => 'Mayo', 
				  'Data' => array('Ingresos' =>$ingresos->Mayo ,'Egresos' =>$egresos->Mayo,'Gastos' =>$gastos->Mayo )
				),
			array('Mes' => 'Junio', 
				  'Data' => array('Ingresos' =>$ingresos->Junio ,'Egresos' =>$egresos->Junio,'Gastos' =>$gastos->Junio )
				),
			array('Mes' => 'Julio', 
				  'Data' => array('Ingresos' =>$ingresos->Julio ,'Egresos' =>$egresos->Julio,'Gastos' =>$gastos->Julio )
				),
			array('Mes' => 'Agosto', 
				  'Data' => array('Ingresos' =>$ingresos->Agosto ,'Egresos' =>$egresos->Agosto,'Gastos' =>$gastos->Agosto )
				),
			array('Mes' => 'Septiembre', 
				  'Data' => array('Ingresos' =>$ingresos->Septiembre ,'Egresos' =>$egresos->Septiembre,'Gastos' =>$gastos->Septiembre )
				),
			array('Mes' => 'Octubre', 
				  'Data' => array('Ingresos' =>$ingresos->Octubre ,'Egresos' =>$egresos->Octubre,'Gastos' =>$gastos->Octubre )
				),
			array('Mes' => 'Noviembre', 
				  'Data' => array('Ingresos' =>$ingresos->Noviembre ,'Egresos' =>$egresos->Noviembre,'Gastos' =>$gastos->Noviembre )
				),
			array('Mes' => 'Diciembre', 
				  'Data' => array('Ingresos' =>$ingresos->Diciembre ,'Egresos' =>$egresos->Diciembre,'Gastos' =>$gastos->Diciembre )
				)

		);

		return array( "success" => true, 
	    			  "message" => "Listas cargadas correctamente",
	    			  "data" => $anual_data);

	}



	function contar_ingresos_anual($idCliente){
		$ano=date("Y");

		$sql   = "SELECT 

			(select IFNULL(sum(Total),0) as Enero from Ingresos where extract(month FROM Fecha) = 01 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Enero,
			(select IFNULL(sum(Total),0) as Febrero from Ingresos where extract(month FROM Fecha) = 02 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Febrero,
			(select IFNULL(sum(Total),0) as Marzo from Ingresos where extract(month FROM Fecha) = 03 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Marzo,
			(select IFNULL(sum(Total),0) as Abril from Ingresos where extract(month FROM Fecha) = 04 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Abril,
			(select IFNULL(sum(Total),0) as Mayo from Ingresos where extract(month FROM Fecha) = 05 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Mayo,
			(select IFNULL(sum(Total),0) as Junio from Ingresos where extract(month FROM Fecha) = 06 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Junio,
			(select IFNULL(sum(Total),0) as Julio from Ingresos where extract(month FROM Fecha) = 07 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Julio,
			(select IFNULL(sum(Total),0) as Agosto from Ingresos where extract(month FROM Fecha) = 08 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Agosto,
			(select IFNULL(sum(Total),0) as Septiembre from Ingresos where extract(month FROM Fecha) = 09 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Septiembre,
			(select IFNULL(sum(Total),0) as Octubre from Ingresos where extract(month FROM Fecha) = 10 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Octubre,
			(select IFNULL(sum(Total),0) as Noviembre from Ingresos where extract(month FROM Fecha) = 11 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Noviembre,
			(select IFNULL(sum(Total),0) as Diciembre from Ingresos where extract(month FROM Fecha) = 12 and extract(year FROM Fecha) = $ano  and Status=1 and IdCliente = $idCliente) as Diciembre

			from Ingresos

			where year(Fecha)=$ano and Status=1

			group by Year(Fecha)

			limit 1";
		
		
		$query = $this->db->query($sql);
	    return $query->result();		
	}

	function contar_egresos_anual( $idCliente ){
		$ano=date("Y");

		$sql   = "SELECT 

			(select IFNULL(sum(Total),0) as Enero from Egresos where extract(month FROM Fecha) = 01 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Enero,
			(select IFNULL(sum(Total),0) as Febrero from Egresos where extract(month FROM Fecha) = 02 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Febrero,
			(select IFNULL(sum(Total),0) as Marzo from Egresos where extract(month FROM Fecha) = 03 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Marzo,
			(select IFNULL(sum(Total),0) as Abril from Egresos where extract(month FROM Fecha) = 04 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Abril,
			(select IFNULL(sum(Total),0) as Mayo from Egresos where extract(month FROM Fecha) = 05 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Mayo,
			(select IFNULL(sum(Total),0) as Junio from Egresos where extract(month FROM Fecha) = 06 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Junio,
			(select IFNULL(sum(Total),0) as Julio from Egresos where extract(month FROM Fecha) = 07 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Julio,
			(select IFNULL(sum(Total),0) as Agosto from Egresos where extract(month FROM Fecha) = 08 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Agosto,
			(select IFNULL(sum(Total),0) as Septiembre from Egresos where extract(month FROM Fecha) = 09 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Septiembre,
			(select IFNULL(sum(Total),0) as Octubre from Egresos where extract(month FROM Fecha) = 10 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Octubre,
			(select IFNULL(sum(Total),0) as Noviembre from Egresos where extract(month FROM Fecha) = 11 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Noviembre,
			(select IFNULL(sum(Total),0) as Diciembre from Egresos where extract(month FROM Fecha) = 12 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Diciembre

			from Egresos

			where year(Fecha)=$ano and Status=1

			group by Year(Fecha)

			limit 1";
		

		$query = $this->db->query($sql);
	    return $query->result();		
	}

	function contar_gastosfijos_anual( $idCliente ){
		$ano=date("Y");

		$sql   = "SELECT 

			(select IFNULL(sum(Total),0) as Enero from GastosFijos where extract(month FROM Fecha) = 01 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Enero,
			(select IFNULL(sum(Total),0) as Febrero from GastosFijos where extract(month FROM Fecha) = 02 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Febrero,
			(select IFNULL(sum(Total),0) as Marzo from GastosFijos where extract(month FROM Fecha) = 03 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Marzo,
			(select IFNULL(sum(Total),0) as Abril from GastosFijos where extract(month FROM Fecha) = 04 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Abril,
			(select IFNULL(sum(Total),0) as Mayo from GastosFijos where extract(month FROM Fecha) = 05 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Mayo,
			(select IFNULL(sum(Total),0) as Junio from GastosFijos where extract(month FROM Fecha) = 06 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Junio,
			(select IFNULL(sum(Total),0) as Julio from GastosFijos where extract(month FROM Fecha) = 07 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Julio,
			(select IFNULL(sum(Total),0) as Agosto from GastosFijos where extract(month FROM Fecha) = 08 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Agosto,
			(select IFNULL(sum(Total),0) as Septiembre from GastosFijos where extract(month FROM Fecha) = 09 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Septiembre,
			(select IFNULL(sum(Total),0) as Octubre from GastosFijos where extract(month FROM Fecha) = 10 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Octubre,
			(select IFNULL(sum(Total),0) as Noviembre from GastosFijos where extract(month FROM Fecha) = 11 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Noviembre,
			(select IFNULL(sum(Total),0) as Diciembre from GastosFijos where extract(month FROM Fecha) = 12 and extract(year FROM Fecha) = $ano  and Status=1 and IdProveedor = $idCliente) as Diciembre

			from GastosFijos

			where year(Fecha)=$ano and Status=1

			group by Year(Fecha)

			limit 1";
		

		$query = $this->db->query($sql);
	    return $query->result();		
	}

	public function ValidateDate($iddoctor, $idpaciente, $fecha, $inicio, $fin ) {

		$sql_doc = "SELECT * FROM Citas 
				where IdDoctor = $iddoctor 
				and Fecha = '$fecha' 
				and ( HoraInicio between '$inicio' and '$fin' or HoraFinal between '$inicio' and '$fin' )";

		$sql = "SELECT * FROM Citas 
				where IdCliente = $idpaciente 
				and Fecha = '$fecha' 
				and ( HoraInicio between '$inicio' and '$fin' or HoraFinal between '$inicio' and '$fin' )";

		$query_doc = $this->db->query( $sql_doc );

		if( $query_doc->num_rows() > 0 ) {
			return  array('success' => false , 'message' => 'El doctor ya tiene una cita asignada en ese horario' );
		}

		$query_cli = $this->db->query( $sql );

		if( $query_cli->num_rows() > 0 ) {
			return  array('success' => false , 'message' => 'El cliente ya tiene una cita asignada en ese horario' );
		}

		return  array('success' => true , 'message' => '' );
	}


	function obtenerHistorialPorPacienteDoctor($idDoctor , $idPaciente){

		$statement = "SELECT
				concat(cl.Nombre,' ',cl.ApellidoPaterno, ' ', cl.ApellidoMaterno ) as NombreCompleto,
				cl.Fotografia,
				cl.FechaNacimiento,
				cl.Sexo,
				cl.Telefono,
				cl.Correo,
				c.Fecha,
				c.HoraInicio,
				c.HoraFinal,
				c.Recomendaciones,
				c.Receta,
				c.Comentarios,
				c.`Status`,
				cv.Convenio,
				tc.Consulta
				from Citas c
				JOIN Clientes cl
				ON cl.IdCliente = c.IdCliente
				JOIN Convenios cv
				ON cv.IdConvenio = c.IdConvenio
				JOIN TiposConsulta tc 
				ON tc.IdConsulta = c.IdConsulta
				where c.IdCliente = $idPaciente 
				and c.IdDoctor = $idDoctor 
				order by c.Fecha Desc , c.HoraInicio Desc;";

		$resultset = $this->db->query($statement);

		return array( "success" => true, 
	    			  "message" => "Lista cargada correctamente",
	    			  "data" => $resultset->result() );

	}

	public function obtener_clientes()
    {
        $sql = "SELECT *, CONCAT(Nombre, ' ', ApellidoPaterno, ' ', ApellidoMaterno) AS NombreCompleto

				FROM Clientes

				where Status=1

				ORDER BY IdCliente";

        $query = $this->db->query($sql);

        $data = $query->result();		
        return array( "success" => true, "message" => "" , "data"=> $data);		
    }
	

}