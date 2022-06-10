<?php session_start();
require('config.php');
ob_start();
function redirection($adresse){
    header("$adresse");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Creer un compte</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="16x16" href="basketball.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header>
        
    </header>

    <form method="post">
            Login <br/>
            <input type="input" name="login" placeholder="login"> <br/>
            Mot de passe 
            <br/><input type="password" name="mdp" placeholder="mdp"><br/>
            confirmer Mot de passe
            <br/><input type="password" name="mdpConfirm" placeholder="mdp"><br/>

            <?php if(isset($_POST['mdp'])&& isset($_POST['mdpConfirm'])){
                if(strlen($_POST['login'])<5) echo 'Login non conforme </br>';
                elseif(strlen($_POST['mdp'])<8) echo 'Mot de passe non conforme</br>'; 
                else{
                    if($_POST['mdp']==$_POST['mdpConfirm']){
                        try{
                            $linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
                        }
                        catch (Exception $e){
                            die('Erreur : ' . $e->getMessage());
                        }	
                
                        $req = $linkpdo->prepare('INSERT INTO user (login,password) VALUES (:identifiant,:motdepasse)');
                        if($req->execute(array('identifiant' => $_POST['login'],'motdepasse' => md5($_POST['mdp'])))){
                            redirection("location:connexion.php");
                            exit();
                        }else{
                            echo "Login deja pris </br>";
                        }

                        
                    }else{?>
                        <h3>Mots de passes different</h3>
    <?php           }
                }
            }
            ?>
            <button type="submit" name="valider">valider</button>
            <a href="connexion.php">Se connecter</a>
    </form>



    <footer>
    </footer>
</body>
</html>