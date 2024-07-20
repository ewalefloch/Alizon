#include <sys/socket.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <unistd.h>
#include <string.h>
#include "ArbreBinaireToken.c"
#include "Liste.c"
#include "log.c"

Liste listeCommandeEnCours;
Liste listeCommandeEnAttente;
Liste listeCommandeTermine;

//retourne valeur aleatoire x, min <= x <= max
int aleatoireEntre(int min, int max){
    return rand() % (max+1 - min) + min;
}

bool estValide(auth authentification){
    FILE* fp = fopen("logins.txt", "r");
    if (fp == NULL) {
        perror("Erreur lors de l'ouverture du fichier");
        return false;
    }
    auth a;
    while(fscanf(fp, "%s %*s %s", a.login, a.hash) == 2){
        if(strcmp(a.login, authentification.login) == 0 && strcmp(a.hash, authentification.hash) == 0){
            fclose(fp);
            return true;
        }
    }
    fclose(fp);
    return false;
}

token generateToken(){
    token t;
    t.id = rand();
    // t.expirationTime = time(0) + 20; //expire dans 10s reel
    // t.expirationTime = time(0) + (3600*24*30)/SIMULATION_SPEED; //expire dans 30j simulateur
    t.expirationTime = time(0) + 3600*2; //expire dans 2h reel
    
    return t;
}

bool tokenValide(node* Arbre , int id_tok){
    token* tmp;
    if((tmp = searchNode(Arbre, id_tok))){
        if(tmp->expirationTime > time(0)){
            return true;
        }else{
            //supprime token de l'arbre
        }
    }
    return false;
}

void MAJEtatCommande(commande* com){
    commande c = *com;
    if(c.dateAcquittement){
        if(c.etat != Livre){
            //retire la commande de la file en attente ou en cours
            
            if(rechercheCommande(&listeCommandeEnAttente, c.idcommande))
                supprimeCommande(&listeCommandeEnAttente, c.idcommande);
            if(rechercheCommande(&listeCommandeEnCours, c.idcommande))
                supprimeCommande(&listeCommandeEnCours, c.idcommande);
            ajouteCommande(&listeCommandeTermine, c);
        }
        com->etat = Livre;
    }
    else if(time(0) > c.dateLivraison){
        if(c.etat != EnAttente){
            supprimeCommande(&listeCommandeEnCours, c.idcommande);
            ajouteCommande(&listeCommandeEnAttente, c);
        }
        com->etat = EnAttente;
    }
    else if(time(0) > c.dateFinVersSiteLocal){
        com->etat = Livraison;
    }
    else if(time(0) > c.dateFinTransportRegion){
        com->etat = TransportVersSiteLocal;
    }
    else{
        com->etat = TransportVersPlateformeRegionale;
    }
}

commande* rechercheListeCommande(int idcommande){
    commande* com;
    if((com = rechercheCommande(&listeCommandeEnCours, idcommande))){
        return com;
    }
    if((com = rechercheCommande(&listeCommandeEnAttente, idcommande))){
        return com;
    }
    if((com = rechercheCommande(&listeCommandeTermine, idcommande))){
        return com;
    }
    return NULL;    
}




