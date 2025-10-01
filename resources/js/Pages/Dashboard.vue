<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tasks: Object, // Now a paginated object
    categories: Array,
    filters: Object
})

const showCreateForm = ref(false)
const showCreateCategoryForm = ref(false)
const editingTask = ref(null)
const editingCategory = ref(null)
const selectedCategory = ref(props.filters.category_id || '')

const createForm = useForm({
    title: '',
    description: '',
    due_date: '',
    category_id: ''
})

const categoryForm = useForm({
    name: '',
    color: '#3B82F6',
    from_dashboard: true,
})

const editCategoryForm = useForm({
    name: '',
    color: '',
    from_dashboard: true,
})

const editForm = useForm({
    title: '',
    description: '',
    due_date: '',
    category_id: ''
})

const createTask = () => {
    createForm.post(route('tasks.store'), {
        onSuccess: () => {
            createForm.reset()
            showCreateForm.value = false
        }
    })
}

const editTask = (task) => {
    editingTask.value = task
    editForm.title = task.title
    editForm.description = task.description || ''
    editForm.due_date = task.due_date || ''
    editForm.category_id = task.category_id || ''
}

const updateTask = () => {
    editForm.patch(route('tasks.update', editingTask.value.id), {
        onSuccess: () => {
            editingTask.value = null
            editForm.reset()
        }
    })
}

const createCategory = () => {
    categoryForm.post(route('categories.store'), {
        onSuccess: () => {
            categoryForm.reset()
            showCreateCategoryForm.value = false
        }
    })
}

const editCategory = (category) => {
    editingCategory.value = category
    editCategoryForm.name = category.name
    editCategoryForm.color = category.color
}

const updateCategory = () => {
    editCategoryForm.put(route('categories.update', editingCategory.value.id), {
        onSuccess: () => {
            editingCategory.value = null
            editCategoryForm.reset()
        }
    })
}

const cancelEditCategory = () => {
    editingCategory.value = null
    editCategoryForm.reset()
}

const deleteCategory = (categoryId) => {
    if (confirm('Are you sure you want to delete this category? This will also remove it from all tasks.')) {
        router.delete(route('categories.destroy', categoryId), {
            onSuccess: () => {
                // Refresh the page to update the categories list
                router.reload()
            }
        })
    }
}

const filterByCategory = (categoryId) => {
    selectedCategory.value = categoryId
    router.get(route('dashboard'), {
        category_id: categoryId || null
    }, {
        preserveState: true,
        replace: true
    })
}

const clearFilter = () => {
    selectedCategory.value = ''
    router.get(route('dashboard'), {}, {
        preserveState: true,
        replace: true
    })
}

const cancelEdit = () => {
    editingTask.value = null
    editForm.reset()
}

const deleteTask = (taskId) => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', taskId))
    }
}

