<?php
    session_start();
    $prefix = 'sae301_a21.';
    include('connect_params.php');
    $sum=0;
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    date_default_timezone_set('Europe/Paris');
    
    if(isset($_POST["supprimer"]) && $_POST["supprimer"]){ //vider l'article
				if (isset($_SESSION["idclient"])){
			$nb=$dbh->prepare("delete from {$prefix}_panier where idclient=".$_SESSION["idclient"]);
			$nb->execute();
			$_POST["valide"] = FALSE;
		}else{
			unset($_SESSION["panier"]);
		}
	}
	
	if(isset($_POST["idarticle"])){
		if(isset($_SESSION["idclient"])){
			$nb=$dbh->prepare("delete from {$prefix}_panier where idarticle = ? and idclient=?");
			$nb->execute(array($_POST["idarticle"],$_SESSION["idclient"]));
		}else{
			unset($_SESSION["panier"][$_POST["idarticle"]]);
		}
		
	}
    function dateValide($dateDebut,$dateFin){ // fonction pour vérfier si la remise est valide en onction des dates
        date_default_timezone_set('Europe/Paris');
        if($dateDebut<=date('Y-m-d') && date('Y-m-d')<$dateFin){
            return true;
        }else{
            return false;
        }
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
    <link rel="stylesheet" type="text/css" href="style/stylePanier.css" />
    <script>
</script>
</head>

<body>
<header> 
    <?php include 'enteteReduit.php' ?>
</header>
    <main>
        
    
    
        <?php
        echo'<div class="hautpanier">
				<p class="panier">Panier</p>
				<form action="" method="POST">
				<input type="hidden" name="supprimer" value=TRUE></input>
				<input type="submit" name="ajouter" value="Vider le panier" class="supprHaut"></input>
				</form>
			</div>';

                        try{

                            
                            if (isset($_POST["maj"])){

                                if(isset($_SESSION["idclient"])){

                                $sth = $dbh->prepare("UPDATE {$prefix}_panier set quantite=? where idarticle=?;");
                                $sth->execute(array($_POST["number"],$_POST["id"]));
                                }else{
                                    unset($_SESSION["panier"][$_POST["idRow"]]);
                                    array_push($_SESSION["panier"],(array("id"=>$_POST["id"],"quantite" =>$_POST["number"])));

                                }
                            }

                            
                            if(isset($_POST["Validercommander"])){

                                $ins = $dbh->query("INSERT INTO {$prefix}_commande(datecommande,datelivrer,dateexpedition,retours,idclient,idadresse,etat,prixcommande) values (current_date,NULL,NULL,FALSE,1,NULL,NULL,NULL)");
                                $req1 = $dbh->query("SELECT DISTINCT *  FROM {$prefix}_article a inner join {$prefix}_panier p  on a.idarticle = p.idarticle  where p.idclient = 1 ");
                                echo ($req1->rowcount());
                                $lastid = $dbh->lastInsertId();
                                
                                foreach ($req1 as $rowvalide){
                                    $ins2 = $dbh->query("INSERT INTO {$prefix}_quantite(idarticle,idcommande,quantite,prixachat) values($rowvalide[idarticle],$lastid,$rowvalide[quantite],0)");
                                    $ins2 = $dbh->query("UPDATE {$prefix}_article set quantitestock= quantitestock-$rowvalide[quantite] where idarticle=$rowvalide[idarticle]");
                                    
                                    
                                }
                                $nb=$dbh->query("DELETE from {$prefix}_panier where idclient=1");
                            }
                           

                            if(isset($_SESSION["idclient"])){

                            $statement = $dbh->query("SELECT * from {$prefix}_panier p inner join {$prefix}_article a on p.idarticle=a.idarticle inner join {$prefix}_souscategorie s on a.idsouscategorie=s.idsouscategorie inner join {$prefix}_categorie c on s.idcategorie=c.idcategorie inner join {$prefix}_image i on i.idarticle=a.idarticle   where p.idclient={$_SESSION["idclient"]} order by p.idarticle", PDO::FETCH_ASSOC);
                            $statement2= $dbh->query("SELECT * from {$prefix}_panier inner join {$prefix}_article on {$prefix}_panier.idarticle={$prefix}_article.idarticle where _panier.idclient={$_SESSION["idclient"]} order by {$prefix}_panier.idarticle", PDO::FETCH_ASSOC);
                            $statement3= $dbh->query("SELECT * from {$prefix}_panier inner join {$prefix}_article on {$prefix}_panier.idarticle={$prefix}_article.idarticle where _panier.idclient={$_SESSION["idclient"]} order by {$prefix}_panier.idarticle", PDO::FETCH_ASSOC);

                                $etatpanier=$dbh->query("select * from {$prefix}_panier where idclient=".$_SESSION["idclient"]);
                                $vide=true;
                                foreach($etatpanier as $row){
                                    $vide=false;
                                }
                                
                                if(!$vide){
                                    echo '
                            <div class = "tout">
                                <div class="colonne1">';
                                    foreach ($statement as $row){
                                        $date=date('Y-m-d');
                                        $idarticle = $row["idarticle"];
                                        $remise=$dbh->query("select * from {$prefix}_remise where date_debut < $date and date_fin > $date and idarticle = $idarticle")->fetch();
                                        print_r($remise);
                                        echo'                                            
                                                <div class ="article1">';
                                                if(file_exists("./".$row["urlimage"])){
                                                    echo '<img src='.$row["urlimage"].' alt="mug">'; //a changer plus tard avec $row["image"]
                                                }else{
                                                    echo '<img src="images/imgArticle/notFound.png" alt="notFound">'; //a changer plus tard avec $row["image"]
                                                } 

                                                    echo'
                                                    <div class="hautArt">
                                                        <div class="description">
                                                            <form action="article.php" method="POST">
                                                                <input type="hidden" name="detailArticle" value="'.$row["idarticle"].'">
                                                                <input type="submit" class="titre" name="nom" value="'.$row["nom"].'">               
                                                            </form>';

                                                            if($row['quantitestock']<$row['seuilalerte']){
                                                                echo '<p class="stockR">il reste '.$row["quantitestock"].' articles en stock</p>';
                                                            }
                                                            else{
                                                                echo '<p class="stockV">il reste '.$row["quantitestock"].' articles en stock</p>';
                                                            }
                                                            if(strlen($row["descript"])<200){
                                                                echo $row["descript"];
                                                            }else{
                                                            echo '<p class="petit">';
                                                            for($i=0;$i<=200;$i++){
                                                                echo $row["descript"][$i];
                                                            }
                                                            echo'... ';
                                                            echo '</p>';
                                                            echo '<p class="grand">'.$row["descript"].'</p>';
                                                            echo'<a class="plus">Voir plus</a>';
                                                            }
                                                            echo '<a href=""><p>Plus de produit similaire</p></a>
                                                            <div class="Boutquantite">
                                                            <form action="panier.php" method="post">
                                                            Quantité : 
                                                            <input name="maj" type="hidden" value="True" />
                                                            <input name="id" type="hidden" value="'.$row["idarticle"].'" />
                                                            <input name="number" type="number" min="1" value="'.$row["quantite"].'"/>
                                                            <input class = "maj" type="submit" name="submit" value="Mettre à jour"/> 
                                                            </form>';
                                                            if($row["enremise"]){
                                                                $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$row[idarticle]");
                                                                    foreach ($remise as $row_remise){
                                                                        if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                                                                            echo'<p>Prix total : '.$row["quantite"]*$row_remise["prixpromo"].'€</p>
                                                                            </div>                                                                  
                                                                            </div>
                                                                            </div>
                                                                            <div class="prixArticle">
                                                                            <div>
                                                                                <p class="prixArtGris"> '.$row["prixttc"]. '€ HT TVA: '.$row["tva"].'</p>
                                                                                <p class="ancienPrix">'.$row["prixttc"].'€/article</p>
                                                                                <p class="prixArt"> '.$row_remise["prixpromo"].'€<span>/article</span></p>
                                                                            </div>
                                                                            <form class="boutonSupprimer" action="" method="POST"> 
                                                                                <input type="hidden" name="idarticle" value="'.$row["idarticle"].'"></input>
                                                                                <input type="submit" name="ajouter" value="supprimer" class="suppr"></input>
                                                                            </form>
                                                                            </div>';
                                                                        }
                                                                        else{
                                                                            echo'<p>Prix total : '.$row["quantite"]*$row["prixttc"].'€</p>

                                                                                </div>                                                                  
                                                                                </div>
                        
                                                                                    
                                                                                </div>
                                                                                <div class="prixArticle">
                                                                                <div>
                                                                                    <p class="prixArtGris"> '.$row["prixht"]. '€ HT TVA: '.$row["tva"].'</p>
                                                                                    <p class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>
                                                                                </div>
                                                                                <form class="boutonSupprimer" action="" method="POST"> 
                                                                                    <input type="hidden" name="idarticle" value="'.$row["idarticle"].'"></input>
                                                                                    <input type="submit" name="ajouter" value="supprimer" class="suppr"></input>
                                                                                </form>
                                                                                </div>';
                                                                        }
                                                                    }
                                                            }else{
                                                                echo'<p>Prix total : '.$row["quantite"]*$row["prixttc"].'€</p>

                                                                </div>                                                                  
                                                                </div>
    
                                                                
                                                            </div>
                                                            <div class="prixArticle">
                                                            <div>
                                                                <p class="prixArtGris"> '.$row["prixht"]. '€ HT TVA: '.$row["tva"].'</p>
                                                                <p class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>
                                                            </div>
                                                            <form class="boutonSupprimer" action="" method="POST"> 
                                                                <input type="hidden" name="idarticle" value="'.$row["idarticle"].'"></input>
                                                                <input type="submit" name="ajouter" value="supprimer" class="suppr"></input>
                                                            </form>
                                                            </div>';
                                                            }
                                                           
                                                        
                                                   echo' </div>';

                                                    
                                    }
                                echo 
                                '</div>';

                                //RECAPITULATIF VERSION ORDINATEUR
                                    
                                    echo '
                                    <div class = "colonne2">
                                        <div id = "recapHaut">
                                            <p id = "titreRec">Récapitulatif</p>
                                            <div>';
                                                foreach($statement2 as $row){
                                                    if($row["enremise"]){
                                                        $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$row[idarticle]");
                                                            foreach ($remise as $row_remise){
                                                                if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                                                                    $sum=$sum+$row_remise["prixpromo"]*$row["quantite"];
                                                                    echo'
                                                                    <div class = "txtRecap"
                                                                        <p class="titreArt">'.$row["nom"].'</p>
                                                                        <p class="Boutquantite"> '."x".$row["quantite"].' </p>
                                                                        <p class="prix"> '.$row_remise["prixpromo"]*$row["quantite"].'€</p>
                                                                    </div>';
                                                                }
                                                            }
                                                        }else{
                                                        $sum=$sum+$row["prixttc"]*$row["quantite"];
                                                        echo'
                                                        <div class = "txtRecap"
                                                            <p class="titreArt">'.$row["nom"].'</p>
                                                            <p class="Boutquantite"> '."x".$row["quantite"].' </p>
                                                            <p class="prix"> '.$row["prixttc"]*$row["quantite"].'€</p>
                                                        </div>';
                                                    }
                                                }
                                                        echo'
                                                        <div id="recBas">
                                                            <p class="prixT">Total '.$sum.'€</p>
                                                            <a href="facturation.php" id="BoutCommander">Commander</a>
                                                        </div>';
                                                    
                                                
                                            echo '</div>
                                        </div>
                                    </div>';

                                //RECAPITULATIF VERSION MOBILE   
                                echo '
                                    <div class = "totaleResponsive">
                                                <p class="prixT">Total '.$sum.'€</p>
                                                <a href="facturation.php" id="BoutCommander">Commander</a>
                                    </div>
                                    <div class="imgResponsive"><img id="img" src="images/icon/flechehaut.png" alt="fleche haut" title="fleche haut"/></div>
                                    <div id="recapResponsive">';
                                        foreach($statement3 as $row){
                                        $sum=$sum+$row["prixttc"]*$row["quantite"];
                                        echo'
                                        <div class = "txtRecap"
                                            <p class="titreArt">'.$row["nom"].'</p>
                                            <p class="Boutquantite"> '."x".$row["quantite"].' </p>
                                            <p class="prix"> '.$row["prixttc"]*$row["quantite"].'€</p>
                                        </div>';
                                        }
                                  echo '</div>';
                                
                                }else{
                                    echo'</div>
                                    <div class="vide">
                                        <img src="images/icon/panierLogoNoir.png" alt="panier" width="200px"
                                        <h3>Votre panier est vide !</h3>
                                        <p>Vous n\'avez ajouté aucun articles dans votre panier</p>
                                        <a href="categorie.php">Continuer mes achats</a>
                                    </div>';
                                }
                               
                            
                            }else{
                                //VERSION INTERNAUTE
                                    if(empty($_SESSION["panier"])){
                                        echo ' 
                                        <div class="vide">
                                            <img src="images/icon/panierLogoNoir.png" alt="panier" width="200px"
                                            <h3>Votre panier est vide !</h3>
                                            <p>Vous n\'avez ajouté aucun articles dans votre panier</p>
                                            <a href="categorie.php">Continuer mes achats</a>
                                        </div>';
                                    }else{
                                        echo'
                                        <div class = "tout">
                                        <div class="colonne1">';
                                        foreach ($_SESSION["panier"] as $idrow=>$panier){  
                                            //foreach($panier as $rowpanier){
                                            $statement = $dbh->query("SELECT * from {$prefix}_article a inner join {$prefix}_souscategorie s on a.idsouscategorie=s.idsouscategorie inner join {$prefix}_categorie c on s.idcategorie=c.idcategorie inner join {$prefix}_image i on i.idarticle=a.idarticle  where a.idarticle = ".$panier["id"], PDO::FETCH_ASSOC);
                                            
                                            
                                                                                    
                                            foreach($statement as $row){       
                                                echo'                                            
                                                <div class ="article1">';
                                                if(file_exists("./".$row["urlimage"])){
                                                    echo '<img src='.$row["urlimage"].' alt="mug">'; //a changer plus tard avec $row["image"]
                                                }else{
                                                    echo '<img src="images/imgArticle/notFound.png" alt="notFound">'; //a changer plus tard avec $row["image"]
                                                } 

                                                    echo'
                                                    <div class="hautArt">
                                                        <div class="description">
                                                            <form action="article.php" method="POST">
                                                                <input type="hidden" name="detailArticle" value="'.$row["idarticle"].'">
                                                                <input type="submit" class="titre" name="nom" value="'.$row["nom"].'">               
                                                            </form>';

                                                            if($row['quantitestock']<$row['seuilalerte']){
                                                                echo '<p class="stockR">il reste '.$row["quantitestock"].' article en stock</p>';
                                                            }
                                                            else{
                                                                echo '<p class="stockV">il reste '.$row["quantitestock"].' article en stock</p>';
                                                            }
                                                            if(strlen($row["descript"])<200){
                                                                echo $row["descript"];
                                                            }else{
                                                            echo '<p class="petit">';
                                                            for($i=0;$i<=200;$i++){
                                                                echo $row["descript"][$i];
                                                            }
                                                            echo'... ';
                                                            echo '</p>';
                                                            echo '<p class="grand">'.$row["descript"].'</p>';
                                                            echo'<a class="plus">Voir plus</a>';
                                                            }
                                                            echo'<a href=""><p>Plus de produit similaire</p></a>
                                                            <div class="Boutquantite">
                                                            <form action="panier.php" method="post">
                                                            Quantité : 
                                                            <input name="maj" type="hidden" value="True" />
                                                            <input name="idRow" type="hidden" value="'.$idrow.'" />
                                                            <input name="id" type="hidden" value="'.$row["idarticle"].'" />
                                                            <input name="number" type="number" min="1" value="'.$panier["quantite"].'"/>
                                                            <input class = "maj" type="submit" name="submit" value="Mettre à jour"/> 
                                                            </form>';
                                                            if($row["enremise"]){
                                                                $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$row[idarticle]");
                                                                    foreach ($remise as $row_remise){
                                                                        if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                                                                            echo'<p>Prix total : '.$panier["quantite"]*$row_remise["prixpromo"].'€</p>
                                                                            </div>                                                                  
                                                                            </div>
                                                                            </div>
                                                                            <div class="prixArticle">
                                                                            <div>
                                                                                <p class="prixArtGris"> '.$row["prixttc"]. '€ HT TVA: '.$row["tva"].'</p>
                                                                                <p class="ancienPrix">'.$row["prixttc"].'€/article</p>
                                                                                <p class="prixArt"> '.$row_remise["prixpromo"].'€<span>/article</span></p>
                                                                            </div>
                                                                            <form class="boutonSupprimer" action="" method="POST"> 
                                                                                <input type="hidden" name="idarticle" value="'.$row["idarticle"].'"></input>
                                                                                <input type="submit" name="ajouter" value="supprimer" class="suppr"></input>
                                                                            </form>
                                                                            </div>
                                                                            </div>';
                                                                        }
                                                                    }
                                                            }else{
                                                            
                                                            echo '<p>Prix total : '.$panier["quantite"]*$row["prixttc"].'€</p>

                                                            </div>                                                                  
                                                            </div>

                                                            
                                                            </div>
                                                            <div class="prixArticle">
                                                            <div>
                                                                <p class="prixArtGris"> '.$row["prixht"]. '€ HT TVA: '.$row["tva"].'</p>
                                                                <p class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>
                                                            </div>
                                                            <form class="boutonSupprimer" action="" method="POST"> 
                                                                <input type="hidden" name="idarticle" value="'.$idrow.'"></input>
                                                                <input type="submit" name="ajouter" value="supprimer" class="suppr"></input>
                                                            </form>
                                                            </div>
                                                            
                                                            </div>';           
                                                
                                            }
                                        }
                                        }
                                        echo 
                                '</div>';
                                    echo '
                                    <div class = "colonne2">';
                                        echo'<div id = "recapHaut">
                                        <p id = "titreRec">Récapitulatif</p>
                                                <div>';
                                                foreach ($_SESSION["panier"] as $panier){
                                                    $internaute=$dbh->query("select * from {$prefix}_article where idarticle = $panier[id]");
                                                    $quantite=$panier["quantite"];
                                                    foreach($internaute as $row){
                                                    $sum=$sum+$row["prixttc"]*$quantite;
                                                    echo'
                                                    
                                                    <div class = "txtRecap">
                                                        <p class="titreArt">'.$row["nom"].'</p>
                                                        <p class="Boutquantite"> '."x".$quantite.' </p>
                                                        <p class="prix"> '.$row["prixttc"]*$quantite.'€</p>
                                                    </div>';
                                                    }
                                                }
                                                    echo'
                                                    <div id="recBas" style="display:block;">
                                                        <p  class="prixT">Total '.$sum.'€ </p>
                                                  
                                                    </div>
                                                    <a class="connect" href="connexion.php">Connecter</a>
                                                    <a class="connect" href="inscription.php">Creer un compte</a>
                                                
                                                </div>
                                    </div>';
                                    echo'</div>';

                                    //RECAP VERSION MOBILE
                                    echo '
                                    <div class = "totaleResponsive">
                                                <p class="prixT">Total '.$sum.'€</p>
                                                <a class="connect" href="connexion.php">Connecter</a>
                                                <a class="connect" href="inscription.php">Creer un compte</a>
                                    </div>
                                    <div class="imgResponsive"><img id="img" src="images/icon/flechehaut.png" alt="fleche haut" title="fleche haut"/></div>
                                    <div id="recapResponsive">';
                                        foreach ($_SESSION["panier"] as $panier){
                                                    $internaute=$dbh->query("select * from {$prefix}_article where idarticle = $panier[id]");
                                                    $quantite=$panier["quantite"];
                                                    foreach($internaute as $row){
                                                    $sum=$sum+$row["prixttc"]*$quantite;
                                                    echo'
                                                    
                                                    <div class = "txtRecap">
                                                        <p class="titreArt">'.$row["nom"].'</p>
                                                        <p class="Boutquantite"> '."x".$quantite.' </p>
                                                        <p class="prix"> '.$row["prixttc"]*$quantite.'€</p>
                                                    </div>';
                                                    }
                                                }
                                  echo '</div>';

                                    }echo '</p>';
                                                
                                }
            
                                } catch (PDOException $e) {
                                // print "Erreur !: " . $e->getMessage() . "<br/>";
                                // die();
                                }
                             
                             
                            //popup
                            if(isset($_POST["idarticle"])){
                                echo '<p id="popInsCon">Article supprimer</p>'; 
                            }
                            if(isset($_POST["supprimer"]) && $_POST["supprimer"]){ //vider l'article
								echo '<p id="popInsCon">Panier supprimer</p>';
							}
							if (isset($_POST["maj"])){
                                echo '<p id="popInsCon">Quantite MAJ</p>';
							}
							
                        ?>
                    
            <!--<div class="colonne3">
                <p class="titreArtSim">Article similaire</p>
                <div class = "articleSimilaire">
                        <img class = "artsim"src="article/billig-crepe-maker.jpg" alt="billig" title="billig" />
                        <img class = "artsim"src="article/billig-crepe-maker.jpg" alt="billig" title="billig" />
                        <img class = "artsim"src="article/billig-crepe-maker.jpg" alt="billig" title="billig" />
                        <img class = "artsim"src="article/billig-crepe-maker.jpg" alt="billig" title="billig" />
                        <img class = "artsim"src="article/billig-crepe-maker.jpg" alt="billig" title="billig" />
                </div>
        </div>-->
        <script>
            var images=document.images["img"];
            function permuterImage(){
                if(images.src.match("flechehaut")){
                    images.src="images/icon/flechebas.png";
                    document.getElementById("recapResponsive").style.display = "flex";
                    document.getElementsByClassName("imgResponsive")[0].style.borderTop="none";

                }else{
                    images.src="images/icon/flechehaut.png";
                    document.getElementById("recapResponsive").style.display = "none";
                    document.getElementsByClassName("imgResponsive")[0].style.borderTop="1px solid";
                }
            }
            
            var elem =document.getElementById("img");
            elem.addEventListener("click",permuterImage);
            var elem2=document.getElementsByClassName("plus");
            for (let i = 0; i < elem2.length; i++) {
                elem2[i].addEventListener("click",()=> {
                    let plus=document.getElementsByClassName("plus");
                    if(plus[i].innerText=="Voir plus"){
                    document.getElementsByClassName("grand")[i].style.display="block";
                    document.getElementsByClassName("petit")[i].style.display="none";
                    plus[i].innerText="Voir moins";
                }else{
                    document.getElementsByClassName("grand")[i].style.display="none";
                    document.getElementsByClassName("petit")[i].style.display="block";
                    plus[i].innerText="Voir plus";
                }
                });
                
            }
    
        </script>
    </main>
    <footer>
        <?php include 'piedpage.php' ?>
    </footer>

</body>

</html>
