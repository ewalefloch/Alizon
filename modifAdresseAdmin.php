<?php
  session_start();

  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

  function getIDAdresse($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT idadresse FROM {$prefix}_client NATURAL JOIN {$prefix}_adresse WHERE idclient = $idclient;")->fetchColumn();
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
    <link rel="stylesheet" type="text/css" href="style/header.css"/>

  </head>
  <body>
    <header>
    <?php include 'enteteReduit.php'; ?>      
    </header>
    <main>
      <!-- dans le cas ou l'utilisateur n'est pas connecté -->
      <?php if(isset($_SESSION['idclientmodif'])){

        if(isset($_POST['modifier'])){
            setAdresse($_SESSION['idclientmodif'], $_POST['code'], $_POST['ville'], $_POST['rue'], $_POST['numrue'], $_POST['infos']);
        }
        ?>
        <div class="vcontainer">
          <div class="champ">
            <h1 style="font-family: ubuntuB;">Modifier la carte de paiement</h1>
          </div>
          
          <form action="" method="post">
                <div class="hcontainer">
                  <!-- verif ancient mdp a faire-->
                    <div class="champ">
                        <label>Numéro de rue</label>
                        <input type="number" name="numrue" value="" required>
                    </div>
                    
                    <div class="champ">
                        <label>Nom de la rue</label>
                        <input type="text" name="rue" value="" required>
                    </div>
                </div>
                <div class="hcontainer">
                    <div class="champ">
                            <label>Ville</label>
                            <input type="text" name="ville" value="" required>
                        </div>

                        <div class="champ">
                            <label>Code postal</label>
                            <input type="text" name="code" value="" required>
                    </div>
                </div>

            <div class="champ">
                <label>Informations complémentaire</label>
                <textarea rows="5" cols="40" name="infos"></textarea>
            </div>

            <div class="hcontainer">
                <div class="champ">
                    <input type="submit" name="modifier" value="Modifier" class="bouton">
                </div>
                <div class="champ">
                    <a href="modifCompteAdmin.php" ><p class="bouton">Annuler</p></a>
                </div>
            </div>
                
          </form>
        </div>
      <?php } else{ ?>
          <h2 class="champ">vous devez d'abord vous connecter pour modifier vos informations</h2>
      <?php }?>
    </main> 
  <footer>
    <?php include 'piedpage.php'; ?>
  </footer> 
  </body>
</html>