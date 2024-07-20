#include <sys/socket.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <unistd.h>
#include <string.h>
#include <time.h>
#include "proto.c"


#define KNRM  "\x1B[0m"
#define KRED  "\x1B[31m"
#define KGRN  "\x1B[32m"
#define KYEL  "\x1B[33m"
#define KBLU  "\x1B[34m"
#define KMAG  "\x1B[35m"
#define KCYN  "\x1B[36m"
#define KWHT  "\x1B[37m"

#include <stdio.h>
#include <inttypes.h>
#include <time.h>

int64_t millis(){
    struct timespec now;
    timespec_get(&now, TIME_UTC);
    return ((int64_t) now.tv_sec) * 1000 + ((int64_t) now.tv_nsec) / 1000000;
}

// void afficheBarDeProgression(time_t debut, time_t fin, char* beforetext, char* aftertext){
//     int longeur = 48;
//     while (time(0) < fin){
//         if(debut < time(0) && time(0) < fin){
//             printf("%s", beforetext);
//             float avancement = (float)(time(0)-debut)/(fin - debut);
//             for(int i = 0 ; i < avancement*longeur ; i++){ // i = barre a afficher
//                 if(avancement < 0.4){
//                     printf("%s|", KRED);
//                 }else if(avancement < 0.70){
//                     printf("%s|", KYEL);
//                 }else{
//                     printf("%s|", KGRN);
//                 }
//             }
//             for(int c = 0 ; c <= longeur - longeur*avancement ; c++){
//                 printf(" ");
//             }
        
//             printf("%s", KNRM);
//             printf("%s", aftertext);
//             printf("\r");
//         }
//     }
// }

void afficheBarDeProgression(time_t debut, time_t fin, char* beforetext, char* aftertext){
    int longeur = 128;
    fin = fin*1000; //passage de s en ms
    debut = debut*1000;
    while (millis() < fin){
        if(debut < millis() && millis() < fin){
            printf("%s", beforetext);
            float avancement = (float)(millis()-debut)/(fin - debut);
            for(int i = 0 ; i < avancement*longeur ; i++){ // i = barre a afficher
                if(avancement < 0.4){
                    printf("%s|", KRED);
                }else if(avancement < 0.70){
                    printf("%s|", KYEL);
                }else{
                    printf("%s|", KGRN);
                }
            }
            for(int c = 0 ; c <= longeur - longeur*avancement ; c++){
                printf(" ");
            }
        
            printf("%s", KNRM);
            printf("%.0f%% %s",avancement*100, aftertext);
            printf("\r");
        }
    }
    printf("\n");
}

// void afficheBarDeProgression(long int debut, long int  fin, char* beforetext, char* aftertext){
//     clock_t start, end, now;
//     start = clock();
    
//     while (1){
//         printf("%.2fs\n", millis()/1000.0);
//     }
// }


bool estValide(auth authentification){
    FILE* fp = fopen("logins.txt", "r");
    if (fp == NULL) {
        perror("Erreur lors de l'ouverture du fichier");
    }
    auth a;
    while(fscanf(fp, "%s %*s %s", a.login, a.hash) == 2){
        printf("%s %s\n", a.login, a.hash);
        if(strcmp(a.login, authentification.login) == 0 && strcmp(a.hash, authentification.hash) == 0){
            fclose(fp);
            return true;
        }
    }
    fclose(fp);
    return false;
}

int aleatoireEntre(int min, int max){
    return rand() % (max+1 - min) + min;
}

int main(){
    // auth a;
    // strcpy(a.login, "jean");
    // strcpy(a.hash, "e52b8910d3dd2b91e6981a5b0df632b7");
    // if(estValide(a)){
    //     printf("present\n");
    // }else{
    //     printf("non present\n");
    // }



    // while(1){
    //     printf("\x1b[3A\x1b[4D\x1b[shello\x1b[J\x1b[1;3Hworld\x1b[u\x1b[13T \n");
    //     // afficheBarDeProgression(time(0), time(0) + aleatoireEntre(1, 5), "test1", "test2");
    // }

    return 0;
}