<script setup>
import avatar from '@/assets/images/avatars/avatar-15.png'
import { logout, me } from '@/api/auth'
import { useRouter } from 'vue-router'

const router = useRouter()
const handleLogout = async () => {

    await logout()
    await me()

    window.location.replace('/#/pages/login')
    // window.location.href = '/#/pages/login'
    console.log(121);
    
    // router.replace('/pages/login')

}

</script>

<template>
  <CDropdown placement="bottom-end" variant="nav-item">
    <CDropdownToggle class="py-0 pe-0" :caret="false">
      <CAvatar :src="avatar" size="md" />
    </CDropdownToggle>
      <CDropdownMenu class="pt-0">
        <CDropdownHeader component="h6" class="bg-body-secondary text-body-secondary fw-semibold mb-2 rounded-top">
          Account
        </CDropdownHeader>
        <CDropdownItem>
          <CIcon icon="cil-user" /> Profile
        </CDropdownItem>
        <CDropdownItem>
          <CIcon icon="cil-settings" /> Đổi mật khẩu
        </CDropdownItem>
          <CDropdownItem @click="handleLogout">
            <CIcon icon="cil-lock-locked" /> Logout
          </CDropdownItem>
      </CDropdownMenu>
  </CDropdown>
</template>
