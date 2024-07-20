<?php
    include('connect_params.php');
    session_start();
    //unset($_SESSION["panier"]); supprimer panier
    $prefix = 'sae301_a21.';
    
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $idclient=$_SESSION["idclient"];

    $etats = [];
    $dates = [];
    if(isset($_POST['numComRecept'])){
      ob_start();
      $commande = './simulateur/clientSite -l test -p test -r '.$_POST['numComRecept'];
      system($commande, $return);
      $sql = $dbh->prepare("UPDATE {$prefix}_commande SET etat='Livré' WHERE idcommande = ?");
      $sql->execute(array($_POST['numComRecept']));
      ob_end_clean();
    }

  function afficheLivraisonCommande($idcommande){
    global $etats;
    global $dates;
    //recupere l'etat de la livraison avec le simulateur
    //si la commande n'est pas deja présente alors la rajouter
    ob_start();
    $return = 0;
    $commande = './simulateur/clientSite -l test -p test -e '.$idcommande;
    system($commande, $return);
    //reccupere l'etat de la commande
    $commande = './simulateur/clientSite -l test -p test -i '.$idcommande;
    // $lines = system($commande, $return);
    ob_end_clean();
    
    $variable = array();
    $lastline = exec($commande, $variable);
    
    // if($return == 0){
      // PrisEnCharge=1,
      // TransportVersPlateformeRegionale=2,
      // TransportVersSiteLocal=3,
      // Livraison=4,
      // EnAttente=5,
      // Livre=6

      $code = intval($variable[0]);
      switch($code){
        default:
        case 1:
          $etat = 0; //Enregistrer
          break;

        case 2:
        case 3:
        case 4:
          $etat = 1; //en transit
          break;

        case 5:
          $etat = 2; //a récup
          break;

        case 6:
          $etat = 3; //Livré
          break;
      }
      array_push($etats, $etat);
    // }
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
    <link rel="stylesheet" type="text/css" href="style/stylemesCommandes.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
</head>

<body>
<header> 
    <?php include 'enteteReduit.php' ?>
            
</header>
    <main>
    
<h2>MES COMMANDES</h2>

        <?php
            try {
                $comm = $dbh->prepare("SELECT * from {$prefix}_commande c inner join {$prefix}_client a on c.idclient=a.idclient  ORDER BY idcommande DESC");
                $comm->execute();
                $compteur=0;
                if ($comm->rowCount()==0){
                    echo "<p style='text-align:center;'>Vous n'avez pas encore de commandes </p>";
                }
                else {
                    foreach ($comm as $indice=>$key ) {
                    afficheLivraisonCommande($key['idcommande']);
                    $commande = './simulateur/clientSite -l test -p test -i '.$key['idcommande'];
                    $variable = array();
                    $return = exec($commande, $variable);

                    echo '<div class="unecomm"><article class="commande_princ" >
                            <div class="maincomm">
                              <div class="carrecommande">
                                  <p>Commande numéro : '.$key["idcommande"].'</p>
                                  <p>Nom du destinataire : '.$key["nom"].'</p>
                                  <p>Prénom du destinataire : '.$key["prenom"].'</p>
                                  <p>Date de la commande : '.$key["datecommande"].'</p>
                                  
                                  
                              </div>';
                

                            
                            echo'    <p class="prix_com">Prix total : '.$key["prixcommande"].'</p>
                            </div>
                                  <div class="container">
                                    <div class="progresscontainer">
                                      <div class="progress" > </div>
                                      <div class="circle active">Enregistré</div>
                                      <div class="circle">En transit</div>
                                      <div class="circle">à récupérer</div>
                                      <div class="circle">Livré</div>
                                    </div>
                                </div>';
                                 
                              //affiche date de livraison
                              echo "<h4 style='margin-bottom:3em;'>Date de livraison estimée: $variable[1]</h4>";
  
                            echo '</article>';   

                        //bouton reception
                        
                      

                        
                  
                        $sql = $dbh->prepare("SELECT * from {$prefix}_commande 
                        inner join {$prefix}_quantite on _commande.idcommande=_quantite.idcommande inner join {$prefix}_article on _quantite.idarticle=_article.idarticle inner join {$prefix}_image on _image.idarticle=_article.idarticle where _commande.idcommande=$key[idcommande];");
                        $sql->execute();
                        echo'<p style="color:grey;"> Détail de la commande </p>';
                        echo '<img src="images/icon/flechebas.png" alt="fleche bas" title="fleche bas" class="butto" onclick="myFunction('.$compteur.')">';

                        echo '<section>';
                    foreach ($sql as $row){
                        
                        echo'<div class="item">
                            <figure>';
                            if(file_exists("./".$row["urlimage"])){
                                echo'<img src='.$row["urlimage"].' alt="mug" width="200em"> ';
                            }else{
                                echo'<img src="images/imgArticle/notFound.png" alt="notFound" width="200em">';
                            } 
                            echo '
                            </figure>
                                <article class="article1">
                                    <p class="nom_art">'.$row["nom"].'</p>
                                    <p class="quantite">Quantité : '.$row["quantite"].'</p>
                                </article>
                                <article class="article2">
                                    <p class="prix"> Prix :'.$row["prixachat"].'€/ article</p>
                                    <p class="prix">Prix total :'.$row["quantite"]*$row["prixachat"].'€</p>
                                </article>
                        
                            </div>';
                        }
                        echo '</section>
                        </div>';
                    echo '<br/>
                    <div class="barre"></div>';
                    $compteur++;
                    }
                
                       }
                  $dbh = null;
                    } catch (PDOException $e) {
                        print "Erreur !: " . $e->getMessage() . "<br/>";
                        die();
                    }
                ?>
               
</main>


<script>



  var progresscontainer = document.querySelectorAll(".progresscontainer");
  var currentActive = 1;

  var array = [];
  var arrayprog= [];

  <?php
  $i = 0;
  foreach($etats as $etat){
    echo "update($i, $etat);";
    $i++;
  }
?>
 /*update(0,2);
 update(1,3);*/

function update(intcommande,intcircle){
  var stepCircles= progresscontainer[intcommande].querySelectorAll(".circle");
  var progress = progresscontainer[intcommande].querySelectorAll(".progress");

  for(let min=1;min<=intcircle;min++){
    stepCircles[min].classList.add("active");
  }
 
 progress[0].style.width =(stepCircles.length - 1) * intcircle*10 + "%";

}

function myFunction(i) {
  var butt = document.getElementsByClassName("butto");
  var x = document.getElementsByTagName("section");
 
    butt[i].style.transform = "rotate(180deg)";
  if (x[i].style.display === "block") {
    x[i].style.display = "none";
    butt[i].style.transform = "none";
    
  } else {
    x[i].style.display = "block";
    
  }
  
}
                                  
                                  
</script>
</body>
</html>