<?php
  session_start();
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

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

//nom, mail, rue, numero rue, code post, ville, texte, img

  function getNom($idvendeur){
    global $dbh, $prefix;
    return $dbh->query("SELECT nom FROM {$prefix}_vendeur WHERE idvendeur=$idvendeur")->fetchColumn(); 
  }

  function setNom($idvendeur, $nom){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_vendeur SET nom =? WHERE idvendeur = ?");
    $req->execute(array($nom, $idvendeur));
  }

  function getEmail($idvendeur){
    global $dbh, $prefix;
    return $dbh->query("SELECT email FROM {$prefix}_vendeur WHERE idvendeur=$idvendeur")->fetchColumn(); 
  }

  function setEmail($idvendeur, $email){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_vendeur SET email =? WHERE idvendeur = ?");
    $req->execute(array($email, $idvendeur));
  }

  function getAdresse($idadresse){
    global $dbh, $prefix;
    return $dbh->query("SELECT codepostal, numrue, nomrue, ville, infocomplementaire FROM {$prefix}_vendeur NATURAL JOIN {$prefix}_adresse WHERE idadresse=$idadresse"); 
  }
  
  function getIDAdresse($idvendeur){
    global $dbh, $prefix;
    return $dbh->query("SELECT idadresse FROM {$prefix}_vendeur NATURAL JOIN {$prefix}_adresse WHERE idvendeur = $idvendeur;")->fetchColumn();
  }

  function setAdresse($idvendeur, $code, $ville, $rue, $numrue, $infos){
    global $dbh, $prefix;
    $idadresse = getIDAdresse($idvendeur);
    if($idadresse){
      $req = $dbh->prepare("UPDATE {$prefix}_adresse SET codepostal = ?, nomrue = ?, numrue = ? , infocomplementaire = ?, ville = ? WHERE idadresse = ?;");
      $req->execute(array($code, $rue, $numrue , $infos, $ville, $idadresse));
    }else{
      //creation d'une nouvelle adresse dans la BDD
      $req = $dbh->prepare("INSERT INTO {$prefix}_adresse (codepostal,nomrue,numrue,infocomplementaire,ville) VALUES (?, ?, ?, ?, ?)");
      $req->execute(array($code, $rue, $numrue , $infos, $ville));
      $idadresse = $dbh->query("SELECT idadresse FROM {$prefix}_adresse ORDER BY idadresse DESC LIMIT 1;")->fetchColumn(); //récupere l'id de l'adresse pour le metre dans le vendeur
      $req = $dbh->prepare("UPDATE {$prefix}_vendeur SET idadresse = ? WHERE idvendeur = ?");
      $req->execute(array($idadresse, $idvendeur));
    }
  }

  function getText($idvendeur){
    global $dbh, $prefix;
    return $dbh->query("SELECT textepresentation FROM {$prefix}_vendeur WHERE idvendeur=$idvendeur")->fetchColumn();
  }

  function setText($idvendeur, $textepresentation){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_vendeur SET textepresentation =? WHERE idvendeur = ?");
    $req->execute(array($textepresentation, $idvendeur));
  }
  //TELECHARGE IMAGE DANS DOSSIER IMGARTICLES
  if ($_SERVER["REQUEST_METHOD"] == "POST")
  {
    if (is_uploaded_file($_FILES["file"]["tmp_name"])) {

      $upload_file_name = $_FILES["file"]["name"];

      $upload_file_name = preg_replace("/[^A-Za-z0-9 .-_]/", " ", $upload_file_name);
  
      $dest=__DIR__."/images/logoVendeur/".$upload_file_name;

      move_uploaded_file($_FILES["file"]["tmp_name"], $dest);

    }
  }
  $updateImg=$dbh->prepare("UPDATE {$prefix}_vendeur SET imglogo = ? WHERE idvendeur = ?");

  if(isset($dest)){
    $updateImg->execute(array("images/logoVendeur/".$upload_file_name,1));
  }
  /*function getImg($idvendeur){
    global $dbh, $prefix;
    return $dbh->query("SELECT imgLogo FROM {$prefix}_vendeur WHERE idvendeur=$idvendeur")->fetchColumn();
  }

  function setImg($idvendeur, $imgLogo){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_vendeur SET imgLogo = ? WHERE idvendeur = ?");
    $req->execute(array($imgLogo, $idvendeur));
  }*/

