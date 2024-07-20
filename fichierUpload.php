<?php
    include('connect_params.php');
    $prefix = 'sae301_a21.';
    session_start();    
    $_SESSION["idvendeur"]=1;
    if(!isset($_SESSION["idvendeur"])){
        header('Location: ./connexionVendeur.php');    
    }
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);
    
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta charset="utf-8" />
    <title>ALIZON</title>
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/styleImport.css" />
    <link rel="stylesheet" type="text/css" href="style/headerpro.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
</head>
<body>
<header> 
    <?php include 'entetePro.php' ?>
</header>
    <main>

    <?php

    include('connect_params.php');

    try {
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);

        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        if (isset($_POST["catalogue"])){
            if ($_SERVER["REQUEST_METHOD"] == "POST")
            {   
                foreach ($_FILES as $index => $file) {
                    
                if (is_uploaded_file($_FILES[$index]["tmp_name"]))
                {
                    
                    $upload_file_name = $_FILES[$index]["name"];            
                    
                    $dest=__DIR__."/images/imgArticle/".$upload_file_name;
                    move_uploaded_file($_FILES[$index]["tmp_name"], $dest);
                    
                }
                
                }
            }
                $file=fopen("./".$_POST["catalogue"],"r");
                $row =0;
                while ($column = fgetcsv($file,1024,";")) {//insertion dans article
                    if ($row>0){ // sauter le header de csv

                    $req = $dbh->prepare("SELECT idsouscategorie from {$prefix}_souscategorie where souslibelle='$column[6]'");
                    $req->execute();
                    foreach($req as $row2){
                        //print_r($row2);
                        $sth = $dbh->prepare("INSERT INTO {$prefix}_article(nom,prixht,prixcoutant,descript,quantitestock,seuilalerte,idsouscategorie,idvendeur)
                        values (?,?,?,?,?,?,?,?)");
                        $sth->execute(array($column[0],$column[1],$column[2],$column[3],$column[4],$column[5],$row2["idsouscategorie"],$_SESSION["idvendeur"]));
                        
                        $lastID = $dbh->lastInsertId(); //recuperer le dernier id insert
                        $sth = $dbh->prepare("INSERT INTO {$prefix}_image(idarticle,urlimage)
                        values (?,?)");
                        $sth->execute(array($lastID,$column[7]));  
                    }

                    }else{
                        $row++;
                    }

                    
                }
            }
            //TELECHARGE IMAGE DANS DOSSIER IMGARTICLES

    $dbh = NULL;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    ?>
    <div>
        <p> Import du Catalogue Finis </p>
        <form action="importArticle.php" method="post">
            <input class= "ajouter" type="submit" name="submit" value="Importer un autre catalogue"/> 
        </form>
        <form action="compteVendeur.php" method="post">
            <input class= "ajouter" type="submit" name="submit" value="Retour à mon profil"/> 
        </form>
    </div>

    </main>
</body>
</html>

