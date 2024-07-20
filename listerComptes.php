<?php
    include('connect_params.php');
    session_start();
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta charset="utf-8" />
    <title>ALIZON</title>
    <link rel="stylesheet" type="text/css" href="style/headeradmin.css" />
    <link rel="stylesheet" type="text/css" href="style/styleListerComptes.css" />
</head>
<body>
    <header>
        <div class="haut">
            <?php include 'enteteAdmin.php' ?>
        </div>
    </header>
    <main>    
        
        <!-- Tab links -->
        <div class="tab" style="padding-left: 8em;">
            <button class="tablinks" onclick="openTable(event, 'Clients')">Clients</button>
            <button class="tablinks" onclick="openTable(event, 'Vendeurs')">Vendeurs</button>
        </div>
        
        <!-- client tab par default grace a display block-->
        <div id="Clients" class="tabcontent" style="display: block;">
            <table class="tableau">
                <thead> <tr> <th>Nom <img src="images/icon/doublefleche.png" width=10px /></th> <th>Prenom <img src="images/icon/doublefleche.png" width=10px /></th> <th>Email <img src="images/icon/doublefleche.png" width=10px /></th> <th>Téléphone <img src="images/icon/doublefleche.png" width=10px /></th> </tr> </thead>
                <?php 
                $sth=$dbh->prepare("SELECT * FROM {$prefix}_client;");
                $sth->execute();
                $c = null;
                while($c = $sth->fetch(PDO::FETCH_ASSOC)){
                    echo "<tr onclick='sub(".$c["idclient"].")'><td>".$c["nom"]."</td> <td>".$c["prenom"]."</td> <td>".$c["email"]."</td> <td>".$c["numtel"]."</td> </tr>";
                }
                ?>
            </table> 
        </div>
        
        <div id="Vendeurs" class="tabcontent">
            <table class="tableau">
                <thead> <tr> <th>Nom <img src="images/icon/doublefleche.png" width=10px /></th> <th>Raison <img src="images/icon/doublefleche.png" width=10px /></th> <th>Adresse <img src="images/icon/doublefleche.png" width=10px /></th> <th>Contact <img src="images/icon/doublefleche.png" width=10px /></th> <th>N°Siret <img src="images/icon/doublefleche.png" width=10px /></th> <th>Status <img src="images/icon/doublefleche.png" width=10px /></th> </tr> </thead>
                <?php
                $sth=$dbh->prepare("SELECT * FROM {$prefix}_vendeur natural join {$prefix}_adresse");
                $sth->execute();
                $v = null;
                while($v = $sth->fetch(PDO::FETCH_ASSOC)){
                    $adresse = $v["numrue"]." ".$v["nomrue"]." ".$v["ville"]." ".$v["codepostal"];
                    if($v['active'] == true){
                        echo "<tr onclick='subvendeur(".$v["idvendeur"].")' ><td>".$v["nom"]."</td> <td>SARL</td> <td>$adresse</td> <td>".$v["email"]."</td> <td>".$v["siret"]."</td> <td>Actif</td>  </tr>";
                    }else{
                        echo "<tr onclick='subvendeur(".$v["idvendeur"].")' ><td>".$v["nom"]."</td> <td>SARL</td> <td>$adresse</td> <td>".$v["email"]."</td> <td>".$v["siret"]."</td> <td>Non-Actif</td>  </tr>";
                    }
                }
                ?>
            </table>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>


<script>
    function sub(id) {
        var f = document.createElement('form');
        f.action='modifCompte.php';
        f.method='POST';

        var i=document.createElement('input');
        i.type='hidden';
        i.name='idclient';
        i.value=id;
        f.appendChild(i);
        document.body.appendChild(f);
        f.submit();
    }

    function subvendeur(id) {
        var f = document.createElement('form');
        f.action='vendeur.php';
        f.method='POST';

        var i=document.createElement('input');
        i.type='hidden';
        i.name='idvendeur';
        i.value=id;
        f.appendChild(i);
        document.body.appendChild(f);
        f.submit();
    }
    
    //gere changement de table
    function openTable(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
    }

    //permet de trier le tableau
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
