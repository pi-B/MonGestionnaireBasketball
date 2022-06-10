<?php 
    session_start();
    require('connexionDB.php');
    var_dump($_POST);
    var_dump($_GET);

    if(isset($_POST['ajout'])){

        $req_ajout_joueur = connexion();
        $req = "INSERT INTO participer(idRencontre,numeroLicence) values (".$_GET['idrencontre'].",".$_POST['ajout'].")";

        $res = $req_ajout_joueur->prepare($req);
        var_dump($res);
        if( $res->execute() ){
            header('location:/projetweb/joueurs_match.php?'.$_SERVER['QUERY_STRING']);
        } 
    }

?>