<?php
    include('connect_params.php');
    session_start();
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);

    if(isset($_POST['idvendeur'])){
        $_SESSION['voiridvendeur'] = $_POST['idvendeur'];
    }
    else if(!isset($_SESSION['voiridvendeur'])){
        //erreur
        header('Location: listerComptes.php');
    }
    if(isset($_GET['spage'])){
        $_SESSION['sPage'] = $_GET['spage'] * 10;
    }

    function afficheArticle($idarticle){ //fonction pour afficher un article 
        global $dbh, $prefix;
        $article = $dbh->query("SELECT * FROM {$prefix}_article LEFT OUTER JOIN {$prefix}_remise ON {$prefix}_article.idarticle={$prefix}_remise.idarticle where {$prefix}_article.idarticle=$idarticle")->fetch();

        $sth = $dbh->prepare("SELECT urlimage FROM _image where idarticle=?");
        $sth->execute(array($idarticle));
        $image = $sth->fetch(PDO::FETCH_ASSOC);
        // {$prefix}_image natural joins
        echo '<div class="article">';
            echo '<form action="article.php" method="POST">';
                echo '<input type="hidden" name="detailArticle" value="'.$idarticle.'">';
            if($image["urlimage"]){
                echo '<input type="image" src="'.$image["urlimage"].'" width=150px height=150px name="detailArticle" value="'.$article["idarticle"].'">';
            }else{
                echo '<input type="image" src="images/imgArticle/notFound.png" width=150px height=150px name="detailArticle" value="'.$article["idarticle"].'">';
            }
            echo '</form>';

            if(isset($article['remise'])){ // si il y a une promo 
                echo '<div class="bloc-reduc">';
                    $reduc = floatval($article["remise"]);
                    echo '<p>-'.$reduc.'%</p>';
                echo '</div>';

                echo '<div class="bloc-prix vcontainer">';
                    echo '<h4><del style="color:red;">'.$article["prixttc"].'€</del></h4><h3>'.$article["prixpromo"].'€.</h3>';
                echo '</div>';
            }else{
                echo '<div class="bloc-prix">';
                    echo '<h3><span class="prixTTC">'.$article["prixttc"].'</span>€</h3>';
                echo '</div>';
            }

            echo '<div class="conteneur">';
                echo '<form action="article.php" method="POST">';
            echo '<input type="hidden" name="detailArticle" value="'.$idarticle.'">
                <input type="submit" class="titre" name="nom" value="'.$article["nom"].'">                            
            </form>';

            //bouton pannier
            echo '<form action="" method="POST">
                <input type="hidden" name="idarticle" value="'.$idarticle.'">
                <input type="hidden" name="ajouter" value=true>
                <input type="image" src="images/icon/ajoutPanier.png" alt="Submit" width="48" height="48" name="ajouter" class="ajoutPanier">
            </form>';
            echo '</div>';
        echo '</div>';
    }
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta charset="utf-8"/>
    <title>ALIZON</title>
    <link rel="stylesheet" type="text/css" href="style/headeradmin.css" />
    <link rel="stylesheet" type="text/css" href="style/styleVendeur.css" />
</head>
<body>
    <header>
        <div class="haut">
            <?php include 'enteteAdmin.php' ?>
        </div>
    </header>
    <main>
        <div class="shadow">
            <img src="images/icon/brickwall.jpeg" width="100%" height="150"/>
            <div class="frame">
                <div class="hcontainer" style="flex-flow: row nowrap;">
                    <!-- charge l'image du vendeur -->
                    
                    <?php
                    $statement = $dbh->prepare("SELECT * from _vendeur  natural join _adresse where idvendeur = ?"); 
                    $statement->execute(array($_SESSION['voiridvendeur']));
                    $infosVendeur = $statement->fetch();
                    echo "<img id='logo' src='images/logoVendeur/".$infosVendeur['imgLogo']."' width='150' height='150'/>";
                    ?>

                    <div class="vcontainer" style="padding-left: 2em;">
                        <h1><?php echo $infosVendeur['nom'];?></h1>
                        <p>évaluation : <?php echo $infosVendeur['note']."/5";?></p>
                        <div class="hcontainer" style="justify-content: space-between;">
                            <!-- affiche adresse -->
                            <?php echo "<p>".$infosVendeur['numrue']." ".$infosVendeur['nomrue']." ".$infosVendeur['codepostal']." ".$infosVendeur['ville']."</p>";?>
                            <?php echo "<p>".$infosVendeur['email']."</p>";?>
                        </div>
                    </div>
                </div>
                
                <h4>Tous les objets</h4>
                <?php
                    $id = $_SESSION['voiridvendeur'];
                    $sql = "SELECT count(idarticle) from _article where idvendeur=$id";
                    $statement = $dbh->prepare($sql);
                    $statement->execute();
                    $num = min(10, $statement->fetch()['count(idarticle)'] / 10);
                    if(isset($_GET['spage'])){
                        $beg = (int)$_GET['spage'] * 10;
                    }else{
                        $beg = 0;
                    }
                    $end = 10;
                    //affiche index de page
                    echo "<form method='get' action=''>";
                    echo "<nobr>Page : </nobr>";
                    for($i = 0 ; $i < $num ; $i++){
                        echo "<input class='cell' type='submit' name='spage' value='$i'>";
                    }
                    echo "</form>";
                ?>
                
                <div class="listeArticle hcontainer">
                    <?php
                        $sql = "SELECT idarticle from _article where idvendeur=$id LIMIT $beg, $end";
                        $statement = $dbh->prepare($sql);
                        $statement->execute();
                        //affiche les articles
                        foreach ($statement as $row){
                            afficheArticle($row['idarticle']);
                        }
                    ?>
                </div>
                
                <h4>Objets dans la catégorie Bretagne</h4>

                <?php
                    $id = $_SESSION['voiridvendeur'];
                    $sql = "SELECT count(idarticle) from _article natural join _souscategorie natural join _categorie where idvendeur=$id and libelle='bretagne'";
                    $statement = $dbh->prepare($sql);
                    $statement->execute();
                    $num = min(10, $statement->fetch()['count(idarticle)'] / 10);
                    if(isset($_GET['spage'])){
                        $beg = $_GET['spage'] * 10;
                    }else{
                        $beg = 0;
                    }
                    $end = 10;
                    //affiche index de page
                    echo "<form method='get' action=''>";
                    echo "<nobr>Page : </nobr>";
                    for($i = 0 ; $i < $num ; $i++){
                        echo "<input class='cell' type='submit' name='spage' value='$i'>";
                    }
                    echo "</form>";
                ?>

                <div class="listeArticle hcontainer">
                    <?php
                        $sql = "SELECT idarticle from _article natural join _souscategorie natural join _categorie where idvendeur=$id and libelle='bretagne' LIMIT $beg, $end";
                        $statement = $dbh->prepare($sql);
                        $statement->execute();
                        //affiche les articles
                        foreach ($statement as $row){
                            afficheArticle($row['idarticle']);
                        }
                    ?>
                </div>


                <div class="listeArticle hcontainer">
                    
                </div>
            </div>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>