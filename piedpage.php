<div class="back">
    <a href='#haut'>Retour en haut</a>
</div>
<div class="bas">
    <div><h2>Réseaux sociaux entreprise</h2>
        <img src="images/icon/iconFacebook.png" alt="logo facebook" title="logo facebook">
        <img src="images/icon/logoInstagram.png" alt="logo instagram" title="logo instagram">
        <img src="images/icon/logoTwitter.png" alt="logo twitter" title="logo twitter">
    </div>
    <div>
        <h2>Moyen de paiement</h2>
        <img src="images/icon/visa.png" alt="logo visa" title="logo visa">
        <img src="images/icon/cbIcon.png" alt="logo cb" title="logo cb">            
        <img src="images/icon/paypalIcon.png" alt="logo paypal" title="logo paypal">
    </div>
    <div>
        <h2>Mode de livraison</h2>
        <img id="colissimo"src="images/icon/colissimoLogo.png" alt="logo colissimo" title="logo colissimo">
    </div>  
    
</div>
<div class="bas">
<p><a href="./fichierpdf/cgu_cgv.pdf">CGU</a> - <a href="./fichierpdf/mention_legales.pdf">Mentions légales</a> - <a href="index.php">Accueil</a>- <a>À propos</a> - <a>Nous contacter</a></p></div>
<div class="responsive">
<a href="index.php"><img class="home" src="images/icon/homelogo.png" alt="logo home" title="logo home"></a>
    <div class="panierdiv" style="position: relative;">
        <a href="panier.php">
            <img class="panier" src="images/icon/panierLogo.png"  alt="logo panier" title="logo panier" >
            <?php
                if(isset($_SESSION["idclient"])){
                    if(getNbArticlePanier($_SESSION["idclient"]) != 0){ //affiche le nombre d'article dans le panier uniquement si il y a des articles dans le panier
                        echo '<p class="nombrepanier">'. getNbArticlePanier($_SESSION["idclient"]).'</p>';
                    }
                }else{ //affiche le panier avec la variable de session
                    if(empty($_SESSION["panier"])){
                        $nb_article=0;
                    }
                    else{
                        $nb_article = sizeof($_SESSION["panier"]);
                    }
                    if($nb_article != 0){ 
                        echo '<p class="nombrepanier">'.$nb_article.'</p>';
                    }
                }
            ?>
        </a>
    </div>
    <?php
        if(isset($_SESSION["idclient"])){
            echo '<a href="monProfil.php"><img class="iconprof" src="images/icon/monProfil.png" alt="logo monProfil" title="logo monProfil"></a>';
        }else{
            echo '<a href="connexion.php"><img class="connexion" src="images/icon/creationCompte.png" alt="logo connexion" title="logo connexion"></a>';
        }
    ?>
    
</div>
