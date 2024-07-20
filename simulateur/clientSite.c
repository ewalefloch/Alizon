#include <sys/socket.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <unistd.h>
#include <string.h>
#include <errno.h>
#include <getopt.h>
#include <signal.h>
#include "proto.c"


#define KNRM  "\x1B[0m"
#define KRED  "\x1B[31m"
#define KGRN  "\x1B[32m"
#define KYEL  "\x1B[33m"

#define MIN(a,b) (((a)<(b))?(a):(b))
#define MAX(a,b) (((a)>(b))?(a):(b))

protocol proto;
int coderet = 0;
int id_token = 0;
auth a;

int connexion(int* sock){
    *sock = socket(AF_INET, SOCK_STREAM, 0);
    //se connecte au serveur
    struct sockaddr_in serv_addr;
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_port = htons(DEFAULT_PORT);
    serv_addr.sin_addr.s_addr = inet_addr("127.0.0.1");
    if(connect(*sock, (struct sockaddr*)&serv_addr, sizeof(serv_addr))){ //si retourne autre que 0 alors erreur
        printf("%s\n",strerror(errno));
        return -1;
    }
    return 0;
}

int deconnexion(int sock){
    proto.id_methode = Deconnexion;
    write(sock, &proto, sizeof(protocol));
}

int authentification(int sock,int* token_id){
    proto.id_methode = Auth;
    write(sock, &proto, sizeof(proto));
    write(sock, &a, sizeof(a));
    read(sock, token_id, sizeof(*token_id));
    if(*token_id == 0){
        return TokenInvalide;
    }
    return 0;
}

int envoieCommande(int sock, int id_token,  int idcommande){
    proto.id_methode = EnvoieCommande;
    write(sock, &proto, sizeof(protocol)); //demande d'envoie
    write(sock, &id_token, sizeof(id_token)); //envoie token
    write(sock, &idcommande, sizeof(idcommande)); //envoie commande

    //code que renvoie le serveur. si != 0 alors c'est une erreur 
    read(sock, &coderet, sizeof(coderet));
    return coderet;
}

int demandeInfo(int sock, int id_token,  int idcommande){
    proto.id_methode = DemandeInfo;
    write(sock, &proto, sizeof(protocol));
    write(sock, &id_token, sizeof(id_token)); //envoie token
    write(sock, &idcommande, sizeof(idcommande));

    //code que renvoie le serveur. si != 0 alors c'est une erreur 
    read(sock, &coderet, sizeof(coderet));
    if(coderet == TokenInvalide){
        printf("erreur votre session a expiré veuillez vous reconnecter\n");
    }
    else if(coderet == CommandeInexistante){
        printf("erreur la commande n'existe pas\n");
    }else{
        commande com;
        read(sock, &com, sizeof(com));
        int etat = com.etat;
        printf("%d\n", etat);

        char s[100];
        strftime(s, sizeof(s), "%F %H:%M:%S", localtime(&com.dateLivraison));
        printf("%s\n", s);
    }
    return coderet;
}

int reception(int sock, int id_token,  int idcommande){
    proto.id_methode = AccuseDeReception;
    write(sock, &proto, sizeof(protocol));
    write(sock, &id_token, sizeof(id_token)); //envoie token
    write(sock, &idcommande, sizeof(idcommande));

    //code que renvoie le serveur. si != 0 alors c'est une erreur 
    read(sock, &coderet, sizeof(coderet));
    if(coderet == TokenInvalide){
        printf("erreur votre session a expiré veuillez vous reconnecter\n");
    }
    else if(coderet == CommandeInexistante){
        printf("erreur la commande n'existe pas\n");
    }
    else{
        printf("accuse de reception pris en compte\n");
    }
    return coderet;
}

void afficheHelp(){

}


int main(int argc, char* argv[]){
    int c;
    int option_index = 0;
    int sockfd;

    initProtocol(&proto);

    static struct option long_options[] = {
            {"help",      no_argument,        0, 'h'},
            {"login",     required_argument,  0, 'l'},
            {"password",  required_argument,  0, 'p'},
            {"envoie",    required_argument,  0, 'e'},
            {"info",      required_argument,  0, 'i'},
            {"reception", required_argument,  0, 'r'},
            {"tableau",     no_argument,        0, 't'},
            {0, 0, 0, 0}
    };


    if((c = getopt_long_only(argc, argv, "", long_options, &option_index)) != -1){
        //connexion
        connexion(&sockfd);
        
        do{
            switch (c) {
            case 'l':{
                strcpy(a.login, optarg);
            } break;
            case 'p':{
                strcpy(a.hash, optarg);
            } break;
            case 'e':{
                int idcommande;
                if((idcommande = strtol(optarg ,NULL,10)) == 0){
                    printf("erreur valeur du numero de commande %s invalide\n", optarg);
                    coderet = ArgumentInvalide;
                }else if(authentification(sockfd, &id_token) == TokenInvalide){
                    printf("erreur login ou mdp invalide\n");
                }else{
                    envoieCommande(sockfd, id_token, idcommande);
                }
                deconnexion(sockfd);
                return coderet;
            } break;
            case 'i':{
                int idcommande;
                if((idcommande = strtol(optarg ,NULL,10)) == 0){
                    printf("erreur valeur du numero de commande %s invalide\n", optarg);
                    coderet = ArgumentInvalide;
                }
                else if(authentification(sockfd, &id_token) == TokenInvalide){
                    printf("erreur login ou mdp invalide\n");
                }else{
                    demandeInfo(sockfd, id_token, idcommande);  
                }
                deconnexion(sockfd);
                return coderet;
            } break;
            case 'r':{
                int idcommande;
                if((idcommande = strtol(optarg ,NULL,10)) == 0){
                    printf("erreur valeur du numero de commande %s invalide\n", optarg);
                    coderet = ArgumentInvalide;
                }
                else if(authentification(sockfd, &id_token) == TokenInvalide){
                    printf("erreur login ou mdp invalide\n");
                }else{
                    reception(sockfd, id_token, idcommande);
                }
                deconnexion(sockfd);
                return coderet;
            } break;
            case 'a':{
            } break;
            case 'h':{
                afficheHelp();
            } break;
            }
            
        }while ((c = getopt_long_only(argc, argv, "", long_options, &option_index)) != -1);
        
        deconnexion(sockfd);
    }
    else{
        printf("pour voir la liste des options disponible tapez --help\n");
    }

    deconnexion(sockfd);
    
    return coderet;
}