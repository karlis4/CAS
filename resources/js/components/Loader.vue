<script setup>

</script>

<template>
<span class="loader"></span>
</template>

<style scoped>
.loader {
    position: absolute;
    top: 300px;
    left: 700px;
    z-index: 10003;
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-bottom-color: #FF3D00;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
    }

    @keyframes rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
    }

</style>
