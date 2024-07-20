
#include "Liste.c"

int main(){
    commande c1 = {10};
    c1.idcommande = 3;
    commande c2 = {20};
    c2.idcommande = 4;
    commande c3 = {30};
    c3.idcommande = 6;



    Liste liste;
    initListe(&liste);
    ajouteCommande(&liste, c1);
    ajouteCommande(&liste, c2);
    ajouteCommande(&liste, c3);
    commande* com = rechercheCommande(&liste, c3.idcommande);


    supprimeCommande(&liste, c1.idcommande);

    printf("%d", tailleListe(&liste));
    supprimeListe(&liste);
}