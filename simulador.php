     <?php

   require_once('sesion.php');
   require_once('funciones.php');
   require_once('lib/pdfcrowd.php');
   if (!isset($_SESSION['login_user'])){
        	header('Location: index.php');
        	exit();
   }
   if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
	$tablas;
	$submit=0;
	$smmlv=0;
	$minSubs=0;
	$maxSubs=0;
	$tasaInt=0;
	$tasaSub=0;
	$edad=0;
	$ingFam=0;
	$tasaSeg=0;
	$plazo=0;
	$valviv=0;
	$valpres = 0;
	$vlrsinsub=0;
	$vlrconsub=0;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="css/topnav.css" media="screen" />
<link rel="stylesheet" href="css/skel-noscript.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<title>Simulador Hipotecario - UPTC</title>
</head>
<body onload="limpiar();">

      <!-- Header -->
        <div id="header">
          <div class="container">
         
            <!-- Logo -->
            <div id="logo">
              <h1><a><img src="images/logo_miniatura.jpg"/> COOPERATIVA FINANCIERA CENTRO ANDINA</a><h2>Ayudamos a tu progreso</h2></h1>

              <br/>
              <h1 align="left">Simulador Hipotecario - Generar Simulacion Hipotecaria</h1> 
            </div>
          </div>
        </div>
      <div class="topnav" id="menu">
        <ul>
        <li><a href="menuppal.php">Regresar al Menú Principal</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
      </div>
      <form name="loginform" action="" method="post">
      <table width="600" border="0" align="center" cellpadding="2" cellspacing="5" >
	  <tr border="0">
	  <td><div width="430" align="left">Ingrese el valor de la vivienda a adquirir *</div></td>
	  <td><input name="ValorVivienda" id="ValorVivienda" size="0" maxlength="10"></td>
      </tr>
      <tr border="0">
	  <td><div width="430" align="left">Ingrese el valor del credito a solicitar</div></td>
	  <td><input name="ValorCredito" id="ValorCredito" size="0" maxlength="10"></td>
	  </tr>
	  <tr border="0">
	  <td><div width="430" align="left">Seleccione el número de cuotas del crédito en meses</div></td>
	  <td><select name="lista" id="lista">
	  <?php
	  $funcion = new funcion();
	   $funcion->llenarLista();
	  ?>
	  </select>
      </td>
	  </tr>
	  <tr border="0"><td colspan="2"><input id="Generar" name="Generar" type="submit" value="Generar"/></td>
	  </tr>
	  </table>

      <?php
	   
	    $sesion= new sesion();
	    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Generar'])) {
	    	 $result = $sesion->getParametros();
		    foreach($result as $row){
		    	if(strcmp($row["nombre"],'SMMLV')==0){
		    		$smmlv=$row["valor"];
		    	}elseif(strcmp($row["nombre"],'MinSMMLVSubsidio')==0){
		    		$minSubs=$row["valor"];
		    	}elseif(strcmp($row["nombre"],'MaxSMMLVsubsidio')==0){
		    		$maxSubs=$row["valor"];
		    	}elseif(strcmp($row["nombre"],'TasaInteresCredito')==0){
		    		$tasaInt=$row["valor"];
		    	}elseif(strcmp($row["nombre"],'TasaSubsidio')==0){
		    		$tasaSub=$row["valor"];
		    	}
		    }
		    $edad= $sesion->getEdadUsuario($_SESSION['user_id']);
		    $ingFam= $sesion->getIngresosUsuario($_SESSION['user_id']);
		    $tasaSeg = $sesion->getTasaPorEdad($edad);
	        $evento=0;
	        if(!empty($_POST['ValorVivienda'])){
	        	$valviv = $_POST['ValorVivienda'];
	        }else{
	        	$evento=1;
	        	$sesion->phpAlert('Por favor digite el valor del inmueble!');
	        	$URL='simulador.php';
	        	echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
				echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
	        }
	         if(!empty($_POST['ValorCredito'])){
	        	$valpres = $_POST['ValorCredito'];
	        }else{
	        	$evento=1;
	        	$sesion->phpAlert('Por favor digite el valor del prestamo a solicitar!');
                 $URL='simulador.php';
	        	echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
				echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';

	        }
	        if(isset($_POST['lista'])){
	        	$plazo=	$_POST['lista'];
	        }else{
	        	$evento=1;
	        	$sesion->phpAlert('Por favor seleccione el plazo correspondiente!');
	        	$URL='simulador.php';
	        	echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
				echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';

	        }
	        if($valpres > $valviv){
	        	$evento=1;
	        	$sesion->phpAlert('El valor del prestamo no puede ser mayor al del inmueble!');
	        	$URL='simulador.php';
	        	echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
				echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';

	        }
	        if ($evento==0){
		     	$funcion=new funcion();
		     	$tablas=$funcion->calcularSimulacion($valviv, $valpres, $plazo, $smmlv, $minSubs, $maxSubs, $tasaInt, $edad, $tasaSub, $tasaSeg, $ingFam);
		     	//echo $tablas;
		     	echo '<hr>';
		     	echo '<h2>Resultado de la simulación</h2><p></p>';
		     	echo $tablas;
		     	$vlrsinsub=$funcion->calcularTotalSinSubsidio($valviv, $valpres, $plazo, $smmlv, $minSubs, $maxSubs, $tasaInt, $edad, $tasaSub, $tasaSeg, $ingFam);
		     	$vlrconsub=$funcion->calcularTotalConSubsidio($valviv, $valpres, $plazo, $smmlv, $minSubs, $maxSubs, $tasaInt, $edad, $tasaSub, $tasaSeg, $ingFam);
		     	echo'<p><h2>El valor total estimado a pagar con subsidio es: $'.$vlrconsub.'</h2></p>';
		     	echo'<p><h2>El valor total estimado a pagar sin subsidio es: $'.$vlrsinsub.'</h2></p>';
		     	$submit=1;
		     	$funcion2=new funcion();
		     	$nomArch = $funcion2->exportarPDF($edad,$ingFam,$valviv,$valpres,$plazo,$tasaInt,$tasaSub,$tasaSeg,$tablas,$vlrconsub,$vlrsinsub,$tablas);
		     	$funcion2->saveAs($nomArch);
		     	$sesion = new sesion();
		     	$idultsim=$sesion->getIdUltSimulacion($_SESSION['user_id'] );
		     	$narch=substr($nomArch,4,strlen($nomArch));
		     	$sesion->actualizarArchivoSim($narch,$idultsim);
		     }
	 } 
	?>

</form>
</body>
</html>