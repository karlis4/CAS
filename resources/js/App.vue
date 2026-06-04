<template>
    <router-view v-slot="{ Component, route }">
      <transition name="slide" mode="out-in">
        <component :is="Component" :key="route.path" />
      </transition>
    </router-view>
</template>

<style>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>
