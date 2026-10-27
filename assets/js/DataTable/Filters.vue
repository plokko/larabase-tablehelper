<script setup>
import {ref} from 'vue';
import FilterChip from './FilterChip.vue';
import FiltersEditor from './FiltersEditor.vue';

defineProps({
    filters: {
        type: Array,
        required: true,
    },
    activeFilters: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:filters']);
const edit = ref(false);

function applyFilter(filters) {
    emit('update:filters', filters);
}
</script>
<template>
    <v-row>
        <v-col class="flex-grow-1 flex-shrink-0">
            <FilterChip
                v-for="(filter, k) in activeFilters"
                :key="filter.name ?? k"
                :model-value="filter"
                :loading="loading"
                @click="edit = true"
            />
        </v-col>

        <v-col class="flex-grow-0 flex-shrink-1">
            <FiltersEditor
                v-model="edit"
                v-bind="{ filters, activeFilters, loading }"
                @apply="applyFilter"
            />
        </v-col>
    </v-row>
</template>
