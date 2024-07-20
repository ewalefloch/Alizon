<?php session_start();
    $prefix='sae301_a21.';

    include('connect_params.php');
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $sth=$dbh->prepare("INSERT INTO {$prefix}_client(nom,prenom,email,numtel,datenaissance,motdepasse) VALUES(?,?,?,?,?,?);");
    $email=false;
    $mdp=false;
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

    function getEmail($email_form){
        global $dbh, $prefix; 
        return $dbh->query("SELECT email FROM {$prefix}_client WHERE email='$email_form'")->fetchColumn();
    }

    function lien(){
        $url = "";
        $longueur = rand(10,30);
        $string_debut = "alizon.com/";
        $string_rand = "";
        $chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for($i=0; $i<$longueur; $i++) {
            $string_rand .= $chaine[rand()%strlen($chaine)];
        }
        $url .= $string_debut . $string_rand;
        echo '<div class="lien">
                <p>Lien pour change le mot de passe :</p>
                <a href="mdpOublie.php"">'.$url.'</a>
            </div>';
            
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
            $error = 1;
            echo '<div class="formulaire">
                <form id="formulaire" method="post" action=""> 
                    <h2>Mot de passe oublié</h2>
                    <label>Mail du compte</label></br><input type="email" name="email_form" /></br>';
                if(isset($_POST['send_email'])){
                $email_form=$_POST['email_form'];
                $email_bdd = getEmail($email_form);
                    if ($email_bdd == $email_form) {
                            $_SESSION['email'] = $email_form;
                            $error = 0;                            
                    }else {
                        echo '<p class="erreur">L\'email est incorrect</p>';
                        $error = 1;
                    }
                }
                echo '<div class="boutons"><input class="valider" type="submit" name="send_email" value="Envoyer le mail" /></div>';
                if ($error == 0) {
                    lien();
                }

        ?>
        
    </main>
    
</body>

<footer>
    <?php include 'piedpageReduit.php' ?>
</footer>
</html>

