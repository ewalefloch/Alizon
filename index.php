<?php
include('connect_params.php');
session_start();
$prefix = 'sae301_a21.';
//unset($_SESSION["panier"]); vide panier session
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function dateValide($dateDebut, $dateFin)
{ // fonction pour vérfier si la remise est valide en onction des dates
    date_default_timezone_set('Europe/Paris');
    if ($dateDebut <= date('Y-m-d') && date('Y-m-d') < $dateFin) {
        return true;
    } else {
        return false;
    }
}


if (isset($_POST["supprimer"])) { //vider l'article
    $update = $dbh->prepare("UPDATE {$prefix}_article SET quantitestock = quantitestock- ? WHERE idarticle = ?;");
    $insertCommande = $dbh->query("INSERT into {$prefix}_commande(datecommande,datelivrer,dateexpedition,idclient,idadresse,etat)
            VALUES(current_date,current_date,current_date,$_SESSION[idclient],1,'EN livraison')");
    $lastIDCommande = $dbh->lastInsertId();

    $idarticle = $dbh->query("SELECT idclient,p.idarticle,nom,quantite,prixttc,enremise,prixpromo FROM {$prefix}_panier p inner join {$prefix}_article a on p.idarticle=a.idarticle LEFT OUTER JOIN {$prefix}_remise ON {$prefix}_article.idarticle={$prefix}_remise.idarticle  WHERE idclient=$_SESSION[idclient];");
    foreach ($idarticle as $row) {
        print_r($row);
        if ($row["enremise"]) {
            $insertQuantite = $dbh->prepare("INSERT INTO {$prefix}_quantite(idarticle,idcommande,quantite,prixachat)
                VALUES(?,?,?,?);");
            $insertQuantite->execute(array($row["idarticle"], $lastIDCommande, $row["quantite"], $row["prixpromo"]));
            $update->execute(array($row["quantite"], $row["idarticle"]));
        } else {
            $insertQuantite = $dbh->prepare("INSERT INTO {$prefix}_quantite(idarticle,idcommande,quantite,prixachat)
                VALUES(?,?,?,?);");
            $insertQuantite->execute(array($row["idarticle"], $lastIDCommande, $row["quantite"], $row["prixttc"]));
            $update->execute(array($row["quantite"], $row["idarticle"]));
        }
    }

    $nb = $dbh->prepare("delete from {$prefix}_panier where idclient=?");
    $nb->execute(array($_SESSION["idclient"]));
    $_POST["valide"] = FALSE;
}

//<-----debut php pour l'ajout au panier-------> 
if (isset($_POST["ajouter"])) {
    if (isset($_SESSION["idclient"])) {
        //danger
        $res = $dbh->query("SELECT count(*) from {$prefix}_panier where idclient=$_SESSION[idclient] and idarticle=$_POST[idarticle]")->fetchColumn();
        if ($res == 0) { //si il n'y a pas deja un panier de cette article pour ce client 
            $req = $dbh->prepare("INSERT INTO {$prefix}_panier(idclient,idarticle,quantite) values (?,?,?)");
            $req->execute(array($_SESSION["idclient"], $_POST["idarticle"], 1));
        } else {
            $quantite = $dbh->query('SELECT quantite from ' . $prefix . '_panier where idclient=' . $_SESSION["idclient"] . ' and idarticle=' . $_POST["idarticle"])->fetchColumn();
            $req = $dbh->prepare('UPDATE ' . $prefix . '_panier set quantite=? where idclient=' . $_SESSION["idclient"] . ' and idarticle=' . $_POST["idarticle"]);
            $req->execute(array($quantite + 1));
        }
    } else {
        $dejaDansPanier = false;
        if (!empty($_SESSION["panier"])) {
            foreach ($_SESSION["panier"] as $row) {
                if ($row["id"] == $_POST["idarticle"]) {
                    $dejaDansPanier = true;
                }
            }

            if (!$dejaDansPanier) {
                array_push($_SESSION["panier"], (array("id" => $_POST["idarticle"], "quantite" => 1)));
            }
        } else {
            $_SESSION["panier"] = array(array("id" => $_POST["idarticle"], "quantite" => 1));
        }
    }
}
//<----- fin php pour l'ajout au panier------->

function afficheArticle($idarticle)
{ //fonction pour afficher un article 
    global $dbh, $prefix;
    $article = $dbh->query("SELECT * FROM {$prefix}_image natural join {$prefix}_article LEFT OUTER JOIN {$prefix}_remise ON {$prefix}_article.idarticle={$prefix}_remise.idarticle where {$prefix}_article.idarticle=$idarticle")->fetch();

    echo '<div class="article">';

    echo '<form action="article.php" method="POST">';
    echo '<input type="hidden" name="detailArticle" value="' . $idarticle . '">';
    echo '<input type="image" src="' . $article["urlimage"] . '" width=150px height=150px name="detailArticle" value="' . $article["idarticle"] . '">';
    echo '</form>';

    if (isset($article['remise'])) { // si il y a une promo 
        echo '<div class="bloc-reduc">';
        $reduc = floatval($article["remise"]);
        echo '<p>-' . $reduc . '%</p>';
        echo '</div>';

        echo '<div class="bloc-prix vcontainer">';
        echo '<h4><del style="color:red;">' . $article["prixttc"] . '€</del></h4><h3>' . $article["prixpromo"] . '€.</h3>';
        echo '</div>';
    } else {
        echo '<div class="bloc-prix">';
        echo '<h3><span class="prixTTC">' . $article["prixttc"] . '</span>€</h3>';
        echo '</div>';
    }




    echo '<div class="conteneur">';
    echo '<form action="article.php" method="POST">';
    echo '<input type="hidden" name="detailArticle" value="' . $idarticle . '">
                                    <input type="submit" class="titre" name="nom" value="' . $article["nom"] . '">                            
                                </form>';


    //bouton pannier
    echo '<form action="" method="POST">
                                    <input type="hidden" name="idarticle" value="' . $idarticle . '">
                                    <input type="hidden" name="ajouter" value=true>
                                    <input type="image" src="images/icon/ajoutPanier.png" alt="Submit" width="48" height="48" name="ajouter" class="ajoutPanier">
                                </form>';
    echo '</div>';
    echo '</div>';
}

function afficheArticleCarrousel($idarticle)
{
    global $dbh, $prefix;
    $article = $dbh->query("SELECT * FROM {$prefix}_image natural join {$prefix}_article LEFT OUTER JOIN {$prefix}_remise ON {$prefix}_article.idarticle={$prefix}_remise.idarticle where {$prefix}_article.idarticle=$idarticle")->fetch();
    
    echo '<div class="slider">';

    echo '<form action="article.php" method="POST">';
    echo '<input type="hidden" name="detailArticle" value="' . $idarticle . '">';
    echo '<input type="image" src="' . $article["urlimage"] . '" width=150px height=150px name="detailArticle" value="' . $article["idarticle"] . '">';
    echo '</form>';

    echo '</div>';
    
}

function afficheCategorie($numcategorie)
{
    global $dbh, $prefix;
    $categorie = $dbh->query("SELECT * FROM {$prefix}_categorie WHERE {$prefix}_categorie.idcategorie = $numcategorie")->fetch();

    echo '<div class="img_categorie">';
    echo '<div class="container_categorie">';
    echo '
        <a href="categorie.php?categorie=' . $categorie["libelle"] . '"><img src="' . $categorie["imgcategorie"] . '"alt="img_categorie"></a></li>
    ';
    echo '</div>';
    echo '</div>';
}

if (!empty($_POST["annule"])) {
    header('Location: ' . $_SERVER['PHP_SELF']);
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
    <link rel="stylesheet" type="text/css" href="style/styleIndex.css">
</head>
<!-- corps -->

<body>
    <header id='haut'>
        <?php include 'entete.php' ?>
    </header>

    <main>
        <?php
        if (isset($_POST["ajouter"])) {
            echo '<p id="popInsCon">Article ajouter au panier</p>';
        }

        if (isset($_SESSION["deco"])) {
            unset($_SESSION["deco"]);
            echo '<p id="popInsCon">Vous êtes déconnecté</p>';
        }

        if (isset($_SESSION["connecter"])) {
            unset($_SESSION["connecter"]);
            echo '<p id="popInsCon">Vous êtes connecté</p>';
        }
        ?>


        <div class="banniere">
            <img src="images/banniere/banniere_edit.png" alt="banniere" width="100%">
        </div>

        <div class="bloc_categorie">
            <?php
            for ($i = 1; $i < 6; $i++) {
                afficheCategorie($i);
            }
            ?>
        </div>

        <div class="liste">
            <div id="entete">
                <h2 id="libelle">Tendance</h2>
            </div>
            <div class="images">
                <?php
                $statement = $dbh->query("SELECT * from {$prefix}_article a INNER JOIN {$prefix}_image i on a.idarticle=i.idarticle  where a.enpromotion = true");
                foreach ($statement as $row) {
                    afficheArticle($row['idarticle']);
                }
                ?>
            </div>
        </div>

        <div class="liste">
            <div id="entete">
                <h2 id="libelle">Remise</h2>
            </div>

            <div class="images">
                <?php
                $statement = $dbh->query("SELECT * from {$prefix}_article natural join {$prefix}_remise natural join {$prefix}_image");
                //$statement = $dbh->query("SELECT * from {$prefix}_article natural join {$prefix}_promotion");
                foreach ($statement as $row) {
                    if (dateValide($row["date_debut"], $row["date_fin"]) == true) {
                        afficheArticle($row['idarticle']);
                    }
                }
                ?>
            </div>
        </div>
    </main>
    <footer>
        <?php include 'piedpage.php' ?>
    </footer>



</body>

</html>