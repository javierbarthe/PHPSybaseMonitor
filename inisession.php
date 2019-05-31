<?php
	session_start(); //comienzo la session
	//obtengo los datos desde el formulario
	$usuario = $_POST["usuario"];
	$contrasenia = $_POST["contrasenia"];
	$motor = $_POST["motor"];

	$existecone=False;
	//Guardo los valores en mi session
	//session_start();
	#session_register('usuario');
	$_SESSION["usuario"] = $usuario ;
	#session_register('contrasenia');
	$_SESSION["contrasenia"] = $contrasenia;
	#session_register('motor');
	$_SESSION["motor"] = $motor;

	header("Location: dbmon.php");
?>
