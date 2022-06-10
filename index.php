<?php 

    session_start();

    ob_start();

    require('fonctionsGlobales.php');

    if (empty($_SESSION['login'])) {
        header('location:connexion.php');
    }else{
        $id= session_id();
    }

    $_SESSION['derniere_page'] = $_SERVER['PHP_SELF'];
   
   
    
    function afficherTableau(){
         require('config.php');
                    // Connexion au serveur MySQL
            try {
                $linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
            }
            catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }
            $linkpdo->exec("SET NAMES 'utf8';");
            $req = 'SELECT  * FROM joueur';

            $res = $linkpdo-> prepare("$req");
            $res-> execute();
            $result="<br/>
            <table class='tabJoueurs'>
            <tr>
                <th>Joueur</th>
                <th>Date de naissance</th>
                <th>taille</th>
                <th>Poids</th>
                <th>Poste</th>
                <th>Statut</th>
                ";
            if(administrateur()) $result .= "<th></th>";

            $result .= "</tr>";
            while ($data = $res->fetch()) {
                $url = "./pageJoueur.php?numeroLicence=".$data['numeroLicence']; // passage du numero de licence dans la variable Get pour afficher la liste du joueur selectionner
                $url2 = "supprimerJoueur.php?numeroLicence=".$data['numeroLicence']; // passage du numero de licence dans la variable Get suprimer le joueur
                $result.="<tr>
                        <th onmouseover=\"afficherPhotoJoueur(".$data['numeroLicence'].")\" onmouseout=\"enleverPhotoJoueur()\"><a href=\"$url\" >".$data['prenom']." ".$data['nom']."</a></th>
                        <td>".date("d/m/Y", strtotime($data['dateNaissance']))."</td>
                        <td>".$data['taille']."</td>
                        <td>".$data['poids']."</td>
                        <td>".$data['poste']."</td>
                        <td>".$data['statut']."</td>
                        
                        ";
                if(administrateur()) 
                    $result.= "<td><a href=\"$url2\" style= 'color: #6281BA'>Supprimer</td>";
                
                $result .= "</tr>";
             }
            
            $result.="</table>";

            echo $result;
    }

    
    function refresh(){

        header("refresh:0");
        ob_end_flush();
        exit();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Application gestion équipes</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="16x16" href="basketball.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src='JS/afficherPhotoJoueur.js' async></script>
</head>
<body>
    <div class="header">
        <input class="menu-btn" type="checkbox" id="menu-btn" />
        <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
        <ul class="menu">
            <li id="select" ><a href="index.php">Accueil</a></li>
            <li><a href="liste_match.php">Liste des matchs</a></li>
            <li><a href="stats.php">Statistiques</a></li>
            <li><?php deconnexion(); ?></li>
        </ul>
</div>
    <div class="photo_et_tableau" style="display: flex;flex-direction: row;">
        <div class="conteneur_photo" style="width: 20px;">
            <img  id="joueurSurvole" src="">
        </div>
        <?php
            afficherTableau();
        ?>
        <?php if ($_SESSION['login'] == 'admin'){
            echo "
           <div class=\"ajoutJoueur\">
                <form action=\"ajouterJoueur.php\" method=\"POST\" enctype=\"multipart/form-data\">
                    <div id=\"nomPrenom\">
                        <input type=\"texte\" name=\"nouveauNom\" placeholder=\"Nom\">
                        <input type=\"texte\" name=\"nouveauPrenom\" placeholder=\"Prenom\">
                    </div>
                    <div id=\"taillePoids\">
                        <input type=\"number\" name=\"nouveauTaille\" placeholder=\"Taille\">
                        <input type=\"number\" name=\"nouveauPoids\" placeholder=\"Poids\">
                    </div>
                    <div id=\"datePoste\">
                        <input type=\"date\" name=\"nouveauDate\" placeholder=\"Date de naissance\">
                        <select id=\"poste\" name=\"nouveauPoste\">
                            <option value=\"\">Poste</option>
                            <option value=\"Meneur\">Meneur</option>
                            <option value=\"Arriere\">Arriere</option>
                            <option value=\"Ailier\">Ailier</option>
                            <option value=\"Ailier Fort\">Ailier Fort</option>
                            <option value=\"Pivot\">Pivot</option>
                        </select>
                    </div>
                    <div id=\"photo\">
                        <label for=\"photo\">Photo :</label> 
                        <input type=\"file\" name=\"photo\" accept=\".jpg,.png,.gif\" >
                    </div>
                    <input type=\"submit\" style=\"margin-top:5%\" value=\"Enregistrer\"> 
                    
                </form>
            </div>";
            }
        ?>
    </div>

</body>
</html>
