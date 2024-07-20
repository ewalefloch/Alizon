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

  function getNom($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT nom FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setNom($idclient, $nom){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET nom =? WHERE idclient = ?");
    $req->execute(array($nom, $idclient));
  }

  function getPrenom($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT prenom FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setPrenom($idclient, $prenom){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET prenom =? WHERE idclient = ?");
    $req->execute(array($prenom, $idclient));
  }

  function getEmail($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT email FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setEmail($idclient, $email){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET email =? WHERE idclient = ?");
    $req->execute(array($email, $idclient));
  }

  function getTelephone($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT numtel FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setTelephone($idclient, $telephone){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET numtel =? WHERE idclient = ?");
    $req->execute(array($telephone, $idclient));
  }

  function getDateDeN($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT datenaissance FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setDateDeN($idclient, $ddn){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET datenaissance =? WHERE idclient = ?");
    $req->execute(array($ddn, $idclient));
  }

  function getAdresse($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT codepostal, numrue, nomrue, ville, infocomplementaire FROM {$prefix}_client NATURAL JOIN {$prefix}_adresse WHERE idclient=$idclient"); 
  }

  
  function getIDAdresse($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT idadresse FROM {$prefix}_client NATURAL JOIN {$prefix}_adresse WHERE idclient = $idclient;")->fetchColumn();
  }

// partie paiement
  function getIDCarte($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT idcarte FROM {$prefix}_client NATURAL JOIN {$prefix}_coordonneesbancaires WHERE idclient=$idclient")->fetchColumn(); 
  }
  function getNumCarte($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT numcarte FROM {$prefix}_client NATURAL JOIN {$prefix}_coordonneesBancaires WHERE idclient=$idclient")->fetchColumn(); 
  }

  function getTypeCarte($numcarte){
    if($numcarte[0] ==  '3' && ( $numcarte[1] == '4' || $numcarte[1] == '7' ) && strlen($numcarte) == 15){
      return "Amex";
    }
    if($numcarte[0] == '5' && $numcarte[1] >= '1' && $numcarte[1] <= '5' && strlen($numcarte) == 16){
      return "MasterCard";
    }
    if($numcarte[0] == '4' && (strlen($numcarte) == 13 || strlen($numcarte) == 16)){
      return "Visa";
    }
    return "";
  }

  function setCarteBancaire($idcarte, $numcarte, $nom , $mois, $annee, $crypto){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_coordonneesbancaires SET cryptogramme = ?, numcarte = ?, dateexpiration = ? , titulairecarte = ? WHERE idcarte = ?;");
    if(strlen($annee) == 2){
      $date = "20".$annee."-".$mois."-01";
    }
    if(strlen($annee) == 4){
      $date = $annee."-".$mois."-01";
    }
    $req->execute(array($crypto, $numcarte, $date , $nom , $idcarte));
  }

  function setAdresse($idclient, $code, $ville, $rue, $numrue, $infos){
    global $dbh, $prefix;
    $idadresse = getIDAdresse($idclient);
    if($idadresse){
      $req = $dbh->prepare("UPDATE {$prefix}_adresse SET codepostal = ?, nomrue = ?, numrue = ? , infocomplementaire = ?, ville = ? WHERE idadresse = ?;");
      $req->execute(array($code, $rue, $numrue , $infos, $ville, $idadresse));
    }else{
      //creation d'une nouvelle adresse dans la BDD
      $req = $dbh->prepare("INSERT INTO {$prefix}_adresse (codepostal,nomrue,numrue,infocomplementaire,ville) VALUES (?, ?, ?, ?, ?)");
      $req->execute(array($code, $rue, $numrue , $infos, $ville));
      $idadresse = $dbh->query("SELECT idadresse FROM {$prefix}_adresse ORDER BY idadresse DESC LIMIT 1;")->fetchColumn(); //récupere l'id de l'adresse pour le metre dans le client
      $req = $dbh->prepare("UPDATE {$prefix}_client SET idadresse = ? WHERE idclient = ?");
      $req->execute(array($idadresse, $idclient));
    }
  }
  //algo de luhn 
  function carteValide($numcarte){
    $somme = 0;
    for($i = strlen($numcarte)-1 ; $i>=0; $i--){
      $n = ord($numcarte[$i]) - ord('0');
      if($i%2 == 1){
        $somme += $n;
      }else{
        $e1 = (int)($n*2/10);
        $e2 = $n*2-10*$e1;
        $somme += $e1 + $e2;
      }
    }
    return $somme%10 == 0;
  }

// partie mdp
  function getMDP($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT motdepasse FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setMDP($idclient, $mdp){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET motdepasse =? WHERE idclient = ?");
    $mdp = chiffrement_mdp($mdp);
    $req->execute(array($mdp, $idclient));
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
    <link rel="stylesheet" type="text/css" href="style/styleModifCompte.css"/>
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    <!-- <link rel="stylesheet" type="text/css" href="style/header.css"/> -->
  </head>
  <body>
  <header> 
            <div class="haut">      
                <a href="index.php"><img class="logo" src="images/logo/logofiniresizes.png" alt="logo alizon" title="logo alizon" ></a>
                <h1 style="flex-grow:0;"><a href="index.php">ALIZON</a></h1><h1 style="color:white; font-size:120%; margin:0; margin-top:0.5%;">ADMIN</h1>
               
            </div> 
            
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
          <!-- page modification des information perso du client  -->
          <div class="panel" id="one-panel">
            <h2>Mes informations</h2>
            <?php 
              if(isset($_POST['idclientmodif'])){
                if(isset($_POST['nom'])){
                  setNom($_POST['idclientmodif'], $_POST['nom']);
                }
                if(isset($_POST['prenom'])){
                  setPrenom($_POST['idclientmodif'], $_POST['prenom']);
                }
                if(isset($_POST['email'])){
                  global $dbh, $prefix;
                  $req = $dbh->query("SELECT email FROM {$prefix}_client");
                  $trouverEmail = false;
                  foreach($req as $row){
                    if($row["email"]==$_POST["email"]){
                      $trouverEmail = true;
                    }
                  }
                  if($trouverEmail){
                    echo '<p id="popInsCon">Email deja existant</p>';
                  }else{
                    setEmail($_POST['idclientmodif'], $_POST['email']);
                  }
                }
                if(isset($_POST['telephone'])){
                  setTelephone($_POST['idclientmodif'], $_POST['telephone']);
                }
                if(isset($_POST['ddn'])){
                  setDateDeN($_POST['idclientmodif'], $_POST['ddn']);
                }

                if(isset($_POST['nomrue']) && isset($_POST['numrue']) && isset($_POST['codepostal']) && isset($_POST['ville'])){
                  setAdresse($_POST['idclientmodif'], $_POST['codepostal'], $_POST['ville'], $_POST['nomrue'], $_POST['numrue'], $_POST['infos']);
                }
                if( isset($_POST['infocompte'])){
                  echo '<div class="champvalide"><p>Informations mise à jour </p></div>';
                }
              }
            ?>
            <h4>Mon compte</h4>
            <form method="post">
              <div class="hcontainer">
                <div class="champ">
                  <label>Prénom</label><br/>
                  <input type="text" name="prenom" value="<?php echo getPrenom($_POST['idclientmodif']) ?>" required/>
                </div>
      
                <div class="champ">
                  <label>Nom</label><br/>
                  <input type="text" name="nom" value="<?php echo getNom($_POST['idclientmodif']) ?>"required/>
                </div>
              </div>

              <div class="hcontainer">
                <div class="champ">
                  <label>Email</label><br/>
                  <input type="email" name="email" value="<?php echo getEmail($_POST['idclientmodif']) ?>" required/><br/>
                </div>

                <div class="champ">
                  <label>Téléphone</label><br/>
                  <input type="tel" name="telephone" pattern="[0-9]{10}" value="<?php echo getTelephone($_POST['idclientmodif']) ?>" required/><br/>
                </div>
              </div>
              <div class="champ">
                <label>Date de naissance</label><br/>
                <input type="date" name="ddn" value="<?php echo getDateDeN($_POST['idclientmodif']) ?>" required/><br/>
              </div>
            
              <h4>Adresse de facturation</h4>
              <?php $adresse = getAdresse($_POST['idclientmodif'])->fetchAll()[0];?>

              <div class="hcontainer">
                <div class="champ">
                  <label>Nom rue</label><br/>
                  <input type="text" name="nomrue" value="<?php echo $adresse['nomrue'];?>" required/><br/>
                </div>
                <div class="champ">
                  <label>Numéro rue</label><br/>
                  <input type="number" name="numrue" value="<?php echo $adresse['numrue'];?>" min=0 required/><br/>
                </div>
              </div>

              <div class="hcontainer">
                <div class="champ">
                  <label>Code postal</label><br/>
                  <input type="text" name="codepostal" pattern="[0-9]{5}" value="<?php echo $adresse['codepostal'];?>" required/><br/>
                </div>
                <div class="champ">
                  <label>Ville</label><br/>
                  <input type="text" name="ville" value="<?php echo $adresse['ville'];?>" required/><br/>
                </div>
              </div>
              <div class="champ">
                <label>Informations complémentaire</label></br>
                <textarea rows="5" name="infos"><?php echo $adresse['infocomplementaire'];?></textarea>
              </div>
              <div class="hcontainer containervalide">
                <a href="listerComptes.php"><p class="retour">< retour<p></a>
                <input type="hidden" name="indextabmodifcompte" value="1"/>

                <input type="hidden" name="idclientmodif" value=<?php echo '"'.$_POST['idclientmodif'].'"';?> />
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
              if(isset($_POST['idclientmodif'])){
                if(isset($_POST['nouveaumdp']) && isset($_POST['nouveaumdpconf'])){
                  if($_POST['nouveaumdp'] == $_POST['nouveaumdpconf']){
                    if($_POST['nouveaumdp'] != ""){
                      setMDP($_POST['idclientmodif'],$_POST['nouveaumdp']);
                      echo '<div class="champvalide"><p>mot de passe changé.</p></div>';
                    }
                    else{
                      echo "Le nouveau mdp ne peut pas être vide";
                    }
                  }
                  else{
                    echo "Erreur confirmation mot de passe";
                  }
                }
              }
            ?>
            <form method="post">
              <div class="champ">
                <label>Nouveau mot de passe</label><br/>
                <input type="password" name="nouveaumdp" value="" required/><br/>
              </div>
              <div class="champ">
                <label>Confirmer le mot de passe</label><br/>
                <input type="password" name="nouveaumdpconf" value="" required/><br/>
              </div>

              <div class="hcontainer containervalide">
                <a href="listerComptes.php"><p class="retour">< retour<p></a>
                <input type="hidden" name="indextabmodifcompte" value="3"/>
                <input type="hidden" name="idclientmodif" value=<?php echo '"'.$_POST['idclientmodif'].'"';?> />
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

