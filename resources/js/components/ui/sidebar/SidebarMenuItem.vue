<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  href?: string,
  active?: boolean,
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <li
    data-slot="sidebar-menu-item"
    data-sidebar="menu-item"
    :class="cn('group/menu-item relative', props.class)"
  >
    <Link
      :href="props.href"
      :class="[
        'flex items-center gap-2 p-2 rounded hover:bg-muted transition',
        props.active ? 'bg-muted font-medium' : 'text-muted-foreground'
      ]"
    >
      <slot name="icon" />
      <slot />
    </Link>
  </li>
</template>
