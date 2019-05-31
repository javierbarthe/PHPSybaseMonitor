<?php
	session_start();
	if(isset($_SESSION['usuario'], $_SESSION['contrasenia'], $_SESSION['motor']))
	{
		$usuario = $_SESSION["usuario"];
		$contrasenia = $_SESSION["contrasenia"];
		$motor = $_SESSION["motor"];
	}
	else 
	{
		header("Location: login.php");
	}	
	
	$opcion="Procesos";
	include("encabezado.php");

	$seg =15;
	$mod ="ACTIVOS";
	$mod2 = "NINGUNO";
	$ord = "SPID";


?>

<div id="maincont">
<div id="main">

<table width="100%" border="1" bgcolor="#E9E9E9">
	  <tr valign="top">
		<td align="center"><strong>  <?	echo 'Monitor';?> </strong></td>
        <td align="center"><strong>  <?	echo 'DB Monitor';?> </strong></td>
		<td align="center"> <?echo 'Process Refress 15 seconds';?> </td>
    </tr>
</table>
<hr>

<?php


if($seg != 0){echo "<meta http-equiv='refresh' content= '$seg'; url='dbmon.php'>";}


// CONECCION
$connection = odbc_connect($motor,$usuario,$contrasenia);

// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------
// nodo1 *********************************************************************************************************
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

if (!$connection) 
{ 
	echo "<br><br><br><br><br><br>";
	echo "<TABLE BORDER=0>";
	echo "<TR bgcolor = #FFCCCC>";
	echo "<TD align='left'>";
		echo "<strong><br>No hubo conexion a ".$motor."!<br>";
		echo "Reintente<br></strong>";
	echo "</TD></TR></TABLE>";
	echo "<br><br><br><br><br><br><br><br><br>";
	
	echo "</div></div>";
	include("pie.php");
	exit;
}

// Traigo todos los procesos activos

$sql = "select 
                mp.SPID,
                suser_name(mp.ServerUserID) as usuario,
                mp.Command as cmd, 
                               case when mp.Command = 'LOAD DATABASE' or mp.Command = 'ONLINE DATABASE'
                               then '' 
                               else object_name(mps.ProcedureID,mp.DBID)
                               end as sp,
                mp.LineNumber as linea,            
                mwei.WaitEventID as wait_event, 
                mp.SecondsWaiting,
                mp.DBName as db,
                tempdb = db_name(tempdb_id(mp.SPID)),
                mp.HostName,
                mp.Application as program_name,                                                                                                                             
                mp.BlockingSPID as blockedSPID, 
                case when isnull(mp.BlockingSPID,0) = 0
                               then null
                     else mp.SecondsWaiting
                     end as Time_blocked,                              
                mps.CpuTime as cpu,
                mps.PhysicalReads as P_Reads_s,
                mps.LogicalReads as L_Reads_s,
                mpa.PhysicalReads as P_Reads_p,
                mpa.LogicalReads as L_Reads_p,
                mps.MemUsageKB as memusage,
                mps.RowsAffected,
                mps.StartTime,
                mpl.ClientIP as ipaddr    
                from master..monProcessStatement mps
                left join master..monProcess mp
                on mp.SPID = mps.SPID
                left join master..monProcessActivity mpa
                on        mpa.SPID = mp.SPID
                and mpa.KPID = mp.KPID
                inner join master..monProcessLookup mpl
                on mp.SPID = mpl.SPID
                inner join master..monWaitEventInfo mwei
                on mp.WaitEventID = mwei.WaitEventID
		        order by SPID"; 

// Traigo todos los procesos lockeadores a la cabecera
$sqllock = "select 
                              mp.SPID,
                suser_name(mp.ServerUserID) as usuario,
                mp.Command as cmd, 
                               case when mp.Command = 'LOAD DATABASE' or mp.Command = 'ONLINE DATABASE'
                               then '' 
                               else object_name(mps.ProcedureID,mp.DBID)
                               end as sp,
                mp.LineNumber as linea,            
                mwei.WaitEventID as wait_event, 
                mp.SecondsWaiting,
                mp.DBName as db,
                tempdb = db_name(tempdb_id(mp.SPID)),
                mp.HostName,
                mp.Application as program_name,                                                                                                                             
                mp.BlockingSPID as blockedSPID, 
                case when isnull(mp.BlockingSPID,0) = 0
                               then null
                     else mp.SecondsWaiting
                     end as Time_blocked,                              
                mps.CpuTime as cpu,
                mps.PhysicalReads as P_Reads_s,
                mps.LogicalReads as L_Reads_s,
                mpa.PhysicalReads as P_Reads_p,
                mpa.LogicalReads as L_Reads_p,
                mps.MemUsageKB as memusage,
                mps.RowsAffected,
                mps.StartTime,
                mpl.ClientIP as ipaddr       
               from master..monProcessStatement mps
                left join master..monProcess mp
                on mp.SPID = mps.SPID
                left join master..monProcessActivity mpa
                on        mpa.SPID = mp.SPID
                and mpa.KPID = mp.KPID

                inner join master..monProcessLookup mpl
                on mp.SPID = mpl.SPID
                                               
                inner join master..monWaitEventInfo mwei
                on mp.WaitEventID = mwei.WaitEventID
                
				right join (select
				bk_process.BlockingSPID,
				max(bk_process.SecondsWaiting) Time_blocked
				from master..monProcess bk_process
				where bk_process.BlockingSPID is not null
				group by bk_process.BlockingSPID
				) bk_process
				on bk_process.BlockingSPID = mp.SPID
					
				order by mp.SecondsWaiting desc";

