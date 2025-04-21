<template>
    <Card>
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">{{ title }}</CardTitle>
        <div class="h-4 w-4 text-muted-foreground">
          <component :is="iconComponent" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ value }}</div>
      </CardContent>
    </Card>
  </template>
  
  <script setup>
  import { computed } from 'vue'; // This was missing!
  import { Card, CardHeader, CardContent, CardTitle } from '@/Components/ui/card';
  import { Building, Users, Home } from 'lucide-vue-next';
  
  const props = defineProps({
    title: String,
    value: [Number, String],
    icon: {
      type: String,
      default: 'Building'
    }
  });
  
  const iconComponent = computed(() => {
    switch(props.icon) {
      case 'Building': return Building;
      case 'Users': return Users;
      case 'Home': return Home;
      default: return Building;
    }
  });
  </script>