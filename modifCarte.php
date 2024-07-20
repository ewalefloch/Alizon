<!-- exemple de carte valide -->
<!-- 4108456278941247 --> 

<?php
  session_start();
  //unset($_SESSION["panier"]); supprimer panier
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

  function getIDCarte($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT idcarte FROM {$prefix}_client NATURAL JOIN {$prefix}_coordonneesBancaires WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setCarteBancaire($idcarte, $numcarte, $nom, $mois, $annee, $crypto){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_coordonneesBancaires SET cryptogramme = ?, numcarte = ?, dateexpiration = ? , titulairecarte = ? WHERE idcarte = ?;");
    if(strlen($annee) == 2){
      $date = "20".$annee."-".$mois."-01";
    }
    if(strlen($annee) == 4){
      $date = $annee."-".$mois."-01";
    }
    $req->execute(array($crypto, $numcarte, $date , $nom , $idcarte));
  }

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
            <h1>Modifier la carte de paiement</h1>
          </div>

        
          <?php
            

            if(isset($_POST['modifier'])){
              if(carteValide($_POST['numcarte'])){
                if(strlen($_POST['exprannee']) == 2 || strlen($_POST['exprannee']) == 4){
                  $idcarte = getIDCarte($_SESSION["idclient"]);
                  setCarteBancaire($idcarte, $_POST['numcarte'], $_POST['titulairenom'], $_POST['exprmois'], $_POST['exprannee'], $_POST['crypto']);
                }else{
                  echo '<div class="champerrone"><p>Erreur année d\'expiration invalide</p></div>';
                }
              }else{
                echo '<div class="champerrone"><p>Erreur numero de carte non valide</p></div>';
              }
            }



            //   if(!empty($_SESSION['idclient'])){
            //     if(!empty($_POST['numcarte'])){
            //       if(!empty($_POST['titulairenom'])){
            //         if(!empty($_POST['titulaireprenom'])){
            //           if(!empty($_POST['exprmois']) && !empty($_POST['exprannee'])){
            //             if(!empty($_POST['crypto'])){
            //               $idcarte = getIDCarte($_SESSION["idclient"]);
            //               setCarteBancaire($idcarte, $_POST['numcarte'], $_POST['titulairenom'], $_POST['titulaireprenom'], $_POST['exprmois'], $_POST['exprannee'], $_POST['crypto']);
            //             }else{
            //               echo "cryptogramme invalide";
            //             }
            //           }else{
            //             echo "date invalide";
            //           }
            //         }else{
            //           echo "veuillez entrer le Prenom du titulaire";
            //         }
            //       }else{
            //         echo "veuillez entrer le Nom du titulaire";
            //       }
            //     }else{
            //       echo "numero de carte vide";
            //     }
            //   }
            //   echo "mis a jour";
            // }
          ?>

          <form action="" method="post">
            <!-- verif ancient mdp a faire-->
            <div class = "hcontainer">
              <div class="champ">
                  <label>Numero carte</label>
                  <input type="number" name="numcarte" value="" min="0" required>
              </div>
  
              <div class="champ">
                  <label>Titulaire carte</label>
                  <label>Nom</label>
                  <input type="text" name="titulairenom" value="" required>
                  <!-- <label>Prenom</label>
                  <input type="text" name="titulaireprenom" value="" required> -->
              </div>
            </div>

            <div class="hcontainer">
              <div class="champ">
                  <label>Date D'expiration mm/aa</label>
                  <div class="hcontainer">
                    <input type="number" name="exprmois" value="" style="width: 4em;" min="0" max="12" required>
                    <input type="number" name="exprannee" value="" style="width: 4em;" required>
                  </div>  
              </div>
  
              <div class="champ">
                  <label>Cryptograme</label>
                  <a href="https://duckduckgo.com/?q=ou+trouver+le+pictogramme+visuel+carte+bancaire"> <label>Ou trouver le cryptograme ?</label></a>
                  <input type="number" name="crypto" value="" min="0" max="999" style="width: 2.3em;" required>
              </div>
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
