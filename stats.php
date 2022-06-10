<?php 
    
    session_start(); 

    require('connexionDB.php');
    require('config.php');
    require('fonctionsGlobales.php');

    $linkpdo=connexion();
    ob_start();

    if (empty($_SESSION['login'])) {
        header('location:connexion.php');
    }else{
        $id= session_id();
    }

    $_SESSION['derniere_page'] = $_SERVER['PHP_SELF'];
    
    
    function getAVGNote($numLicence, $req){
        $linkpdo=connexion();
        $res2 = $linkpdo-> prepare("$req");
        $res2-> execute(array('numLicence'=>$numLicence));
        $avgNotes= $res2-> fetch();
        return round($avgNotes['avg'], 1);
    }

    function getNbSelectTit($numLicence, $req){
        $linkpdo=connexion();
        $res = $linkpdo-> prepare("$req");
        $res-> execute(array('numLicence'=>$numLicence));
        $NbSelectTit= $res-> fetch();
        return $NbSelectTit['nbTitulaire'];
    }

    function getNbSelectRemp($numLicence, $req){
        $linkpdo=connexion();
        $res = $linkpdo-> prepare("$req");
        $res-> execute(array('numLicence'=>$numLicence));
        $NbRemp= $res-> fetch();
        return $NbRemp['nbNonTitulaire'];
    }

    function getNbMatchs($req){
        $linkpdo=connexion();
        $res = $linkpdo-> prepare("$req");
        $res-> execute();
        $NbRemp= $res-> fetch();
        return $NbRemp[0];
    }

    function getNbWin($req){
        $linkpdo=connexion();
        $res = $linkpdo-> prepare("$req");
        $res-> execute();
        $NbRemp= $res-> fetch();
        return $NbRemp['nbWin'];
    }

    function getPourcentWinJoueur($numLicence,$req){
        $linkpdo=connexion();
        $res = $linkpdo-> prepare("$req");
        $res-> execute(array('numLicence'=>$numLicence));
        $NbPourcent= $res-> fetch();
        return $NbPourcent['pourcent'];
    }

    function getStatut($numLicence, $req){
        $linkpdo=connexion();
        $res = $linkpdo-> prepare("$req");
        $res-> execute(array('numLicence'=>$numLicence));
        $NbSelectTit= $res-> fetch();
        return $NbSelectTit['statut'];
    }
   
 
    function refresh(){

        header("refresh:0");
        ob_end_flush();
        exit();
    }

    if(!isset($_POST['equipe'])){
        $equipeSelectionnee = 1; // Choix de l'équipe senior 1 par défault, on peut améliorer en utilisant une requete qui va chercher l'id de l'équipe qui correspond à 'Senior 1' 
    } else{
        $equipeSelectionnee = $_POST['equipe'];
    }

    $req= "SELECT idEquipe AS id, libelle AS equipe from equipe";
    $res = $linkpdo->prepare("$req");
    $res->execute();
    
    $options_select_equipe ="";
    while($liste_equipes = $res->fetch()){
        $options_select_equipe .= "<option value=".$liste_equipes['id'].">".$liste_equipes['equipe']."</option>";
    }

    echo $equipeSelectionnee;

    // Récupération des matchs qui correspondent à l'équipe sélectionnée
    $req = "SELECT nom, prenom, poste, joueur.numeroLicence as numeroLicence from joueur, appartenir where joueur.numeroLicence = appartenir.numeroLicence and idEquipe = $equipeSelectionnee ORDER BY numeroLicence"; 
    $req2= "SELECT AVG(noteMatch) as avg from participer where numeroLicence = :numLicence and idRencontre in (select idRencontre from jouer where idEquipe = $equipeSelectionnee)";
    $req3="SELECT count(idRencontre) as nbTitulaire from participer where numeroLicence = :numLicence and titulaire = 1 and idRencontre in ( select idRencontre from jouer where idEquipe = $equipeSelectionnee )";
    $req4="SELECT count(idRencontre) as nbNonTitulaire from participer where numeroLicence = :numLicence and titulaire =0 and idRencontre in ( select idRencontre from jouer where idEquipe = $equipeSelectionnee) ";
    $req5="SELECT COUNT(idRencontre) as nbMatchs FROM jouer WHERE IdEquipe=$equipeSelectionnee"; 
    $req6="SELECT COUNT(idRencontre) as nbWin FROM jouer WHERE IdEquipe=$equipeSelectionnee and scoreEquipe > scoreAdversaire;";
    $req7="SELECT round(((nbVictoires/nbSelections)*100),2) as pourcent from (SELECT count(idRencontre) as nbSelections from participer where numeroLicence = :numLicence and idRencontre in 
    ( select idRencontre from jouer where idEquipe = $equipeSelectionnee )) as matchs, (select count(numeroLicence) as nbVictoires from participer where numeroLicence = :numLicence and 
    participer.idRencontre in ( select idRencontre from jouer where idEquipe = $equipeSelectionnee and scoreEquipe > scoreAdversaire )) as vic;";
    $reqStatus="SELECT statut from joueur where numeroLicence=:numLicence";
    $res = $linkpdo-> prepare("$req");
    $res-> execute();

     
    $selecMatch = $linkpdo-> prepare("$req");
    $selecMatch-> execute();
    
     $tabJoueurs="<br/>
    <table class='tab'>
    <tr>
        <th>Joueur</th>
        <th>Poste</th>
        <th>Statut</th>
        <th>Nombre de selection titulaire</th>
        <th>Nombre de selection remplaçant</th>
        <th>Evaluation moyenne</th>
        <th>% matchs gagnés</th>
    </tr>";
 
 
    while($data = $res->fetch()){
        $statut=getStatut($data['numeroLicence'], $reqStatus);
        $noteAVG=getAVGNote($data['numeroLicence'], $req2);
        $nbSelectTit=getNbSelectTit($data['numeroLicence'],$req3);
        $nbRemp=getNbSelectRemp($data['numeroLicence'], $req4);
        $pourcentWin=getPourcentWinJoueur($data['numeroLicence'],$req7);
        if($pourcentWin<0.01)$pourcentWin=0;
        $tabJoueurs.="<tr>
            <th>".$data['prenom']." ".$data['nom']."</th>
            <td>".$data['poste']."</td>
            <td>$statut</td>
            <td>$nbSelectTit</td>
            <td>$nbRemp</td>
            <td>$noteAVG</td>
            <td>$pourcentWin %</td>
        </tr>";
        }
 
    $tabJoueurs.="</table>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Application gestion équipes : Statistiques</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="16x16" href="basketball.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="header">
        
        <input class="menu-btn" type="checkbox" id="menu-btn" />
        <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
        <ul class="menu">
            <li  ><a href="index.php">Accueil</a></li>
            <li><a href="liste_match.php">Liste des matchs</a></li>
            <li id="select"><a href="stats.php">Statistiques</a></li>
            <li><?php deconnexion(); ?></li>
        </ul>
        <li id = "scores"><form method="post">
            <select name="equipe" id="selectEquipe">
                <option value="">Equipe</option> <!-- Besoin de JS pour afficher la valeur de la derniere équipe selectionnée -->
                <?php echo"$options_select_equipe"; ?>
            </select>
            <input type="submit" name="" value="Afficher">
        </form></li>
    </div>

    <div id="nomEtNum" style="margin-top:70px">
    <span class="nomJoueur">Equipe <?php echo $equipeSelectionnee ?> </span>
    <?php
        $nbMatchsEquipe=getNbMatchs($req5);
        $tauxWin=round((getNbMatchs($req6)/$nbMatchsEquipe)*100);
    ?>
        <p id="numLicence"><strong>nombre de matchs : </strong><?php echo $nbMatchsEquipe ?> <strong>taux de victoire : </strong><?php echo $tauxWin?> %</p>
    </div>

    <div class="generalTab">
        <?php
            echo $tabJoueurs;
        ?>
    </div>
</body>
</html>