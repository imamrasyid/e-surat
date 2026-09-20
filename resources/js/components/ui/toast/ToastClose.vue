<script setup lang="ts">
import type { ToastCloseProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { X } from "@lucide/vue"
import { ToastClose } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<ToastCloseProps & {
  class?: HTMLAttributes["class"]
}>()

const delegatedProps = reactiveOmit(props, "class")
</script>

<template>
  <ToastClose v-bind="delegatedProps"
    :class="cn(
      'absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100',
      'group-[.destructive]:text-red-300 group-[.destructive]:hover:text-red-50 group-[.destructive]:focus:ring-red-400 group-[.destructive]:focus:ring-offset-red-600',
      'group-[.success]:text-sky-300 group-[.success]:hover:text-sky-50 group-[.success]:focus:ring-sky-400 group-[.success]:focus:ring-offset-sky-600',
      'group-[.warning]:text-amber-300 group-[.warning]:hover:text-amber-50 group-[.warning]:focus:ring-amber-400 group-[.warning]:focus:ring-offset-amber-600',
      'group-[.info]:text-indigo-300 group-[.info]:hover:text-indigo-50 group-[.info]:focus:ring-indigo-400 group-[.info]:focus:ring-offset-indigo-600',
      props.class)"
  >
    <X class="h-4 w-4" />
  </ToastClose>
</template>