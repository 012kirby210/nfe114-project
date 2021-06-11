
// get jquery to handle ajax
import jquery from 'jquery';

let $ = jquery;

document.addEventListener('DOMContentLoaded',(event) => {
   document.querySelector('#button-nouvelle-conversation').addEventListener('click',(event) => {
      event.preventDefault();
      event.stopPropagation();
      let buttonElement = event.target;
      let buttonClasses = buttonElement.getAttribute('class').split(' ');
      if (buttonClasses.includes('working')){
         // already working
      }else{
         buttonClasses.push('working');
         buttonElement.setAttribute('class',buttonClasses.toString().replaceAll(',',' '));
         $.ajax(buttonElement.getAttribute('data-href'),{
            success: (data) => {
               let html = data.html;
               // sur safari, remove child en fonctionne pas convenablement
               // peut - être un niveau de récursivité.
               let container = document.querySelector('#conversation-contenu-container');
               let $container = $(container);
               $container.children().remove();
               $container.append(html);

               // remove working class on the button

               let classes =  buttonElement.getAttribute('class').split(' ')
                   .filter((item) => item!=='working').toString().replaceAll(',',' ')
               buttonElement!==undefined && buttonElement.setAttribute('class',classes);
            },
            error: (jqXHR, textStatus, errorThrown) => {console.log('erreur réponse nouvelle conversation.')}
         })
      }

   });
});