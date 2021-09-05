document.addEventListener('DOMContentLoaded',(event)=>{
    let panelSelector = document.querySelector('#display-panel');
    panelSelector.addEventListener('click',(event)=> {
       event.preventDefault();
       event.stopPropagation();
       console.log('eh');
       let panelElement = document.querySelector('#aside-panel');
       if(!panelSelector.classList.contains("active")){
       panelSelector.classList.add("active");
       panelElement.classList.add("active");
       }else{
           panelSelector.classList.remove("active");
           panelElement.classList.remove("active");
       }
    });
});