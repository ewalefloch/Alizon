#pragma once
#include <stdbool.h>
#include <stdlib.h>
#include <stdio.h>
#include "proto.c"

typedef struct node{
    token tok;
    struct node *left;
    struct node *right;
} node ;

void addNode(node **tree, token tok){
    node *tmpNode;
    node *tmpTree = *tree;

    node *elem = malloc(sizeof(node));
    elem->tok = tok;
    elem->left = NULL;
    elem->right = NULL;

    if(tmpTree)
    do{
        tmpNode = tmpTree;
        if(tok.id > tmpTree->tok.id ){
            tmpTree = tmpTree->right;
            if(!tmpTree) tmpNode->right = elem;
        }
        else{
            tmpTree = tmpTree->left;
            if(!tmpTree) tmpNode->left = elem;
        }
    }
    while(tmpTree);
    else  *tree = elem;
}

// int searchNode(node *tree, token tok)
// {
//     while(tree)
//     {
//         if(tok.id == tree->tok.id) return 1;

//         if(tok.id > tree->tok.id ) tree = tree->right;
//         else tree = tree->left;
//     }
//     return 0;
// }

token* searchNode(node *tree, int id){
    while(tree){
        if(id == tree->tok.id) return &tree->tok; //retourne l'adresse du token 

        if(id > tree->tok.id ) tree = tree->right;
        else tree = tree->left;
    }
    return NULL;
}



// int main(){
//     token t = {123,40};
//     token e = {574,20};
//     token f = {1747,30};

//     node *Arbre = NULL;
//     addNode(&Arbre, t);
//     addNode(&Arbre, e);
//     addNode(&Arbre, f);
//     addNode(&Arbre, f);
//     addNode(&Arbre, f);
//     return 0;
// }