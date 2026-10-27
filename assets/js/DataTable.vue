<script setup>
import {computed, reactive, ref, useSlots, watch} from 'vue';
import {trans} from 'laravel-vue-i18n';
import {TableQueryParser} from './TableQueryParser.js';
import ActionRow from './DataTable/ActionRow.vue';
import SearchBar from './DataTable/SearchBar.vue';
import FilterChip from './DataTable/FilterChip.vue';
import FiltersEditor from './DataTable/FiltersEditor.vue';

function equals(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
}

const props = defineProps({
    name: {type: String, required: false},
    multiSort: {type: Boolean, default: true},
    modelValue: {type: Array, default: () => []},

    showSearch: {type: Boolean, default: true},
    showFilters: {type: Boolean, default: false},
    rowProps: {},
    title: {},
});
const emit = defineEmits(['update:modelValue', 'click:row']);
const slots = useSlots();

const parser = reactive(new TableQueryParser(props.name, {
    dataPrefix: '_tables'
}));
const editFilters = ref(false);
const selected = ref(props.modelValue);

const searchValue = computed({
    get() {
        return parser.searchValue;
    },
    set(value) {
        parser.searchValue = value;
    }
});
const table = computed(() => parser.table);
const currentPage = computed(() => table.value.current_page ?? 1);
const perPage = computed(() => table.value.per_page);
const page = computed({
    get() {
        return currentPage.value;
    },
    set(value) {
        setPage(value);
    },
});
const itemsPerPage = computed({
    get() {
        return perPage.value;
    },
    set(value) {
        setItemsPerPage(value);
    },
});
const headers = computed(() => (parser?.headers || []).map(h => Object.assign({title: h.name, value: h.name}, h)));
const items = computed(() => parser.data);
const filters = computed({
    get() {
        return parser.filters;
    },
    set(value) {
        parser.filters = value;
    },
});
const activeFilters = computed(() => parser.activeFilters);
const sortBy = computed(() => parser.sort);
const itemsLength = computed(() => table.value?.total || 0);
const loading = computed(() => parser.loading);
const lastPage = computed(() => table.value.last_page);
const itemSlots = computed(() => {
    const slotsByName = {};

    for (const header of headers.value) {
        const slotName = `item.${header.value}`;
        if (slots[slotName] === undefined) {
            //No item prop defined
            slotsByName[slotName] = header;
        }
    }

    return slotsByName;
});
const hasSearchBar = computed(() => props.showSearch && parser.showSearch);
const hasFilters = computed(() => props.showFilters && filters.value.length > 0);
const selectionValue = computed(() => table.value?.selection?.value);
const showSelect = computed(() => !!table.value?.selection);
const returnObject = computed(() => table.value?.selection?.returnObject ?? true);
const selectStrategy = computed(() => table.value?.selection?.strategy ?? 'page');
const itemValue = computed(() => table.value?.selection?.itemKey ?? 'id');
const allowedPageSizes = computed(() => parser?.allowedPageSizes);
const itemsPerPageOptions = computed(() => allowedPageSizes.value.map(value => ({value, title: `${value}`})));

function setItemsPerPage(itemsPerPage) {
    const page = currentPage.value;
    const sortBy = parser.sort;
    loadItems({page, sortBy, itemsPerPage});
}

function loadItems({page, itemsPerPage, sortBy}) {
    //console.warn('loaditems!', {page, itemsPerPage, sortBy});
    if (JSON.stringify(sortBy) !== JSON.stringify(parser.sort)
        || page !== currentPage.value
        || itemsPerPage !== perPage.value
    ) {
        parser.reloadWith({
            //filter
            sort: sortBy,
            //search
            page,
            //
            itemsPerPage,
        });
    }
}

function formatItem(header, item) {
    const key = header.value;
    const format = header.format;
    const value = item[key];

    const formatters = {
        date: value => value && (new Date(value).toLocaleDateString()),
        datetime: value => value && (new Date(value).toLocaleString()),
        trans(value, key, item, args) {
            if (args.length === 0)
                return value;
            const tr = args[0];
            const transId = tr.replace('$value', value ?? '');

            return trans(transId);
        },
    };

    if (format) {
        if (formatters[format] !== undefined) {
            return (formatters[format])(value, key, item);
        }
        const split = format.split(':');
        if (split.length > 0 && formatters[split[0]] !== undefined) {
            const args = split.slice(1).join(':').split(',');
            return (formatters[split[0]])(value, key, item, args);
        }
    }

    return value;
}

