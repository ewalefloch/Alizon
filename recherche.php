<?php
    include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';
    //unset($_SESSION["panier"]); vide panier session
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);

    //<-----debut php pour l'ajout au panier-------> 
    if (isset($_POST["ajouter"])) {
      if (isset($_SESSION["idclient"])){
          $res = $dbh->query('SELECT count(*) from '.$prefix.'_panier where idclient='.$_SESSION["idclient"].' and idarticle='.$_POST["idarticle"])->fetchColumn();
          if($res == 0){ //si il n'y a pas deja un panier de cette article pour ce client 
              $req = $dbh->prepare("INSERT INTO {$prefix}_panier(idclient,idarticle,quantite) values (?,?,?)");
              $req->execute(array($_SESSION["idclient"], $_POST["idarticle"], 1));
          }
          else{
              $quantite = $dbh->query('SELECT quantite from '.$prefix.'_panier where idclient='.$_SESSION["idclient"].' and idarticle='.$_POST["idarticle"])->fetchColumn();
              $req = $dbh->prepare('UPDATE '.$prefix.'_panier set quantite=? where idclient='.$_SESSION["idclient"].' and idarticle='.$_POST["idarticle"]);
              $req->execute(array($quantite+1));
          }
      }else{
          $dejaDansPanier=false;
          if(!empty($_SESSION["panier"])){
              foreach($_SESSION["panier"] as $row){
                  if($row["id"]==$_POST["idarticle"]){
                      $dejaDansPanier = true;
                  }
              }
              
              if(!$dejaDansPanier){
              array_push($_SESSION["panier"],(array("id"=>$_POST["idarticle"],"quantite" =>1)));
              }
          }else{
              $_SESSION["panier"]=array(array("id"=>$_POST["idarticle"],"quantite" =>1));
          }
      }
    }
    //<----- fin php pour l'ajout au panier------->

    function dateValide($dateDebut,$dateFin){ // fonction pour vérfier si la remise est valide en onction des dates
      date_default_timezone_set('Europe/Paris');
      if($dateDebut<=date('Y-m-d') && date('Y-m-d')<$dateFin){
          return true;
      }else{
          return false;
      }
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
        <link rel="stylesheet" type="text/css" href="style/header.css" />
        <link rel="stylesheet" type="text/css" href="style/footer.css" />
        <link rel="stylesheet" type="text/css" href="style/styleRecherche.css"> 
    </head>
    <!-- corps -->
    <body>
      <header> 
        <?php include 'entete.php' ?>
      </header>
      <main>
        <!-- recherche dans la base en fonction des caractères de la recherche -->
        <?php 
          $keywords=$_GET["keywords"];
          if(!empty($keywords)) {
              $words=explode(" ",trim($keywords));
              for($i=0;$i<count($words);$i++)
                  $kw[$i]="UPPER(nom) like UPPER('%".$words[$i]."%')";
              $res=$dbh->prepare("SELECT * from {$prefix}_article a inner join {$prefix}_image i on a.idarticle=i.idarticle inner join {$prefix}_souscategorie s on a.idsouscategorie=s.idsouscategorie inner join {$prefix}_categorie c on s.idcategorie=c.idcategorie where ".implode(" or ",$kw));
              $res->setFetchMode(PDO::FETCH_ASSOC);
              $res->execute();
              $tab=$res->fetchAll();
          }
          if(isset($_POST["ajouter"])){
            echo '<p id="popInsCon">Article ajouté au panier</p>';
          }        
        ?>
            <!-- Affichage du nombre de résultats trouvés -->
            <div id="resultats">
                <div class="nbr">
                  <?=count($tab)." ".(count($tab)>1?"Résultats trouvés":"Résultat trouvé") ?>
                </div>
                <!-- Liste des articles de la recherche -->
                <ul class="listeResultat">
                  <?php
                    // affichage de chaque article
                    foreach ($tab as $row) {
                      $numArt = $row["idarticle"];
                      echo'<div class="article1">
                        <div class="imageart">'; // affichage de l'image 
                          if(file_exists("./".$row["urlimage"])){
                            echo '<img src='.$row["urlimage"].' alt="img">'; //a changer plus tard avec $row["image"]                         
                          }else{
                            echo '<img src="images/imgArticle/notFound.png" alt="notFound">'; //a changer plus tard avec $row["image"]
                          } 
                          if ($row["enremise"] == true) { // étiquette si il y a une réduction
                            $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$numArt");
                            foreach ($remise as $row_remise){
                              if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                                echo '<div style="position: absolute;
                                        background-color: #ff0000d2;
                                        color: white;
                                        border-radius: 2em;
                                        border: solid;
                                        border-width: 2px;
                                        border-color: white;
                                        padding: 15px 5px 0 5px;
                                        height: 40px;" 
                                        class="bloc-reduc-recherche">'; // problème de style
                                    $reduc = floatval($row_remise["remise"]);
                                    echo '<p>-'.$reduc.'%</p>';
                                echo '</div>';
                              }
                            }
                          }
                        echo'
                        </div>
                        <div class="contient">
                          <div class="description"> 
                              <form action="article.php" method="POST">
                                  <input type="hidden" name="detailArticle" value='.$row["idarticle"].' >
                                  <input type="submit" class="titre" name="nom" value="'.$row["nom"].'" >               
                              </form>
                            <div class="ladescription">';
                              if(strlen($row["descript"])<200){
                                echo $row["descript"];
                              }else{
                                echo '<p class="petit">';
                                for($i=0;$i<=200;$i++){
                                  echo $row["descript"][$i];
                                }
                                echo '... ';
                                echo '</p>';
                                echo '<p class="grand">'.$row["descript"].'</p>';
                                echo '<a class="plus">Voir plus</a>';
                              } 
                            echo '  
                            </div>
                          </div>  
                          <div class="prixArticle">
                              <div id="prix">';
                                if ($row["enremise"] == true) {
                                  $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$numArt");
                                  foreach ($remise as $row_remise){
                                    if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                                      echo '<p class="prixArt"> '.number_format($row_remise["prixpromo"],2).'€<span>/article</span></p>';
                                      echo '<p style="color : red; text-decoration: line-through;" class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>';
                                    }
                                  }
                                }
                                else {
                                  echo '<p class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>';
                                }
                                // <p class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>
                                echo '
                                <p class="prixArtGris"> '.$row["prixht"]. '€ HT TVA: '.$row["tva"].'</p>
                              </div>
                              <div class="btPanier"> 
                                <form action="" method="POST">
                                    <input type="hidden" name="idarticle" value="'.$row["idarticle"].'">
                                    <input type="hidden" name="ajouter" value=true>
                                    <input type="image" src="images/icon/ajoutPanier.png" alt="Submit" width="48" height="48" name="ajouter" class="ajoutPanier">
                                </form>
                              </div>
                              <div class="btPanierResp"> 
                                <form action="" method="POST">
                                    <input type="hidden" name="idarticle" value="'.$row["idarticle"].'">
                                    <input type="hidden" name="ajouter" value=true>
                                    <input type="image" src="images/icon/ajoutPanier.png" alt="Submit" width="48" height="48" name="ajouter" class="ajoutPanier">
                                </form>
                              </div> 
                          </div>                      
                        </div>
                      </div>';
                    }
                    
                    ?>
                </ul>
            </div>


        </form>
      </main>
      <footer>
        <?php include 'piedpage.php' ?>
      </footer>
      <script>
        var elem2=document.getElementsByClassName("plus");
            for (let i = 0; i < elem2.length; i++) {
                elem2[i].addEventListener("click",()=> {
                  let plus=document.getElementsByClassName("plus");
                  if(plus[i].innerText=="Voir plus"){
                      document.getElementsByClassName("grand")[i].style.display="block";
                      document.getElementsByClassName("petit")[i].style.display="none";
                      plus[i].innerText="Voir moins";
                  }else{
                      document.getElementsByClassName("grand")[i].style.display="none";
                      document.getElementsByClassName("petit")[i].style.display="block";
                      plus[i].innerText="Voir plus";
                  }
                });
              }   
      </script>
    </body>
