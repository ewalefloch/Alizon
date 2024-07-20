<!DOCTYPE html>
<?php
    include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);
    date_default_timezone_set("Europe/Paris");
    if(!isset($_SESSION["idvendeur"])){
        header('Location: ./connexionVendeur.php');    
    }
    function verifPrixRemise($pourcent_remise, $prix_article,$prix_coutant){
        $prixremise = ($pourcent_remise/100) * $prix_article ;
        $prix_article_remise=$prix_article-$prixremise;
        if ($prix_article_remise < $prix_coutant) {
            $erreur_prix = 1;
        }
        else {
            $erreur_prix = 2;
        }
        return $erreur_prix;
    }
            if(isset($_POST["deremise"])){
                $nbremise=$dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$_POST[deremise]")->rowCount();
                if($nbremise==1){
                    $nb=$dbh->query("UPDATE {$prefix}_article set enremise = false where idarticle= $_POST[deremise] ");
                }
                $dbh->query("DELETE FROM {$prefix}_remise WHERE idarticle=$_POST[deremise] AND date_debut='$_POST[datedebut]' ");
            }

            function dateValide($dateDebut,$dateFin){ // fonction pour vérfier si la remise est valide en fonction des dates
                date_default_timezone_set('Europe/Paris');
                if($dateFin>=date('Y-m-d')){
                    return true;
                }else{
                    return false;
                }
            }
            ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta charset="utf-8" />
    <title>ALIZON</title>
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/headerpro.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    <link rel="stylesheet" type="text/css" href="style/styleEnRemise.css" />
</head>
<body>
    <header> 
          <?php include "entetePro.php"?>  
    </header>
    <main>
    <?php 
   
    ?>
        <?php
        
        $liste=$dbh->prepare("SELECT a.nom from {$prefix}_article a inner join {$prefix}_vendeur v on a.idvendeur=v.idvendeur where a.idvendeur=$_SESSION[idvendeur]  order by nom ASC");
        $liste->execute();

    // Formulaire pour mise en remise
    echo'<form id="formuProm" action="" method="POST"> 
        <h2>Remise des articles</h2>';
        if(isset($_POST["remise"])){
            echo'
            <p class="ligne_form"><label class="text">Début de la remise </label><input type="date" name="date_debut" value="'.$_POST["date_debut"].'" /></br></p>
            <p class="ligne_form"><label class="text">Fin de la remise </label><input type="date" name="date_fin" value="'.$_POST["date_fin"].'"/></br></p>
            <p class="ligne_form"><label>Pourcentage de la remise </label><input type="number" step="0.01" placeholder="20, 0,63" name="remise" value="'.$_POST["remise"].'" /></br></p>
            ';
        }else{
            echo'
            <p class="ligne_form"><label class="text">Début de la remise </label><input type="date" name="date_debut"  /></br></p>
            <p class="ligne_form"><label class="text">Fin de la remise </label><input type="date" name="date_fin" /></br></p>
            <p class="ligne_form"><label>Pourcentage de la remise </label><input type="number" step="0.01" placeholder="2,3" name="remise" /></br></p>
            ';
        }
        echo '
        <img src="" >
        <div>
        <label>Liste des articles </label>
        <select class="select" name="liste_article">
            <option value="texte">- Sélectionner un article -</option>;
        ';

        // Sélection de tous les articles de la base de données
        foreach($liste as $row){
            echo'<option value="'.$row["nom"].'">'.$row["nom"].'</option>';
        }
        echo '</select> </div>';
        
        echo'
        <input type ="submit" onClick=afficherBloc() class = "bouton" name="remettre2" value ="Créer la remise">
    </form>';

    // Insertion des articles en remises après vérification
    $sth=$dbh->prepare("INSERT INTO {$prefix}_remise(idarticle,date_debut,date_fin,remise) VALUES(?,?,?,?);");
    if (isset($_POST["liste_article"]) && $_POST["liste_article"]!="texte"){
        $idarticleRow=$dbh->query("select idarticle from {$prefix}_article where nom='$_POST[liste_article]';");
    }
        if(!empty($_POST['date_debut']) && !empty($_POST['date_fin']) && !empty($_POST['remise']) && $_POST["liste_article"]!="texte"){
            $date_debut = $_POST['date_debut'];
            $date_fin = $_POST['date_fin'];
            $idarticle=$idarticleRow->fetch();
            $pcout=$dbh->query("SELECT prixcoutant from {$prefix}_article where idarticle = $idarticle[idarticle];")->fetch();
            $particle=$dbh->query("SELECT prixttc from {$prefix}_article where idarticle = $idarticle[idarticle];")->fetch();
            $articleDeja=$dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$idarticle[idarticle];");
            $rowcount=$articleDeja->rowCount();
            $pcout=$pcout["prixcoutant"];
            $particle=$particle["prixttc"];

            // Vérification si la date est cohérente et si le prix en remise n'est pas inférieur au prix coutant
            if($date_fin > $date_debut && $date_debut >= date("Y-m-d") && verifPrixRemise($_POST["remise"],$particle,$pcout)==2) {
                $good=true;
                if($rowcount!=0){
                    foreach($articleDeja as $row){
                        if(($date_debut<$row["date_debut"]&&$date_fin<$row["date_debut"])||($date_debut>$row["date_fin"]&&$date_fin>$row["date_fin"])){
                            
                        }else{
                            $good=false;
                        }
                    }
                    if($good){
                        $sth->execute(array($idarticle["idarticle"],$_POST["date_debut"],$_POST["date_fin"],$_POST["remise"]));
                        $dbh->query("UPDATE {$prefix}_article SET enremise=true where idarticle=$idarticle[idarticle];");
                    }else{
                        echo'<p class="erreur">Attention les dates correpsondent à une remise déjà existante</p>';
                    }

                }else{
                $sth->execute(array($idarticle["idarticle"],$_POST["date_debut"],$_POST["date_fin"],$_POST["remise"]));
                $dbh->query("UPDATE {$prefix}_article SET enremise=true where idarticle=$idarticle[idarticle];");
                }
            } else if ($date_fin < $date_debut){
                echo'<p class="erreur">Attention la date de début se trouve après la date de fin</p>';
            } else if($date_debut < date("Y-m-d")){
                echo'<p class="erreur">Attention la date de début est déjà passée</p>';
            } else if (verifPrixRemise($_POST["remise"],$particle,$pcout)==1){
                echo'<p class="erreur">Attention la réduction est trop importante</p>';
            }
        } else if(isset($_POST['remise'])){
            echo'<p class="erreur">Veuillez remplir tous les champs</p>';
        }

        // Affichage des articles en remise
        $statement = $dbh->query("SELECT * from {$prefix}_article a inner join {$prefix}_image i  on a.idarticle = i.idarticle inner join {$prefix}_remise r on a.idarticle = r.idarticle ORDER BY a.nom ASC", PDO::FETCH_ASSOC);
        echo '<div class="objet">';
        foreach ($statement as $row){
            if (dateValide($row["date_debut"],$row["date_fin"])){
            echo' <section class="itemPromu">
            
            <figure>';
            if(file_exists("./".$row["urlimage"])){
                echo'<img src='.$row["urlimage"].' alt="mug"> ';
            }else{
                echo'<img src="images/imgArticle/notFound.png" alt="notFound">';
            } 
            echo '
            </figure>
                <article class="article1">
                <form action="article.php" method="POST">
                <input type="hidden" name="detailArticle" value='.$row["idarticle"].'>
                <input type="submit" class="titre" name="nom" value='.$row["nom"].'>               
            </form>
                    <p> Prix : '.$row["prixttc"].'€</p>
                    <p> Prix après remise : '.number_format($row["prixpromo"],2).'€</p>
                    <p> Début remise : '.$row["date_debut"].'</p>
                    <p> Fin remise : '.$row["date_fin"].'</p>
                </article>';
                echo'<form id= "formu" action="" method = "POST">
                  
                <input type ="hidden" name="deremise" value ='.$row["idarticle"].' >
                <input type ="hidden" name="datedebut" value ='.$row["date_debut"].' >
                <input type ="hidden" name="datefin" value ='.$row["date_fin"].' >
                <input type ="submit" class = "bout" name="deremise1" value ="Enlever la remise">
              </form>';
            
            }
            echo"
            </section>";
        }
        echo '</div>';
    ?>
</main>
<footer>
    <?php
    include("piedpageReduit.php");
    ?>
</footer>

</body>
</html>

