<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInboundStore } from '@/stores/inbound'
import { CCard, CCardHeader, CCardBody, CNav, CNavItem, CNavLink, CBadge } from '@coreui/vue'

const route = useRoute()
const router = useRouter()
const store = useInboundStore()
onMounted(() => store.fetchOne(route.params.id as string))

</script>

<template>
  <CCard>
    <CCardHeader class="d-flex align-items-center gap-2">
      <span class="fw-semibold">Inbound</span>
      <CBadge color="secondary">{{ store.current?.income_no }}</CBadge>
      <span class="ms-auto text-body-secondary">Vendor: {{ store.current?.vendor }}</span>
    </CCardHeader>
    <CCardBody>
      <CNav variant="tabs" class="mb-3">
        <CNavItem><CNavLink :active="$route.name==='Inbound Lines'"
          @click="$router.push({name:'Inbound Lines', params:$route.params})">Lines</CNavLink></CNavItem>
        <CNavItem><CNavLink :active="$route.name==='Inbound QR'"
          @click="$router.push({name:'Inbound QR', params:$route.params})">QR</CNavLink></CNavItem>
        <CNavItem><CNavLink :active="$route.name==='Inbound History'"
          @click="$router.push({name:'Inbound History', params:$route.params})">History</CNavLink></CNavItem>
      </CNav>
      <router-view />
    </CCardBody>
  </CCard>
</template>