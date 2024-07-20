
<?php
    session_start();
    //$idclient=$_SESSION["idclient"];
    include('connect_params.php');
    $prefix="sae301_a21.";
    /*if(isset($_POST["supression"])){
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        $comm = $dbh->prepare("DELETE from a21._client where idclient='$idclient'");
        $comm->execute();
        unset($_SESSION["idclient"]);
        header("Location: http://localhost:8080/index.php");    
    }

    if(isset($_POST["deconnexion"])){
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        unset($_SESSION["idclient"]);
        header("Location: http://localhost:8080/index.php");    

    }
    $idclient=$_SESSION["idclient"];*/

    

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
        <link rel="stylesheet" type="text/css" href="style/styleProfilUser.css"> 
    </head>
    <!-- corps -->
    <body>
        <header> 
                <div class="haut">      
                    <a href="index.php"><img class="logo" src="images/logo/logofiniresizes.png" alt="logo alizon" title="logo alizon" ></a>
                    <h1 style="flex-grow:0;"><a href="index.php">ALIZON</a></h1><h1 style="color:white; font-size:120%; margin:0; margin-top:0.5%;">ADMIN</h1>
                
                </div> 
                
        </header>
        <h1 class="tit">Profil Utilisateur</h1>
        <main>
            
            <div class="centre">
                <div class="detail">
                    <?php
                    try {
                            $sum=0;
                            
                            $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
                            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
                            $idclient=$_POST["idclient"];
                            
                        $comm = $dbh->prepare("SELECT * from {$prefix}_client where idclient='$idclient'");
                        $comm->execute();
                        foreach ($comm as $row){
                            echo '<span>Nom : '.$row['nom'].' </span>
                            <span>Prénom : '.$row['prenom'].'</span>
                            <span>Email : '.$row['email'].'</span>
                            <span>Numéro : '.$row['numtel'].'</span>
                            <span>Date de naissance : '.$row['datenaissance'].'</span>';
                        }
                        
                    } catch (PDOException $e) {
                        print "Erreur !: " . $e->getMessage() . "<br/>";
                        die();
        
                    }
                        
                        echo'
                        </div>
                        <div class="boutons">
                            <form action="modifCompteAdmin.php" method="POST"> 
                                <input type="hidden" name="idclientmodif" value="'.$idclient.'"/>
                                <input class="bouton" type="submit" name="ajouter" value="Modifier le profil" ></input>
                            </form>
                        </div>
                        ';
                    ?>
               
            
        </main>
        <footer>
            <div class="back">
                <a href="index.php">Retour en haut</a>
            </div>
            <div class="bas">
                <div><h2>Réseaux sociaux entreprise</h2>
                    <img src="images/icon/iconFacebook.png" alt="logo facebook" title="logo facebook">
                    <img src="images/icon/logoInstagram.png" alt="logo instagram" title="logo instagram">
                    <img src="images/icon/logoTwitter.png" alt="logo twitter" title="logo twitter">
                </div>
                <div>
                    <h2>Moyen de paiement</h2>
                    <img src="images/icon/visa" alt="logo visa" title="logo visa">
                    <img src="images/icon/cbIcon.png" alt="logo cb" title="logo cb">            
                    <img src="images/icon/paypalIcon.png" alt="logo paypal" title="logo paypal">
                </div>
                <div>
                    <h2>Mode de livraison</h2>
                    <img id="colissimo"src="images/icon/colissimoLogo.png" alt="logo colissimo" title="logo colissimo">
                </div>  
                
            </div>
            <div class="bas">
            <p><a>CGU</a> - <a>Mentions légales</a> - <a>À propos</a> - <a>Nous contacter</a></p></div>
        </footer>
    </body>   
</html>
