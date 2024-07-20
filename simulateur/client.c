#include <sys/socket.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <unistd.h>
#include <string.h>
#include <errno.h>
#include "proto.c"


#define KNRM  "\x1B[0m"
#define KRED  "\x1B[31m"
#define KGRN  "\x1B[32m"
#define KYEL  "\x1B[33m"

#define MIN(a,b) (((a)<(b))?(a):(b))
#define MAX(a,b) (((a)>(b))?(a):(b))

int64_t millis(){
    struct timespec now;
    timespec_get(&now, TIME_UTC);
    return ((int64_t) now.tv_sec) * 1000 + ((int64_t) now.tv_nsec) / 1000000;
}

void afficheBarDeProgression(time_t debut, time_t fin, char* beforetext, char* aftertext){
    int longeur = 25;
    fin = fin*1000; //passage de s en ms
    debut = debut*1000;
    float avancement = 0.0f;
    do{
        printf("%s  [", beforetext);
        avancement = (float)(millis()-debut)/(fin - debut);
        avancement = MIN(MAX(avancement, 0.0f), 1.0f);
        for(int i = 0 ; i < avancement*longeur ; i++){ // i = barre a afficher
            if(avancement < 0.4){
                printf("%s|", KRED);
            }else if(avancement < 0.70){
                printf("%s|", KYEL);
            }else{
                printf("%s|", KGRN);
            }
        }
        for(int c = 0 ; c < longeur - longeur*avancement ; c++){
            printf(" ");
        }
        printf("%s] %.0f%% %s\r", KNRM, avancement*100, aftertext);
    }while (avancement < 1.0f);
        
    printf("\n");
}

