<?php session_start();
    $prefix='sae301_a21.';
    include('connect_params.php');
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $email=false;
    $mdp=false;

    $cle="Amogus21";

    function chiffrement_mdp($mdp, $cle) {
        // Convertir la clé en majuscules et la mot de passe en minuscules
        $cle= strtoupper($cle);
        $mdp= strtolower($mdp);
              $mdpChiffrement= "";
      
        // Pour chaque caractère du mot de passe
        for ($i = 0; $i < strlen($mdp); $i++) {
          // Récupérez le code ASCII du caractère de mot de passe et de la clé
          $p = ord($mdp[$i]) - 97;
          $k = ord($cle[$i % strlen($cle)]) - 65;
      
          // Ajoutez la clé au code ASCII du caractère de mot de passe et modulo par 26
          // pour obtenir un nouveau caractère compris entre 0 et 25
          $mdpChiffrement.= chr((($p + $k) % 26) + 97);
        }
      
        return $mdpChiffrement;
      }
    
    if(isset($_POST["email"])){
        $mail=$dbh->prepare("select * from {$prefix}_vendeur where email=?");
        $mail->execute(array($_POST["email"]));
        $count=$mail->rowCount();
        if($count==1){
            $email=true;
        }
    }
    if(isset($_POST["email"])&&$email){
        $mdp=$dbh->prepare("select mdp from {$prefix}_vendeur where email=?");
        $mdp->execute(array($_POST["email"]));
        foreach($mdp as $row){
            $mdp=$row["mdp"];
        }
        $_POST["mdp"]= chiffrement_mdp($_POST["mdp"],$cle);
        if($_POST["mdp"]==$mdp){
            $mdp=true;
        }else{
            $mdp=false;}
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
    <link rel="stylesheet" type="text/css" href="style/styleConnexion.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    <link rel="stylesheet" type="text/css" href="stylePopUp.css">
    
</head>
<header>
    <?php include 'enteteReduit.php' ?>
</header>
<body>

    <main>
        <?php
        if (isset($_SESSION["deconnexion1"])) {
            unset($_SESSION["deconnexion1"]);
            echo '<div id="popup">
            <span id="close-popup">&times;</span>
            <p>déconnecté</p>
        </div>
        ';
        }
        if(isset($_SESSION["connexionVendeurValide"])){
            unset($_SESSION["connexionVendeurValide"]);
            echo'
            <div id="popup">
                <span id="close-popup">&times;</span>
                <p>Inscription réussis</p>
            </div>
            ';
        }
        ?>
        <?php
            if(isset($_SESSION['passValide'])){
                unset($_SESSION["passValide"]);
                echo '<p id="popInsCon"> Mot de passe modifier </p>';
            }
        ?>

        <?php
            echo '<div class="formulaire">
                <form id="formulaire" method="post" action="connexionVendeur.php">
                    <h2>S\'identifier</h2>
                    <label>Adresse email</label></br><input type="email" name="email" /></br>
                    <label>Mot de passe </label></br><input type="password" name="mdp" /></br>';
            if(!empty($_POST["email"]) && !empty($_POST["mdp"])){
                if($mdp==false){
                    echo '<div id="mdp"> Mot de passe ou email incorrect </div>';
                }
                else{
                    $id=$dbh->prepare("select idvendeur from {$prefix}_vendeur where email = ?");
                    $id->execute(array($_POST["email"]));
                    foreach($id as $row){
                        $idvendeur=$row["idvendeur"];
                    }
                    $_SESSION["idvendeur"]=$idvendeur;
                    $_SESSION["connecter"]=true;
                    header("Location: ./compteVendeur.php");
                }
            }
            echo '
                    <a href="mailmdpOublie.php">Mot de passe oublié ?</a></br>
                    <div class="boutons"><input class="valider" type="submit" value="Connexion" /></div>
                    <p>En passant commande, vous acceptez les <a href="">conditions générales de vente</a> d\'Alizon.</p>
                </form>
            </div>';
        ?>

        <script>

        const popup = document.getElementById("popup");
        const closePopup = document.getElementById("close-popup");

        popup.style.display = "block";
        popup.classList.add("show");

        setTimeout(function() {
        popup.style.display = "none";
        }, 20000);

        closePopup.addEventListener("click", function() {
        popup.style.display = "none";
        });

        </script>
        <script>
            const popup = document.getElementById("popup");
            const closePopup = document.getElementById("close-popup");

            popup.style.display = "block";

            setTimeout(function() {
            popup.style.display = "none";
            }, 2000000);

            closePopup.addEventListener("click", function() {
            popup.style.display = "none";
            });
        </script>

        
    </main>
    
</body>

<footer>
    <?php include 'piedpageReduit.php' ?>
</footer>
</html>
