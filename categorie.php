<?php

    include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';

    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);
    
    //<-----debut php pour l'ajout au panier-------> 
    if (isset($_POST["ajouter"])) {
        if (isset($_SESSION["idclient"])){
			//danger
            $res = $dbh->query('SELECT count(*) from '.$prefix.'_panier where idclient='.$_SESSION["idclient"].' and idarticle='.$_POST["idarticle"])->fetchColumn();
            if($res == 0){ //si il n'y a pas deja un panier de cette article pour ce client 
                $req = $dbh->prepare("INSERT INTO {$prefix}_panier(idclient,idarticle,quantite) values (?,?,?)");
                $req->execute(array($_SESSION["idclient"], $_POST["idarticle"], 1));
            }
            else{
                $quantite = $dbh->query('SELECT quantite from '.$prefix.'_panier where idclient='.$_SESSION["idclient"].' and idarticle='.$_POST["idarticle"])->fetchColumn();
                $req = $dbh->prepare('UPDATE '.$prefix.'_panier set quantite=? where idclient='.$_SESSION["idclient"].' and idarticle='.$_POST["idarticle"]);
                $req->execute(array($quantite+1));
            }
        }else{
            $dejaDansPanier=false;
            if(!empty($_SESSION["panier"])){
                foreach($_SESSION["panier"] as $row){
                    if($row["id"]==$_POST["idarticle"]){
                        $dejaDansPanier = true;
                    }
                }
                
                if(!$dejaDansPanier){
                array_push($_SESSION["panier"],(array("id"=>$_POST["idarticle"],"quantite" =>1)));
                }
            }else{
                $_SESSION["panier"]=array(array("id"=>$_POST["idarticle"],"quantite" =>1));
            }
        }
    }

    function dateValide($dateDebut,$dateFin){ // fonction pour vérfier si la remise est valide en onction des dates
        date_default_timezone_set('Europe/Paris');
        if($dateDebut<=date('y-m-d') && date('y-m-d')<=$dateFin){
            return true;
        }else{
            return false;
        }
    }

    function afficheArticle($idarticle){ //fonction pour afficher un article 
        global $dbh, $prefix;
        $article = $dbh->query("SELECT * FROM {$prefix}_image natural join {$prefix}_article  where {$prefix}_article.idarticle=$idarticle")->fetch();

                                echo '<div class="article">';
                                
									echo '<form action="article.php" method="POST">';
										echo '<input type="hidden" name="detailArticle" value="'.$article["idarticle"].'">';
											
											echo '<input type="image" src="'.$article["urlimage"].'" width=150px height=150px name="detailArticle" value="'.$article["idarticle"].'">';
										
                                    echo '</form>';
									if($article['enremise']){ // si il y a une promo 
                                        $article2 = $dbh->query("SELECT * FROM {$prefix}_image natural join {$prefix}_article natural join {$prefix}_remise where {$prefix}_article.idarticle=$idarticle");
                                        foreach($article2 as $remise){
                                            if(dateValide($remise["date_debut"],$remise["date_fin"])){
                                                echo '<div class="bloc-reduc">';
                                                    $reduc = floatval($remise["remise"]);
                                                    echo '<p>-'.$reduc.'%</p>';
                                                echo '</div>';
                                
                                                echo '<div class="bloc-prix vcontainer">';
                                                    echo '<h4><del style="color:red;">'.$article["prixttc"].'€</del></h4><h3>'.$remise["prixpromo"].'€.</h3>';
                                                echo '</div>';
                                            }
                                        }
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
//<----- fin php pour l'ajout au panier------->

    if(!empty($_POST["annule"])){
        header('Location: '.$_SERVER['PHP_SELF']);    
    }
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <meta charset="utf-8" />
        <title>ALIZON</title>
        <meta name="description" content="Site de e-commerce ALIZON" />
        <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="style/header.css" />
        <link rel="stylesheet" type="text/css" href="style/footer.css" />
        <link rel="stylesheet" type="text/css" href="style/styleCategorie.css" />
    </head>
    <header id='haut'> 
        <?php include 'entete.php'; ?>
    </header>
    <main>
        <?php
            if (isset($_POST["ajouter"])) { 
                echo '<p id="popInsCon">Article ajouter au panier</p>';
            } 
                ?>
        <body>
            
            <div class="conteneur">
                <aside class="bloc" id="asideDroit" >
                    <div id="filtrer">
                        <h2 style="margin-left: 20px;margin-right: 20px;">Filtrer par prix</h2>
                       <!-- Filtre par prix -->
                       <?php
                       
                        
                            if(isset($_GET["categorie"])){
                                if($_GET["categorie"]=="categorie"){
                                    $statement = $dbh->query("SELECT  max(round(prixttc, 2)) as prixttc from {$prefix}_article");
                                }else{
                                    $statement = $dbh->prepare("SELECT max(round(prixttc, 2)) as prixttc from {$prefix}_article natural join {$prefix}_souscategorie natural join {$prefix}_categorie  where libelle=? or souslibelle=?");
                                    $statement->execute(array($_GET["categorie"], $_GET["categorie"]));
                                }
                            }else{
                                $statement = $dbh->query("SELECT  max(round(prixttc, 2)) as prixttc from {$prefix}_article");
                            }
                       
                       
                        foreach($statement as $prix){
                           $prixTTC = $prix["prixttc"];
                        }
                        
                       ?>
                       <div class="wrapper">
                            <article class ="values">
                                <span class="range1">0</span>
                                <span>&dash;</span>
                                <span class="range2"><?php echo(intval($prixTTC)) ?></span>
                            </article>
                            <div class="container">
                                <div class="slider-track"></div>
                                <?php
                                echo'<input type="range" min="0" max='.intval($prixTTC).' step="5" value="0" class="slider-1" oninput="slideOne()">';
                                echo'<input type="range" min="0" max='.intval($prixTTC).' step="5" value='.intval($prixTTC).' class="slider-2" oninput="slideTwo()">';
                                ?> <!-- on créer deux range qu'on va rendre invisible et superpossé pour simuler un double curseur -->
                            </div>
                        </div>
                            
                        <form class="formPortable" method="GET">
                            <h2>Filtrer par catégorie</h2>
                            <select name="categorie"> <!-- Affichage du menu déroullant des catégorie et sous catégorie --> 
                                    <?php
                                    $categoriesBDD = $dbh->query("SELECT  idcategorie, libelle from {$prefix}_categorie");
                                    echo '<option value="categorie">Catégorie</option>';
                                    foreach($categoriesBDD as $row){
                                        echo '<optgroup value='.$row["libelle"].' label="'.$row["libelle"].'">';
                                        $souscategoriesBDD = $dbh->query("SELECT  souslibelle from {$prefix}_souscategorie where idcategorie={$row["idcategorie"]}");
                                        foreach($souscategoriesBDD as $sousrow){
                                            echo '<option value="'.$sousrow["souslibelle"].'"> '.$sousrow["souslibelle"].' </option> ';
                                        }
                                        echo'</optgroup>';
                                    }
                                ?>
                            </select>
                            <input type="submit" value="Valider" name="" class="valider">                               
                        </form>

                        </div>
                        <!-- affiche les categorie et sous categorie -->
                        <div class="filtre">
                            <div class ="titrefiltre" onclick="deroule()">
                                <h2   >Filtrer par catégorie</h2>
                                <img class="butto" src="images/icon/flechebas.png" title="fleche bas" alt="fleche bas"/>
                            </div>
                        
                        <form id="cate" action="" method="GET" style="display:none;">
                        <ul style="padding-left: 0px;">
                        <?php
                            $categories = $dbh->query("SELECT libelle from {$prefix}_categorie");
                            foreach($categories as $c){
                                echo "<li>";
                                echo '<input type="submit" value='.$c["libelle"].' name="categorie" class="categorie">';
                                $souscategories = $dbh->query("select souslibelle from {$prefix}_souscategorie natural join {$prefix}_categorie where libelle='".$c["libelle"]."'");
                                    echo '<ul>';
                                    foreach($souscategories as $sc){
                                        echo "<li>";
                                        echo '<input type="submit" value="'.$sc["souslibelle"].'" name="categorie" class="categorie souscategorie">';
                                        echo "</li>";
                                    }
                                    echo '</ul>';
                                echo "</li>";
                            }
                        ?>
                        <script>
                            function deroule(){
                                let butt = document.getElementsByClassName("butto");
                                let cat=document.getElementById("cate");
                                
                                if (cat.style.display === "none") {
                                    cat.style.display = "flex";
                                    butt[0].style.transform = "rotate(180deg)";
                                }
                                else{
                                    cat.style.display = "none";
                                    butt[0].style.transform = "none";
                                }
                            }
                        
                        </script>
                        </ul>
                        </form>
                        <hr/>
                        <div class ="titrefiltre" onclick="deroule2()">
                            <h2>Filtrer par vendeur</h2>
                            <img class="butto2" src="images/icon/flechebas.png" title="fleche bas" alt="fleche bas"/>
                            
                        </div>
                        
                        <form id="vend" action="" method="GET" style="display:none;">
                        <ul >
                        <?php
                            $vendeur = $dbh->query("SELECT nom as nomvendeur from {$prefix}_vendeur ");
                            foreach($vendeur as $v){
                                echo "<li>";
                                echo '<input type="submit" value='.$v["nomvendeur"].' name="vendeur" class="vendeur">';
                                
                                echo "</li>";
                            }
                        ?>
                        </ul>
                        </form>
                        </div>
                        <script>
                            function deroule2(){
                                let butt = document.getElementsByClassName("butto2");
                                let cat=document.getElementById("vend");
                                
                                if (cat.style.display === "none") {
                                    cat.style.display = "block";
                                    butt[0].style.transform = "rotate(180deg)";
                                    
                                }
                                else{
                                    cat.style.display = "none";
                                    butt[0].style.transform = "none";
                                }
                            }
                        </script>
                        
                        
                </aside>
                <div class="bloc" id="centre">
                    <?php 
                    if((isset($_GET["categorie"]))&&($_GET["categorie"]!="categorie")){
                        echo '<p>Filtre selectionné : ' .$_GET["categorie"];
                        }
                    ?></p>
                    <?php 
                    if((isset($_GET["vendeur"]))&&($_GET["vendeur"]!="categorie")){
                        echo '<p>Filtre selectionné : ' .$_GET["vendeur"];
                        }
                    ?></p>
                    
                        <?php
                            // article a afficher
                            if(isset($_GET["categorie"])){
                                if($_GET["categorie"]=="categorie"){
                                    $statement = $dbh->query("SELECT  nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_image");
                                }else{
                                    $statement = $dbh->prepare("SELECT nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_souscategorie natural join {$prefix}_categorie natural join {$prefix}_image  where souslibelle=?");
                                    $statement->execute(array($_GET["categorie"]));                                 
                                
                                    $statement = $dbh->prepare("SELECT nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_souscategorie natural join {$prefix}_categorie natural join {$prefix}_image  where libelle=? or souslibelle=?");
                                    $statement->execute(array($_GET["categorie"], $_GET["categorie"]));
                                }
                            }else if(isset($_GET["portableCategorie"])){
                                if($_GET["categorie"]=="categorie"){
                                    $statement = $dbh->query("SELECT  nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_image");
                                }else{
                                    $statement = $dbh->prepare("SELECT nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_souscategorie natural join {$prefix}_categorie natural join {$prefix}_image  where souslibelle=?");
                                    $statement->execute(array($_GET["categorie"]));                                 
                                
                                    $statement = $dbh->prepare("SELECT nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_souscategorie natural join {$prefix}_categorie natural join {$prefix}_image  where libelle=? or souslibelle=?");
                                    $statement->execute(array($_GET["categorie"], $_GET["categorie"]));
                                }
                            }else if (isset($_GET["vendeur"])) {
                                if($_GET["vendeur"]=="vendeur"){
                                    $statement = $dbh->query("SELECT  nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_image");
                                }else{
                                    $statement = $dbh->prepare("SELECT a.nom, idarticle, urlimage, round(prixttc, 2) as prixttc, v.nom as nomvendeur from {$prefix}_article a natural join {$prefix}_souscategorie natural join {$prefix}_categorie natural join {$prefix}_image natural join {$prefix}_vendeur v where v.nom=?");
                                    $statement->execute(array($_GET["vendeur"]));                                 
                                
                                    
                                }
                            }
                            else{
                                $statement = $dbh->query("SELECT  nom, idarticle, urlimage, round(prixttc, 2) as prixttc from {$prefix}_article natural join {$prefix}_image");

                            }
                            
                            if($statement->rowCount()==0){
                                echo '  <p id="bad">:(</p>
                                        <p>Aucun articles pour ce filtre ! </p>';
                            }else{
                                echo '<div class="images">';
                                foreach ($statement as $row){
                                    afficheArticle($row["idarticle"]);
                                }
                                echo '</div>';
                            }
                        ?>
                           
                    </div>
                        
                <aside class="bloc" id="asideGauche">
                    <h2>Articles en promotions : </h2>
                    <div class="images">
                        <?php
                            $statement = $dbh->query("SELECT nom, _article.idarticle, urlimage, round(prixttc, 2) as prixttc, round(prixpromo, 2) as prixpromo from {$prefix}_article natural join {$prefix}_image LEFT OUTER JOIN {$prefix}_remise ON {$prefix}_article.idarticle={$prefix}_remise.idarticle where {$prefix}_article.enpromotion=true");
                            //$statement = $dbh->query("SELECT * from {$prefix}_article natural join {$prefix}_promotion");

                            
                            foreach ($statement as $row){
                                $article = $dbh->query("SELECT * FROM {$prefix}_image natural join {$prefix}_article LEFT OUTER JOIN {$prefix}_remise ON {$prefix}_article.idarticle={$prefix}_remise.idarticle where {$prefix}_article.idarticle=$row[idarticle] and {$prefix}_article.enpromotion=true")->fetch();

                                echo '<div class="article">';
                                
									echo '<form action="article.php" method="POST">';
										echo '<input type="hidden" name="detailArticle" value="'.$row["idarticle"].'">';
										if(file_exists("./".$row["urlimage"])){
											
											echo '<input type="image" src="'.$row["urlimage"].'" width=150px height=150px name="detailArticle" value="'.$row["idarticle"].'">';
										}else{
											
											echo '<input type="image" src="images/imgArticle/notFound.png" width=150px height=150px name="detailArticle" value="'.$row["idarticle"].'">';
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
									echo '<input type="hidden" name="detailArticle" value="'.$row["idarticle"].'">
										<input type="submit" class="titre" name="nom" value="'.$row["nom"].'">                            
									</form>';

									
									//bouton pannier
									echo '<form action="" method="POST">
										<input type="hidden" name="idarticle" value="'.$row["idarticle"].'">
										<input type="hidden" name="ajouter" value=true>
										<input type="image" src="images/icon/ajoutPanier.png" alt="Submit" width="48" height="48" name="ajouter" class="ajoutPanier">
									</form>';
                                    echo '</div>';
                                echo '</div>';
                            }
                        ?>
                    </div>
                </aside>
                </div>
            </div>
            </div>
        <!--Script-->
        <script src="javascript/scriptCategorie.js"></script>
        </body>
    </main>
    <footer>
        <?php include 'piedpage.php'; ?>
    </footer>

</html>

