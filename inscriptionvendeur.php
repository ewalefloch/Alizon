
<?php session_start();
include('connect_params.php');
$dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$prefix = 'sae301_a21.';

$sth=$dbh->prepare("INSERT INTO {$prefix}_vendeur(nom,email,mdp,idadresse,numtva,siret,textepresentation,imglogo) VALUES(?,?,?,?,?,?,?,?);");
$adresse=$dbh->prepare("INSERT INTO {$prefix}_adresse(codepostal,nomrue,numrue,infocomplementaire,ville) VALUES(?,?,?,?,?);");
$email=true;
$mdp=true;
if(isset($_POST["email"])){
    $mail=$dbh->prepare("select * from {$prefix}_vendeur where email=?");
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
    <div class="haut">      
        <a href="index.php"><img class="logo" src="images/logo/logofiniresizes.png" alt="logo alizon" title="logo alizon" ></a>
        <h1 style="flex-grow:0;"><a href="crudvendeur.php">ALIZON</a></h1><h1 style="color:white; font-size:120%; margin:0; margin-top:0.5%;">ADMIN</h1>
    </div>  
</header>
    <main>
    <?php
            echo '<div class="formulaire">';
                    if(!empty($_POST["nom"])&&!empty($_POST["texte"])&&!empty($_POST["siret"])&&!empty($_POST["numtva"])&&!empty($_POST["email"])&&!empty($_POST["mdp"])&&!empty($_POST["numrue"])&&!empty($_POST["nomrue"])&&!empty($_POST["ville"])&&!empty($_POST["codepostal"])&&$mdp==true&&$email==true){
                        $adresse->execute(array($_POST["codepostal"],$_POST["nomrue"],$_POST["numrue"],$_POST["info"],$_POST["ville"]));
                        $idadresse=$dbh->lastInsertId();
                        $_POST["mdp"]=chiffrement_mdp($_POST["mdp"],$cle);
                        $sth->execute(array($_POST["nom"],$_POST["email"],$_POST["mdp"],$idadresse,$_POST["numtva"],$_POST["siret"],$_POST["texte"],"images/logoVendeur/".$_FILES["file"]["name"]));
                        echo' <form id="formulaire" method="post" action="inscriptionvendeur.php">';
                        $_SESSION["connexionVendeurValide"]=true;
                        header("Location: ./connexionVendeur.php");    
                    }
                    else{
                        echo'<form id="formulaire" method="post" action="inscriptionvendeur.php" enctype="multipart/form-data">';
                    }
                    if(isset($_POST["nom"])){                    
                        echo'
                        <h1>Création compte Vendeur</h1>
                        <label>Nom</label></br><input placeholder="Ex : Jean " type="text" name="nom" value="'.$_POST["nom"].'"/>';
                        if (empty($_POST["nom"])) {
                            echo'<p>* Champ obligatoire Exemple : Jean</p>';
                        }
                        echo'<label>Adresse email</label></br><input type="text" name="email" value="'.$_POST["email"].'"/>';
                        if (empty($_POST["email"])) {
                            echo'<p>* Champ obligatoire Exemple : emali.alizon@gmail.com</p>';
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
                        echo'<label>Numéro TVA intracommunautaire</label></br><input placeholder="Ex : FR" type="text" name="numtva" value="'.$_POST["numtva"].'"/>';
                        if (empty($_POST["numtva"])) {
                            echo'<p>* Champ obligatoire Exemple: FR</p>';
                        }
                        echo'<label>Numéro Siret</label></br><input placeholder="Ex : 15128141529425 " type="text" name="siret" value="'.$_POST["siret"].'""/>';
                        if (empty($_POST["siret"])) {
                            echo'<p>* Champ obligatoire Exemple : 15128141529425</p>';
                        }
                        echo'<label>Texte Présentation</label></br><input type="text" name="texte" value="'.$_POST["texte"].'"/>';
                        if (empty($_POST["texte"])) {
                            echo'<p>* Champ obligatoire Merci de faire un résumer de votre entreprise</p>';
                        }

                        echo'<label>Numéro de rue</label></br><input type="number" name="numrue" value="'.$_POST["numrue"].'"/>';
                        if (empty($_POST["numrue"])) {
                            echo'<p>* Champ obligatoire Exemple : 30</p>';
                        }
                        
                        echo'<label>Nom de rue</label></br><input type="text" name="nomrue" value="'.$_POST["nomrue"].'"/>';
                        if (empty($_POST["nomrue"])) {
                            echo'<p>* Champ obligatoire Rentrer le nom de la rue</p>';
                        }
                       echo'<label>Informations Complémentaires</label></br><input type="text" name="info" value="'.$_POST["info"].'"/>
                        <label>Code Postal</label></br><input type="text" name="codepostal" pattern="[0-9]{5}" value="'.$_POST["codepostal"].'"/>';
                        if (empty($_POST["codepostal"])) {
                            echo'<p>* Champ obligatoire exmple : 29710</p>';
                        }
                        echo'<label>Ville</label></br><input type="text" name="ville" value="'.$_POST["ville"].'"/>';
                        if (empty($_POST["ville"])) {
                            echo'<p>* Champ obligatoire exemple : Plozevet</p>';
                        }
                        echo'<label>Logo entreprise</label>
                                <div id="img-preview"></div>
                                <input type="file" accept="image/*" id="choose-file" name="file" />
                                ';
                        

                        
                    }else{
                        echo'
                        <h1>Création compte Vendeur</h1>
                        <label>Nom</label></br><input type="text" name="nom" />
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
                        <label>Numéro TVA intracommunautaire</label></br><input placeholder="Ex : FR " type="text" name="numtva" />                      
                        <label>Numéro Siret</label></br><input placeholder="Ex : 15128141529425 " type="text" name="siret" pattern="[0-9]{14}"/>
                        <label>Texte Présentation</label></br><input type="text" name="texte" />

                        <label>Numéro de rue</label></br><input type="number" name="numrue" />
                        <label>Nom de rue</label></br><input type="text" name="nomrue" />
                        <label>Informations Complémentaires</label></br><input type="text" name="info" />
                        <label>Code Postal</label></br><input placeholder="Ex : 29710" type="text" name="codepostal" />
                        <label>Ville</label></br><input type="text" name="ville"/>
                        <label>Image</label>
                        <div id="img-preview"></div>
                        <input type="file" accept="image/*" id="choose-file" name="file" />

                       ';
                    }
                    echo'                        
                    <div class="boutons"><input class="valider" type="submit" value="Créer un compte" /></div>
                    </form>';

                    if ($_SERVER["REQUEST_METHOD"] == "POST")
                {
                    if (is_uploaded_file($_FILES["file"]["tmp_name"]))
                    {
                        
                        $upload_file_name = $_FILES["file"]["name"];
                        
                        $upload_file_name = preg_replace("/[^A-Za-z0-9 .-_]/", " ", $upload_file_name);
                        
                        
                        $dest=__DIR__."/images/logoVendeur/".$upload_file_name;
                        move_uploaded_file($_FILES["file"]["tmp_name"], $dest);
                        
                    }
                }
        ?>       

        <script>
            const chooseFile = document.getElementById("choose-file");
            const imgPreview = document.getElementById("img-preview");

            chooseFile.addEventListener("change", function () {
                getImgData();
            });
            function getImgData() {
                const files = chooseFile.files[0];
                if (files) {
                    const fileReader = new FileReader();
                    fileReader.readAsDataURL(files);
                    fileReader.addEventListener("load", function () {
                    imgPreview.style.display = "block";
                    imgPreview.innerHTML = '<img src="' + this.result + '" />';
                    });    
                }
                }
        </script>
    </main>

    <footer>
    <?php include "./piedpageReduit.php"; ?>
    </footer>
</body>

</html>