const toggleTask = (taskId) => {
    router.patch(route('tasks.toggle', taskId), {}, {
        preserveState: true
    })
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tasks</h3>
                            <p class="text-gray-600 mb-4">Manage your tasks and stay organized</p>
                            <button
                                @click="showCreateForm = !showCreateForm"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium inline-block"
                            >
                                {{ showCreateForm ? 'Cancel' : 'Add Task' }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Categories</h3>
                            <p class="text-gray-600 mb-4">Organize your tasks with categories</p>
                            <div class="flex gap-2">
                                <button
                                    @click="showCreateCategoryForm = !showCreateCategoryForm"
                                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium inline-block"
                                >
                                    {{ showCreateCategoryForm ? 'Cancel' : 'Add Category' }}
                                </button>
                            </div>

                            <!-- Create Category Form -->
                            <div v-if="showCreateCategoryForm" class="mt-4 p-4 border rounded-lg bg-gray-50">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Create New Category</h4>
                                <form @submit.prevent="createCategory" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                        <input
                                            v-model="categoryForm.name"
                                            type="text"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                                            <input
                                                v-model="categoryForm.color"
                                                type="color"
                                                class="h-10 w-16 p-0 border-gray-300 rounded-md shadow-sm cursor-pointer"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="showCreateCategoryForm = false"
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="categoryForm.processing"
                                            class="bg-purple-500 hover:bg-purple-600 disabled:opacity-50 text-white px-4 py-2 rounded-md text-sm font-medium"
                                        >
                                            {{ categoryForm.processing ? 'Creating...' : 'Create Category' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Edit Category Form -->
                            <div v-if="editingCategory" class="mt-4 p-4 border rounded-lg bg-blue-50">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Edit Category</h4>
                                <form @submit.prevent="updateCategory" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                        <input
                                            v-model="editCategoryForm.name"
                                            type="text"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                                            <div class="flex items-center gap-3">
                                                <input
                                                    v-model="editCategoryForm.color"
                                                    type="color"
                                                    class="h-10 w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />
                                                <input
                                                    v-model="editCategoryForm.color"
                                                    type="text"
                                                    placeholder="#3B82F6"
                                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    pattern="^#[0-9A-Fa-f]{6}$"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="cancelEditCategory"
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="editCategoryForm.processing"
                                            class="bg-blue-500 hover:bg-blue-600 disabled:opacity-50 text-white px-4 py-2 rounded-md text-sm font-medium"
                                        >
                                            {{ editCategoryForm.processing ? 'Updating...' : 'Update Category' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Categories List -->
                            <div v-if="categories.length > 0" class="mt-4">
                                <h4 class="text-md font-medium text-gray-900 mb-3">Your Categories</h4>
                                <div class="space-y-2">
                                    <div
                                        v-for="category in categories"
                                        :key="category.id"
                                        v-show="!editingCategory || editingCategory.id !== category.id"
                                        class="flex items-center justify-between p-3 border rounded-lg hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-4 h-4 rounded-full"
                                                :style="{ backgroundColor: category.color }"
                                            ></div>
                                            <span class="font-medium text-gray-900">{{ category.name }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="editCategory(category)"
                                                class="text-indigo-500 hover:text-indigo-600 text-xs"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteCategory(category.id)"
                                                class="text-red-500 hover:text-red-600 text-xs"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="!showCreateCategoryForm && !editingCategory" class="mt-4 text-center py-4 text-gray-500">
                                No categories yet. Click "Add Category" to create your first category.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Tasks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">All Tasks</h3>
                            <button
                                @click="showCreateForm = !showCreateForm"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                {{ showCreateForm ? 'Cancel' : 'Add Task' }}
                            </button>
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-6">
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-medium text-gray-700">Filter by category:</label>
                                <select
                                    v-model="selectedCategory"
                                    @change="filterByCategory(selectedCategory)"
                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <button
                                    v-if="selectedCategory"
                                    @click="clearFilter"
                                    class="text-sm text-gray-500 hover:text-gray-700"
                                >
                                    Clear filter
                                </button>
                            </div>
                        </div>
                        
                        <!-- Create Task Form -->
                        <div v-if="showCreateForm" class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Create New Task</h4>
                            <form @submit.prevent="createTask" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                    <input
                                        v-model="createForm.title"
                                        type="text"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea
                                        v-model="createForm.description"
                                        rows="2"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                        <input
                                            v-model="createForm.due_date"
                                            type="date"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                        <select
                                            v-model="createForm.category_id"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">No Category</option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button
                                        type="button"
                                        @click="showCreateForm = false"
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="createForm.processing"
                                        class="bg-blue-500 hover:bg-blue-600 disabled:opacity-50 text-white px-4 py-2 rounded-md text-sm font-medium"
                                    >
                                        {{ createForm.processing ? 'Creating...' : 'Create Task' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div v-if="tasks.data.length === 0 && !showCreateForm" class="text-center py-8 text-gray-500">
                            No tasks yet. Click "Add Task" to create your first task.
                        </div>
                        
                        <div v-else class="space-y-4">
                            <!-- Edit Task Form -->
                            <div v-if="editingTask" class="p-4 border rounded-lg bg-blue-50">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Edit Task</h4>
                                <form @submit.prevent="updateTask" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                        <input
                                            v-model="editForm.title"
                                            type="text"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea
                                            v-model="editForm.description"
                                            rows="2"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        ></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                            <input
                                                v-model="editForm.due_date"
                                                type="date"
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                            <select
                                                v-model="editForm.category_id"
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">No Category</option>
                                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                                    {{ category.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="cancelEdit"
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="editForm.processing"
                                            class="bg-blue-500 hover:bg-blue-600 disabled:opacity-50 text-white px-4 py-2 rounded-md text-sm font-medium"
                                        >
                                            {{ editForm.processing ? 'Updating...' : 'Update Task' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div
                                v-for="task in tasks.data"
                                :key="task.id"
                                v-show="!editingTask || editingTask.id !== task.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                                :class="{
                                    'bg-red-50 border-red-200': task.is_overdue,
                                    'bg-yellow-50 border-yellow-200': task.is_due_soon && !task.is_overdue,
                                    'bg-green-50 border-green-200': task.completed
                                }"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <input
                                                type="checkbox"
                                                :checked="task.completed"
                                                @change="toggleTask(task.id)"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <h4 class="text-md font-medium" :class="{ 'line-through text-gray-500': task.completed }">
                                                {{ task.title }}
                                            </h4>
                                            <span
                                                v-if="task.category"
                                                class="px-2 py-1 text-xs rounded-full text-white"
                                                :style="{ backgroundColor: task.category.color }"
                                            >
                                                {{ task.category.name }}
                                            </span>
                                        </div>
                                        
                                        <p v-if="task.description" class="text-gray-600 mb-2 text-sm">{{ task.description }}</p>
                                        
                                        <div class="flex items-center gap-4 text-xs text-gray-500">
                                            <span v-if="task.due_date">
                                                Due: {{ formatDate(task.due_date) }}
                                                <span v-if="task.is_due_soon" class="text-yellow-600 font-medium">(Due Soon!)</span>
                                                <span v-if="task.is_overdue" class="text-red-600 font-medium">(Overdue!)</span>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-2 ml-4">
                                        <button
                                            @click="editTask(task)"
                                            class="text-indigo-500 hover:text-indigo-600 text-xs"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteTask(task.id)"
                                            class="text-red-500 hover:text-red-600 text-xs"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="tasks.last_page > 1" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ tasks.from }} to {{ tasks.to }} of {{ tasks.total }} results
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    v-if="tasks.prev_page_url"
                                    :href="tasks.prev_page_url"
                                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <template v-for="page in tasks.links" :key="page.label">
                                    <Link
                                        v-if="page.url"
                                        :href="page.url"
                                        class="px-3 py-2 text-sm font-medium rounded-md"
                                        :class="page.active 
                                            ? 'text-white bg-indigo-600 border border-indigo-600' 
                                            : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'"
                                        v-html="page.label"
                                    />
                                    <span
                                        v-else
                                        class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md"
                                        v-html="page.label"
                                    />
                                </template>
                                <Link
                                    v-if="tasks.next_page_url"
                                    :href="tasks.next_page_url"
                                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
