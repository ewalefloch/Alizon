
<?php
  session_start();
  //unset($_SESSION["panier"]); 
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);


  //fonction qui vide le panier 
  if(isset($_POST["supprimer"])){ //vider l'article
    $update=$dbh->prepare("UPDATE {$prefix}_article SET quantitestock = quantitestock- ? WHERE idarticle = ?;");
    $insertCommande=$dbh->query("INSERT into {$prefix}_commande(datecommande,datelivrer,dateexpedition,idclient,idadresse,etat)
        VALUES(current_date,current_date,current_date,$_SESSION[idclient],1,'EN livraison')");
    $lastIDCommande = $dbh->lastInsertId();
    
  }

  $sth=$dbh->prepare("INSERT INTO {$prefix}_client(idadresse) VALUES(?);");
  $adresse=$dbh->prepare("INSERT INTO {$prefix}_adresse(codepostal,nomrue,numrue,infocomplementaire,ville) VALUES(?,?,?,?,?);");

  //Fonction qui récupère l'id de la carte associé a l'id client
  function getIDCarte($idclient){
    global $dbh, $prefix;
    return $dbh->query("SELECT idcarte FROM {$prefix}_client NATURAL JOIN {$prefix}_coordonneesbancaires WHERE idclient=$idclient")->fetchColumn(); 
  }

  function setCarteBancaire($idcarte, $numcarte, $nom, $prenom, $mois, $annee, $crypto){
    global $dbh, $prefix;
    $req = $dbh->prepare("UPDATE {$prefix}_coordonneesbancaires SET cryptogramme = ?, numcarte = ?, dateexpiration = ? , titulairecarte = ? WHERE idcarte = ?;");
    if(strlen($annee) == 2){
      $date = "20".$annee."-".$mois."-01";
    }
    if(strlen($annee) == 4){
      $date = $annee."-".$mois."-01";
    }
    $req->execute(array($crypto, $numcarte, $date , $nom." ".$prenom, $idcarte));
  }

  //Fonction qui verifie si la carte est valide
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <meta charset="utf-8" />
    <title>ALIZON</title>
    <meta name="description" content="Site de e-commerce ALIZON" />
    <meta name="keywords" content="alizon,e-commerce,commerce,vente" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/styleFacturation.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    
</head>
<header>
    <?php include 'enteteReduit.php' ?>
</header>
<body>

    <main>
<?php
        echo'
            <div class="container">
                <div id="colonne1">
                    <form class ="form"method="POST" action="">
                    <h3>Adresse de livraison</h3>';
                    $idclient=$_SESSION["idclient"];
                    $requete = $dbh->prepare("SELECT idadresse FROM {$prefix}_client where idclient=$idclient");
                    $requete->execute();
                    $idadresse = $requete->fetch(PDO::FETCH_COLUMN, 0);
                    $query = $dbh->prepare("SELECT * FROM {$prefix}_adresse a  inner JOIN {$prefix}_client c on a.idadresse=c.idadresse  WHERE a.idadresse =?;");
                    $query->execute(array($idadresse));
                    $r= $query->fetch();
                    
                    // Permet de pré-remplir le formulaire d'adresse avec les informations déja posséedées sinon affiche un formulaire à remplir
                            if(isset ($r["numrue"])){
                                echo'<label>Numéro de rue</label></br><input type="number" class ="boutForm" name="numrue" value="'.$r["numrue"].'"/>';
                            }
                        else if (empty($r["numrue"])) {
                                echo'<label>Numéro de rue</label></br><input type="number" class ="boutForm" name="numrue" value=""/>';
                            }
    
                            if(isset ($r["nomrue"])){
                                echo'<label>Nom de rue</label></br><input type="text" class ="boutForm" name="nomrue" value="'.$r["nomrue"].'"/>';
                            }
                        else if (empty($r["nomrue"])) {
                                echo'<label>Nom de rue</label></br><input type="text" class ="boutForm" name="nomrue" value=""/>';
                            }

                            if(isset ($r["info"])){
                                echo'<label>Informations Complémentaires</label></br><input type="text" class ="boutForm" name="info" value="'.$r["info"].'"/>';
                            }  else if (empty($r["info"])) {
                                echo'<label>Informations Complémentaires</label></br><input type="text" class ="boutForm" name="info" value=""/>';
                            }
    
                            if(isset ($r["codepostal"])){
                                echo'<label>Code Postal</label></br><input type="text" class ="boutForm" name="codepostal" value="'.$r["codepostal"].'"/>';
                            }
                        else if (empty($r["codepostal"])) {
                                echo'<label>Code Postal</label></br><input type="text" class ="boutForm" name="codepostal" value=""/>';
                            }
    
                            if(isset ($r["ville"])){                        
                                echo'<label>Ville</label></br><input type="text"class ="boutForm" name="ville" value="'.$r["ville"].'"/>';
                            }
                        else if (empty($r["ville"])) {
                                echo'<label>Ville</label></br><input type="text"class ="boutForm" name="ville" value=""/>';
                            }
                        
                        ?>
                    </form>
                </div>
                <div id="colonne2">
                    <div class="blochaut">
                        <div>
                            <p class="text-center">Sélectionnez votre méthode puis cliquez sur "Continuer"</p>
                        </div>
                        <fieldset>
                            <legend>Choisissez votre moyen de paiement</legend>

                            <!-- Choix entre CB et PayPal-->
                            <div class ="champs">
                                <input type="radio" class="boutCB" name="drone" onClick="afficherBloc()" onClick="masquerBlocPP()" value="mastercard">
                                <label for="mastercard">Carte Bancaire </label>
                                <img src="images/icon/cbIcon.png" alt="logo cb">
                                <img src="images/icon/visa.png" alt="logo visa">
                                <img src="images/icon/mastercard.png" alt="logo mc">
    
                            </div>
                            <div class ="champs">
                                <input type="radio" class="boutCB" name="drone" onClick="afficherBlocPP()" onClick="masquerBlocCB()" value="paypal">
                                <label for="paypal">Paypal</label>
                                <img src="images/icon/paypalIcon.png" alt="logo paypal">
                            </div>
                        </fieldset>   
                    </div>

                    <!-- bloc qui affiche le formulaire bancaire-->
                    <div id = "choixCB">
                        <form action="" method="post">
                        <h3>Informations bancaires</h3>
                                <label for="cardNumber">Numéro de carte :</label><br><input type="text" id="cardNumber" name="cardNumber" value="" min="0" onblur="checkCard()"  required><br>
                                <label>Nom</label></br><input  class =" boutForm" type="text" name="titulairenom" value="" required></br>
                                <label>Date D'expiration mm/aa</label></br>
                                <input  class =" boutForm" type="number" name="exprmois" value="" style="width: 4em;" min="0" max="12" required>
                                <input  class =" boutForm" type="number" name="exprannee" value="" style="width: 4em;" required></br>
                                <label>Cryptograme</label></br>
                                <input  class =" boutForm"type="number" name="crypto" value="" min="0" max="999" style="width: 20%;" required></br>
                        </form>
                        <div class="navigation">
                            <form class="env" action="commandeValide.php" method="POST">
                                <input type="submit" name="supprimer" value="Commander" class="valider"  ></input>
                            </form>
                        </div>
                    </div>
                    
                    <!-- bloc qui affiche le formulaire paypal-->
                    <div id = "choixPP">
                        <form method="POST" action="">
                            <h3>Informations paypal</h3>
                            <label>Nom</label></br><input class =" boutForm" type="text" name="nom" /></br>
                            <label>Adresse mail</label></br><input class ="boutForm" type="text" name="mail" /></br>
                            <label>Mot de passe</label></br><input class ="boutForm" type="password" name="mdp" /></br>
                        </form>
                            <div class="navigation">
                                <form class="env" action="" method="POST">
                                    <input type="submit" name="supprimer" class="validerPP" value = "Commander" ></input>
                                </form>
                            </div>
                    </div>
                </div>
            </div>

        
            <script>
                var bouton= document.getElementsByClassName("valider");
                bouton[0].style.backgroundColor="grey";
                bouton[0].disabled = true; //rend bouton incliquable tant que numéro de carte invalide


                //Fonction qui affiche le formulaire bancaire et masque le formulaire PayPal si il est affiché
                let d2 = document.getElementById("choixCB");
                let d1 = document.getElementById("choixPP");
                function afficherBloc(){
                    d1.style.display ="none";
                    if(getComputedStyle(d2).display != "none"){
                        d2.style.display = "none";
                    } else {
                    d2.style.display = "flex";
                    }
                };
                
                //Fonction qui affiche le formulaire PayPal et masque le formulaire Bancaire si il est affiché
                let d3 = document.getElementById("choixPP");
                let d4 = document.getElementById("choixCB");
                function afficherBlocPP(){
                    d4.style.display="none";
                    if(getComputedStyle(d3).display != "none"){
                        d3.style.display = "none";
                    } else {
                    d3.style.display = "flex";
                    }
                };
                
            </script>
            <script>
                     function checkCard() {
    // Récupère le numéro de carte saisi par l'utilisateur
    var cardNumber = document.getElementById("cardNumber").value;
    var bouton= document.getElementsByClassName("valider");
    
    // Vérifie que le numéro de carte ne contient que des chiffres
    if (/^\d+$/.test(cardNumber)) {
      // Calcule le chiffre de contrôle en utilisant l'algorithme de Luhn
      var sum = 0;
      for (var i = 0; i < cardNumber.length; i++) {
        var digit = parseInt(cardNumber[i]);
        if ((i + cardNumber.length) % 2 === 0) {
          digit *= 2;
          if (digit > 9) {
            digit -= 9;
          }
        }
        sum += digit;
      }
      
      // Vérifie si le chiffre de contrôle est valide
      if (sum % 10 === 0) {
        bouton[0].style.backgroundColor="#1F4F51";  
        bouton[0].disabled = false; //rend le bouton cliquable
        bouton[0].onmouseover = function() {
            this.style.backgroundColor = "#FFD365";
        }

        bouton[0].onmouseout = function() {
            this.style.backgroundColor = "#1F4F51";
        }

      } else {
        alert("Carte non valide !");
        bouton[0].style.backgroundColor="grey";
        bouton[0].disabled = true; //rend le bouton incliquable

      }
    } else { //si autre chose que des chiffres
      alert("Le numéro de carte ne doit contenir que des chiffres !");
      bouton[0].style.backgroundColor="grey";
      bouton[0].disabled = true; //rend le bouton incliquable

    }
  }


</script>
    </main>
    
</body>

<footer>
    <?php include 'piedpageReduit.php' ?>
</footer>
</html>
