
<?php session_start();
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$prefix = 'sae301_a21.';

$sth=$dbh->prepare("INSERT INTO {$prefix}_client(nom,prenom,email,numtel,datenaissance,motdepasse,idadresse) VALUES(?,?,?,?,?,?,?);");
$adresse=$dbh->prepare("INSERT INTO {$prefix}_adresse(codepostal,nomrue,numrue,infocomplementaire,ville) VALUES(?,?,?,?,?);");
$email=true;
$mdp=true;
if(isset($_POST["email"])){
    $mail=$dbh->prepare("select * from {$prefix}_client where email=?");
    $mail->execute(array($_POST["email"]));
    $count=$mail->rowCount();
    if($count!=0){
        $email=false;
    }
}
if(isset($_POST["mdp"])&&isset($_POST["mdpconfirm"])){
    if($_POST["mdp"]!=$_POST["mdpconfirm"]){
        $mdp=false;
    }
}?>

<?php
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
      
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <meta charset="utf-8" />
    <title>ALIZON</title>
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/styleInscription.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
</head>

<body>
<header>
    <?php include 'enteteReduit.php' ?>
</header>
    <main>
    <?php
            echo '<div class="formulaire">';
                    if(!empty($_POST["nom"])&&!empty($_POST["prenom"])&&!empty($_POST["tel"])&&!empty($_POST["date"])&&!empty($_POST["email"])&&!empty($_POST["mdp"])&&!empty($_POST["numrue"])&&!empty($_POST["nomrue"])&&!empty($_POST["ville"])&&!empty($_POST["codepostal"])&&$mdp==true&&$email==true){
                        $adresse->execute(array($_POST["codepostal"],$_POST["nomrue"],$_POST["numrue"],$_POST["info"],$_POST["ville"]));
                        $idadresse=$dbh->lastInsertId();
                        $_POST["mdp"]=chiffrement_mdp($_POST["mdp"],$cle);
                        $sth->execute(array($_POST["nom"],$_POST["prenom"],$_POST["email"],$_POST["tel"],$_POST["date"],$_POST["mdp"],$idadresse));
                        echo' <form id="formulaire" method="post" action="inscription.php">';
                        $_SESSION["connexionValide"]=true;
                        header("Location: ./connexion.php");    
                    }
                    else{
                        echo'<form id="formulaire" method="post" action="inscription.php">';
                    }
                        if(isset($_POST["nom"])){                    
                        echo'
                        <h1>Inscription</h1>
                        <label>Nom</label></br><input type="text" name="nom" value="'.$_POST["nom"].'"/>';
                        if (empty($_POST["nom"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Prénom</label></br><input type="text" name="prenom" value="'.$_POST["prenom"].'"/>';
                        if (empty($_POST["prenom"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Adresse email</label></br><input type="text" name="email" value="'.$_POST["email"].'"/>';
                        if (empty($_POST["email"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        if(!$email){
                            echo'<p>Email déjà utilisé</p>';
                        }
                        echo'<label>Mot de passe </label></br><input type="password" name="mdp" />';
                        if (empty($_POST["mdp"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Confirmer mot de passe</label></br><input type="password" name="mdpconfirm" />';
                        if (empty($_POST["mdpconfirm"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        if(!$mdp){
                            echo'<p>Mots de passe différents</p>';
                        }
                    
                    echo'
                        <label>Numéro de téléphone</label></br><input type="tel" name="tel" pattern="[0-9]{10}" required value="'.$_POST["tel"].'"/>';
                        if (empty($_POST["tel"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Date de naissance</label></br><input type="date" name="date" value="'.$_POST["date"].'"/>';
                        if (empty($_POST["date"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        
                        echo'<label>Numéro de rue</label></br><input type="number" name="numrue" value="'.$_POST["numrue"].'"/>';
                        if (empty($_POST["numrue"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        
                        echo'<label>Nom de rue</label></br><input type="text" name="nomrue" value="'.$_POST["nomrue"].'"/>';
                        if (empty($_POST["nomrue"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                       echo'<label>Informations Complémentaires</label></br><input type="text" name="info" value="'.$_POST["info"].'"/>
                        <label>Code Postal</label></br><input type="text" name="codepostal" value="'.$_POST["codepostal"].'"/>';
                        if (empty($_POST["codepostal"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Ville</label></br><input type="text" name="ville" value="'.$_POST["ville"].'"/>';
                        if (empty($_POST["ville"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }

                        
                    }else{
                        echo'
                        <h1>Inscription</h1>
                        <label>Nom</label></br><input type="text" name="nom" />
                        <label>Prénom</label></br><input type="text" name="prenom" />
                        <label>Adresse email</label></br><input type="text" name="email" />';
                        if(!$email){
                            echo'<p>Email déjà utilisé</p>';
                        }
                        echo'<label>Mot de passe </label></br><input type="password" name="mdp" />
                        <label>Confirmer mot de passe</label></br><input type="password" name="mdpconfirm" />';
                        if(!$mdp){
                            echo'<p>Mots de passe différents</p>';
                        }
                    
                    echo'
                        <label>Numéro de téléphone</label></br><input type="tel" name="tel" />
                        <label>Date de naissance</label></br><input type="date" name="date" />
                        
                        <label>Numéro de rue</label></br><input type="number" name="numrue" />
                        <label>Nom de rue</label></br><input type="text" name="nomrue" />
                        <label>Informations Complémentaires</label></br><input type="text" name="info" />
                        <label>Code Postal</label></br><input type="text" name="codepostal" />
                        <label>Ville</label></br><input type="text" name="ville"/>

                       ';
                    }
                    echo'
                    <div><label>En créant votre compte, vous acceptez les <a href="">conditions générales de vente</a> d\'Alizon.</label></div>
                        
                    <div class="boutons"><input class="valider" type="submit" value="Créer un compte" /></div>
                    </form>';
        ?>
       
    </main>
</body>
<footer>
    <?php include 'piedpageReduit.php' ?>
</footer>
</html>