//traigo todos los lockeados para mostrar cuantos hay en la cabecera
$sqllocked = "SELECT count(SPID) as cant
                from master..monProcess
               where BlockingSPID > 0";

//---------------------------------------------------------------------------------------------
// EJECUTO SQL QUERY Y OBTENGO LOS RESULTADOS
$sql_result = odbc_exec($connection,$sql);
$sql_result2 = odbc_exec($connection,$sqllocked);


//si hay lockeos ejecuto la consulta
  while ($row = odbc_fetch_array($sql_result2)) 
    {
    	$rowF = $row["cant"];
		}
if ($rowF > 0)
   {
    $sql_resultl = odbc_exec($connection,$sqllock);
   }

date_default_timezone_set('America/Araguaina');

//---------------------------------------------------------------------------------------------
echo "<TABLE BORDER=1 align='center' width='100%'>";
	echo "<TR bgcolor = #F2F2F2>";
	echo "<TD align='center'>" ;
	echo strftime("%A %d de %B del %Y  -  ");
  echo date("H:i:s"); 
  echo "</TD>";
  
  echo "<TD align='center'> Instance: ".$motor; 
  echo "</TD>";
  
  echo "<TD align='center'>"; 
	echo "&nbsp;".odbc_num_rows($sql_result)." Proc Active </font></TD>";
  
  if (odbc_num_rows($sql_result) > 0 )
    {
  	   echo "<TD align='center'>"; 
		   echo "&nbsp;".odbc_num_rows($sql_resultl)." Proc Locking </TD>";
		   echo "<TD><div style='margin: 0 auto; width: 12px'><img src='images/BOLITA.gif' style='width: 12x' /></TD>"; 
		}

	if (odbc_num_rows($sql_result2) > 0 )
    {
  	   echo "<TD align='center'>"; 
		   echo "&nbsp;".$rowF." Proc Locked </font></TD>";
		}
 	echo "</TR>";
echo "</TABLE>";

//---------------------------------------------------------------------------------------------
//Muestro los lockeadores por encima de los procesos activos y lockeados
if (odbc_num_rows($sql_result) > 0 )
{
echo "<TABLE BORDER=1 align='center' width='100%'>";
	echo "<TR bgcolor = 'silver'>";
	echo "<TD align='center'><strong>SPID</strong></TD>";
	echo "<TD align='center'><strong>Usuario</strong></TD>";
	echo "<TD align='center'><strong>Comando</strong></TD>";
	echo "<TD align='center'><strong>Sp</strong></TD>";
	echo "<TD align='center'><strong>Linea</strong></TD>";
	echo "<TD align='center'><strong>WaitID</strong></TD>";
	echo "<TD align='center'><strong>SecW</strong></TD>";	
	echo "<TD align='center'><strong>Dbname</strong></TD>";
	echo "<TD align='center'><strong>Tempdb</strong></TD>";
	echo "<TD align='center'><strong>HostName</strong></TD>";	
	echo "<TD align='center'><strong>Program</strong></TD>";
	echo "<TD align='center'><strong>Blocked</strong></TD>";
	echo "<TD align='center'><strong>Bk Time</strong></TD>";
	echo "<TD align='center'><strong>Cpu</strong></TD>";
	echo "<TD align='center'><strong>PReads</strong></TD>";
	echo "<TD align='center'><strong>LReads</strong></TD>";
	//echo "<TD align='center'><strong>PReads_P</strong></TD>";
	//echo "<TD align='center'><strong>LReads_P</strong></TD>";
	echo "<TD align='center'><strong>Mem</strong></TD>";
	echo "<TD align='center'><strong>Rows</strong></TD>";
	echo "<TD align='center'><strong>StartTime</strong></TD>";		
	echo "<TD align='center'><strong>Ip</strong></TD>";
  echo "</TR>";

			$color1 = "#FBE1E1";
			$color2 = "#FBE1E1";
      $a = 0;

while ($row = odbc_fetch_array($sql_resultl)) 
    {
	     $row_color = ($a % 2) ? $color1 : $color2;
		   $a++;
	
		echo "<TR>";
		echo "<TD align='center' bgcolor = $row_color><a href='proceso_info.php?SPID=".$row["SPID"]."' target='_blank'>".$row["SPID"]."</a></TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["usuario"].	"</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["cmd"].	"</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["sp"]."</TD>";		
		echo "<TD align='left' bgcolor = $row_color>".$row["linea"].	"</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["wait_event"].	"</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["SecondsWaiting"].	"</TD>";		
		echo "<TD align='left' bgcolor = $row_color>".$row["db"].	"</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["tempdb"].	"</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["HostName"]."</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["program_name"]."</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["blockedSPID"]."</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["Time_blocked"]."</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["cpu"].	"</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["P_Reads_s"]."</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["L_Reads_s"]."</TD>";
		//echo "<TD align='center' bgcolor = $row_color>".$row["P_Reads_p"]."</TD>";
		//echo "<TD align='center' bgcolor = $row_color>".$row["L_Reads_p"]."</TD>";		
		echo "<TD align='center' bgcolor = $row_color>".$row["memusage"].	"</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["RowsAffected"].	"</TD>";
		echo "<TD align='center' bgcolor = $row_color>".$row["StartTime"].	"</TD>";		
		echo "<TD align='left' bgcolor = $row_color>".$row["ipaddr"]."</TD>";
		echo "</TR>";
	} 
echo "</TABLE>";
}

