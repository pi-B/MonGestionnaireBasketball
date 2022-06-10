<?php
	session_start();
	ob_start();

	function numPoste(){
		switch($_POST['nouveauPoste']){
			case 'Meneur':
				return 1;
			case 'Arriere':
				return 2;
			case 'Ailier':
				return 3;
			case 'Ailier Fort':
				return 4;
			case 'Pivot':
				return 5;
		}
	}
	
    if (empty($_SESSION['login'])) {
        header('location:connexion.php');
    }else{
        $id= session_id();
    }
	
	require('config.php');   
    require('connexionDB.php');
    require('fonctionsGlobales.php');

			
	$tmpName = $_FILES['photo']['tmp_name'];
	$name = $_FILES['photo']['name'];
	$size = $_FILES['photo']['size'];
	$error = $_FILES['photo']['error'];
	try {
		$linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
	}

	catch (Exception $e) {
		die('Erreur : ' . $e->getMessage());
	}
	$linkpdo->exec("SET NAMES 'utf8';");
	$req = $linkpdo->prepare("insert into joueur(prenom,nom,dateNaissance,taille,poids,poste,numPoste) values (:nouveauPrenom,:nouveauNom,:nouveauDate,:nouveauTaille,:nouveauPoids,:nouveauPoste,:nouveauNumPoste)");

	$req->execute(array(
		'nouveauPrenom' => $_POST['nouveauPrenom'],
		'nouveauNom' => $_POST['nouveauNom'],
		'nouveauDate' => $_POST['nouveauDate'],
		'nouveauTaille' => $_POST['nouveauTaille'],
		'nouveauPoids' => $_POST['nouveauPoids'],
		'nouveauPoste' => $_POST['nouveauPoste'],
		'nouveauNumPoste' => numPoste()
		)
	);


	
	$res = $linkpdo -> prepare("SELECT numeroLicence FROM joueur WHERE Nom = :Nom AND prenom = :Prenom AND dateNaissance = :nDate");
	$res-> execute(array(
		'Prenom' => $_POST['nouveauPrenom'],
		'Nom' => $_POST['nouveauNom'],
		'nDate' => $_POST['nouveauDate']
		));

		$data = $res->fetch();
		move_uploaded_file($tmpName, 'photos_joueurs/'.$data['numeroLicence'].".jpg");
		redirection("location:".$_SESSION['derniere_page']);
		
		exit();
	

    	    
?>