DROP SCHEMA IF EXISTS sae301_a21 CASCADE;
CREATE SCHEMA sae301_a21;
SET SCHEMA 'sae301_a21';

CREATE TABLE _client(
idclient      SERIAL PRIMARY KEY,
nom           VARCHAR(30),
prenom        VARCHAR(30),
email         VARCHAR(50) UNIQUE,
numtel       VARCHAR(20),
datenaissance DATE,
motdepasse    VARCHAR(50),
idcarte       INTEGER,
idadresse    INTEGER,
valider       BOOLEAN);

CREATE TABLE _commande(
idcommande    SERIAL PRIMARY KEY,
datecommande  DATE,
datelivrer    DATE,
dateexpedition Date,
retours       BOOLEAN default FALSE,
idclient      INTEGER,
idadresse    INTEGER,
etat          VARCHAR(20),
prixcommande INTEGER default 0);




CREATE TABLE _commentaire(
idcommentaire SERIAL PRIMARY KEY,
textecomment  VARCHAR(500),
note          INTEGER,
idclient    INTEGER,
utile       NUMERIC,
idarticle INTEGER);

CREATE TABLE _reponse(
idreponse     SERIAL PRIMARY KEY,
textereponse  VARCHAR(500),
idcommentaire INTEGER);


CREATE TABLE _adresse(
idadresse     SERIAL PRIMARY KEY,
codepostal    VARCHAR(20),
nomrue        VARCHAR(100),
numrue        VARCHAR(20),
infocomplementaire VARCHAR(200),
ville         VARCHAR(50));



CREATE TABLE _administrateur(
idadmin       SERIAL PRIMARY KEY,
nom           VARCHAR(30),
prenom        VARCHAR(30),
email         VARCHAR(50),
motdepasse    VARCHAR(50),
autorisation  INTEGER);

CREATE TABLE _panier(
idclient      INTEGER,
idarticle     INTEGER,
quantite      INTEGER,
PRIMARY KEY(idclient,idarticle));

CREATE TABLE _article(
idarticle     SERIAL PRIMARY KEY,
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

CREATE TABLE _remise(
idarticle INT not null,
remise NUMERIC not null,
prixpromo NUMERIC,
date_debut DATE,
date_fin DATE,
PRIMARY KEY(idarticle,date_debut)
);



CREATE TABLE _categorie(
idcategorie   SERIAL PRIMARY KEY,
libelle       VARCHAR(30) UNIQUE,
tva           NUMERIC NOT NULL
);

CREATE TABLE _souscategorie(
idsouscategorie SERIAL PRIMARY KEY,
idcategorie     INTEGER,
souslibelle   VARCHAR(30)UNIQUE
);


CREATE TABLE  _quantite(
idarticle     INTEGER,
idcommande    INTEGER,
quantite      INTEGER NOT NULL,
prixachat     NUMERIC NOT NULL,
PRIMARY KEY(idarticle,idcommande));


CREATE TABLE _coordonneesBancaires(
idcarte       SERIAL PRIMARY KEY,
cryptogramme  VARCHAR(5),
numcarte      VARCHAR(50),
dateexpiration DATE,
titulairecarte VARCHAR(30));



CREATE TABLE _image(
idarticle INTEGER NOT NULL,
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
--INSERT INTO _image(idarticle,nom,imagebytea) values(1,'test',lo_import('/home/etuinfo/ewalefloch/Documents/web/SAE3/alizon-thibault/SITE/img/articles/ilenoir1.png'));
ALTER TABLE _article
     ADD CONSTRAINT article_FK_vendeur FOREIGN KEY (idvendeur) REFERENCES _vendeur (idvendeur);

ALTER TABLE _client
     ADD CONSTRAINT client_FK_coordonnesBancaires FOREIGN KEY (idcarte) REFERENCES _coordonneesBancaires (idcarte),
     ADD CONSTRAINT client_FK_adresse FOREIGN KEY (idadresse) REFERENCES _adresse (idadresse);

ALTER TABLE _commande
     ADD CONSTRAINT commande_FK_client FOREIGN KEY (idclient) REFERENCES _client (idclient),
     ADD CONSTRAINT commande_FK_adresse FOREIGN KEY (idadresse) REFERENCES _adresse (idadresse);


ALTER TABLE _commentaire
     ADD CONSTRAINT commentaire_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle),
     ADD CONSTRAINT commentaire_FK_client FOREIGN KEY (idclient) REFERENCES _client (idclient);

ALTER TABLE _reponse
     ADD CONSTRAINT reponse_FK_commentaire FOREIGN KEY (idcommentaire) REFERENCES _commentaire (idcommentaire);

ALTER TABLE _panier
     ADD CONSTRAINT panier_FK_client FOREIGN KEY (idclient) REFERENCES _client (idclient),
     ADD CONSTRAINT panier_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);

