<?php
    include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';
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
    <title>ALIZON Importer articles</title>
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/styleImport.css" />
    <link rel="stylesheet" type="text/css" href="style/headerpro.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
</head>
<body>
  <header>             
      <?php include "entetePro.php" ?>          
  </header>
    <main>
    <form enctype="multipart/form-data" action="afficherCatalogue.php" method="post" id="formulaire">
        <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
        <h1>Importer un catalogue</h1> <br> <input type="file" name="fichier" id="fichier" accept=".csv"/>
        <input type="submit" class= "envoyer" value="Importer" id="buttonEnvoyer"/>
      </form>
      <br>
      <!--
      <p> Ou importer un article </p>
      <form id=formulaire method=post action="fichierUpload.php">
        <label>Nom :</label><input type=text name=nom /><br>
        <label>prixht :</label><input type=text name=prixht /><br>
        <label>prixttc :</label><input type=text name=prixttc /><br>
        <label>prixcoutant :</label><input type=text name=prixcoutant /><br>
        <label>prixlivraison :</label><input type=text name=prixlivraison /><br>
        <label>description :</label><input type=text name=description /><br>
        <label>remise :</label><input type=text name=remise /><br>
        <label>prix de vente remise :</label><input type=text name=prixdeventeremise /><br>
        <label>quantite :</label><input type=text name=quantite /><br>
        <label>seuil alerte :</label><input type=text name=seuilalerte /><br>

        <input type=submit value=Valider />
    </form>
    -->
    </main>
    <script>
        const submitButton = document.getElementById("buttonEnvoyer");
        const fichiers = document.getElementById("fichier");

        submitButton.addEventListener("click", function(event) {
          const fichier = fichiers.files[0];
          const extensionAutoriser = [".csv"];

          if (fichier) {
            const fichierNom = fichier.name;
            const extension = fileName.substr(fichierNom.lastIndexOf("."));
            if (allowedExtensions.indexOf(extension) === -1) {
              alert("Seuls les fichiers avec l'extension .csv sont autorisés.");
              event.preventDefault();
            }
          } else {
            alert("Veuillez sélectionner un fichier.");
            event.preventDefault();
          }
        });
      </script>
</body>
</html>
