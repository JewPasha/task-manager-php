<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Categories
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('tasks.index')"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Back to Tasks
                    </Link>
                    <Link
                        :href="route('categories.create')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Add Category
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="categories.length === 0" class="text-center py-8 text-gray-500">
                            No categories found. <Link :href="route('categories.create')" class="text-blue-500 hover:text-blue-600">Create your first category</Link>
                        </div>
                        
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="category in categories"
                                :key="category.id"
                                class="border rounded-lg p-6 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-4 h-4 rounded-full"
                                            :style="{ backgroundColor: category.color }"
                                        ></div>
                                        <h3 class="text-lg font-medium">{{ category.name }}</h3>
                                    </div>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('categories.edit', category.id)"
                                            class="text-indigo-500 hover:text-indigo-600 text-sm"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteCategory(category.id)"
                                            class="text-red-500 hover:text-red-600 text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-sm text-gray-600">
                                    <p>{{ category.tasks_count }} {{ category.tasks_count === 1 ? 'task' : 'tasks' }}</p>
                                    <p>Created: {{ formatDate(category.created_at) }}</p>
                                </div>
                                
                                <div class="mt-4">
                                    <Link
                                        :href="route('tasks.index', { category_id: category.id })"
                                        class="text-blue-500 hover:text-blue-600 text-sm font-medium"
                                    >
                                        View Tasks →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    categories: Array
})

const deleteCategory = (categoryId) => {
    if (confirm('Are you sure you want to delete this category? All tasks in this category will become uncategorized.')) {
        router.delete(route('categories.destroy', categoryId))
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
