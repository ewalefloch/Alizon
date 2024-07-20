<?php 
include('connect_params.php');
    session_start();
    $prefix = 'sae301_a21.';

    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE ,PDO::FETCH_ASSOC);
    if(!isset($_SESSION["idvendeur"])){
        header('Location: ./connexionVendeur.php');    
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
        <link rel="stylesheet" type="text/css" href="style/headerpro.css" />
        <link rel="stylesheet" type="text/css" href="style/footer.css" />
        <link rel="stylesheet" type="text/css" href="style/articlesVendeur.css" />
    </head>
<body>
    <header> 
        <?php include 'entetePro.php'; ?>
    </header>
<main>
    <?php $articles = $dbh->query("SELECT * FROM {$prefix}_article a INNER JOIN {$prefix}_souscategorie sc ON a.idsouscategorie=sc.idsouscategorie INNER JOIN {$prefix}_categorie c on sc.idcategorie=c.idcategorie where idvendeur=2 ORDER BY idarticle  ");?>
    <h1>Mes articles</h1>
    <table>
        <thead>
            <tr>
                <th class="petit"></th>
                <th>IdArticle <img src="images/icon/doublefleche.png" width=10px /></th>
                <th>Nom <img src="images/icon/doublefleche.png" width=10px /></th>
                <th class="grand">Prix Coutant <img src="images/icon/doublefleche.png" width=10px /></th>
                <th>Prix HT <img src="images/icon/doublefleche.png" width=10px /></th>
                <th>Prix TTC <img src="images/icon/doublefleche.png" width=10px /></th>
                <th class="grand">Quantité stock <img src="images/icon/doublefleche.png" width=10px /></th>
                <th>Catégorie <img src="images/icon/doublefleche.png" width=10px /></th>
                <th class="grand">SousCatégorie <img src="images/icon/doublefleche.png" width=10px /></th>
                <th class="grand">En Promotion <img src="images/icon/doublefleche.png" width=10px /></th>
                <th>En Remise <img src="images/icon/doublefleche.png" width=10px /></th>
                <th class="petit"><img src="images/icon/editBlanc.png" width=15px></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $taille=$articles->rowCount();
            foreach($articles as $row){?>
                <tr>
                <td><input type="checkbox" ></td>
                <td><?php  echo $row["idarticle"];?></td>
                <td>
                    <form action="article.php" method="POST">
                        <input type="hidden" name="detailArticle" value="<?php echo $row["idarticle"] ?>">
                        <input type="submit" class="titre" name="nom" value="<?php echo $row["nom"] ?>">
                    </form> 
                </td>
                <td><?php  echo $row["prixcoutant"];?></td>
                <td><?php  echo $row["prixht"];?></td>
                <td><?php  echo $row["prixttc"];?></td>
                <td><?php  echo $row["quantitestock"];?></td>
                <td><?php  echo $row["libelle"];?></td>
                <td><?php  echo $row["souslibelle"];?></td>
                <td><?php  if($row["enpromotion"]){echo "Oui";}else{echo "Non";}?></td>
                <td><?php  if($row["enremise"]){echo "Oui";}else{echo "Non";}?></td>
                <td>
                    <form action="modifierArticle.php" method="POST">
                        <input type="hidden" name="modifArticle" value="<?php echo $row["idarticle"]; ?>">
                        <input type="image" src="images/icon/edit.png" width=15px name="modifArticle" value="<?php echo $row["idarticle"]; ?>">
                    </form>
                </td>
                </tr>
                <?php
                }
             
            ?>
        </tbdoy>

    </table>
</main>
<footer>
            <?php include 'piedpage.php' ?>
</footer>
</body>
</html>
<script>
    const compare = (ids, asc) => (row1, row2) => {
        const tdValue = (row, ids) => row.children[ids].textContent;
        const tri = (v1, v2) => v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2);
        return tri(tdValue(asc ? row1 : row2, ids), tdValue(asc ? row2 : row1, ids));
    };

    const tbody = document.querySelector('tbody');
    const thx = document.querySelectorAll('th');
    const trxb = tbody.querySelectorAll('tr');
    thx.forEach(th => th.addEventListener('click', () => {
        let classe = Array.from(trxb).sort(compare(Array.from(thx).indexOf(th), this.asc = !this.asc));
        classe.forEach(tr => tbody.appendChild(tr));
    }));
</script>