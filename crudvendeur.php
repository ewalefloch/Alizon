<!DOCTYPE html>
<?php
    include('connect_params.php');
    session_start();
    $prefix = 'a21.';
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" type="text/css" href="style/stylevendeurCRUD.css" />
</head>
<body>
    <header> 
            <div class="haut">      
            <div class="haut">      
                <a href="index.php"><img class="logo" src="images/logo/logofiniresizes.png" alt="logo alizon" title="logo alizon" ></a>
                <h1 style="flex-grow:0;"><a href="crudvendeur.php">ALIZON</a></h1><h1 style="color:white; font-size:120%; margin:0; margin-top:0.5%;">PRO</h1>
            </div> 
            <div class="nav">
                <a href="index.php" class= "adroite">Acceuil</a>
                <a href="crudvendeur.php" class= "adroite">Menu</a>
            </div>
            </div> 
            
    </header>
    <main>
        

            <div id="boutons">
                <a href="createArticle.php">Créer un article</a>
                <a href="importArticle.php">Importer des articles</a>
                <a href="lesCommandes.php">Liste des commandes clients</a>
                <a href="miseEnAvant.php">Promouvoir un article</a>
                <a href="creerRemise.php">Créer une remise</a>

            </div>
        

    </main>
    <footer>
        <div class="back">
            <a href="index.php">Retour en haut</a>
        </div>
        <div class="bas">
            <div><h2>Réseaux sociaux entreprise</h2>
                <a href="https://www.facebook.com/"><img src="images/icon/iconFacebook.png" alt="logo facebook" title="logo facebook"></a>
                <a href="https://www.instagram.com/"><img src="images/icon/logoInstagram.png" alt="logo instagram" title="logo instagram"></a>
                <a href="https://www.twitter.com/"><img src="images/icon/logoTwitter.png" alt="logo twitter" title="logo twitter"></a>
            </div>
            <div>
                <h2>Moyen de paiement</h2>
                <a href="https://www.visa.fr/"><img src="images/icon/visa" alt="logo visa" title="logo visa"></a>
                <a href="https://www.cartes-bancaires.com/"><img src="images/icon/cbIcon.png" alt="logo cb" title="logo cb"></a>       
                <a href="https://www.paypal.com/"><img src="images/icon/paypalIcon.png" alt="logo paypal" title="logo paypal"></a>
            </div>
            <div>
                <h2>Mode de livraison</h2>
                <a href="https://www.laposte.fr/colissimo"><img id="colissimo"src="images/icon/colissimoLogo.png" alt="logo colissimo" title="logo colissimo"></a>
            </div>  
            
        </div>
        <div class="bas">
        <p><a>CGU</a> - <a>Mentions légales</a> - <a>À propos</a> - <a>Nous contacter</a></p></div>
    </footer>
</body>
</html>