ALTER TABLE _quantite 
     ADD CONSTRAINT commandeUnArticle_FK_commande FOREIGN KEY (idcommande) REFERENCES _commande (idcommande),
     ADD CONSTRAINT commandeUnArticle_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);
     
ALTER TABLE _image
     ADD CONSTRAINT image_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);
  
ALTER TABLE _souscategorie
    ADD CONSTRAINT _souscategorie_FK_categorie FOREIGN KEY (idcategorie) REFERENCES _categorie (idcategorie);
    
ALTER TABLE _remise
    ADD CONSTRAINT _remise_FK_article FOREIGN KEY (idarticle) REFERENCES _article (idarticle);



CREATE OR REPLACE FUNCTION fInsertArticle()
  RETURNS TRIGGER AS
$$
DECLARE
tva NUMERIC;
idtmp int;
BEGIN
    select idcategorie from sae301_a21._souscategorie into idtmp where idsouscategorie=NEW.idsouscategorie;
    select c.tva from sae301_a21._categorie c into tva where c.idcategorie=idtmp;

    NEW.prixttc = NEW.prixht+NEW.prixht*tva;
    return NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_InsertArticle
BEFORE INSERT
ON _article
FOR EACH ROW
EXECUTE PROCEDURE fInsertArticle();

CREATE OR REPLACE FUNCTION fInsertRemise()
  RETURNS TRIGGER AS
$$
DECLARE 
prix NUMERIC;
BEGIN
    select prixttc from sae301_a21._article into prix where idarticle=NEW.idarticle;
    NEW.prixpromo = prix - prix*(NEW.remise/100);
    
    return NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_InsertRemise
BEFORE INSERT
ON _remise
FOR EACH ROW
EXECUTE PROCEDURE fInsertRemise();

CREATE OR REPLACE FUNCTION fInsertPanier()
  RETURNS TRIGGER AS
$$
DECLARE 
qstock NUMERIC;
BEGIN
    select quantitestock from sae301_a21._article into qstock where idarticle=NEW.idarticle;
    IF (qstock-NEW.quantite<0)THEN 
    RAISE EXCEPTION 'Impossible de rajouter au panier La quantite demander est trop grande : veuillez choisir un quantite moins important et inférieur la quantite en stock';
    END IF;
    return NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_InsertPanier
BEFORE INSERT
ON _panier
FOR EACH ROW
EXECUTE PROCEDURE fInsertPanier();

CREATE OR REPLACE FUNCTION fUpdatePanier()
  RETURNS TRIGGER AS
$$
DECLARE 
qstock NUMERIC;
BEGIN
    select quantitestock from sae301_a21._article into qstock where idarticle=NEW.idarticle;
    IF (qstock-NEW.quantite<0)THEN 
    RAISE EXCEPTION 'Impossible de Modifier la quantite, la quantite demander est trop grande : veuillez choisir un quantite moins important et inférieur la quantite en stock';
    END IF;
    return NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_UpdatePanier
