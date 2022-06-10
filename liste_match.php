<?php
    session_start();
    
    ob_start();
    require('fonctionsGlobales.php');
    require('connexionDB.php');
    
    //vérifier que les informations passés ne sont pas déjà dans rencontre
    function ajouter_match($idEquipe){
      if( $idEquipe != null && !empty($_POST['date_match']) && !empty($_POST['lieu_match']) && !empty($_POST['adversaire_match'] && !empty($_POST['heure_match']))) {
           echo "ajout";
            $date_req = date("Y-m-d",strtotime( $_POST['date_match']));
            $req_match = "INSERT INTO rencontre(dateMatch,lieuMatch,nomAdversaire,heure) values (:dateM ,:lieu,:adversaire,:heure)" ;
            $pdo_match = connexion();

        
            $res = $pdo_match->prepare($req_match);
                $res->execute(array(
                'dateM' => $date_req,
                'lieu' => $_POST['lieu_match'],
                'adversaire' => $_POST['adversaire_match'],
                'heure' => $_POST['heure_match']
            )
            );

            $req_jouer = 'INSERT INTO jouer(idRencontre,idEquipe) values ((select idRencontre from rencontre where dateMatch = :dateM and lieuMatch = :lieu and nomAdversaire = :adversaire and heure like :heure ),:idEquipe);'; //finir la requete qui ajoute le match dans jouer !!!!!!!!!!!!!!!!!!!!!
            $pdo_match = connexion();
            $res = $pdo_match->prepare($req_jouer);
            $res->execute(
                array(
                'dateM' => $date_req,
                'lieu' => $_POST['lieu_match'],
                'adversaire' => $_POST['adversaire_match'],
                'heure' => $_POST['heure_match']."%",
                'idEquipe' => $idEquipe
                )
            );
      }
    }

    function liste_deroulante(){
        $linkpdo = connexion();

        $options_select_equipe ="";
        $req= "SELECT idEquipe AS id, libelle AS equipe from equipe";
        $res = $linkpdo->prepare("$req");
        $res->execute();
        
        while($liste_equipes = $res->fetch()){
            $options_select_equipe .= "<option value=".$liste_equipes['id'].">".$liste_equipes['equipe']."</option>";
        }

        echo $options_select_equipe;
    }

    function equipe_selectionnee(){
        if(!isset($_COOKIE['derniere_equipe_vue'])){
            $equipeSelectionnee = 1;
           
        } else{
            if(isset($_POST['equipe']) && !empty($_POST['equipe']))
                 $equipeSelectionnee = $_POST['equipe'];
             else
                $equipeSelectionnee = $_COOKIE['derniere_equipe_vue'];
        } 
        setcookie("derniere_equipe_vue", $equipeSelectionnee, 0);
        return $equipeSelectionnee;
    }

    function premiere_equipe(){
         $linkpdo = connexion();
         $id_equipe = equipe_selectionnee();
        
        $req= "SELECT libelle AS equipe from equipe where idEquipe = $id_equipe";
        $res = $linkpdo->prepare("$req");
        $res->execute();
        
        $data = $res->fetch();
        return array('idEquipe' => $id_equipe, 'nomEquipe' => $data['equipe']);
    }


    $equipe_actuelle = equipe_selectionnee();
    ajouter_match($equipe_actuelle);
    
    //echo $equipe_actuelle;

    $linkpdo = connexion();

    // Récupération de toutes les équipes du club et insertion dans un select HTML

    
    $_SESSION['derniere_page'] = $_SERVER['PHP_SELF'];
    


    // Récupération des matchs qui correspondent à l'équipe sélectionnée
    $linkpdo = connexion();

    $req = "
    SELECT dateMatch as 'Date', lieuMatch as Lieu, nomAdversaire as Adversaire, DATE_FORMAT(heure, \"%H:%i\") as heure, CONCAT(jouer.scoreEquipe,' - ',jouer.scoreAdversaire) as Score, rencontre.idRencontre from rencontre, jouer where rencontre.idRencontre = jouer.idRencontre and idEquipe = $equipe_actuelle
    order by dateMatch 
    "; 

    $res = $linkpdo-> prepare("$req");
    $res-> execute();
   
     $tableau_matchs="<br/>
    <table class='tab' style='margin-top:70px'>
    <tr>
        <th>Date</th>
        <th>Lieu</th>
        <th>Adversaire</th>
        <th>Heure</th>
        <th>Score</th>
        <th>      </th>
    </tr>";


    while($data = $res->fetch()){
        $tableau_matchs.="<tr>
            <th>".date("d/m/Y", strtotime($data['Date']))."</th>
            <td>".$data['Lieu']."</td>
            <td>".$data['Adversaire']."</td>
            <td>".$data['heure']."</td>
            <td>".$data['Score']."</td>
            <td>  <a style='color:#6281ba; font-size:15px;text-decoration: none;' href=\"./joueurs_match.php?idrencontre=".$data['idRencontre']."&adversaire=".$data['Adversaire']."\"> + </a></td>
        </tr>";
        }

    $tableau_matchs.="</table>";

premiere_equipe();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Application gestion équipes : Matchs </title>
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
            <li id="select"><a href="liste_match.php">Liste des matchs</a></li>
            <li ><a href="stats.php">Statistiques</a></li>
            <li><?php deconnexion(); ?></li>
        </ul>
        <li id = "scores"><form method="post" >
            <select name="equipe">
                <option value="<?php $val = premiere_equipe(); echo $val['idEquipe']; ?>"><?php $val = premiere_equipe(); echo $val['nomEquipe'] ?></option> <!-- Besoin de JS pour afficher la valeur de la derniere équipe selectionnée -->
                <?php liste_deroulante() ?>
            </select>
            <input type="submit" name="" value="Afficher">
        </form></li>
    </div>

    <div class="generalTab">
    <?php
        echo $tableau_matchs;
    ?>
    <form id="newMatch" method="post">
        <div>
            <input type='date' name='date_match' placeholder='Date'>
            <input type ='text' name='lieu_match' placeholder='Lieu'>
            <input type = 'text' name='adversaire_match' placeholder='Adversaire'>
            <input type = 'text' name='score_match' placeholder="Domicile-Locaux">
            <input type = 'time' name='heure_match' placeholder="">
            <input type = 'submit' value = 'Ajouter'>
            <p class="message_obligatoire"> Date, lieu, adversaire et heure obligatoires</p>
        </div>
    </form>
    </div>
</body>
</html>
