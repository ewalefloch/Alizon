#pragma once
#include <time.h> //pour calculer la date d'expiration des tokens 


#define DEFAULT_PORT 8080
#define PROTOCOL_VERSION 1
#define MAX_USERNAME_LENGTH 64
#define MAX_PASSWORD_LENGTH 16 //taille d'un hash MD5
#define SIMULATION_SPEED 20000.0

typedef enum {Auth=1, EnvoieCommande=2, DemandeInfo=3, AccuseDeReception=4, DemandeInfoListe=6,  Deconnexion=5} Methode;
typedef enum {PrisEnCharge=1, TransportVersPlateformeRegionale=2, TransportVersSiteLocal=3, Livraison=4, EnAttente=5, Livre=6} EtatLivraison;
typedef enum {Bon=0, TokenInvalide=-1, CommandeInexistante=-2, CommandeExistante=-3, FileDeLivraisonPleine=-4, ArgumentInvalide=-5} CodeRetour;


typedef struct protocol{
    int version; //version de proto en cas de MAJ future 
    Methode id_methode;
} protocol;

typedef struct auth{
    char login[65];
    char hash[33];
} auth;

void initProtocol(protocol* p){
    p->version = PROTOCOL_VERSION;
    p->id_methode = 0;
}

typedef struct{
    int id;
    int expirationTime;
}token;


// ________________________________________________________________________________________________________________
//                   |                   |                          |                        |                    |
//   pris en charge->| transport region->|         transport local->|             livraison->|      acquittement->|  Fin 
// ________________________________________________________________________________________________________________
//                   ^ datePrisEnCharge  ^dateFinTransportRegion    ^dateFinVersSiteLocal    ^dateLivraison       ^dateAcquittement 


typedef struct{
    EtatLivraison etat;
    long int datePrisEnCharge;
    long int dateFinTransportRegion;
    long int dateFinVersSiteLocal;
    long int dateLivraison;
    long int dateAcquittement;
    int idcommande;
}commande;