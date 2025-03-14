<template>
    <div>
        <v-row>
            <v-col class="flex-grow-1 flex-shrink-0">
                <FilterChip v-for="(filter, k) of activeFilters" v-model="activeFilters[k]" @click="edit = true;" />
            </v-col>
            <v-col class="flex-grow-0 flex-shrink-1">
                <FiltersEditor v-model="edit" v-bind="{ filters, activeFilters, loading }" @apply="applyFilter" />
            </v-col>
        </v-row>
    </div>
</template>

<script>
import FilterChip from './FilterChip.vue';
import FiltersEditor from './FiltersEditor.vue';


export default {
    events: ['update:filters'],
    props: {
        filters: { type: Array, required: true },
        activeFilters: { type: Array, required: true },
        loading: { default: false },

    },
    data() {
        return {
            edit: false,
        };
    },
    methods: {
        applyFilter(filters) {
            this.$emit('update:filters', filters);
        },
    },
    components: {
        FilterChip,
        FiltersEditor,
    }
}
</script>
