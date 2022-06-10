<?php
    function deconnexion(){
        if(empty($_SESSION['login'])){
            echo '<a href="connexion.php"><button type="submit" name="connect">Connexion</button></a>';
        }else{
            echo "
            <form method=\"post\"><button type=\"submit\" name=\"disconnect\">Se déconnecter de ".$_SESSION['login']."</button></form>
            ";
            if(isset($_POST['disconnect'])) {
                session_destroy();
                header("location:index.php");
                ob_end_flush();
                exit();
            }
         }
    }

    function redirection($adresse){
        header("$adresse");
        ob_end_flush();
        exit();
      }

    function administrateur(){
        return $_SESSION['login'] == 'admin' ;
    }
?>