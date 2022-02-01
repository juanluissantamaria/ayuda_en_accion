<?php
    include_once('conexion.php');
    $configuracion=mysqli_query($conexion,"select * from Configuracion where Status=1 order by rand() limit 1");
    $registros_configuracion = mysqli_num_rows($configuracion);
?>

<?php
    while($configuracion_lista=mysqli_fetch_assoc($configuracion)) {
?>

    <?php $correo_envio = $configuracion_lista['Correo'];?>

<?php
    }
?>

<?php
	$nombre=$_POST['nombre'];
	$correo=$_POST['correo'];
	$telefono=$_POST['telefono'];
	$asunto="Formulario WEB";
	$mensaje=$_POST['mensaje'];
	$fecha=date("Y-m-d H:i:s");

    $resultado = mysqli_query($conexion,"INSERT INTO  Contactos (Correo, Telefono, Nombre, Asunto, Mensaje, Fecha, Status) VALUES ('".$correo."', '".$telefono."', '".$nombre."', '".$asunto."', '".$mensaje."', '".$fecha."', 1)");

	//Estoy recibiendo el formulario, compongo el cuerpo
	$cuerpo = "<h1>Mensaje de www.xtremegym.com.mx</h1>";
		
	$cuerpo .= "<br/>";
	$cuerpo .= "<p>Nombre: " . $nombre ;
	$cuerpo .= "<p>Correo: " . $correo . "</p>";
	$cuerpo .= "<p>Telefono: " . $telefono . "</p>";
	$cuerpo .= "<p>-------------------------------------------------------------------------------</p>";

	$cuerpo = $cuerpo.nl2br($mensaje);

	//mando el correo...
	$success = mail($correo_envio,"xtremegym.com.mx",$cuerpo,"MIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\nFrom: Mensaje de: www.xtremegym.com.mx");
	
	if (!$success) {
		echo "FALSO";
    } else {
		echo "VERDADERO";
	}
?>