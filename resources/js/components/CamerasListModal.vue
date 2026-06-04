<script setup>
import { ref, computed, defineEmits, defineProps } from 'vue'
import gsap from 'gsap'
import { useMarkersStore } from '../stores/markers';
import { useRouter } from 'vue-router';

const emit = defineEmits(['hideCamerasWindow', 'removeCamera']);
const props = defineProps(['hide']);
const router = useRouter();

function hideWindow(){
    emit('hideCamerasWindow');
}

function remove(cameraId){
    emit('removeCamera', cameraId);
}

const showCameraInfo = (cameraId) => {
    router.push({ name: 'camera', params: {id: cameraId}});
}

const changeCameraForm = (cameraId) => {
    router.push({ name: 'changeCameraInfo', params: {id: cameraId} })
}

const store = useMarkersStore();

const query = ref('');

const computedList = computed(() => {
    if(query.value == ''){
        return [...store.markers];
    } else {
        return store.markers.filter(camera => camera.real_camera_id.includes(query.value) ||
                                                    camera.name.includes(query.value) ||
                                                    camera.adress.includes(query.value));
    }
});

function onBeforeEnter(el) {
  el.style.opacity = 0
  el.style.height = 0
}

function onEnter(el, done) {
  gsap.to(el, {
    opacity: 1,
    height: '60px',
    delay: el.dataset.index * 0.15,
    onComplete: done
  })
}

function onLeave(el, done) {
  gsap.to(el, {
    opacity: 0,
    height: 0,
    delay: el.dataset.index * 0.15,
    onComplete: done
  })
}
</script>

<template>
<Transition name="slide-fade">
<div v-if="!hide" class="modalWindow">
    <div class="modalWindowTop">
        <img src="../assests/images/search.svg" class="searchCamera"></img>
        <input type="text" name="searchCamera" v-model="query"></input>
        <img src="../assests/images/xmark.svg" class="closeSearchForm" @click="hideWindow"></img>
    </div>
    <transition-group
                    tag="ul"
                    name="cameraList"
                    class="cameraList"
                    :css="false"
                    @before-enter="onBeforeEnter"
                    @enter="onEnter"
                    @leave="onLeave">
        <li v-for="camera in computedList"
            class="camera-item"
            :key="camera.id"
        >
            <p>{{ camera.real_camera_id }} | {{ camera.adress }} | {{ camera.name }}
                <button class="changeCamera" @click="changeCameraForm(camera.id)"><img src="../assests/images/pen-solid-full.svg" class="changeIcon"></img></button>
                <button class="deleteCamera" @click="remove(camera.id)"><img src="../assests/images/trash.svg" class="deleteIcon"></img></button>
                <button class="showCameraInfo" @click="showCameraInfo(camera.id)"><img src="../assests/images/eye-solid-full.svg" class="showIcon"></img></button>
            </p>
        </li>
    </transition-group>
</div>
</Transition>
</template>

<style scoped>
.modalWindow{
    position: absolute;
    z-index: 10000;
    top: 40px;
    left: 440px;
    width: 650px;
    height: 600px;
    background-color: #f8f3f3;
    border-radius: 10px;
    border: 1px solid black;
}

.hideForm{
    display: none;
}

.modalWindowTop{
    width: 100%;
    height: 60px;
    border-radius: 10px;
    border: 1px solid black;
    background-color: rgb(229, 229, 237);
    border-bottom-left-radius: 0px;
    border-bottom-right-radius: 0px;
    border-top: 0px;
    border-left: 0px;
    border-right: 0px;
}

.searchCamera{
    margin-top: 15px;
    margin-left: 130px;
    width: 25px;
    height: 25px;
}

input[name="searchCamera"]{
    margin-left: 20px;
}

.closeSearchForm{
    position: relative;
    top: 0px;
    left: 110px;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.cameraList{
    list-style-type: none;
    overflow-y: auto;
    background-color: rgb(255, 255, 255);
    margin: 0px;
    padding: 0px;
    width: 100%;
    height: 538px;
    border-radius: 10px;
}

.camera-item{
    height: 60px;
    width: 100%;
    border-bottom: 1px solid #000000;
}

.camera-item > p{
    margin: 0px;
    padding-top: 15px;
    padding-left: 30px;
    font-size: 12px;
}

.camera-item > p > .deleteCamera{
    background-color: #aa0b0b;
    margin-left: 10px;
}

.showCameraInfo{
    margin-left: 5px;
    background-color: #4045ec;
}

.changeCamera{
    margin-right: -5px;
    margin-left: 50px;
    background-color: #2ac60b;
}

.camera-item button{
    margin-top: -5px;
    width: 40px;
    height: 40px;
    border-radius: 50px;
    border: none;
    margin-left: 5px;
}


.deleteIcon{
    padding-top: 5px;
    width: 20px;
    height: 20px;
}

.showIcon{
    padding-top: 5px;
    width: 20px;
    height: 20px;
}

.changeIcon{
    padding-top: 5px;
    width: 20px;
    height: 20px;
}

/* масштабирование */

.slide-fade-enter-active {
    transition: all 0.3s ease;
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
    transform: scale(0.9);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: scale(0.9);
    opacity: 0;
}
</style>
