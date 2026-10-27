<script setup>
import {computed, ref, watch} from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
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

const emit = defineEmits([
    'update:modelValue',
    'apply',
]);

const headers = [
    {
        key: 'label',
        title: 'Campo',
    },
    {
        key: 'filter',
        title: 'filtro',
        sortable: false,
        filterable: false,
        width: 100,
    },
    {
        key: 'value',
        title: 'Valore',
        sortable: false,
        filterable: false,
    },
];

const items = ref(
    JSON.parse(JSON.stringify(props.activeFilters))
);

const addFilter = ref(null);

const model = computed({
    get: () => props.modelValue,
    set: value => emit('update:modelValue', value),
});

const selectedFilterNames = computed(() =>
    items.value.map(item => item.name)
);

const addableFilters = computed(() =>
    props.filters
        .filter(filter => !selectedFilterNames.value.includes(filter.name))
        .map(filter => ({
            value: filter,
            title: filter.label ?? filter.name,
        }))
);

const canAddFilters = computed(() =>
    addableFilters.value.length > 0
);

function close() {
    model.value = false;
}

function apply() {
    emit('apply', items.value);
    close();
}

function deleteRow(item) {
    const index = items.value.indexOf(item);

    if (index !== -1) {
        items.value.splice(index, 1);
    }
}

watch(addFilter, value => {
    if (value) {
        items.value.push(value);
        addFilter.value = null;
    }
});
</script>
<template>
    <v-dialog
        v-model="model"
        :width="700"
        persistent
        scrollable
    >
        <template #activator="{ props: activatorProps }">
            <v-btn
                v-bind="{
                    icon: 'filter_alt',
                    text: 'Filtri',
                    density: 'compact',
                    variant: 'outlined',
                    ...activatorProps,
                }"
            />
        </template>

        <v-card
            v-bind="{
                title: 'Gestisci filtri',
                loading,
                prependIcon: 'filter_alt',
            }"
        >
            <template #append>
                <v-btn
                    variant="text"
                    icon="close"
                    size="small"
                    @click="close"
                />
            </template>

            <v-data-table
                v-bind="{
                    items,
                    headers,
                    loading,
                    itemsPerPage: -1,
                    hideDefaultFooter: true,
                    fixedFooter: true,
                    fixedHeader: true,
                }"
            >
                <template #item.label="{ item }">
                    <strong>
                        {{ item.title ?? item.label ?? item.name }}
                    </strong>
                </template>

                <template #item.filter>
                    <span class="font-italic font-weight-medium">
                        Contiene
                    </span>
                </template>

                <template #item.value="{ item }">
                    <v-text-field
                        v-model="item.value"
                        v-bind="{
                            loading,
                            clearable: true,
                            density: 'compact',
                            variant: 'outlined',
                        }"
                    >
                        <template #append>
                            <v-btn
                                v-bind="{
                                    icon: 'delete',
                                    color: 'error',
                                    density: 'compact',
                                    variant: 'text',
                                }"
                                @click="deleteRow(item)"
                            />
                        </template>
                    </v-text-field>
                </template>

                <template v-if="canAddFilters" #tfoot>
                    <tr>
                        <td colspan="3" class="px-2">
                            <v-autocomplete
                                v-model="addFilter"
                                v-bind="{
                                    items: addableFilters,
                                    prependIcon: 'playlist_add',
                                    loading,
                                    density: 'compact',
                                    variant: 'outlined',
                                }"
                            />
                        </td>
                    </tr>
                </template>
            </v-data-table>

            <v-card-actions>
                <v-btn
                    v-bind="{
                        prependIcon: 'close',
                        text: trans('common.cancel'),
                    }"
                    @click="close"
                />

                <v-spacer/>

                <v-btn
                    v-bind="{
                        color: 'primary',
                        prependIcon: 'save',
                        text: trans('common.save'),
                    }"
                    @click="apply"
                />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
