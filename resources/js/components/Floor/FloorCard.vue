<template>
  <Card class="h-full">
    <CardHeader>
      <div class="flex justify-between items-start">
        <div>
          <CardTitle>{{ floor.name }}</CardTitle>
          <CardDescription>Floor #{{ floor.number }}</CardDescription>
        </div>
        <Badge :variant="floor.room_count > 0 ? 'default' : 'secondary'">
          {{ floor.room_count }} rooms
        </Badge>
      </div>
    </CardHeader>
    
    <CardContent>
      <div v-if="isAdmin" class="text-sm text-muted-foreground mb-2">
        Manager: {{ floor.created_by.name }}
      </div>
      <div class="text-sm text-muted-foreground">
        Created: {{ formatDate(floor.created_at) }}
      </div>
    </CardContent>
    
    <CardFooter v-if="canManage" class="flex justify-end gap-2">
      <Button variant="outline" size="sm" @click="$emit('edit')">
        <PencilIcon class="h-4 w-4 mr-1" /> Edit
      </Button>
      <Button variant="destructive" size="sm" @click="$emit('delete')">
        <TrashIcon class="h-4 w-4 mr-1" /> Delete
      </Button>
    </CardFooter>
  </Card>
</template>