function setPage(page) {
    console.warn('setpage', page);
    if (page === currentPage.value) {
        return;
    }
    const itemsPerPage = perPage.value;
    const sortBy = parser.sort;
    loadItems({page, sortBy, itemsPerPage});
}

function reload() {
    parser.reload();
}

function applyFilter(value) {
    console.warn('applyFilter', value)
    filters.value = value;
}

watch(() => props.modelValue, modelValue => {
    if (!equals(selected.value, modelValue)) {
        selected.value = modelValue;
    }
});
watch(selected, value => {
    if (!equals(value, props.modelValue)) {
        emit('update:modelValue', value);
    }
});
</script>
<template>
    <VCard v-bind="{
        title,
    }">
        <v-data-table-server v-model="selected" v-model:items-per-page="itemsPerPage" v-bind="{
            headers,
            items,
            sortBy,
            itemsLength,
            multiSort,
            page: currentPage,
            loading,
            showSelect,
            returnObject,
            selectStrategy,
            itemValue,
            elevation: 2,
            rowProps,
        }" @update:options="loadItems" @click:row="(e, v) => emit('click:row', v)">
            <template #top v-if="hasSearchBar || hasFilters">
                <div class="align-center mx-4 my-2">
                    <!-- Search bar/ -->
                    <div v-if="hasSearchBar" class="datatable-search-bar d-flex justify-center align-center">
                        <div class="flex-grow-1 flex-shrink-0">
                            <SearchBar v-model="searchValue" v-bind="{
                                loading,
                                search: table?.search,
                            }"/>
                        </div>
                        <!-- Filter editor, if enabled -->
                        <div v-if="hasFilters" class="flex-grow-0 flex-shrink-1 ml-4">
                            <FiltersEditor v-model="editFilters" v-bind="{ filters, activeFilters, loading }"
                                           @apply="applyFilter"/>
                        </div>
                    </div>
                    <!-- /Search bar -->
                    <!-- Filters/ -->
                    <div v-if="hasFilters" class="d-flex justify-center align-center">
                        <!-- Filter list -->
                        <div class="flex-grow-1 flex-shrink-0">
                            <FilterChip v-for="(filter, k) of activeFilters" v-model="activeFilters[k]"
                                        @click="editFilters = true;"/>
                        </div>
                        <!-- Filter editor, only if not present in search bar -->
                        <div v-if="!hasSearchBar" class="flex-grow-0 flex-shrink-1">
                            <FiltersEditor v-model="editFilters" v-bind="{ filters, activeFilters, loading }"
                                           @apply="applyFilter"/>
                        </div>
                    </div>
                    <!-- /Filters -->
                </div>
            </template>
            <!-- Pass all slots/ -->
            <template v-for="(_, slotName) in slots" v-slot:[slotName]="slotProps">
                <slot :name="slotName" v-bind="slotProps ?? {}"/>
            </template>
            <!-- /Pass all slots -->
            <!-- Default slots -->
            <template v-for="(header, slotName) in itemSlots" v-slot:[slotName]="{ item }">
                <component :is="header.component" v-if="header.component" v-bind="{
                    header,
                    item,
                    name: header.value,
                    value: item[header.value]
                }"/>
                <template v-else-if="['boolean', 'bool'].includes(header.format)">
                    <span v-if="item[header.value] === null">-</span>
                    <v-icon v-if="item[header.value]" color="success" icon="check_circle"/>
                    <v-icon v-else color="error" icon="cancel"/>
                </template>
                <template v-else-if="header.type === 'action'">
                    <ActionRow v-bind="{ value: item, header, }"/>
                </template>
                <template v-else>{{ formatItem(header, item) }}</template>
            </template>
            <!-- custom paginator -->
            <template v-slot:bottom>
                <slot name="bottom" v-bind="{ page, lastPage }">
                    <div class="d-flex justify-center align-center text-center pt-2">
                        <!-- <div>total: {{ itemsLength }}</div>-->
                        <v-pagination v-model="page" v-bind="{
                            length: lastPage,
                            totalVisible: 7,
                            size: 'small',
                        }"/>
                        <v-btn icon="refresh" size="small" variant="text" @click="reload"/>
                    </div>
                </slot>
            </template>
        </v-data-table-server>
        <slot name="card-footer"></slot>
    </VCard>
</template>
