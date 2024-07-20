<?php
  session_start();
  //unset($_SESSION["panier"]); supprimer panier
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

  

  function getMDP($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT motdepasse FROM {$prefix}_client WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setMDP($idclient, $mdp){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_client SET motdepasse =? WHERE idclient = ?");
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
    <link rel="stylesheet" type="text/css" href="style/header.css"/>

  </head>
  <body>
    <header> 
    <?php include 'enteteReduit.php'; ?>      
    </header>
    <main>
      <!-- dans le cas ou l'utilisateur n'est pas connecté -->
      <?php if(isset($_SESSION['idclient'])){?> 
        <div class="vcontainer">
            <div class="champ">
                <h1>Modifier le mot de passe</h1>
            </div>

            <div class="champerrone">
              <?php 
                if(isset($_SESSION['idclient'])){
                  if(isset($_POST['actuelmdp']) && isset($_POST['nouveaumdp']) && isset($_POST['nouveaumdpconf'])){
                    if($_POST['actuelmdp'] == getMDP($_SESSION['idclient']) ){
                      if($_POST['nouveaumdp'] == $_POST['nouveaumdpconf']){
                        if($_POST['nouveaumdp'] != ""){
                          if($_POST['actuelmdp'] != $_POST['nouveaumdp']){
                            setMDP($_SESSION['idclient'], $_POST['nouveaumdp']);
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
            </div>
            <form action="" method="post">
                <!-- verif ancient mdp a faire-->
                <div class="champ">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="actuelmdp" value="" required>
                </div>

                <div class="champ">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="nouveaumdp" value="" required>
                </div>

                <div class="champ">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="nouveaumdpconf" value="" required>
                </div>
                
                <div class="champ">
                  <div class="hcontainer">
                    <input type="submit" name="modifier" value="Modifier" class="bouton">
                    <a href="modifCompte.php" style="margin: 0em;"><p class="bouton">Annuler</p></a>
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

