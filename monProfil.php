
<?php
    session_start();
    $prefix = 'sae301_a21.';
    $idclient=$_SESSION["idclient"];
    include('connect_params.php');
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);
    if(isset($_POST["supression"])){
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        $comm = $dbh->prepare("DELETE from {$prefix}_client where idclient='$idclient'");
        $comm->execute();
        unset($_SESSION["idclient"]);
        header("Location: index.php");    
    }

    if(isset($_POST["deconnexion"])){
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        unset($_SESSION["idclient"]);
        header("Location: index.php");    

    }
    $idclient=$_SESSION["idclient"];

    

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
        <link rel="stylesheet" type="text/css" href="style/styleMonProfil.css"> 
    </head>
    <!-- corps -->
    <body>
        <header> 
                <?php include 'enteteReduit.php'?>
        </header>
        <h1 class="tit">Mon Profil</h1>
        <main>
            
            <div class="centre">
                <div class="detail">
                    <?php
                    try {
                        $sum=0;
                        
                        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
                        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

                        $comm = $dbh->prepare("SELECT * from {$prefix}_client where idclient='$idclient'");
                        $comm->execute();
                        foreach ($comm as $row){
                            echo '<span><a class="info" >Nom :</a>  <a class="info2"> '.$row['nom'].'</a></span><br>
                            <span><a class="info" >Prénom :</a> <a class="info2"> '.$row['prenom'].'</a></span><br>
                            <span><a class="info" >Email :</a> <a class="info2">'.$row['email'].' </a></span><br>
                            <span><a class="info" >Numéro :</a> <a class="info2">'.$row['numtel'].' </a></span><br>
                            <span><a class="info" >Date de naissance :</a>  <a class="info2"> '.$row['datenaissance'].'</a></span><br>';
                        }
                        
                    } catch (PDOException $e) {
                        print "Erreur !: " . $e->getMessage() . "<br/>";
                        die();
        
                    }
                        
                    ?>
                
                </div>
                <div class="boutons">
                    
                    <a href="modifCompte.php" class="bouton">Modifier mon compte</a>
                    <form action="" method="POST"> 
                    <input type="hidden" name="deconnexion" value="true"/>
                    <input class="bouton" type="submit" name="ajouter" value="Deconnexion" ></input>
                    </form> 
                    <a class="bouton" href="mesCommandes.php">Historique des commandes</a>
                    
                </div>
                
        </main>
        <footer>
            <?php include 'piedpage.php'?>
        </footer>
    </body>   
</html>