int main(int argc, char* argv[]){


    createLog("server start");
    
    srand(time(0));
    // arbre binaire contenant les tokens d'identification valide
    int tailleMaxFileCommande = 12;
    node *ArbreTokens = NULL;

    
    initListe(&listeCommandeEnCours);
    initListe(&listeCommandeEnAttente);
    initListe(&listeCommandeTermine);

    int sock;
    int coderet = 0;
    int size;
    int cnx;
    struct sockaddr_in addr;

    //SOCK_STREAM = TCP
    sock = socket(AF_INET, SOCK_STREAM, 0);
    printf("socket crée\n");
    addr.sin_addr.s_addr = inet_addr("0.0.0.0"); //adresse d'ecoute
    // addr.sin_addr.s_addr = INADDR_ANY;
    addr.sin_family = AF_INET;
    addr.sin_port = htons(DEFAULT_PORT); //port d'ecoute
    coderet = bind(sock, (struct sockaddr *)&addr, sizeof(addr));
    if(coderet == -1){
        perror("erreur fonction bind()\n");
    }
    printf("bind\n");
    coderet = listen(sock, 1);
    if(coderet == -1){
        perror("erreur fonction listen()\n");
    }
    printf("écoute\n");
    
    while(1){
        struct sockaddr_in conn_addr;
        size = sizeof(conn_addr);
        cnx = accept(sock, (struct sockaddr *)&conn_addr, (socklen_t *)&size);
        printf("connexion accepté %s:%d\n", (char*)inet_ntoa((struct in_addr)conn_addr.sin_addr), (int)conn_addr.sin_port);
    

        bool fin = false;
        while(!fin){
            protocol proto[1];
            initProtocol(proto);

            if(read(cnx, proto, sizeof(protocol)) > 0){
                // printf("version : %d\n", proto->version);
                // printf("code_methode : %d\n", proto->id_methode);
                // printf("data : %s\n", proto->data);
                if(proto->version != PROTOCOL_VERSION){
                    printf("methode inconnue, fermeture %s:%d\n", (char*)inet_ntoa((struct in_addr)conn_addr.sin_addr), (int)conn_addr.sin_port);
                    
                    fin = true;
                }else{
                    switch (proto->id_methode){
                    case Auth :{
                        auth a;
                        read(cnx, &a, sizeof(auth));
                        printf("login : %s\tpasswd : %s\n", a.login, a.hash);

                        token t;
                        if(estValide(a)){
                            printf("authentifiant correct\n");
                            //donne un token au client

                            t = generateToken();
                            //insere le token dans l'arbre
                            addNode(&ArbreTokens, t);
                            
                            //envoie le l'id du token au client 
                            write(cnx, &t.id, sizeof(t.id));
                        }else{//login + mdp incorrect
                            printf("authentifiant incorrect\n");
                            //renvoie le code 0 pour dire login invalide
                            t.id = 0;
                            t.expirationTime = 0;
                            write(cnx, &t, sizeof(t));
                        }
                    } break;

                    case EnvoieCommande :{
                        int idcommande;
                        int id_token = 0;
                        read(cnx, &id_token, sizeof(id_token));
                        read(cnx, &idcommande, sizeof(idcommande));
                        //verif le token
                        commande* com  = rechercheListeCommande(idcommande);
                        if(!tokenValide(ArbreTokens, id_token)){//si le token n'existe pas alors
                            coderet = TokenInvalide; //code retour;
                            printf("token invalide\n"); //envoie -1 pour dire au client que sont token n'est pas ou plus valide                             
                        }
                        else if(com) { //si la commande existe deja
                            coderet = CommandeExistante; //code retour;
                            printf("erreur commande deja existante\n"); //envoie -1 pour dire au client que sont token n'est pas ou plus valide
                        }
                        else if(tailleListe(&listeCommandeEnCours) >= tailleMaxFileCommande){
                            coderet = FileDeLivraisonPleine; //code retour;
                            printf("erreur file de livraison pleine\n"); //envoie -1 pour dire au client que sont token n'est pas ou plus valide
                        }
                        else{
                            //ajoute dans la liste des commandes
                            commande com;
                            com.etat = PrisEnCharge;
                            com.datePrisEnCharge = time(0);
                            com.dateFinTransportRegion = com.datePrisEnCharge + aleatoireEntre(3600*24, 3600*24*3)/SIMULATION_SPEED; // + 1 a 3 jours 
                            com.dateFinVersSiteLocal = com.dateFinTransportRegion + 3600*24/SIMULATION_SPEED; // + 1 jours
                            com.dateLivraison = com.dateFinVersSiteLocal;
                            com.dateAcquittement = 0;
                            com.idcommande = idcommande;

                            ajouteCommande(&listeCommandeEnCours, com);

                            printf("nouvelle commande, idcommande = %d\n", com.idcommande);
                            coderet = Bon; //code retour;
                        }
                        write(cnx, &coderet, sizeof(coderet));
                    } break;

                    case DemandeInfo :{
                        int id_token = 0;
                        int idcommande = 0;
                        read(cnx, &id_token, sizeof(id_token));
                        read(cnx, &idcommande, sizeof(idcommande));

                        //lire la commande depuis la file
                        commande* com  = rechercheListeCommande(idcommande);
                        if(!tokenValide(ArbreTokens, id_token)){//si le token et la commande existe alors
                            printf("token invalide\n"); //envoie -1 pour dire au client que sont token n'est pas ou plus valide 
                            coderet = TokenInvalide; //code retour;
                            write(cnx, &coderet, sizeof(coderet));
                        }
                        else if(!com){
                            printf("commande inexistante\n");
                            coderet = CommandeInexistante; //code retour;
                            write(cnx, &coderet, sizeof(coderet));
                        }
                        else{
                            coderet = Bon; //code retour;
                            write(cnx, &coderet, sizeof(coderet));
                            printf("envoie info commande N°%d\n", idcommande);
                            MAJEtatCommande(com);
                            com  = rechercheListeCommande(idcommande);
                            write(cnx, com, sizeof(*com));
                        }
                    } break;
                    
                    case AccuseDeReception:{
                        int id_token = 0;
                        int idcommande = 0;
                        read(cnx, &id_token, sizeof(id_token));
                        read(cnx, &idcommande, sizeof(idcommande));

                        commande* com = rechercheListeCommande(idcommande);
                        if(!tokenValide(ArbreTokens, id_token)){//si le token et la commande existe alors
                            printf("token invalide\n"); //envoie -1 pour dire au client que sont token n'est pas ou plus valide 
                            coderet = TokenInvalide; //code retour;
                            write(cnx, &coderet, sizeof(coderet));
                        }
                        else if(!com){
                            printf("commande inexistante\n");
                            coderet = CommandeInexistante; //code retour;
                            write(cnx, &coderet, sizeof(coderet));
                        }
                        else{
                            com->dateAcquittement = time(0);
                            MAJEtatCommande(com);
                            coderet = Bon; //code retour;
                            write(cnx, &coderet, sizeof(coderet));
                            printf("commande N°%d terminer\n", idcommande);
                        }
                    } break;

                    case DemandeInfoListe:{
                        int id_token = 0;
                        int taille = tailleListe(&listeCommandeEnCours);
                        read(cnx, &id_token, sizeof(id_token));
                        write(cnx, &taille, sizeof(taille));
                        for(int c = 0 ; c < taille ; c++){
                            // commande com;
                            // write(cnx, &com, sizeof(com));
                        }
                        
                    } break;

                    case Deconnexion:{
                        printf("deconnexion %s:%d\n", (char*)inet_ntoa((struct in_addr)conn_addr.sin_addr), (int)conn_addr.sin_port);
                        fin = true;
                    } break;

                    default:{
                        printf("methode inconnue, fermeture %s:%d\n", (char*)inet_ntoa((struct in_addr)conn_addr.sin_addr), (int)conn_addr.sin_port);
                        fin = true;
                    } break;  
                        
                    }
                }
            }
        }
        close(cnx);
    }

    shutdown(sock, SHUT_RDWR);

    supprimeListe(&listeCommandeEnCours);
    supprimeListe(&listeCommandeEnAttente);
    supprimeListe(&listeCommandeTermine);
    return coderet;
}