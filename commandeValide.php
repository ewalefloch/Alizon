
<?php
  session_start();
  //unset($_SESSION["panier"]); supprimer panier
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
 

  $sth=$dbh->prepare("INSERT INTO {$prefix}_client(idadresse) VALUES(?);");
  $adresse=$dbh->prepare("INSERT INTO {$prefix}_adresse(codepostal,nomrue,numrue,infocomplementaire,ville) VALUES(?,?,?,?,?);");

  if(isset($_POST["supprimer"])){ //vider l'article
    $update=$dbh->prepare("UPDATE {$prefix}_article SET quantitestock = quantitestock- ? WHERE idarticle = ?;");
    $insertCommande=$dbh->query("INSERT into {$prefix}_commande(datecommande,datelivrer,dateexpedition,idclient,idadresse,etat)
        VALUES(current_date,current_date,current_date,$_SESSION[idclient],1,'EN livraison')");
    $lastIDCommande = $dbh->lastInsertId();

    $idarticle=$dbh->query("SELECT idclient,p.idarticle,nom,quantite,prixttc FROM {$prefix}_panier p inner join {$prefix}_article a on p.idarticle=a.idarticle  WHERE idclient=$_SESSION[idclient];");
    foreach($idarticle as $row){
        $insertQuantite = $dbh->prepare("INSERT INTO {$prefix}_quantite(idarticle,idcommande,quantite,prixachat)
        VALUES(?,?,?,?);");
        $insertQuantite->execute(array($row["idarticle"],$lastIDCommande,$row["quantite"],$row["prixttc"]));
        $update->execute(array($row["quantite"],$row["idarticle"]));
    }
    
    $nb=$dbh->prepare("delete from {$prefix}_panier where idclient=?");
    $nb->execute(array($_SESSION["idclient"]));
    $_POST["valide"] = FALSE;
} 

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
    <link rel="stylesheet" type="text/css" href="style/styleCommandeValide.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
    
</head>
<header>
    <?php include 'enteteReduit.php' ?>
</header>
<body>

    <main>
        <div id="commande">
            <div id="blocVal">
                <p>Votre commande est validée !</p>
                <img id="camion"src="images/icon/camion.gif" alt="commande validée">
                <a class="valider2" href="index.php">Retour à l'accueil</a>
            </div>
        </div>
    </main>
    
</body>

<footer>
    <?php include 'piedpageReduit.php' ?>
</footer>
</html>
