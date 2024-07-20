<!DOCTYPE html>
<?php
    include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);


?>
<?php

        //Fonction qui permet de promouvoir un produit
        if(isset($_POST["promouvoir"])){
                $nb=$dbh->prepare("UPDATE {$prefix}_article set enpromotion = true where idarticle= ?");
                $nb->execute(array($_POST["promouvoir"]));
            }
            //Fonction qui permet d'enlever la promotion un produit
            if(isset($_POST["depromouvoir"])){
                $nb=$dbh->prepare("UPDATE {$prefix}_article set enpromotion = false where idarticle= ?");
                $nb->execute(array($_POST["depromouvoir"]));
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
    <link rel="stylesheet" type="text/css" href="style/styleMiseEnAvant.css" />
</head>
<body>
    <header> 
        <?php include 'entetePro.php'; ?>        
    </header>
    <main>
        <?php

    //Récuperation depuis la base de données
    $statement = $dbh->query("SELECT * from {$prefix}_vendeur v inner join {$prefix}_article a on a.idvendeur=v.idvendeur  inner join {$prefix}_image i on a.idarticle = i.idarticle where a.idvendeur=$_SESSION[idvendeur] ORDER BY a.nom ASC", PDO::FETCH_ASSOC);

    echo '<div>';
    foreach ($statement as $row){

        //On recupere ici tout les produits en promotions
        if ($row["enpromotion"]){
            echo' <section><div class="itemPromu">
            <p class="messProm">Promu</p>
            <figure>';
            if(file_exists("./".$row["urlimage"])){
                echo'<img src='.$row["urlimage"].' alt="mug"> ';
            }else{
                echo'<img src="images/imgArticle/notFound.png" alt="notFound">';  //Si l'image du produit n'est pas trouvé ,affiche une image notfound
            } 
            echo '
            </figure>
                <article class="article1">
                <form action="article.php" method="POST">
                <input type="hidden" name="detailArticle" value='.$row["idarticle"].'>
                <input type="submit" class="titre" name="nom" value='.$row["nom"].'>               
            </form>
                    <p> Prix :'.$row["prixttc"].'€</p>
                </article>';

                //Formulaire qui fait appel a la fonction depromouvoir
                echo'<form id= "formu" action="" method = "POST">
                    <input type ="hidden" name="depromouvoir" value ='.$row["idarticle"].' >
                    <input type ="submit" class = "bout" name="depromouvoir2" value ="Enlever la promotion">
                </form>';

        }else { 
            //Permet d'afficher les produits qui ne sont pas promus
            echo' <section><div class="item">
                <p class="messpasProm"></p>
                <figure>';
                    if(file_exists("./".$row["urlimage"])){
                        echo'<img src='.$row["urlimage"].' alt="mug"> ';
                    }else{
                        echo'<img src="images/imgArticle/notFound.png" alt="notFound">';//Si l'image du produit n'est pas trouvé ,affiche une image notfound
                    } 
                    echo '
                </figure>
                    <article class="article1">
                        <form action="article.php" method="POST">
                            <input type="hidden" name="detailArticle" value='.$row["idarticle"].'>
                            <input type="submit" class="titre" name="nom" value='.$row["nom"].'>               
                        </form>
                        <p id="prix"> Prix :'.$row["prixttc"].'€</p>
                    </article>';

                    //Formulaire qui fait appel a la fonction depromouvoir
                    echo'
                    <form id= "formuProm" action="" method = "POST">
                        <input type ="hidden" name="promouvoir" value ='.$row["idarticle"].' >
                        <input type ="submit" onClick=afficherBloc() class = "bout" name="promouvoir2" value ="Promouvoir">
                    </form>';
            }

            echo'</div>
        </section>';
    }

    echo'</div>';
    ?>
</main>
<footer>
    <?php
    include("piedpageReduit.php");
    ?>
</footer>
</body>
</html>