
let sliderOne = document.getElementsByClassName("slider-1");
let sliderTwo = document.getElementsByClassName("slider-2");
let displayValOne = document.getElementsByClassName("range1");
let displayValTwo = document.getElementsByClassName("range2");
let minGap = 10;
let sliderTrack = document.querySelector(".slider-track");
let sliderMaxValue = document.getElementsByClassName("slider-1").max;
var tabPrix = document.getElementsByClassName("prixTTC");
var tabArticle = document.getElementsByClassName("article");

i=0;
    function slideOne(){

        if(parseInt(sliderTwo[i].value) - parseInt(sliderOne[i].value) <= minGap){
            sliderOne[i].value = parseInt(sliderTwo[i].value) - minGap;
        }
        displayValOne[i].textContent = sliderOne[i].value;
    
        for(let index=0;index<tabPrix.length;index++){
            if(parseInt(sliderOne[i].value)>parseFloat(tabPrix[index].innerHTML)){
                tabArticle[index].style.display="none";
            }else{
                tabArticle[index].style.display="block";
            }
        }
        fillColor();
    }
    function slideTwo(){
            if(parseInt(sliderTwo[i].value) - parseInt(sliderOne[i].value) <= minGap){
                sliderTwo[i].value = parseInt(sliderOne[i].value) + minGap;
            }
            displayValTwo[i].textContent = sliderTwo[i].value;
    
            for(let index=0;index<tabPrix.length;index++){
                if(parseInt(sliderTwo[i].value)<parseFloat(tabPrix[index].innerHTML)){
                    tabArticle[index].style.display="none";
                }else{
                    tabArticle[index].style.display="block";
                }
            }
            fillColor();
    }
    
    function fillColor(){
        percent1 = (sliderOne[i].value / sliderMaxValue[i]) * 100;
        percent2 = (sliderTwo[i].value / sliderMaxValue[i]) * 100;
        sliderTrack.style.background = `linear-gradient(to right, #FFFF ${percent1}% , #1F4F51 ${percent1}% , #1F4F51 ${percent2}%, #FFFF ${percent2}%)`;
    }


