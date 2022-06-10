<?php

    ob_start();
    session_start();

    $revenir_page = $_SERVER['QUERY_STRING'];    

    require('connexionDB.php');
    require('fonctionsGlobales.php');

    if (empty($_SESSION['login'])) {
        header('location:connexion.php');
    }else{
        $id= session_id();
    }

    function dropdown($note_actuelle){
    // créer une liste dropdown qui contient les notes de 0 à 10 
    // avec la note actuelle en premier et qui ne se répéte pas 
        $dropdown = "<option value=".$note_actuelle.">".$note_actuelle."</option>";
        for($i = 1 ; $i <= 10; $i++){
            if($i != $note_actuelle){
                $dropdown .= "<option value=".$i.">".$i."</option>";
            }
        }

        return $dropdown;
    }

    function checkTitulaire($titu){
        if($titu){
            return "checked";
        }
    }

    function checkNbJoueurs($cpt){
        $err="";

        if(isset($_POST['verification'])){
            if($cpt < 5){
                $err='<script type="text/javascript"> erreurSelection() </script>';
                echo "c'est moi";
            }
        }
        return $err;
    }

    function checkErreurs(){
        $err = "";
        if(isset($_POST['verification'])){

            $cpt = 0;
            $tab_cles = array_keys($_POST);
                        
            for ($i = 0; $i < sizeof($tab_cles); $i++){
                if(substr($tab_cles[$i], 0,9) == "titulaire"){
                    $cpt++;
                }
            }

            if($cpt > 5)
                $err='<script type="text/javascript"> erreurSelection() </script>';
        }

        return $err;
    }

    function supprimerJoueurMatch(){
        if(isset($_POST) && !empty($_POST)){
            $suppresion_joueur = connexion();
            $req= "SELECT joueur.numeroLicence as licence  from joueur, participer WHERE joueur.numeroLicence = participer.numeroLicence and participer.idRencontre =".$_GET['idrencontre']." order by joueur.numeroLicence" ;

            $res = $suppresion_joueur->prepare($req);
            $res->execute();

            while($data = $res->fetch()){
                if(array_key_exists(('supprimer_'.$data['licence']), $_POST)){
                    $res_suppression = $suppresion_joueur->prepare("DELETE FROM participer where numeroLicence = ".$data['licence']." and idRencontre =".$_GET['idrencontre']);
                    $res_suppression->execute();
                }   
            }
        }
    }

    function listeJoueurAjouter(){
        $rencontre = $_GET['idrencontre'];
        $req_liste_joueur = connexion();
        $req = (" SELECT nom, joueur.numeroLicence as licence, joueur.poste FROM joueur,appartenir, jouer WHERE jouer.idRencontre = $rencontre and appartenir.idEquipe = jouer.idEquipe and joueur.numeroLicence = appartenir.numeroLicence and joueur.numeroLicence not in ( SELECT joueur.numeroLicence from joueur, participer where joueur.numeroLicence = participer.numeroLicence and participer.idRencontre = $rencontre) order by numPoste;");
        $res = $req_liste_joueur->prepare($req);
        $res->execute();

        $liste_joueurs ="";
        while($data = $res->fetch()){
            $liste_joueurs .= "<option value=".$data['licence'].">".$data['nom']." - ".$data['poste']."</option>";
        }

        return $liste_joueurs;
    }

    function majTitulaires($maj_titulaire,$licence){
        $err = checkErreurs(5);
        if(array_key_exists('titulaire_'.$licence, $_POST)){
            if($_POST['titulaire_'.$licence] == 'on'){
                $_POST['titulaire_'.$licence] = 1;
            }
            $res_titu = $maj_titulaire->prepare("UPDATE participer set titulaire =".$_POST['titulaire_'.$licence]." where numeroLicence = $licence and idRencontre =".$_GET['idrencontre']);    
            $res_titu->execute();
        } 
        else {
            $res_titu = $maj_titulaire->prepare("UPDATE participer set titulaire = 0 where numeroLicence = $licence and idRencontre =".$_GET['idrencontre']);    
            $res_titu->execute();
        }
    }

    function getEquipes(){
        $linkpdo = connexion();
        $req_equipes = "SELECT libelle, nomAdversaire as adversaire FROM rencontre,equipe where idRencontre =  ".$_GET['idrencontre']." group by idRencontre";
        $res = $linkpdo->prepare($req_equipes);
        $res->execute();

        $data = $res->fetch();
        return $data;
    }

    function majScore(){
        if(isset($_POST['score_equipe']) && isset($_POST['score_adversaire'])){
            $linkpdo = connexion();
            $req_scores = "UPDATE jouer set scoreEquipe = :scoreEquipe, scoreAdversaire = :socreAdv where idRencontre = :idRen";

           $res_scores = $linkpdo->prepare($req_scores);
           $res_scores->execute(
            array('scoreEquipe' =>$_POST['score_equipe'],
           'socreAdv' => $_POST['score_adversaire'],
           'idRen' => $_GET['idrencontre'])
           ); 
        }
    }

    function getScores(){
        $linkpdo = connexion();
        $req_scores = "SELECT scoreEquipe as sEquipe, scoreAdversaire as sAdversaire FROM jouer where idRencontre =  ".$_GET['idrencontre'];
        $res = $linkpdo->prepare($req_scores);
        $res->execute();

        $data = $res->fetch();
        return $data;
    }

    function montrerScore(){
        $scoreDuMatch = getScores();
        if ($scoreDuMatch['sEquipe'] != 0 || $scoreDuMatch['sAdversaire'] != 0){
           $valueScore = array(
                'equipe' => "value = ".$scoreDuMatch['sEquipe'],
                'adversaire' => "value = ".$scoreDuMatch['sAdversaire']
            );
           return $valueScore;
        } else {
            $placeholderScore = array(
                'equipe' => "placeholder = 'Score Equipe'",
                'adversaire' => "placeholder = 'Score Adversaires'"
            );
            return $placeholderScore;
        }
    }



    // On enregistre les notes dans des inputs dont le name contient le numero de licence du joueur
    // on récupère tous les numéros de licence associés à une rencontre et on met à jour la note du joueur
    // en réupérant dans post note_match$NumeroLicence pour chaque NumeroLicence renvoyé par la premiere requete
    
    majScore();

    if(isset($_POST['verification'])){

        supprimerJoueurMatch();

        $maj_notes = connexion();
        $maj_titulaire = connexion();

        $req= "SELECT joueur.numeroLicence as licence  from joueur, participer WHERE joueur.numeroLicence = participer.numeroLicence and participer.idRencontre =".$_GET['idrencontre']." order by joueur.numeroLicence" ;
      
        $res = $maj_notes->prepare($req);
        $res->execute();
        
        while($data = $res->fetch()){
            $licence = $data['licence'];

            if(array_key_exists('note_match_'.$licence, $_POST)){
                $res_notes = $maj_notes->prepare("update participer set noteMatch =".$_POST['note_match_'.$licence]." where numeroLicence = $licence and idRencontre =".$_GET['idrencontre']);            
                $res_notes->execute();    
            }

            if(checkErreurs() == "")
                majTitulaires($maj_titulaire,$licence);
        }


    } 
    

    $compteur_minimum_cinq = 0;
    $linkpdo = connexion();
    $req = "
    SELECT joueur.numeroLicence ,CONCAT(joueur.prenom,\" \",joueur.nom) as joueur, titulaire , noteMatch as note , round((taille/100),2) as taille, poste, commentaire
    from joueur, participer 
    WHERE joueur.numeroLicence = participer.numeroLicence
    and participer.idRencontre =".$_GET['idrencontre']." order by joueur.numeroLicence";

    $res = $linkpdo->prepare($req);
    $res->execute();

    $tableau_matchs = "
    <table class='tab'>
    <tr>
        <th>Joueur</th>
        <th>Poste</th>
        <th>Taille</th>
        <th class='com'>Commentaire</th>
        <th>Titulaire</th>
        <th>Note Match</th>";
    if(administrateur())
        $tableau_matchs .= "<th>Retirer</th>";
    $tableau_matchs .= "</tr>";

    // Chaque ligne du tableau contient un select dont la premiere valeur est la note actuelle 
    // chaque select est identifié par le nom "note_match_" concaténé avec le numero de licence du joueur
    // ainsi dans le POST on aura des clés de valeurs 'note_match_$numeroLicence'
    // on peut envoyer le formulaire est mettre à jour chaque note d'une ligne associé à un numéro de 
    // licence en récupérant la case de POST associé

    while($data = $res->fetch()){
       /* $tableau_matchs .= "<tr>
            <th> ".$data['joueur']."</th>
            <td>". signeTitulaire($data['titulaire'])."</td>
            <td> 
                <select name='note_match_".$data['numeroLicence']."'> 
                    ".dropdown($data['note'])."
                </select>
            </td>
        </tr>";
        */
        $tableau_matchs .= "<tr>
        <th> ".$data['joueur']."</th>
        <td>".$data['poste']."</td>
        <td>".$data['taille']."m</td>
        <td class='comd'>".$data['commentaire']."</td>
        <td><input type = \"checkbox\" name = \"titulaire_".$data['numeroLicence']."\" ".checkTitulaire($data['titulaire'])."></td>";
        if (administrateur()){
           $tableau_matchs .= "<td> 
                <select name='note_match_".$data['numeroLicence']."'> 
                    ".dropdown($data['note'])."
                </select>
            </td>";
        } else {
            $tableau_matchs .= "<td>".$data['note']."</td>";
        }
        if(administrateur())
            $tableau_matchs .= "<td><input type = \"checkbox\" name = \"supprimer_".$data['numeroLicence']."\"></td>";
        
        $tableau_matchs .= "</tr>";

        $compteur_minimum_cinq++;

        }

    $tableau_matchs .= "</table>";


    $erreurs = checkErreurs(); 
    $pas_assez_joueurs = checkNbJoueurs($compteur_minimum_cinq);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title> Match contre <?php $adversaire = getEquipes(); echo $adversaire['adversaire'] ?> </title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="16x16" href="./basketball.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src='JS/messageErreur.js' async></script>

</head>
<body>

<div class="header">
        
        <input class="menu-btn" type="checkbox" id="menu-btn" />
        <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
        <ul class="menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="liste_match.php">Liste des matchs</a></li>
            <li><a href="stats.php">Statistiques</a></li>
            <li><?php deconnexion(); ?></li>
        </ul>
        <li id = "scores"><form method = "post">
            <?php $adversaire = getEquipes(); echo $adversaire['libelle'] ?>
                <input type="text" name="score_equipe" <?php $valueOuPlaceholder = montrerScore(); echo $valueOuPlaceholder['equipe'] ?>>
                <input type="text" name="score_adversaire" <?php $valueOuPlaceholder = montrerScore(); echo $valueOuPlaceholder['adversaire'] ?>>
                <?php $adversaire = getEquipes(); echo $adversaire['adversaire'] ?> 
                <?php if (administrateur()) echo "<input type=\"submit\" value=\"+\">" ; ?>
        </form> 
        <?php if (administrateur()) echo "
        <div id=\"ajout_joueur\">
            <form method =\"post\" action=\"ajouterJoueurMatch.php?".$revenir_page."\"  id=\"bouton_ajout\">
                <select name=\"ajout\">".
                 listeJoueurAjouter()."
                </select>
                <input type=\"submit\" name=\"\" value = \"+\"> <!-- Ajouter la fonction qui ajoute le joueur au tableau -->
            </form> 
        </div>
        "; ?>
    </li>
</div>

    <div class="generalTab">
        <form method="post" id="tableau_joueurs_match">
            <?php
                echo $tableau_matchs;
            ?>
            <input type="hidden" name="verification" value="maj_tableau">
           <?php if(administrateur())
                echo "<input class=\"envoyer\" type=\"image\" src=\"save.png\">";
            ?>
        </form> 
    </div>
    <div id="erreur_titulaires" onclick="fermerErreurSelection()">
        <h1> ERREUR DE SAISIE</h1>
        <p> Votre séléction comporte actuellement plus de cinq titulaires ou moins de cinq joueurs</p>
        <p>Cliquez sur cette fenetre pour la fermer</p>
    </div>
    <?php echo $erreurs; echo $pas_assez_joueurs; ?>
</body>
</html>
