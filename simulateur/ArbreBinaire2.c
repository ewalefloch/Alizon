#include <stdbool.h>
#include <stdlib.h>
#include <stdio.h>
#include "Utilisateur.c"

//Arbre Binaire
typedef struct Noeud{
    Utilisateur* client;
    struct Noeud* gauche;
    struct Noeud* droite;
} Noeud;

typedef struct {
    Noeud* racine;
} ArbreBinaire;

void initArbre(ArbreBinaire* ab){
    ab->racine = NULL;
}

bool cmpclient(Utilisateur c1, Utilisateur c2){
    //critere de recherche pour l'arbre binaire
    return c1.numclient > c2.numclient;
}

void insereArbre(ArbreBinaire* ab, Utilisateur c){
    Noeud* it;
    it = ab->racine; //iterator
    bool fin = false;

    if(ab->racine == NULL){ //premiere branche
        //cree une branche
        ab->racine = (Noeud*)malloc(sizeof(Noeud));
        ab->racine->gauche = NULL;
        ab->racine->droite = NULL;

        //insere le client
        Utilisateur* client = (Utilisateur*)malloc(sizeof(Utilisateur));
        *client = c;
        ab->racine->client = client;
        fin = true;
    }
    while(!fin){
        if(it->client == NULL){ 
            //insere le client sur la branche
            Utilisateur* client = (Utilisateur*)malloc(sizeof(Utilisateur));
            *client = c;
            it->client = client;
            fin = true;
        }else{
            //si une branche existe alors y aller
            if(cmpclient(*it->client, c)){ //va a gauche
                if(it->gauche == NULL){
                    it->gauche = (Noeud*)malloc(sizeof(Noeud));
                    it->gauche->gauche = NULL;
                    it->gauche->droite = NULL;
                }
                it = it->gauche;
            }else{
                if(it->droite == NULL){
                    it->droite = (Noeud*)malloc(sizeof(Noeud));
                    it->droite->gauche = NULL;
                    it->droite->droite = NULL;
                }
                it = it->droite;
            }
        }
    }
}


Utilisateur* rechercheArbre(ArbreBinaire* ab, Utilisateur c){
    Noeud* it = ab->racine;
    bool trouver = false;
    do{
        if(it->client->numclient == c.numclient){
            return (Utilisateur*)it->client;
        }else if(!cmpclient(*it->client, c)){
            it = it->droite;
        }else{
            it = it->gauche;
        }
    }while(!trouver && it != NULL);
    return NULL;
}


int main(){
    Utilisateur c1 = {1};
    Utilisateur c2 = {2};
    Utilisateur c3 = {3};
    Utilisateur c4 = {4};
    Utilisateur c5 = {5};
    ArbreBinaire arbre;
    initArbre(&arbre);
    insereArbre(&arbre, c3);
    insereArbre(&arbre, c1);
    insereArbre(&arbre, c4);
    insereArbre(&arbre, c2);
    insereArbre(&arbre, c5);


    Utilisateur* cp = rechercheArbre(&arbre, c2);
    return 0;
}
