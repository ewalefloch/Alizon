DROP tables if exists sae301_a21._administrateur cascade;
            DROP tables if exists sae301_a21._panier cascade;
            DROP tables if exists sae301_a21._image cascade;
            DROP tables if exists sae301_a21._reponse cascade;
            DROP tables if exists sae301_a21._commentaire cascade;
            DROP tables if exists sae301_a21._remise cascade;
            DROP tables if exists sae301_a21._quantite cascade;
            DROP tables if exists sae301_a21._souscategorie cascade;
            DROP tables if exists sae301_a21._categorie cascade;
            DROP tables if exists sae301_a21._commande cascade;
            DROP tables if exists sae301_a21._client cascade;
            DROP tables if exists sae301_a21._coordonneesBancaires cascade;
            DROP tables if exists sae301_a21._adresse cascade;
            DROP tables if exists sae301_a21._article cascade; 
            DROP tables if exists sae301_a21._vendeur cascade;

CREATE TABLE sae301_a21._client(
idclient      INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
nom           VARCHAR(30),
prenom        VARCHAR(30),
email         VARCHAR(50) UNIQUE,
numtel       VARCHAR(20),
datenaissance DATE,
motdepasse    VARCHAR(50),
idcarte       INTEGER,
idadresse    INTEGER,
valider       BOOLEAN);

CREATE TABLE sae301_a21._commande(
idcommande    INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
datecommande  DATE,
datelivrer    DATE,
dateexpedition Date,
retours       BOOLEAN default FALSE,
idclient      INTEGER,
idadresse    INTEGER,
etat          VARCHAR(20),
prixcommande INTEGER default 0);

CREATE TABLE sae301_a21._commentaire(
idcommentaire INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
textecomment  VARCHAR(500),
note          INTEGER,
idclient    INTEGER,
utile       FLOAT,
idarticle INTEGER);

CREATE TABLE sae301_a21._reponse(
idreponse     INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
textereponse  VARCHAR(500),
idcommentaire INTEGER);

CREATE TABLE sae301_a21._adresse(
idadresse     INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
codepostal    INTEGER,
nomrue        VARCHAR(100),
numrue        INTEGER,
infocomplementaire VARCHAR(200),
ville         VARCHAR(50));

CREATE TABLE sae301_a21._administrateur(
idadmin       INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
nom           VARCHAR(30),
prenom        VARCHAR(30),
email         VARCHAR(50),
motdepasse    VARCHAR(50),
autorisation  INTEGER);

CREATE TABLE sae301_a21._panier(
idclient      INTEGER,
idarticle     INTEGER,
quantite      INTEGER,
PRIMARY KEY(idclient,idarticle));

CREATE TABLE sae301_a21._article(
idarticle     INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
nom           VARCHAR(30),
prixht        NUMERIC,
prixcoutant   NUMERIC,
descript      VARCHAR(5000),
quantitestock      INTEGER,
seuilalerte   INTEGER,
prixttc       NUMERIC,
idsouscategorie int,
enpromotion BOOL default false,
enremise    BOOL default false,
idvendeur int
);

CREATE TABLE sae301_a21._remise(
idarticle INT not null,
remise NUMERIC not null,
prixpromo NUMERIC,
date_debut DATE not null,
date_fin DATE not null,
PRIMARY KEY(idarticle,date_debut)
);

CREATE TABLE sae301_a21._categorie(
idcategorie   INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
libelle       VARCHAR(30) UNIQUE,
tva           FLOAT NOT NULL
);

CREATE TABLE sae301_a21._souscategorie(
idsouscategorie INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
idcategorie     INTEGER,
souslibelle   VARCHAR(30)UNIQUE
);

CREATE TABLE  sae301_a21._quantite(
idarticle     INTEGER,
idcommande    INTEGER,
quantite      INTEGER NOT NULL,
prixachat     FLOAT NOT NULL,
PRIMARY KEY(idarticle,idcommande));

CREATE TABLE sae301_a21._coordonneesBancaires(
idcarte       INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
cryptogramme  VARCHAR(5),
numcarte      VARCHAR(50),
dateexpiration DATE,
titulairecarte VARCHAR(30));

