<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Category
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': errors.name }"
                                    required
                                />
                                <div v-if="errors.name" class="text-red-500 text-sm mt-1">
                                    {{ errors.name }}
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                    Color *
                                </label>
                                <div class="flex items-center gap-3">
                                    <input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': errors.color }"
                                        required
                                    />
                                    <input
                                        v-model="form.color"
                                        type="text"
                                        placeholder="#3B82F6"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': errors.color }"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                    />
                                </div>
                                <div v-if="errors.color" class="text-red-500 text-sm mt-1">
                                    {{ errors.color }}
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Choose a color to help identify this category
                                </p>
                            </div>

                            <div class="flex justify-end gap-3">
                                <Link
                                    :href="route('categories.index')"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-600 disabled:opacity-50 text-white px-4 py-2 rounded-md text-sm font-medium"
                                >
                                    {{ form.processing ? 'Updating...' : 'Update Category' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    category: Object
})

const form = useForm({
    name: props.category.name,
    color: props.category.color
})

const submit = () => {
    form.put(route('categories.update', props.category.id))
}

const errors = form.errors
</script>
