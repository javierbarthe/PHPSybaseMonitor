<?php   
	$opcion="Login";
	session_start(); 
	session_destroy(); 
	include("encabezado.php");
?>

<div id="maincont">
<div id="main">
	<br><br>
	<h1> Motor de Base de Datos.</h1>
	<img alt="image" src="images/logon.jpg" />
	
	<form name="form1" id="form1" method="post" action="inisession.php">
          <table width="400" border="0">
            <tr>
              <td width="120">Seleccione</td>
              	<td width="280">
		<select name="motor" size="1" id="motor">
        	        <option>DB1</option>
              	</select>
		</td>
            </tr>
          </table>
          <br>
          <br>

	<h1>login.</h1>
	
	<table width="400" border="0">
            <tr>
              <td width="120">Usuario</td>
              <td width="280"><input name="usuario" type="text" id="usuario" size="30" maxlength="25" readonly="true"  value="sa" /></td>
            </tr>
            <tr>
              <td>Contrase&ntilde;a</td>
              <td><input name="contrasenia" type="password" id="contrasenia" size="30" maxlength="25" readonly="true" value="passw0rd" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td><input type="submit" name="LogOn" value=" Aceptar " /></td>
            </tr>
        </table>

	</form>
	
</div>
</div>

<br>
<br>


<script type="text/javascript">
    function myfunc () {
        var frm = document.getElementById("form1");
        frm.submit();
    }
    window.onload = myfunc;
</script>


<?php 
	include("pie.php");
?>
