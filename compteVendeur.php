<!DOCTYPE html>
<?php
    include('connect_params.php');
    session_start();
    if(!isset($_SESSION["idvendeur"])){
        header('Location: ./connexionVendeur.php');    
      }
    $prefix = 'sae301_a21.';
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);

    if(isset($_POST["deconnexion1"])){
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        unset($_SESSION["idvendeur"]);
        $_SESSION["deconnexion1"]=true;
        header("Location: ./connexionVendeur.php");   
         

    }


?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">        
    <link rel="stylesheet" type="text/css" href="style/headerpro.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    <link rel="stylesheet" type="text/css" href="style/styleCompteVendeur.css"> 
    <title>Document</title>
</head>
<body>
    <header>
    <?php include 'entetePro.php'; ?>
    </header>
    <main>
    <?php

    //Récuperation depuis la base de données
    $statement = $dbh->query("SELECT * from {$prefix}_vendeur natural join {$prefix}_adresse where idvendeur=$_SESSION[idvendeur]", PDO::FETCH_ASSOC);

    ?>
        <div id="gauche">
            <!-- Bloc 1-->
            <div  class="bloc">
                <a class="lien" href="articlesVendeur.php"><img src="images/imgArticle/eye.jpg" alt="Image article"></a>
                <nav class="nav">
                    <a class="lien" href="articlesVendeur.php">Consulter mes articles </a>  
                </nav>
            </div>
            <!-- Bloc 2-->
            <div class="bloc">
               <a class="lien" href="Createoffre.php"> <img src="images/imgArticle/plus.jpg" alt="Image article"></a>
                <nav class="nav">
                    <a class="lien" href="Createoffre.php">Ajouter un article</a>  
                </nav>
            </div>
            <!-- Bloc 3-->
            <div class="bloc">
                <a class="lien" href="creerRemise.php"><img src="images/icon/pc.webp" alt="Image article"></a>
                <nav class="nav">
                    <a class="lien" href="creerRemise.php">Remises</a>  
                </nav>
            </div>
            <!-- Bloc 4-->
                <div class="bloc">
                <a  class="lien" href="miseEnAvant.php"><img src="images/imgArticle/meg.jpg" alt="Image article"></a>
                    <nav class="nav">
                        <a class="lien" href="miseEnAvant.php">Promotions </a>  
                    </nav>
                </div>
                <!-- Bloc 5-->
                <div class="bloc">
                <a class="lien" href="importArticle.php"><img src="images/icon/cat.png" alt="Image article"></a>
                    <nav class="nav">
                        <a class="lien" href="importArticle.php">Importer un catalogue</a>  
                    </nav>
                </div>
            
        </div>
    
        <!-- Partie droite-->
        <?php
         foreach ($statement as $row){
        echo'
        <div id="droite">
            <div class="blocForm"> 
                <img id="logo" src="'.$row["imglogo"].'" alt="logo entreprise">
                <form class ="form" method="POST" action="">
                    <h2>Vos Informations</h2>
                    <label>Nom Entreprise</label></br><input type="text" class ="boutForm" name="nomrue" value="'.$row["nom"].'" readonly/></br>
                    <label>Adresse</label></br><input type="text" class ="boutForm" name="nomrue" value="'.$row["numrue"].' '.$row["nomrue"].'" readonly/></br>
                    <label>Code postal / Ville</label></br><input type="text" class ="boutForm" name="nomrue" value="'.$row["codepostal"].' '.$row["ville"].'" readonly/></br>
                    <label>Mail</label></br><input type="text" class ="boutForm" name="nomrue" value="'.$row["email"].'" readonly/></br>
                    <label>TVA / Numéro de Siret</label></br><input type="text" class ="boutForm" name="numrue" value="'.$row["numtva"].''.$row["siret"].'" readonly/></br>
                    <label>Texte de présentation</label></br><input type="text" class ="boutForm" name="numrue" value="'.$row["textepresentation"].'" readonly/></br>
                </form>
                <div id="note">
                    <p><label>Satisfaction vendeur : </label>'.$row["note"].'/5</p>
                </div>
  
                <div class="boutons">
                    <a href="profilVendeur.php" class="valider"  />Mon profil</a>
                    <form action="" method="POST"> 
                    <input type="hidden" name="deconnexion1" value="true"/>
                    <input class="valider2" type="submit" name="ajouter" value="Deconnexion" ></input>
                    </form> 
                </div>
            </div>
            
        </div>';
         }
        ?>
    </main>
</body>
</html>
