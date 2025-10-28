<template>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1000055;">
      <div 
        v-for="(toast, index) in toasts" 
        :key="toast.id"
        :class="['toast', toastClass(toast.type)]"
        class="d-block"
        role="alert" 
        aria-live="assertive" 
        aria-atomic="true"
      >
        <div class="toast-header">
          <i :class="iconClass(toast.type)" class="me-2"></i>
          <strong class="me-auto">{{ toast.title }}</strong>
          <small class="text-muted">{{ timeAgo(toast.timestamp) }}</small>
          <button 
            type="button" 
            class="btn-close" 
            @click="removeToast(toast.id)"
            aria-label="Close"
          ></button>
        </div>
        <div class="toast-body">
          {{ toast.message }}
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue'
  
  const toasts = ref([])
  let toastId = 0
  
  // Mapeo de tipos de toast a clases de Bootstrap
  const toastClasses = {
    success: 'text-bg-success',
    error: 'text-bg-danger',
    warning: 'text-bg-warning',
    info: 'text-bg-info',
    default: 'text-bg-primary'
  }
  
  // Mapeo de tipos a iconos (usando Bootstrap Icons)
  const iconClasses = {
    success: 'bi bi-check-circle-fill',
    error: 'bi bi-exclamation-circle-fill',
    warning: 'bi bi-exclamation-triangle-fill',
    info: 'bi bi-info-circle-fill',
    default: 'bi bi-bell-fill'
  }
  
  const toastClass = (type) => {
    return toastClasses[type] || toastClasses.default
  }
  
  const iconClass = (type) => {
    return iconClasses[type] || iconClasses.default
  }
  
  const timeAgo = (timestamp) => {
    const now = new Date()
    const diff = now - timestamp
    const minutes = Math.floor(diff / 60000)
    
    if (minutes < 1) return 'Ahora mismo'
    if (minutes === 1) return 'Hace 1 minuto'
    return `Hace ${minutes} minutos`
  }
  
  // Función para agregar un nuevo toast
  const addToast = (toastData) => {
    console.log(toastData);
    const id = ++toastId
    const toast = {
      id,
      type: toastData.type || 'default',
      title: toastData.title || 'Notificación',
      message: toastData.message || '',
      timestamp: new Date()
    }
    
    toasts.value.push(toast)
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
      removeToast(id)
    }, 5000)
  }
  
  const removeToast = (id) => {
    toasts.value = toasts.value.filter(toast => toast.id !== id)
  }
  
  // Exponer la función addToast para que pueda ser usada globalmente
  defineExpose({ addToast })
  </script>
  
  <style scoped>
  .toast-container {
    min-width: 300px;
    max-width: 400px;
  }
  
  .toast {
    opacity: 1;
    transform: translateX(0);
    transition: opacity 0.3s ease, transform 0.3s ease;
  }
  
  .toast:not(:last-child) {
    margin-bottom: 0.5rem;
  }
  </style>