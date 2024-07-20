<?php
    include('connect_params.php');
    session_start();
    if(!isset($_SESSION["idvendeur"])){
      header('Location: ./connexionVendeur.php');    
    }
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
        <link rel="stylesheet" type="text/css" href="style/footer.css" />
        <link rel="stylesheet" type="text/css" href="style/styleAfficherCatalogue.css"> 
        <link rel="stylesheet" type="text/css" href="style/headerpro.css" />

    </head>
    <!-- corps -->
    <body>
    <header>
      <?php include 'entetePro.php' ?>    
    </header>
      <main>
        <!-- recherche dans la base en fonction des caractères de la recherche -->
        
            <!-- Affichage du nombre de résultats trouvés -->
            <div id="resultats">
            <div class="nbr">
                 <p>Catalogue Importer</p>
                </div>
                <!-- Liste des articles de la recherche -->
                <?php
        if (is_uploaded_file($_FILES["fichier"]["tmp_name"]) && $_FILES["fichier"]["type"]== "text/csv"){
            $filetmp=$_FILES["fichier"];
            if($_FILES["fichier"]["size"]>0){

                $file=fopen($_FILES["fichier"]["tmp_name"],"r");
                

                $row =0;
                ?>
                
                <form id="formulaire"enctype="multipart/form-data" method="post"  action="fichierUpload.php">
                <?php
                while ($column = fgetcsv($file,1024,";")) {//insertion dans article
                    if($row!=0){
                        echo'<ul class="listeResultat">';
                          // affichage de chaque article
                            echo'<div class="article1">
                              <div class="imageart">'; // affichage de l'image 
                                  ?>
                                  <div class="imGlobal">
                                  <div class="img-preview"></div>
                                    <input type="file" accept="image/*" class="choose-file" name="file<?php echo $row?>"/>
                                  </div>
                                </div>
                                  <div class="contient">
                                    <div class="description"> 
                                      <div class="ladescription">
                                        <p> Description : </p>
                                  <?php
                                    if(strlen($column[3])<200){
                                      echo $column[3];
                                    }else{
                                      echo '<p class="petit">';
                                      for($i=0;$i<=200;$i++){
                                        echo $column[3][$i];
                                      }
                                      echo '... ';
                                      echo '</p>';
                                      echo '<p class="grand">'.$column["3"].'</p>';
                                      echo '<a class="plus">Voir plus</a>';
                                    } 
                                  echo '  
                                  </div>
                                </div>  
                                <div class="info">';
                                      
                                        echo '<p class="prixHT"> Prix hors taxe : '.$column["1"].'€</p>';
                                      // <p class="prixArt"> '.$row["prixttc"].'€<span>/article</span></p>
                                      echo '
                                      <p class="prixCoutant"> Prix Coutant : '.$column["2"]. '€ </p> 
                                      <p>Quantite : '.$column["4"].'</p>                                    
                                </div>                      
                              </div>
                            </div>
                          
                      </ul>
                  </div>';
                  $row++;
                }else{
                  $row++;
                }
                    
                }
            }else{
                echo "fichier vide";
            }
        }else{
        echo "Erreur pas de fichier ou pas bonne extension (.csv) ou pas de nom";


    } 
    
    ?>

    <div class="bouton">
    <form action="fichierUpload.php" method="post">
        <?php echo '<input name="catalogue" type="hidden" value='.$filetmp["name"].' />';?>
        <input class= "ajouter" type="submit" name="submit" value="Valider"/> 
    </form>
    <form action="importArticle.php" method="post">
      <input class= "ajouter" type="submit" name="submit" value="Annuler"/> 
    </form>
    </div>
      </main>
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
      <script>
            const chooseFiles = document.getElementsByClassName("choose-file");
            const imgPreviews = document.getElementsByClassName("img-preview");

            for (let i = 0; i < chooseFiles.length; i++) {
                chooseFiles[i].addEventListener("change", function () {
                    getImgData(i);
                });
            }

            function getImgData(index) {
                const files = chooseFiles[index].files[0];
                if (files) {
                    const fileReader = new FileReader();
                    fileReader.readAsDataURL(files);
                    fileReader.addEventListener("load", function () {
                        imgPreviews[index].style.display = "block";
                        imgPreviews[index].innerHTML = '<img src="' + this.result + '" />';
                    });
                }
            }

        </script>
    </body>
