<?php 

	session_start();
	
	$echec_connexion = false; //A ajouter dans un if à l'intérieur de la page de connexion, si cette variable est vraie alors afficher quelque chose
	echo md5('admin');
	require("config.php");
   
    if(isset($_SESSION['login'])) 
    	header("location:index.php");

	if(!empty($_POST['login']) && !empty($_POST['password'])){
		try{
			$linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
		}
		catch (Exception $e){
			die('Erreur : ' . $e->getMessage());
		}	

		$req = $linkpdo->prepare('select login,password from user WHERE login like :login ;');
	
		if($req->execute(array('login' => $_POST['login']))) {			
			$connexion = $req->fetch();
			

			if($connexion['password'] == md5($_POST['password'])) {
                $_SESSION['login']=$_POST['login'];
				header("location:index.php");
				
			} else{
				$echec_connexion = true;
			}
		}

		}
?>


<!DOCTYPE html>
<html>
<head>
	<title>Connexion</title>
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
	<div class="box-connexion">	
		<form method="post">
			<div class="connexion-info" id="identifiant"> 
				<label>Identifiant</label>
				<input type="input" name="login">
			</div>
			<div class="connexion-info" id="motdepasse"> 
				<label>Mot de passe</label>
				<input type="password" name="password">
			</div>
			<button type="submit" >Connexion</button>
			<a href="CreerCompte.php">Creer un compte</a>
		</form>
	</div>	
</body>
</html>



