<?php
  session_start();
  //unset($_SESSION["panier"]); supprimer panier
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

  if(!empty($_POST["modifArticle"])){
    $_SESSION["idarticle"]=$_POST["modifArticle"];
  }
  $idarticle=$_SESSION["idarticle"];

  function setNom($idarticle, $nom){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET nom =? WHERE idarticle = ?");
    $req->execute(array($nom, $idarticle));
  }
  function setPrixht($idarticle, $prixht){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET prixht =? WHERE idarticle = ?");
    $req->execute(array($prixht, $idarticle));
  }
  function setPrixCoutant($idarticle, $prixcoutant){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET prixcoutant =? WHERE idarticle = ?");
    $req->execute(array($prixcoutant, $idarticle));
  }
  function setQuantite($idarticle, $quantitestock){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET quantitestock =? WHERE idarticle = ?");
    $req->execute(array($quantitestock, $idarticle));
  }
  function setSeuilAlerte($idarticle, $seuilalerte){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET seuilalerte =? WHERE idarticle = ?");
    $req->execute(array($seuilalerte, $idarticle));
  }
  function setDescription($idarticle, $descript){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET descript =? WHERE idarticle = ?");
    $req->execute(array($descript, $idarticle));
  }
  function setCategorie($idarticle, $categorie){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_article SET idsouscategorie =? WHERE idarticle = ?");
    $req->execute(array($categorie, $idarticle));
  }
  if(isset($_POST["categorie"])){
    $idsouscategorie=$dbh->query("SELECT idsouscategorie FROM {$prefix}_categorie NATURAL JOIN  {$prefix}_souscategorie WHERE souslibelle='$_POST[categorie]'")->fetch();
  }
  if(isset($_SESSION['idarticle'])){
    if(isset($_POST['nom'])){
      setNom($_SESSION['idarticle'], $_POST['nom']); 
    }
    if(isset($_POST['prixht'])){
      setPrixht($_SESSION['idarticle'], $_POST['prixht']);
    }
    if(isset($_POST['prixcoutant'])){
      setPrixCoutant($_SESSION['idarticle'], $_POST['prixcoutant']);
    }
    if(isset($_POST['quantitestock'])){
      setQuantite($_SESSION['idarticle'], $_POST['quantitestock']);
    }
    if(isset($_POST['seuilalerte'])){
      setSeuilAlerte($_SESSION['idarticle'], $_POST['seuilalerte']);
    }
    if(isset($_POST['descript'])){
      setDescription($_SESSION['idarticle'], $_POST['descript']);
    }
    if(isset($_POST['categorie'])){
      setCategorie($_SESSION['idarticle'], $idsouscategorie["idsouscategorie"]);
    }
  }
  if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if (is_uploaded_file($_FILES["file"]["tmp_name"]))
        {             
            $upload_file_name = $_FILES["file"]["name"];   
            $upload_file_name = preg_replace("/[^A-Za-z0-9 .-_]/", " ", $upload_file_name);
            $dest=__DIR__."/images/imgArticle/".$upload_file_name;
            print_r($dest);
            move_uploaded_file($_FILES["file"]["tmp_name"], $dest);
        }
    }
    

  
  
  $updateImg=$dbh->prepare("UPDATE {$prefix}_image SET urlimage = ? WHERE idarticle = ?");
 
  

  if(isset($dest)){
    $updateImg->execute(array("images/imgArticle/".$upload_file_name,$idarticle));
  }
  if(isset($idarticle)){

  $article=$dbh->query("SELECT * FROM {$prefix}_article a INNER JOIN {$prefix}_image i ON a.idarticle=i.idarticle WHERE a.idarticle=$idarticle ;")->fetch();
  $idsouscategorieBase=$dbh->query("SELECT souslibelle FROM {$prefix}_souscategorie WHERE idsouscategorie = $article[idsouscategorie]")->fetch();
  }else{
    $article=0;
  }
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
    <title>ALIZON</title>
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="./images/logo/logoSansCaddie.png">
    <link rel="stylesheet" type="text/css" href="style/styleModifierArticle.css" />
    <link rel="stylesheet" type="text/css" href="style/headerpro.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
</head>
<body>
  <header>
    <?php include "entetePro.php" ?>
  </header>
  <main>
    <?php
    if(isset($_SESSION["idarticle"])){ ?>
    <div class="tout">

      <div class="gauche">
  
        <form method="post" enctype="multipart/form-data">
          <div class="hcontainer">
            <div class="champ">
              <label>Titre de l'article</label><br/>
              <input type="text" name="nom" value="<?php echo $article["nom"] ?>" required/>
            </div>
          </div>
          <div class="hcontainer">
            <div class="champ">
              <label>Prix HT</label><br/>
              <input type="number" name="prixht" value="<?php echo $article["prixht"] ?>"required/>
            </div>
            <div class="champ">
              <label>Prix Coutant</label><br/>
              <input type="number" name="prixcoutant" value="<?php echo $article["prixcoutant"] ?>"required/>
            </div>
          </div>
          <div class="hcontainer">
            <div class="champ">
              <label>Quantite Stock</label><br/>
              <input type="number" name="quantitestock" value="<?php echo $article["quantitestock"] ?>"required/>
            </div>
            <div class="champ">
              <label>Seuil d'alerte</label><br/>
              <input type="text" name="seuilalerte" value="<?php echo $article["seuilalerte"] ?>"required/>
            </div>
          </div>
          <div class="hcontainer">
            <div class="champ">
              <label>Catégorie</label><br/>
              <select name="categorie"> <!-- Affichage du menu déroullant des catégorie et sous catégorie --> 
                <?php
                  $categoriesBDD = $dbh->query("SELECT  idcategorie, libelle from {$prefix}_categorie");
                  echo '<option value='.$idsouscategorieBase["souslibelle"]. '>'.$idsouscategorieBase["souslibelle"].'</option>';
                  foreach($categoriesBDD as $row){
                    echo '<optgroup value='.$row["libelle"].' label="'.$row["libelle"].'">';
                    $souscategoriesBDD = $dbh->query("SELECT  souslibelle from {$prefix}_souscategorie where idcategorie={$row["idcategorie"]}");
                    foreach($souscategoriesBDD as $sousrow){
                        echo '<option value="'.$sousrow["souslibelle"].'"> '.$sousrow["souslibelle"].' </option> ';
                    }
                    echo'</optgroup>';
                  }
                ?>
              </select>
              </div>
          </div>
          <div class="hcontainer">
            <div class="champ">
              <label>Description</label><br/>
              <textarea name="descript" rows="12" cols="70"><?php echo $article["descript"] ?>"</textarea>
            </div>
          </div>
      </div>
      
      <div class="droite">
        <div class="hcontainerImage">
          <h4>Image de l'article</h4>
          <div id="img-preview"><img src="<?php echo $article["urlimage"] ?>"></div>
          <input class="image" type="file" accept="image/*" id="choose-file" name="file" />
        </div>
      </div>
    </div>
    <div class="bouton">
      <input class="valider" type="submit" value="Modifier"/>
      <a href="articlesVendeur.php"><p class="retour">Annuler<p></a>
    </div>
    </form>
    <?php 

    }else{
      echo "Erreur aucun article sélectionné";
    }
    
    ?>
  </main>
  <footer>
    <?php include "piedpage.php"?>
  </footer>
</body>
</html>
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
