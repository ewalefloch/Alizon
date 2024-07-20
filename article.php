<?php
  session_start();
  //unset($_SESSION["panier"]); supprimer panier
  $prefix = 'sae301_a21.';
  include('connect_params.php');
  $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

  if(empty($_POST["detailArticle"])){
      $numarticle=$_SESSION["detailArticle"];

  }else{
    $numarticle=$_POST["detailArticle"];
    $_SESSION["detailArticle"]=$numarticle;
  }

  if (isset($_POST["maj"])){
    $sth = $dbh->prepare("UPDATE {$prefix}_panier set quantite=? where idarticle=?;");
    $sth->execute(array($_POST["number"],$_POST["id"]));
    
  }
  if(isset($_SESSION["idclient"])){
    if (isset($_POST["ajouterPanier2"])) {
      $req = $dbh->prepare("UPDATE {$prefix}_panier set quantite=? where idclient=$_SESSION[idclient] and idarticle=$numarticle");
      $req->execute(array($_POST["number2"]));
    }else{
      $req = $dbh->query("SELECT quantite FROM {$prefix}_panier where idclient=$_SESSION[idclient] and idarticle=$numarticle");
      foreach ($req as $row){
        $_POST["number2"]=$row["quantite"];
      }
    }

    if (isset($_POST["ajouterPanier"])) {
      $nbarticle=$dbh->query("select * from {$prefix}_panier where idclient=$_SESSION[idclient] and idarticle=$_POST[id]");
      if($nbarticle->rowCount()==0){
      $req = $dbh->prepare("INSERT INTO {$prefix}_panier(idclient,idarticle,quantite) values (?,?,?)");
      $req->execute(array($_SESSION["idclient"],$_POST["id"], $_POST["number"]));
      $_POST["number2"]=$_POST["number"];
      }else{
        $_POST["number"]=1;
      }
    }else{
      $_POST["number"]=1;
    }
  }

  function dateValide($dateDebut,$dateFin){ // fonction pour vérfier si la remise est valide en fonction des dates
    date_default_timezone_set('Europe/Paris');
    if($dateDebut<=date('Y-m-d') && date('Y-m-d')<= $dateFin){
        return true;
    }else{
        return false;
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
    <link rel="stylesheet" type="text/css" href="style/styleArticle.css" />
    <link rel="stylesheet" type="text/css" href="style/header.css" />
    <link rel="stylesheet" type="text/css" href="style/footer.css" />
  </head>
  <body>
  <header> 
            <?php include 'entete.php'?>
    </header>

    <main>
    <?php
            if (isset($_POST["ajouterPanier"])) { 
                echo '<p id="popInsCon">Article ajouter au panier</p>';
            } 

            if (isset($_POST["ajouterPanier2"])) { 
              echo '<p id="popInsCon">Quantité mise à jour</p>';
          } 
                ?>
      <div class="monarticle">
        <div class="grpImg">
          
          <div class="imgArt">
            <figure class="imgArt1">
              <?php
              $imageUrl = $dbh->query("SELECT urlimage from {$prefix}_article a INNER JOIN {$prefix}_image i on a.idarticle=i.idarticle where a.idarticle={$numarticle}");
              foreach ($imageUrl as $row){  

              if(file_exists("./".$row["urlimage"])){
                                  echo '<img style="widtch:300px;height:300px" src='.$row["urlimage"].' alt="mug">'; //a changer plus tard avec $row["image"]
                      }else{
                                  echo '<img style="widtch:300px;height:300px" src="images/imgArticle/notFound.png" alt="notFound">'; //a changer plus tard avec $row["image"]
              }
            }   
            ?>
            </figure>
            <!--
            <figure class="imgArt2">
              <img src="./images/imgArticle/handspinner_rouge.jpg" alt="redhandspinner" width="150px">
            </figure>
            <figure class="imgArt3">
              <img src="./images/imgArticle/handspinner_bleu.jpg" alt="bluehandspinner" width="150px">
            </figure>--->
          </div>
        </div>
        <div class="description">
          <h2 class="titleDesc">
            <?php
              if(isset($_POST["ajouter"])){
                echo '<p id="popInsCon">Article ajouter au panier</p>';
              }

              $row2 = $dbh->query("SELECT * FROM {$prefix}_article a inner join {$prefix}_souscategorie s on a.idsouscategorie=s.idsouscategorie inner join {$prefix}_categorie c on s.idcategorie=c.idcategorie where idarticle=$numarticle ", PDO::FETCH_ASSOC)->fetch();
                      echo $row2["nom"];
                      
                      echo '
                          </h2>
                          <article class="descP"><p>'.$row2["descript"].'</p></article>
                          <article class="descPortable">';
                        if(strlen($row2["descript"])<200){
                            echo $row2["descript"];
                        }else{
                        echo '<p class="petit">';
                        for($i=0;$i<=200;$i++){
                            echo $row2["descript"][$i];
                        }
                        echo'... ';
                        echo '</p>';
                        echo '<p class="grand">'.$row2["descript"].'</p>';
                        echo'<a class="plus">Voir plus</a>';
                        }
                        echo'  </article>
                        </div>
                        <aside class="prix">
                          <h2>
                      ';
                      $bonneremise=false;
                      $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$numarticle");
                        foreach ($remise as $row_remise){
                          if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                            $bonneremise=true;
                          }
                        }
                      if ($row2["enremise"] == true && $bonneremise) {
                        $remise = $dbh->query("SELECT * FROM {$prefix}_remise where idarticle=$numarticle");
                        foreach ($remise as $row_remise){
                          if(dateValide($row_remise["date_debut"],$row_remise["date_fin"]) == true) {
                            echo"<a id='ancienprix'>".$row2["prixttc"]."€</a>
                            </br>";
                            echo number_format($row_remise["prixpromo"],2)."€";
                          }
                        }
                      }
                      else {
                        echo $row2["prixttc"]."€";
                      }

                      echo'
                          </h2>
                          <p> (TVA: '.$row2["tva"].' | '.$row2["prixht"].' € HT)  </p>
                          <article class="quantitereste">
                            <p>Quantite en stock :
                          
                      ';
                      echo $row2["quantitestock"];
                      echo '
                        </p>
                          </article>
                          <article class="blocquantite">
                          <p class="quantite">
                      ';

                      if (isset($_SESSION["idclient"])){
                        $statement = $dbh->query("SELECT quantite FROM  {$prefix}_panier  where idarticle=$numarticle and idclient=".$_SESSION["idclient"], PDO::FETCH_ASSOC);
                        $count = $statement->rowcount();
                        if($count<=0) {
                              echo '
                            <form action="" method="post">
                                  <label>Quantité :</label> 
                                  <input name="ajouterPanier" type="hidden" value="True" />
                                  <input name="id" type="hidden" value="'.$row2["idarticle"].'" />
                                  <input name="number" type="number" min="1" value="'.$_POST["number"].'"max="'.$row2["quantitestock"].'"/>
                                  <input class= "ajouter" type="submit" name="submit" value="Ajouter au panier"/> 
                            </form>';

                            echo '
                                </p> 
                                </article>
                            ';
                        }else{
                          $statement2 = $dbh->query("SELECT * FROM {$prefix}_article a inner join {$prefix}_souscategorie s on a.idsouscategorie=s.idsouscategorie inner join {$prefix}_categorie c on s.idcategorie=c.idcategorie inner join {$prefix}_panier p on a.idarticle=p.idarticle where a.idarticle=$numarticle ");
                          $row2 = $statement2->fetch(PDO::FETCH_ASSOC);
                            echo'     
                            <form action="" method="post">
                            <label>Quantité :</label> 
                                  <input name="ajouterPanier2" type="hidden" value="True" />
                                  <input name="id" type="hidden" value="'.$numarticle.'" />
                                  <input name="number2" type="number" min="1" value="'.$_POST["number2"].'"max="'.$row2["quantitestock"].'"/>
                                  <input class= "ajouter" type="submit" name="submit" value="Modifier Quantite"/> 
                            </form>';         
                            echo '
                                </p> 
                                </article>
                            ';
                          
                        }
                      }else{
                        $statement2 = $dbh->query("SELECT * FROM {$prefix}_article a inner join {$prefix}_souscategorie s on a.idsouscategorie=s.idsouscategorie inner join {$prefix}_categorie c on s.idcategorie=c.idcategorie where idarticle=$numarticle ", PDO::FETCH_ASSOC);
                        foreach ($statement2 as $row2){
                        if (isset($_POST["ajouterPanier3"])) {
                          echo '<p id="popInsCon">Article ajouter au panier</p>';
                          $dejaDansPanier=false;
                          if(!empty($_SESSION["panier"])){
                              foreach($_SESSION["panier"] as $row){
                                  if($row["id"]==$numarticle){
                                      $dejaDansPanier = true;
                                  }
                              }
                              if(!$dejaDansPanier){
                              array_push($_SESSION["panier"],(array("id"=>$numarticle,"quantite" =>$_POST["number"])));

                              }
                          }else{
                              $_SESSION["panier"]=array(array("id"=>$numarticle,"quantite" =>$_POST["number"]));

                          }
                       }
    
                      if(!isset($_POST["number"])){
                        $_POST["number"]=1;
                        $quantite=1;
                      }else{
                        $quantite=$_POST["number"];
                        $i=0;
                        foreach($_SESSION["panier"] as $row){
                          if($row["id"]==$numarticle){
                              $dejaDansPanier = true;
                              $numTableau = $i;
                          }
                          $i++;
                        }
                        $_SESSION["panier"][$numTableau]["quantite"]=$quantite;
                      }
                        echo '
                          <form action="" method="post"> 
                            <label> Quantité : </label> 
                            <input name="ajouterPanier3" type="hidden" value="True"/>
                            <input name="id" type="hidden" value="'.$numarticle.'"/>
  
                            <input name="number" type="number" min="1" value="'.$_POST["number"].'" max="'.$row2["quantitestock"].'">
                            <input class="ajouter" type="submit" name="submit" value="Ajouter au panier"> 
                          </form> '  ;                  
                          echo '
                              </p> 
                              </article>
                          ';
                      }

                      }
        

                    
                
          ?>
        </aside>
      </div>
      <div class="artSimil">
        <h2>Articles similaires</h2>
        <picture class="img1">
          <img src="./images/imgArticle/images.jpeg" alt="cube" width="250px">
        </picture>
        <picture class="img2">
          <img src="./images/imgArticle/handspinner_batman.jpg" alt="handspinner_batman" width="250px">
        </picture>
        <picture class="img3">
          <img src="./images/imgArticle/lego_paris.jpeg" alt="lego_paris" width="250px">
        </picture>
        <picture class="img4">
          <img src="./images/imgArticle/billig-crepe-maker 3.png" alt="billig-crepe-maker" width="250px">
        </picture>
        <picture class="img5">
          <img src="./images/imgArticle/sioizig 3.png" alt="sioizig" width="250px">
        </picture>
      </div> 
      <div class="comment">
        <article class="avis1">
          <h2>Titre commentaire</h2>
          <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Odio dolorem recusandae doloribus neque temporibus corrupti excepturi veniam reiciendis? Quod dolorem repellendus quibusdam eos blanditiis at minus doloremque, id tenetur nesciunt?
          Commodi vitae pariatur dolor cum nostrum, at corporis voluptatum consequatur eos est atque temporibus praesentium deleniti modi, placeat accusamus soluta. Tempora expedita nihil reiciendis aliquam rerum eos veniam magni commodi.</p>
        </article>
      </div>
    </main> 
    <footer>
      <?php include 'piedpage.php'?>
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
</html>
