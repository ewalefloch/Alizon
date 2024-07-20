<!DOCTYPE html>
<?php
    include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);

    if(!isset($_SESSION["idvendeur"])){
        header('Location: ./connexionVendeur.php');    
      }
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
    <link rel="stylesheet" type="text/css" href="style/styleCreateoffre.css" />
</head>
<body>
    <header>  
            <?php include "entetePro.php" ?>
    </header>
    <main>
    <?php
        try{
            $comm=$dbh->prepare("SELECT * from {$prefix}_souscategorie");
            $comm->execute();

            $sth=$dbh->prepare("INSERT INTO {$prefix}_article(nom,prixht,prixcoutant,descript,quantitestock,seuilalerte,idsouscategorie,idvendeur) VALUES(?,?,?,?,?,?,?,?);");
            $imgbdd=$dbh->prepare("INSERT INTO {$prefix}_image(idarticle,urlimage) VALUES (?,?);");
            echo '<div class="formulaire"> 
                    <a href="#"><h1>Créer une offre</h1></a>';
                    if(!empty($_POST["nom"])&&!empty($_POST["descript"])&&!empty($_POST["prixcoutant"])&&!empty($_POST["prixht"])&&!empty($_POST["quantitestock"])&&!empty($_POST["seuilalerte"])){
                        $sth->execute(array($_POST["nom"],$_POST["prixht"],$_POST["prixcoutant"],$_POST["descript"],$_POST["quantitestock"],$_POST["seuilalerte"],$_POST["idsouscategorie"]));
                        
                            $last_id = $dbh->lastInsertId();
                            
                        $imgbdd->execute(array($last_id,"images/imgArticle/".$_FILES["file"]["name"]));
                          
                        echo' <form id="formulaire" enctype="multipart/form-data" method="post" action="Createoffre.php"  >';
                        echo '<div id="creer">Offre créé avec succès</div>';
                    }
                    else{
                        echo'<p>Veuillez remplir tous les champs</p>';
                        echo'<form id="formulaire"enctype="multipart/form-data" method="post" action="Createoffre.php">';
                    }
                    if (isset($_POST["nom"])) {
                        echo'<form id="formulaire"enctype="multipart/form-data" method="post" action="Createoffre.php">';
                        echo'<label>Nom de l\'offre</label></br><input type="text" name="nom" value="'.$_POST["nom"].'" /></br>';
                        if (empty($_POST["nom"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Description</label></br><textarea name="descript" value="'.$_POST["descript"].'" /></textarea></br>';
                        if (empty($_POST["descript"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Categorie</label></br><select name="idsouscategorie" >';
                        foreach ($comm as $row ) {
                            
                            echo'<option value="'.$row["idsouscategorie"].'">'.$row["souslibelle"].' '.$row["idsouscategorie"].'</option>';
                        }
                    
                        echo'</select></br>';
                        
                        echo'<label>Prix coutant </label></br><input type="number" min="0" step="0.01" name="prixcoutant" value="'.$_POST["prixcoutant"].'"/></br>';
                        if (empty($_POST["prixcoutant"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                        echo'<label>Prix hors taxe</label></br><input type="number" min="0" step="0.01" name="prixht" value="'.$_POST["prixht"].'"/></br>';
                        if (empty($_POST["prixht"])) {
                            echo'<p>* Champ obligatoire</p>';
                        }
                    
                            echo'<label>Quantité du stock</label></br><input type="number" min="1" name="quantitestock" value="'.$_POST["quantitestock"].'"/></br>';
                            if (empty($_POST["quantitestock"])) {
                                echo'<p>* Champ obligatoire</p>';
                            }
                            echo'<label>Seuil d\'alerte</label></br><input type="number" min="1" name="seuilalerte" value="'.$_POST["seuilalerte"].'"/></br>';
                            if (empty($_POST["seuilalerte"])) {
                                echo'<p>* Champ obligatoire</p>';
                            }
                            echo'<label>Image</label>
                                <div id="img-preview"></div>
                                <input type="file" accept="image/*" id="choose-file" name="file" />
                                ';
                            
                            /*echo'<div class="boutons"><input class="valider" type="submit" value="Créer l\'offre" /></div>
                        </form>
                        
                        </div>';*/
                    }else {
                            echo'<label>Nom de l\'offre</label></br><input type="text" name="nom" /></br>
                            <label>Description</label></br><textarea name="descript" /></textarea></br>
                            <label>Categorie</label></br><select name="idsouscategorie">';
                            foreach ($comm as $row ) {
                                
                                echo'<option value="'.$row["idsouscategorie"].'">'.$row["souslibelle"].' '.$row["idsouscategorie"].'</option>';
                            }
                        
                            echo'</select></br>';
                            
                            echo'<label>Prix coutant </label></br><input type="number" min="0" step="0.01" name="prixcoutant" /></br>
                            <label>Prix hors taxe</label></br><input type="number" min="0" step="0.01" name="prixht" /></br>';
                            
                        
                        echo'
                            
                            <label>Quantité du stock</label></br><input type="number" min="1" name="quantitestock" /></br>
                            <label>Seuil d\'alerte</label></br><input type="number" min="1" name="seuilalerte" /></br>
                            <label>Image</label>
                                <div id="img-preview"></div>
                                <input type="file" accept="image/*" id="choose-file" name="file" />';
                            
                           /* echo'<div class="boutons"><input class="valider" type="submit" value="Créer l\'offre" /></div>
                        </form>
                            
                        </div>';*/
                    }
                    echo'<div class="boutons"><input class="valider" type="submit" value="Créer l\'offre" /></div>
                        </form>
                        
                        </div>';
                    

                //TELECHARGE IMAGE DANS DOSSIER IMGARTICLES
                if ($_SERVER["REQUEST_METHOD"] == "POST")
                {
                    if (is_uploaded_file($_FILES["file"]["tmp_name"]))
                    {
                        
                        $upload_file_name = $_FILES["file"]["name"];
                        
                        $upload_file_name = preg_replace("/[^A-Za-z0-9 .-_]/", " ", $upload_file_name);
                        
                        
                        $dest=__DIR__."/images/imgArticle/".$upload_file_name;
                        move_uploaded_file($_FILES["file"]["tmp_name"], $dest);
                        
                    }
                }
                
                
               
            } catch (PDOException $e) {
                print "Erreur !: " . $e->getMessage() . "<br/>";
                die();
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