BEFORE Update
ON _panier
FOR EACH ROW
EXECUTE PROCEDURE fUpdatePanier();



--INSERTION --

      


CREATE OR REPLACE FUNCTION fInsertQuantite()
  RETURNS TRIGGER AS
$$
BEGIN
    UPDATE sae301_a21._commande set prixcommande=prixcommande+NEW.prixachat*NEW.quantite where idcommande=NEW.idcommande;
    
    return NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_InsertQuantite
AFTER INSERT
ON _quantite
FOR EACH ROW
EXECUTE PROCEDURE fInsertQuantite();


INSERT INTO _adresse(codepostal,nomrue,numrue,infocomplementaire,ville)
VALUES (29710,'rue de la mort',30,'','Lannion'),
(29710,'rue de la ange',300,'','PARIS'),
(19510,'rue de la vivant',300,'','LILE'),
(19510,'rue de la vivant',300,'','ALLO');


INSERT into _vendeur(nom,email,mdp,tel,idadresse) VALUES
('cobrecEntreprise','cobrec@gmail.com','mpd','0782715129',4);
       
INSERT INTO _categorie(libelle,tva) VALUES
('meubles',0.20),
('divers',0.50),
('animaux',0.4),
('bretagne',0.1),
('jardinage',0.80);

INSERT INTO _souscategorie(idcategorie,souslibelle) VALUES
('1','chaise'),
('1','lit'),
('1','lampe'),
('1','table'),
('2','Jeux Vidéo'),
('2','Film'),
('2','BD'),
('2','Musique'),
('2','Chips'),
('3','chien'),
('3','chat'),
('3','tortue'),
('3','oiseaux'),
('3','autre'),
('4','drapeau'),
('4','nourriture'),
('4','tradition'),
('4','lannion'),
('5','Jardin'),
('5','foret'),
('5','arbre'),
('5','plante');
INSERT INTO _article(nom,prixht,prixcoutant,descript,quantitestock,seuilalerte,prixttc,idsouscategorie,idvendeur)
values ('BD Tintin 1',15,10,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.'
        ,400,100,5,7,1),
       ('BD Tintin 2',15,10,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.'
       ,4000,200,8,7,1),
       ('Hand spinner',20,50,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.'
       ,2000,100,14,1,1),
       ('Drapeau',40,100,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.'
       ,100,50,17,17,1),
	   ('Puzzle',10,50,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.'
       ,10000,200,58,5,1),
       ('Patate',2,5,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.'
       ,1000,50,6,8,1),
       ('Chaise',8,10,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',20,10,36,1,1),
       ('Chargeur',5,10,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',50,25,25,5,1),
       ('Lego',25,10,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',600,50,14,14,1),
       ('Chargeur2',9,20,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',200,75,9,5,1),
       ('Hand spinner',20,50,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',2000,100,14,1,1),
	   ('Hand spinner',20,50,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',2000,100,14,1,1), 
       ('Hand spinner',25,60,'Batman Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',2000,100,14,1,1),
       ('DVD creed',15,20,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',20,5,25,6,1),
       ('Poster Bleach',10,20,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',100,50,20,6,1),
       ('Hunger Games',21,30,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',50,15,27,6,1),
       ('Baywatch',14,20,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',60,25,19,6,1),
       ('Interstellar',21,30,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.7',70,15,27,6,1),
       ('Rocky',23,30,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',30,5,30,6,1),
       ('The rock',50,80,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nec dui nunc mattis enim ut tellus elementum. Adipiscing elit duis tristique sollicitudin nibh sit. Mi in nulla posuere sollicitudin aliquam ultrices sagittis orci a. Donec ac odio tempor orci dapibus ultrices in iaculis nunc. Orci eu lobortis elementum nibh. Sed elementum tempus egestas sed sed risus pretium quam vulputate. Quis auctor elit sed vulputate mi sit amet. Mollis nunc sed id semper risus in hendrerit gravida rutrum. Dictum fusce ut placerat orci nulla pellentesque. Et malesuada fames ac turpis egestas integer eget aliquet. Congue quisque egestas diam in arcu cursus euismod quis. Viverra vitae congue eu consequat ac felis donec. Pellentesque diam volutpat commodo sed egestas egestas fringilla. Lorem sed risus ultricies tristique nulla. Netus et malesuada fames ac turpis. Tortor posuere ac ut consequat. Nisi vitae suscipit tellus mauris a. Faucibus ornare suspendisse sed nisi. Nunc lobortis mattis aliquam faucibus purus in massa tempor nec.',10,3,85,6,1);
insert into _image(idarticle,urlimage) values 
(1,'images/imgArticle/ilenoir1.png'),
(2,'images/imgArticle/ilenoir2.png'),
(3,'images/imgArticle/handspinner_bleu.jpg'),
(4,'images/imgArticle/drapeau.jpg'),
(5,'images/imgArticle/puzzle.jpg'),
(6,'images/imgArticle/patate.jpg'),
(7,'images/imgArticle/chaise2.png'),
(8,'images/imgArticle/chargeur2.png'),
(9,'images/imgArticle/lego_paris.jpeg'),
(10,'images/imgArticle/chargeur1.png'),
(11,'images/imgArticle/handspinner_blanc.jpg'),
(12,'images/imgArticle/handspinner_rouge.jpg'),
(13,'images/imgArticle/handspinner_batman.jpg'),
(14,'images/imgArticle/creed.jpg'),
(15,'images/imgArticle/bleach.webp'),
(16,'images/imgArticle/hg.jpg'),
(17,'images/imgArticle/images.jpg'),
(18,'images/imgArticle/interstellar.jpg'),
(19,'images/imgArticle/rocky.webp'),
(20,'images/imgArticle/rock.jpg');


INSERT INTO _coordonneesBancaires(cryptogramme,numcarte,dateexpiration,titulairecarte)
VALUES (145,456784687486,current_date,'JESSE');


INSERT INTO _client(nom,prenom,email,numtel,datenaissance,motdepasse,idcarte,idadresse,valider)
VALUES ('PELLE','Sarah','e@email.fr','07850210',current_date,'mpd',1,3,TRUE),
      ('TERIEUR','Alex','e@email.com','065451604',current_date,'mpd',1,3,TRUE),
      ('PORTE','Mateo','e@gmail.com','04631',current_date,'mpd',1,3,TRUE),
      ('MASSE','Lara','e@gmail.fr','8147051',current_date,'mpd',1,3,TRUE),
      ('NEMARD','Jean','e@mail.fr','7804505',current_date,'mpd',1,3,TRUE);
       
INSERT INTO _panier(idclient,idarticle,quantite)
VALUES (1,1,10),
       (1,2,20),
       (1,3,50),
       (1,4,50);



INSERT INTO _commande(datecommande,datelivrer,dateexpedition,idclient,idadresse,etat)
VALUES (current_date,current_date,current_date,1,1,'EN livraison'),
       (current_date,current_date,current_date,2,2,'EN livraison');


INSERT INTO _quantite(idarticle,idcommande,quantite,prixachat)
VALUES (1,1,10,10),
       (2,1,1,20),
       (4,2,100,50),
       (3,2,100,500);

update _article set enpromotion = true where idarticle < 5;

       


/*
CREATE OR REPLACE FUNCTION fInsertQuantite()
  RETURNS TRIGGER AS
$$
BEGIN
    select prixventeremise from _article into NEW.prixachat where idarticle=NEW.idarticle;
    
    return NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_InsertQuantite
BEFORE INSERT
ON _quantite
FOR EACH ROW
EXECUTE PROCEDURE fInsertQuantite();
*/