// partie mdp
  function getMDP($idvendeur){
    global $dbh, $prefix;
    return $dbh->query("SELECT mdp FROM {$prefix}_vendeur WHERE idvendeur=$idvendeur")->fetchColumn(); 
  }

  function setMDP($idvendeur, $mdp){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_vendeur SET mdp =? WHERE idvendeur = ?");
    $mdp = chiffrement_mdp($mdp);
    $req->execute(array($mdp, $idvendeur));
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
    <link rel="stylesheet" type="text/css" href="style/styleModifCompteVendeur.css"/>
    <!-- <link rel="stylesheet" type="text/css" href="style/header.css"/> -->
  </head>
  <body>
    <header> 
      <?php include 'enteteReduit.php'; ?>      
    </header>
    <main>
      <div class="block">
		<?php if(empty($_POST['indextabmodifcompte'])){
			$_POST['indextabmodifcompte'] = 1;
		}?>
        <input class="radio" id="one" name="group" type="radio"   <?php if($_POST['indextabmodifcompte'] == 1 ) {echo 'checked';}?>>
        <input class="radio" id="two" name="group" type="radio"   <?php if($_POST['indextabmodifcompte'] == 2 ) {echo 'checked';}?>>
        <input class="radio" id="three" name="group" type="radio" <?php if($_POST['indextabmodifcompte'] == 3 ) {echo 'checked';}?>>
        <div class="tabs">
          <label class="tab" id="one-tab" for="one">Information Personnelle</label>
          <label class="tab" id="three-tab" for="three">Changer de mot de passe</label>
        </div>
        <div class="panels">
          <!-- page modification des information perso du vendeur  -->
          <div class="panel" id="one-panel">
            <h2>Mes informations</h2>
            <?php 
              if(isset($_SESSION['idvendeur'])){
                if(isset($_POST['nom'])){
                  setNom($_SESSION['idvendeur'], $_POST['nom']); 
                }
                if(isset($_POST['email'])){
                  setEmail($_SESSION['idvendeur'], $_POST['email']);
                }

                if(isset($_POST['nomrue']) && isset($_POST['numrue']) && isset($_POST['codepostal']) && isset($_POST['ville'])){
                  setAdresse($_SESSION['idvendeur'], $_POST['codepostal'], $_POST['ville'], $_POST['nomrue'], $_POST['numrue'], $_POST['infos']);
                }
                if(isset($_POST['pres'])){
                    setText($_SESSION['idvendeur'],$_POST['pres']);
                }
                /*if(isset($_POST['img'])){
                    setImg($_POST['idvendeur'], $_POST['img']);
                }*/
                }
                if( isset($_POST['infocompte'])){
                  ?>
                  <link rel="stylesheet" type="text/css" href="stylePopUp.css"> 
                  <div id="popup">
                      <span id="close-popup">&times;</span>
                      <p>Inscription réussis</p>
                  </div>
                    
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
                <?php
                }
              
              $data = $dbh->query("SELECT * FROM {$prefix}_vendeur NATURAL JOIN {$prefix}_adresse WHERE idvendeur = $_SESSION[idvendeur];")->fetch();
            ?>
            <h4>Mon compte</h4>
            <form enctype="multipart/form-data" method="post">
              <div class="hcontainer">      
                <div class="champ">
                  <label class="titre">Nom</label><br/>
                  <input class="gauche" type="text" name="nom" value="<?php echo $data["nom"] ?>"required/>
                </div>
                <div class="champ">
                  <label class="titre">Email</label><br/>
                  <input type="email" name="email" value="<?php echo $data["email"] ?>" required/><br/>
                </div>
              </div>
            
              <h4>Adresse de l'entreprise</h4>  

              <div class="hcontainer">
                <div class="champ">
                  <label class="titre">Nom de la rue</label><br/>
                  <input class="gauche" type="text" name="nomrue" value="<?php echo $data['nomrue'];?>" required/><br/>
                </div>
                <div class="champ">
                  <label class="titre">Numéro de la rue</label><br/>
                  <input type="number" name="numrue" value="<?php echo $data['numrue'];?>" min=0 required/><br/>
                </div>
              </div>

              <div class="hcontainer">
                <div class="champ">
                  <label class="titre">Code postal</label><br/>
                  <input class="gauche" type="text" name="codepostal" pattern="[0-9]{5}" value="<?php echo $data['codepostal'];?>" required/><br/>
                </div>
                <div class="champ">
                  <label class="titre">Ville</label><br/>
                  <input type="text" name="ville" value="<?php echo $data['ville'];?>" required/><br/>
                </div>
              </div>
              <div class="champ">
                <label class="titre">Informations complémentaire</label></br>
                <textarea rows="5" name="infos"><?php echo $data['infocomplementaire'];?></textarea><br/>
              </div>

              <h4>Texte de présentation</h4>  
              
                <div class="champ">
                    <textarea rows="5" name="pres"><?php echo $data["textepresentation"];?></textarea><br/>
                </div>

              <h4>Logo de l'entrepise</h4>  

              <div class="hcontainer">
                <div class="champ">
                    <div id="img-preview"><img src="<?php echo $data["imglogo"];?>"></div>
                    <input type="file" accept="image/*" id="choose-file" name="file" /><br/>
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
                </div>
              </div>
              <div class="hcontainer containervalide">
                <a href="monProfil.php"><p class="retour">< retour<p></a>
                <input type="hidden" name="indextabmodifcompte" value="1"/>
                <input type="submit" name="infocompte" value="Enregistrer les modifications" class="bouton"/>
              </div>  
            </form>
          </div>

          <!-- page modification du moyen de paiement -->
          <!-- exemple de carte valide  -->
          <!-- 4108456278941247 -->

          <!-- page modification du mot de passe -->
          <div class="panel" id="three-panel">
            <h4>Modifier le mot de passe</h4>
            <?php 
              if(isset($_SESSION['idvendeur'])){
                if(isset($_POST['actuelmdp']) && isset($_POST['nouveaumdp']) && isset($_POST['nouveaumdpconf'])){
                  if(chiffrement_mdp($_POST['actuelmdp'])== getMDP($_SESSION['idvendeur']) ){
                    if($_POST['nouveaumdp'] == $_POST['nouveaumdpconf']){
                      if($_POST['nouveaumdp'] != ""){
                        if($_POST['actuelmdp'] != $_POST['nouveaumdp']){
                          setMDP($_SESSION['idvendeur'], $_POST['nouveaumdp']);
                          echo '<div id="popInsCon"><p>mot de passe changé.</p></div>';
                        }
                        else{
                          echo "Erreur le nouveau mot de passe doit être différent du mot de passe actuel";
                        }
                      }
                      else{
                        echo "Le nouveau mdp ne peut pas être vide";
                      }
                    }
                    else{
                      echo "Erreur confirmation mot de passe";
                    }
                  }
                  else{
                    echo "Erreur mot de passe erroné";
                  }
                }
              }
            ?>
            <form enctype="multipart/form-data" method="post">
              <div class="champ">
                <label>Mot de passe actuel</label><br/>
                <input type="password" name="actuelmdp" value="" required/><br/>
              </div>
              <div class="champ">
                <label>Nouveau mot de passe</label><br/>
                <input type="password" name="nouveaumdp" value="" required/><br/>
              </div>
              <div class="champ">
                <label>Confirmer le mot de passe</label><br/>
                <input type="password" name="nouveaumdpconf" value="" required/><br/>
              </div>

              <div class="hcontainer containervalide">
                <a href="monProfil.php"><p class="retour">< retour<p></a>
                <input type="hidden" name="indextabmodifcompte" value="3"/> 
                <input type="submit" name="mdp" value="Enregistrer les modifications" class="bouton"/>
                
              </div>  
            </form>
          </div>
        </div>
      </div>

    </main>
    <footer>
      <?php include 'piedpage.php';?>
    </footer> 
  </body>
</html>

