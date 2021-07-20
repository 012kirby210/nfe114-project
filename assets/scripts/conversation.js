
// get jquery to handle ajax
import jquery from 'jquery';

let $ = jquery;

document.addEventListener('DOMContentLoaded',(event) => {
   Conversations.setNouvelleConversationAjaxLogicHandler();
});

let Conversations = {

   setNouvelleConversationAjaxLogicHandler: function(){

      document.querySelector('#button-nouvelle-conversation').addEventListener('click',(event) => {
         event.preventDefault();
         event.stopPropagation();
         let buttonElement = event.target;
         let buttonClasses = buttonElement.getAttribute('class').split(' ');

         //
         // !TODO it is needed the working state is global to the form page
         //
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

                  // set the listener on the button :
                  Conversations.setCreateConversationAjaxLogicHandler();

                  /*console.log(
                    document.querySelector('#nouvelle_conversation_form_creer').parentNode.parentNode
                  );
                  $('#nouvelle_conversation_form_creer').closest('form').submit(function (event) {
                     event.preventDefault();
                     event.stopPropagation();
                     console.log('submitting');
                     return false;
                  });*/
               },
              error: (jqXHR, textStatus, errorThrown) => {console.log('erreur réponse nouvelle conversation.')}
            })
         }
      });
   },
   setCreateConversationAjaxLogicHandler: function(){
      let button = document.querySelector('#nouvelle_conversation_form_creer');
      let formElement = button.closest('#nouvelle_conversation_form');
      formElement.addEventListener( 'submit', (event) => {
         event.preventDefault();
         event.stopPropagation();

         button.setAttribute('disabled','');

         let titreConversation = $(formElement).find('#nouvelle_conversation_form_titre');
         // !TODO mettre un vrai token anti CSRF
         $.ajax(formElement.getAttribute('data-href'),{
            contentType: 'application/x-www-form-urlencoded',
            data: {"nouvelle_conversation_form[titre]": titreConversation.val(),"nouvelle_conversation_form[_token]":"je suis un token"},
            method: 'POST',
            success: (d) => {
               let htmlContent = undefined;
               d.html && (htmlContent = d.html);
               $('.conversation-list-item-container').append()
               button.removeAttribute('disabled');
               let container = document.querySelector('#conversation-contenu-container');
               let $container = $(container);
               $container.children().remove();
               $container.append(htmlContent);

               Conversations.setUpdateConversationAjaxLogicHandler();
               Conversations.setInviteNewPeopleAjaxLogicHandler();
            },
            error: (jqXHR,textStatus,errorThrown) => { console.log('erreur réponse creation conversation');}
         });
         return false;
      });
   },
   setUpdateConversationAjaxLogicHandler: function(){
      let invitationSendingButton = document.querySelector('#invitation_sending_form_invitation_submit');
      // as the button is a submit type and the form got multi submit button => we need to intercept the submit
      // of the form to cancel it.
      /*invitationSendingButton.closest('form').addEventListener('submit',(event)=>{
         // do not submit !!
         event.preventDefault();
         event.stopPropagation();
         console.log('submit canceled');
      });*/

      invitationSendingButton.addEventListener('click',(event) => {
         event.preventDefault();
         event.stopPropagation();
         let eventTarget = event.target;
         event.target.setAttribute('disabled','');
         let participantUuid = document.querySelector('#invitation_sending_form_guest_uuid').value;
         $.ajax(event.target.getAttribute('data-href'),{
            method: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            accepts: 'application/json',
            data: {
               "invitation_sending_form[guest_uuid]":participantUuid
            },
            success: (data) => {

            },
            error: (jqXHR, textStatus,errorThrown) => {
               let errorResponse = jqXHR.responseJSON;
               let html = errorResponse.html;
               console.log(errorResponse.error);
               let invitationSendingContainerElement = document.querySelector('#invitation-sending-container-id');
               $(invitationSendingContainerElement).replaceWith(html);
               eventTarget.removeAttribute('disabled');
               Conversations.setUpdateConversationAjaxLogicHandler();

            }
         })
      });
   },
   setInviteNewPeopleAjaxLogicHandler: function(){
      // invite de nouveaux participants
   }
};