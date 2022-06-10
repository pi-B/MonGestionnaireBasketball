<?php
	session_start();
	
	ob_start();
    
    function redirection($adresse){
        header("$adresse");
        ob_end_flush();
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body><?php
     	require('config.php');

	 	try {
	        $linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
        }
        catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
		$linkpdo->exec("SET NAMES 'utf8';");
        $req = $linkpdo->prepare("UPDATE joueur SET commentaire = :commentaire, statut = :statut WHERE joueur.numeroLicence=".$_SESSION['licenceJoueurActuel']);
        $req->execute(array(
			'commentaire' => $_POST['com'],
        	'statut' => $_POST['statut']
        ));
        
    	   	redirection("location:".$_SESSION['derniere_page']);
    	    
?>


	<?php var_dump($_POST)?>
</body>
</html>