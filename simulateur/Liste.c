#include <stdlib.h>
#include <stdio.h>
#include "proto.c"


typedef struct Element{
    commande comm;
    struct Element* next;
}Element;

typedef struct{
    Element* tete;
}Liste;

void initListe(Liste* liste){
    liste->tete = NULL;
}

int tailleListe(Liste* liste){
    if(liste->tete == NULL){
        return 0;
    }
    Element* it = liste->tete;
    int c = 1;
    while (it->next != NULL){
        it = it->next;
        c++;
    }
    return c;
}

void supprimeListe(Liste* liste){
    int taille = tailleListe(liste);
    Element* it = liste->tete;
    while (taille--){
        Element* elemSuivant = it->next;
        free(it);
        it = elemSuivant;
    }
    liste->tete = NULL;
}


void ajouteCommande(Liste* liste, commande com){
    //ajoute une commande en fin de liste
    int taille = tailleListe(liste);
    Element* it = liste->tete;
    if(taille == 0){
        liste->tete = (Element*)malloc(sizeof(Element));
        liste->tete->comm = com;
        liste->tete->next = NULL;
        return;
    }
    while (--taille){
        it = it->next;
    }
    it->next = (Element*)malloc(sizeof(Element));
    it->next->next = NULL;
    it->next->comm = com;
}

void supprimeCommande(Liste* liste, int idcommande){
    Element* it = liste->tete;
    Element* prev = NULL;

    int taille = tailleListe(liste);
    while (taille--){
        if(it->comm.idcommande == idcommande){
            if(prev){
                prev->next = it->next;
            }else{
                liste->tete = it->next;
            }
            free(it);
            return;
        }
        prev = it;
        it = it->next;
    }
}

commande* rechercheCommande(Liste* liste, int idcommande){
    Element* it = liste->tete;
    int taille = tailleListe(liste);
    while (taille--){
        if(it->comm.idcommande == idcommande){
            return &it->comm;
        }
        it = it->next;
    }
    return NULL;
}

