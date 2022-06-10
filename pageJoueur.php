<?php session_start();
   ob_start();
    
    require('config.php');
    require('fonctionsGlobales.php'); 
    

    try {
        $linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
    }
    catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
    $_SESSION['derniere_page'] = $_SERVER['PHP_SELF']."?numeroLicence=".$_GET['numeroLicence'];

    $req = "SELECT * FROM joueur where numeroLicence like ".$_GET['numeroLicence'];
   
    $res = $linkpdo-> prepare("$req");
    $res-> execute();
    $data = $res->fetch();  
    $statuts = array('Actif', 'Suspendu', 'Absent','Blesse');
    if (($key = array_search($data['statut'], $statuts)) !== false) {
        unset($statuts[$key]);
    }
    $_SESSION['licenceJoueurActuel']= $_GET['numeroLicence'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Application gestion équipes : <?php echo $data['prenom']." ".$data['nom'] ?></title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="16x16" href="basketball.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
<header class="header">
        
        <input class="menu-btn" type="checkbox" id="menu-btn" />
        <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
        <ul class="menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="liste_match.php">Liste des matchs</a></li>
            <li><a href="stats.php">Statistiques</a></li>
            <li> <?php deconnexion(); ?></li>
        </ul>
</header>

    <div class="photoEtInfos">
        <img class="photoJoueur" alt="" src="./photos_joueurs/<?php echo $data['numeroLicence']?>.jpg"/>
        <div class="info">
        <p class="infos"><strong>Poste&nbsp: </strong><?php echo $data['poste']?></p>
        <div style="display: flex;flex-direction: row;">
            <p class="infos"><strong>Taille&nbsp: </strong><?php echo $data['taille']?>&nbspcm</p>
            <p class="infos"><strong>Poids&nbsp: </strong><?php echo $data['poids']?>&nbspkg</p>
        </div>
        <p id="dateNaissance"><strong>Date de naissance : </strong><?php echo date("d/m/Y", strtotime($data['dateNaissance']))?></p>
        <div style="display: flex;flex-direction: row;">
        <p class="infos">
            <strong>Status : </strong> <?php if(!administrateur()) echo $data['statut'] ?> </p>
           
                <?php 
                if(administrateur()){
                    $statut = "
                    <form method=\"post\" action=\"updateJoueur.php\">
                        <select name=\"statut\" id=\"statut\">
                            <option value=\"".$data['statut'].">".$data['statut']."</option>";
                            

                            foreach($statuts as $val){
                                $statut .= "<option value=\"".$val."\   ">$val</option>";
                            }
                    $statut .= "
                        </select>
                    </form>";

                    echo $statut;
                }

                ?>

            </div> 
        </div>
    </div>
            <div id="nomEtNum"><span class="nomJoueur"><?php echo $data['prenom']." ".$data['nom'] ?> </span><p id="numLicence"><strong> Licence&nbsp:&nbsp</strong><?php echo $data['numeroLicence']?></p></div>
            <div style="display: flex;flex-direction: row; justify-content: space-between;" ><textarea name="com" id="commentaire" maxlength="8000" placeholder="Commentaire"><?php echo $data['commentaire']?></textarea>
           <?php if(administrateur()) echo "<input class=\"enregister\" type=\"image\" src=\"save.png\">"; ?> </div>
        </form>

</body>
</html>