#include <sys/socket.h>
#include <arpa/inet.h>

#include <fcntl.h>
#include <time.h>
#include <sys/types.h>
#include <sys/stat.h>

char ipPort[16];

char* dateCourant(){
    time_t now; //permet d'avoir date + heure
    struct tm *timeinfo; // structure qui recupere l'heure
    char date[23];

    time(&now);
    timeinfo = localtime(&now);
    strftime(date,23,"[%d-%m-%Y %H:%M:%S]",timeinfo);

    char *copie=NULL;
    copie=malloc((strlen(date)+1)*sizeof(char));
    strcpy(copie,date);

    return copie;
}

void createLog(char *message){
    int fLog; // gerer erreur pendant l'ecriture du log
    char messageLog[100]=""; // variable qui suit le format [date] ip "message"

    int idlog = open("./log", O_WRONLY | O_CREAT | O_APPEND, S_IRWXU); // création ou ouverture du fichier log
    strcat(messageLog,dateCourant());
    strcat(messageLog, ipPort);
    strcat(messageLog,message);

    fLog = write(idlog,&messageLog,strlen(messageLog));
    if (fLog==-1)
    {
        perror("erreur");
    }

    int idclose = close(idlog);
    if (idclose==-1)
    {
        perror("Erreur");
    }
    

}
