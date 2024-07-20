<?php session_start();
    $prefix="sae301_a21.";
    include('connect_params.php');
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $sth=$dbh->prepare("INSERT INTO {$prefix}_client(nom,prenom,email,numtel,datenaissance,motdepasse) VALUES(?,?,?,?,?,?);");
    $email=false;
    $mdp=false;
    function chiffrement_mdp($mdp) {
        $cle="Amogus21";
    
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
        $mail=$dbh->prepare("select * from {$prefix}_client where email=?");
        $mail->execute(array($_POST["email"]));
        $count=$mail->rowCount();
        if($count==1){
            $email=true;
        }
    }
    if(isset($_POST["email"])&&$email){
        $mdp=$dbh->prepare("select motdepasse from {$prefix}_client where email=?");
        $mdp->execute(array($_POST["email"]));
        foreach($mdp as $row){
            $motdepasse=$row["motdepasse"];
        }
        if($_POST["mdp"]==$motdepasse){
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
    <link rel="stylesheet" type="text/css" href="style/styleMdpOublie.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    
</head>
<header>
    <?php include 'enteteReduit.php' ?>
</header>
<body>

    <main>

        <?php
            echo '<div class="formulaire">
                <form id="formulaire" method="post" action="">
                    <h2>Mot de passe oublié</h2>
                    <label>Nouveau mot de passe</label></br><input type="password" name="new_mdp" /></br>
                    <label>Confirmation du nouveau mot de passe </label></br><input type="password" name="conf_new_mdp" /></br>';
                if(isset($_POST['update_mdp'])){
                $new_mdp=$_POST['new_mdp'];
                $conf_new_mdp=$_POST['conf_new_mdp'];
                    if (($new_mdp!='')&&($conf_new_mdp!='')) {
                            if($new_mdp==$conf_new_mdp){
                            $new_mdp=chiffrement_mdp($new_mdp);
                            $mod_mdp=$dbh->query("UPDATE {$prefix}_client SET motdepasse='$new_mdp' WHERE email='$_SESSION[email]'");
                            echo 'Modification du mot de passe effectuee avec succes';
                            $_SESSION['password']=$new_mdp;
                            $_SESSION['passValide']=true;
                            header("Location: ./connexion.php");

                            } else {
                                echo '<p class="erreur">Erreur, les mots de passe ne correspondent pas</p>';
                            }
                        }
                    }
                
                echo '<div class="boutons"><input class="valider" type="submit" name="update_mdp" value="Mettre à jour le mot de passe" /></div>';
        ?>
        
    </main>
    
</body>

<footer>
    <?php include 'piedpageReduit.php' ?>
</footer>
</html>

