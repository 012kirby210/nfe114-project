import '../styles/profile.css';

import jquery from 'jquery';

import Dropzone from 'dropzone';

Dropzone.autoDiscover = false;
let myDropzone = new Dropzone('.dropzone',{
    maxFiles: 1,
    acceptedFiles: 'image/*',
    maxFilesize: 3
});
import "dropzone/dist/dropzone.css";

document.addEventListener('DOMContentLoaded',(event) => {
    /*let profilePictureElement = document.querySelector('.profile-picture');
    let dropZoneElement = document.querySelector('.dropzone');
    dropZoneElement.style['width'] =  profilePictureElement.clientWidth + 'px';
    dropZoneElement.style['height'] = profilePictureElement.clientHeight + 'px';
    console.log(profilePictureElement.clientWidth);
    console.log(dropZoneElement.clientWidth);*/

    let avatarPencilElement = document.querySelector('.avatar-pencil-position');
    avatarPencilElement.addEventListener('click',(event) => {
        console.log('event');
        let profilePictureElement = document.querySelector('.profile-picture');
        let dropZoneElement = document.querySelector('.dropzone');

        if (profilePictureElement.classList.contains('displayed')) {
            profilePictureElement.classList.remove('displayed');
            profilePictureElement.classList.add('not-displayed');
            dropZoneElement.classList.add('displayed');
            dropZoneElement.classList.remove('not-displayed');
        }else{
            profilePictureElement.classList.add('displayed');
            profilePictureElement.classList.remove('not-displayed');
            dropZoneElement.classList.add('not-displayed');
            dropZoneElement.classList.remove('displayed');
        }

    });
})