int main(int argc, char* argv[]){
    int sockfd;
    int coderet = 0;
    
    protocol proto;
    int id_token = 0;
    initProtocol(&proto);
    
    char c;
    bool fin = false, connecter = false;
    while(!fin){
        
        printf("c : CONNEXION\tQ : QUITTER\n>>>");
        do{
            scanf("%c", &c);
        }while(c == '\n');

        switch (c)
        {
        case 'c':{
            //cree le socket pour la connexion
            sockfd = socket(AF_INET, SOCK_STREAM, 0);
            printf("socket cree\n");
            //se connecter au serveur
            struct sockaddr_in serv_addr;
            serv_addr.sin_family = AF_INET;
            serv_addr.sin_port = htons(DEFAULT_PORT);
            serv_addr.sin_addr.s_addr = inet_addr("127.0.0.1");
            // serv_addr.sin_addr.s_addr = inet_addr("78.113.80.251");
            if(connect(sockfd, (struct sockaddr*)&serv_addr, sizeof(serv_addr))){ //si retourne autre que 0 alors erreur
                printf("%s\n",strerror(errno));
                return -1;
            }
            printf("connected to server\n");
            connecter = true;
        }
            break;

        case 'q':
            fin = true;
            break;
        default:
            break;
        }

        while(connecter){
            printf("a : Authentification\te : Envoie commande\ni : Information commande\tr : Accuse reception\td : Deconnexion\n>>>");
            do{
                scanf("%c", &c);
            }while(c == '\n');
            switch(c){
                case 'a':{
                    proto.id_methode = Auth;
                    write(sockfd, &proto, sizeof(proto));

                    auth a;
                    strcpy(a.login, "jean");
                    strcpy(a.hash, "e52b8910d3dd2b91e6981a5b0df632b7");
                    write(sockfd, &a, sizeof(a));

                    read(sockfd, &id_token, sizeof(id_token));
                    if(id_token != 0){
                        printf("authentification accompli, token recu : %d\n", id_token);
                    }
                    else{
                        printf("Erreur login ou mdp invalide\n");
                    }
                } break;
                

                case 'e':{
                    int idcommande = 55;
                    proto.id_methode = EnvoieCommande;
                    write(sockfd, &proto, sizeof(protocol)); //demande d'envoie 
                    write(sockfd, &id_token, sizeof(id_token)); //envoie token 
                    write(sockfd, &idcommande, sizeof(idcommande)); //envoie commande 

                    //code que renvoie le serveur. si != 0 alors c'est une erreur 
                    read(sockfd, &coderet, sizeof(coderet));
                    if(coderet == TokenInvalide){
                        printf("erreur votre session a expiré veuillez vous reconnecter\n");
                    }
                    else if(coderet == CommandeExistante){
                        printf("erreur une commande avec ce numero existe deja\n");
                    }
                    else if(coderet == FileDeLivraisonPleine){
                        printf("erreur la file de livraison est pleine :( , veuillez réessayer ultérieurement\n");
                    }
                    else{
                        printf("commande N°%d envoye\n", idcommande);
                    }
                } break;

                case 'i':{
                    int idcommande = 55;
                    proto.id_methode = DemandeInfo;
                    write(sockfd, &proto, sizeof(protocol));
                    write(sockfd, &id_token, sizeof(id_token)); //envoie token
                    write(sockfd, &idcommande, sizeof(idcommande));

                    //code que renvoie le serveur. si != 0 alors c'est une erreur 
                    read(sockfd, &coderet, sizeof(coderet));
                    if(coderet == TokenInvalide){
                        printf("erreur votre session a expiré veuillez vous reconnecter\n");
                    }
                    else if(coderet == CommandeInexistante){
                        printf("erreur la commande n'existe pas\n");
                    }else{
                        commande com;
                        char s[100];

                        read(sockfd, &com, sizeof(com));
                        // printf("etat de la commande N°%d : %d\n", com.idcommande, com.etat);
                        strftime(s, sizeof(s), "%F %H:%M:%S %Z", localtime(&com.datePrisEnCharge));
                        printf("date de prise en charge : %s\n", s);

                        strftime(s, sizeof(s), "%F %H:%M:%S %Z", localtime(&com.dateFinTransportRegion));
                        printf("date livraison plateforme region : %s\n", s);
                        afficheBarDeProgression(com.datePrisEnCharge, com.dateFinTransportRegion, "entrepot -> region ", "");

                        strftime(s, sizeof(s), "%F %H:%M:%S %Z", localtime(&com.dateFinVersSiteLocal));
                        printf("date livraison plateforme local : %s\n", s);
                        afficheBarDeProgression(com.dateFinTransportRegion, com.dateFinVersSiteLocal, "region -> site local ", "");

                        strftime(s, sizeof(s), "%F %H:%M:%S %Z", localtime(&com.dateLivraison));
                        printf("date de livraison %s : \n", s);
                    }
                } break;

                case 'r':{
                    int idcommande = 55;
                    proto.id_methode = AccuseDeReception;
                    write(sockfd, &proto, sizeof(protocol));
                    write(sockfd, &id_token, sizeof(id_token)); //envoie token
                    write(sockfd, &idcommande, sizeof(idcommande));

                    //code que renvoie le serveur. si != 0 alors c'est une erreur 
                    read(sockfd, &coderet, sizeof(coderet));
                    if(coderet == TokenInvalide){
                        printf("erreur votre session a expiré veuillez vous reconnecter\n");
                    }
                    else if(coderet == CommandeInexistante){
                        printf("erreur la commande n'existe pas\n");
                    }
                    else{
                        printf("accuse de reception pris en compte\n");
                    }
                } break;
                case 'l':{
                    proto.id_methode = DemandeInfoListe;
                    write(sockfd, &proto, sizeof(protocol));
                    write(sockfd, &id_token, sizeof(id_token)); //envoie token
                    int taille = 0;

                    read(sockfd, &taille, sizeof(taille));
                    for(int c = 0 ; c < taille ; c++){
                        // commande com;
                        // read(sockfd, &com, sizeof(com));
                    }

                } break;
                

                case 'd':{
                    proto.id_methode = Deconnexion;
                    write(sockfd, &proto, sizeof(protocol));
                    connecter = false;
                } break;
                

                default:{

                } break;
                
            }
        }
    }
    return 0;
}