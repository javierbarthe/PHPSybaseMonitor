<?php 
 
	
	$SPID=$_GET["SPID"];
	session_start();
	
	if(isset($_SESSION['usuario'], $_SESSION['contrasenia'], $_SESSION['motor']))
	{
		$usuario = $_SESSION["usuario"];
		$contrasenia = $_SESSION["contrasenia"];
		$motor = $_SESSION["motor"];
		
	}
	else{
		header("Location: login.php");
	}	
	
	$opcion="Procesos";
	include("encabezado.php");

print ("<br><strong>Process: ".$SPID."</strong><br><br>");

// CONECCION
$connection2 = odbc_connect($motor,$usuario,$contrasenia);


echo "---------------------------------------------------------------------------------------------------------------------------------<br>";
echo "&nbsp;&nbsp;&nbsp;SQLTEXT DEL SPID SOLICITADO<br>";
echo "---------------------------------------------------------------------------------------------------------------------------------<br>";

$sql1 = "select SQLText from master..monProcessSQLText where SPID = ".$SPID; 

// EJECUTO SQL QUERY Y OBTENGO LOS RESULTADOS 
$odbc_result1 = odbc_exec($connection2,$sql1);

if (odbc_num_rows($odbc_result1) > 0 )
{
echo "<TABLE BORDER=0 align='left' width='100%'>";
$a = 0;
while ($row = odbc_fetch_array($odbc_result1)) 
    {
		   $a++;
		   echo "<TR>";
		   echo "<TD>&nbsp;&nbsp;&nbsp;".$row["SQLText"]."&nbsp;</TD>";
		   echo "</TR>";
	  } 
echo "</TABLE>";
}


// LIBERO RECURSOS Y CIERRO LA CONECCION 
odbc_free_result($odbc_result1); 
odbc_close($connection2); 

?>