CREATE TABLE sae301_a21._image(
idarticle INT,
urlimage VARCHAR(500) NOT NULL,
PRIMARY KEY (idarticle,urlimage));

CREATE TABLE _vendeur(
idvendeur SERIAL PRIMARY KEY,
nom VARCHAR(20) NOT NULL,
email VARCHAR(100) NOT NULL,
mdp varchar(100) NOT NULL,
tel VARCHAR(100) NOT NULL,
active BOOL default TRUE,
idadresse int );

ALTER TABLE _vendeur
     ADD CONSTRAINT vendeur_FK_adresse FOREIGN KEY (idadresse) REFERENCES _adresse (idadresse);
     
ALTER TABLE _article
     ADD CONSTRAINT article_FK_vendeur FOREIGN KEY (idvendeur) REFERENCES _vendeur (idvendeur);

ALTER TABLE sae301_a21._client
     ADD CONSTRAINT client_FK_coordonnesBancaires FOREIGN KEY (idcarte) REFERENCES sae301_a21._coordonneesBancaires (idcarte);

ALTER TABLE sae301_a21._commande
     ADD CONSTRAINT commande_FK_client FOREIGN KEY (idclient) REFERENCES _client (idclient),
     ADD CONSTRAINT commande_FK_adresse FOREIGN KEY (idadresse) REFERENCES _adresse (idadresse);

ALTER TABLE sae301_a21._commentaire
     ADD CONSTRAINT commentaire_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle),
     ADD CONSTRAINT commentaire_FK_client FOREIGN KEY (idclient) REFERENCES _client (idclient);

ALTER TABLE sae301_a21._reponse
     ADD CONSTRAINT reponse_FK_commentaire FOREIGN KEY (idcommentaire) REFERENCES _commentaire (idcommentaire);

ALTER TABLE sae301_a21._panier
     ADD CONSTRAINT panier_FK_client FOREIGN KEY (idclient) REFERENCES _client (idclient),
     ADD CONSTRAINT panier_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);

ALTER TABLE sae301_a21._quantite 
     ADD CONSTRAINT commandeUnArticle_FK_commande FOREIGN KEY (idcommande) REFERENCES _commande (idcommande),
     ADD CONSTRAINT commandeUnArticle_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);
     
ALTER TABLE sae301_a21._image
     ADD CONSTRAINT image_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);
  
ALTER TABLE sae301_a21._souscategorie
    ADD CONSTRAINT _souscategorie_FK_categorie FOREIGN KEY (idcategorie) REFERENCES _categorie (idcategorie);
    
ALTER TABLE sae301_a21._remise
    ADD CONSTRAINT _remise_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);

delimiter //
CREATE TRIGGER sae301_a21.tg_InsertArticle
BEFORE INSERT
ON sae301_a21._article
FOR EACH ROW
BEGIN
	DECLARE idtmp INT;
    DECLARE tva FLOAT;
    select idcategorie from sae301_a21._souscategorie where idsouscategorie=NEW.idsouscategorie into idtmp;
    select c.tva from sae301_a21._categorie c where c.idcategorie=idtmp into tva;

    SET NEW.prixttc = NEW.prixht+NEW.prixht*tva;
END;//

delimiter ;


delimiter //

CREATE TRIGGER sae301_a21.tg_InsertPromotion
BEFORE INSERT
ON sae301_a21._remise
FOR EACH ROW
BEGIN
	DECLARE prix FLOAT;
    select prixttc from sae301_a21._article  where idarticle=NEW.idarticle into prix;
    SET NEW.prixpromo = prix - prix*(NEW.remise/100);
END//;

delimiter ;



delimiter //
CREATE TRIGGER sae301_a21.tg_InsertQuantite
AFTER INSERT
ON _quantite
FOR EACH ROW
BEGIN
    UPDATE _commande set prixcommande=prixcommande+NEW.prixachat*NEW.quantite where idcommande=NEW.idcommande;
END;//
delimiter ;













