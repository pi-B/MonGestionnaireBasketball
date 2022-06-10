<?php
    session_start();
    ob_start();
    require('connexionDB.php');
    require('fonctionsGlobales.php');

    $linkpdo = connexion();
    


    
    if(isset($_GET) && !empty($_GET)){
        var_dump($_GET);
        $req=$linkpdo->prepare("DELETE from joueur where numeroLicence=:numLicence");
        $req->execute(array('numLicence' => $_GET['numeroLicence'],));
        unlink("photos_joueurs/".$_GET['numeroLicence'].".jpg");
        redirection('location:index.php');
    }
    
?>