//---------------------------------------------------------------------------------------------
//Muestro los procesos activos y/o loqueados

echo "<TABLE BORDER=1 align='center' width='100%'>";
	echo "<TR bgcolor = 'silver'>";
	echo "<TD align='center'><strong>SPID</strong></TD>";
	echo "<TD align='center'><strong>Usuario</strong></TD>";
	echo "<TD align='center'><strong>Comando</strong></TD>";
	echo "<TD align='center'><strong>Sp</strong></TD>";
	echo "<TD align='center'><strong>Linea</strong></TD>";
	echo "<TD align='center'><strong>WaitID</strong></TD>";
	echo "<TD align='center'><strong>SecW</strong></TD>";	
	echo "<TD align='center'><strong>Dbname</strong></TD>";
	echo "<TD align='center'><strong>Tempdb</strong></TD>";
	echo "<TD align='center'><strong>HostName</strong></TD>";	
	echo "<TD align='center'><strong>Program</strong></TD>";
	echo "<TD align='center'><strong>Blocked</strong></TD>";
	echo "<TD align='center'><strong>Bk Time</strong></TD>";
	echo "<TD align='center'><strong>Cpu</strong></TD>";
	echo "<TD align='center'><strong>PReads</strong></TD>";
	echo "<TD align='center'><strong>LReads</strong></TD>";
	//echo "<TD align='center'><strong>PReads_P</strong></TD>";
	//echo "<TD align='center'><strong>LReads_P</strong></TD>";
	echo "<TD align='center'><strong>Mem</strong></TD>";
	echo "<TD align='center'><strong>Rows</strong></TD>";
	echo "<TD align='center'><strong>StartTime</strong></TD>";		
	echo "<TD align='center'><strong>Ip</strong></TD>";
	echo "</TR>";
	
	
			$color1 = "#FBFBFB";
			$color2 = "#E9F7E5";
			
			$color1 = "#F8F8F8";
			$color2 = "#eef6eb";
			
			$color1 = "#E9E9E9";
			$color2 = "#F6F6F6";			
			
	$a = 0;

	while ($row = odbc_fetch_array($sql_result)) {
	    $row_color = ($a % 2) ? $color1 : $color2;
		$a++;


			$color5 = "#ed6d6d";
			$color6 = "#ed6d6d";		
			$color7 = "#db788e";		
		
  if ($row["L_Reads_s"] > 150000) 
  {
	    $row_color_lec =  $color5 ;
  }
  if ( ($row["L_Reads_s"] > 7000 ) and ($row["L_Reads_s"] <= 150000 ) )
  {
	    $row_color_lec =  $color6 ;
  }
  if ( ($row["L_Reads_s"] >= 5000 ) and ($row["L_Reads_s"] <= 7000 ) )
  {
	    $row_color_lec =  $color7 ;
  }
	
		echo "<TR>";
		echo "<TD align='center' bgcolor = $row_color><a href='proceso_info.php?SPID=".$row["SPID"]."' target='_blank'>".$row["SPID"]."</a></TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["usuario"].	"</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["cmd"].	"</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["sp"]."</TD>";
		echo "<TD 	align='center'	bgcolor = $row_color>".$row["linea"].	"</TD>";
		echo "<TD 	align='center'	bgcolor = $row_color>".$row["wait_event"].	"</TD>";
		echo "<TD 	align='center'	bgcolor = $row_color>".$row["SecondsWaiting"]. "</TD>";		
		echo "<TD 	align='left'	bgcolor = $row_color>".$row["db"].	"</TD>";
		echo "<TD 	align='left'	bgcolor = $row_color>".$row["tempdb"].	"</TD>";
		echo "<TD 	align='left'	bgcolor = $row_color>".$row["HostName"]."</TD>";
		echo "<TD align='left' bgcolor = $row_color>".$row["program_name"].	"</TD>";
		if ($row["Time_blocked"]>0) 
		{
			$color_bloqueo ="#E3E4FA";
			if ($row["Time_blocked"]<15)  { $color_bloqueo ="#F6CECE"; } 
			if ($row["Time_blocked"]>=15)  { $color_bloqueo ="#F78181"; }
			if ($row["Time_blocked"]>=30)  { $color_bloqueo ="#FA5858"; }
			if ($row["Time_blocked"]>=60) { $color_bloqueo ="#FE2E2E"; }
			if ($row["Time_blocked"]>=90) { $color_bloqueo ="#FF0000"; }
			if ($row["Time_blocked"]>=120) { $coLor_bloqueo ="#B40404"; }

			if ($row["Time_blocked"]>=30) {
				echo "<TD align='center' bgcolor = ".$color_bloqueo."><strong><font color='#FFFFFF'>".$row["blockedSPID"]."</font></strong></TD>";
				echo "<TD align='center' bgcolor = '".$color_bloqueo."'><strong><font color='#FFFFFF'>".$row["Time_blocked"]."</font></strong></TD>";
			} else {
				echo "<TD align='center' bgcolor = ".$color_bloqueo."><strong>".$row["blockedSPID"]."</strong></TD>";
				echo "<TD align='center' bgcolor = '".$color_bloqueo."'><strong>".$row["Time_blocked"]."</strong></TD>";
			}
		} else {
			echo "<TD align='center' bgcolor = $row_color>".$row["blockedSPID"]."</TD>";
			echo "<TD align='center' bgcolor = $row_color>".$row["Time_blocked"]."</TD>";
		}
		
		
		echo "<TD		align='center' bgcolor = $row_color>".$row["cpu"].	"</TD>";

		if (  ($row["P_Reads_s"]>'2000')  )
		{ 
		$color_incidentes ="#DF512E";	
		echo "<TD align='center' bgcolor = ".$color_incidentes."><font color='#FFFFFF'>".$row["P_Reads_s"]."</font></TD>";
		} 
		else 
		{	
		echo "<TD align='center' bgcolor = $row_color>".$row["P_Reads_s"].	"</TD>";
		}



		if 	(  ($row["L_Reads_s"]<'5000')  )
			
			{
				$row_color_lec = $row_color;
			}
		
		if 	(  ($row["L_Reads_s"]>'500000')  )
		{ 
		$color_incidentes ="#DF512E";	
		echo "<TD align='center' bgcolor = ".$color_incidentes."><font color='#FFFFFF'>".number_format($row["L_Reads_s"],0,",",".")."</font></TD>";
		} 
		else 
		{	
		echo "<TD align='center' bgcolor = $row_color_lec>".number_format($row["L_Reads_s"],0,",",".")."</TD>";
		}
	
	
		//echo "<TD align='center' bgcolor = $row_color>".$row["P_Reads_p"].	"</TD>";
	
		//echo "<TD align='center' bgcolor = $row_color>".$row["L_Reads_p"].	"</TD>";
	
		echo "<TD align='center' bgcolor = $row_color>".$row["memusage"].	"</TD>";

		echo "<TD align='center' bgcolor = $row_color>".$row["RowsAffected"].	"</TD>";
		
		echo "<TD align='left' bgcolor = $row_color>".$row["StartTime"]."</TD>";		
		
		echo "<TD align='left'	bgcolor = $row_color>".$row["ipaddr"]."</TD>";			
				
		//}
		echo "</TR>";
	} 
echo "</TABLE>";

echo "<hr>";

//---------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------

// LIBERO RECURSOS Y CIERRO LA CONECCION
      	
if (odbc_num_rows($sql_result) > 0 )
   {
     odbc_free_result($sql_result);
   }
	if (odbc_num_rows($sql_result) > 0 )
    {
    	odbc_free_result($sql_result);
		}
	if (odbc_num_rows($sql_result2) > 0 )
    {
      odbc_free_result($sql_result2);
    } 
odbc_close($connection);
?>
</div>
</div>
<?php 
	include("pie.php");
?>
