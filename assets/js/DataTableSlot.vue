<script setup>
import {computed, reactive, ref, watch, useSlots, nextTick} from 'vue';
import {TableQueryParser} from './TableQueryParser.js';

function equals(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
}

const props = defineProps({
    name: {
        type: String,
        required: false,
    },
    preserveState: {
        type: Boolean,
        default: true,
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);
const slots = useSlots();

const parser = reactive(
    new TableQueryParser(props.name, {
        dataPrefix: '_tables',
    }),
);

const editFilters = ref(false);
const selected = ref(props.modelValue);

const searchValue = computed({
    get: () => parser.searchValue,
    set: (value) => {
        parser.searchValue = value;
    },
});

const table = computed(() => parser.table);

const currentPage = computed(() => table.value?.current_page ?? 1);
const perPage = computed(() => table.value?.per_page);

const page = computed({
    get: () => currentPage.value,
    set: (value) => setPage(value),
});

const itemsPerPage = computed({
    get: () => perPage.value,
    set: (value) => setItemsPerPage(value),
});

const headers = computed(() =>
    (parser.headers || []).map((header) => ({
        title: header.name,
        value: header.name,
        ...header,
    })),
);

const items = computed(() => table.value?.data || []);

const filter = computed({
    get() {
        return filters.value.reduce((acc, item) => {
            acc[item.name] = item;
            return acc;
        }, {});
    },
    set(newObject) {
        filters.value = Object.values(newObject);
    },
});

const filters = computed({
    get: () => parser.filters,
    set: (value) => {
        parser.filters = value;
    },
});

const activeFilters = computed(() => parser.activeFilters);
const availableFilters = computed(() => parser.availableFilters);
const sortBy = computed(() => parser.sort);

const itemsTotal = computed(() => table.value?.total || 0);
const loading = computed(() => parser.loading);
const lastPage = computed(() => table.value?.last_page);
const hasSearchBar = computed(() => parser.showSearch);
const hasFilters = computed(() => filters.value.length > 0);

const showSelect = computed(() => !!table.value?.selection?.selection);

const returnObject = computed(
    () => typeof table.value?.selection?.selection !== 'string',
);

const selectStrategy = computed(() => 'all');

const itemValue = computed(() =>
    typeof table.value?.selection?.selection === 'string'
        ? table.value.selection.selection
        : 'id',
);

const itemSlots = computed(() => {
    const result = {};

    for (const header of headers.value) {
        const slotName = `item.${header.value}`;

        if (slots[slotName] === undefined) {
            result[slotName] = header;
        }
    }

    return result;
});

function setItemsPerPage(value) {
    loadItems({
        page: currentPage.value,
        sortBy: parser.sort,
        itemsPerPage: value,
    });
}

function loadItems({page, itemsPerPage, sortBy}) {
    if (
        JSON.stringify(sortBy) !== JSON.stringify(parser.sort) ||
        page !== currentPage.value ||
        itemsPerPage !== perPage.value
    ) {
        parser.reloadWith({
            sort: sortBy,
            page,
            itemsPerPage,
        });
    }
}

function formatItem(header, item) {
    const key = header.value;
    const value = item[key];

    const formatters = {
        date: (value) =>
            value && new Date(value).toLocaleDateString(),

        datetime: (value) =>
            value && new Date(value).toLocaleString(),
    };

    if (header.format && formatters[header.format]) {
        return formatters[header.format](value, key, item);
    }

    return value;
}

function setPage(value) {
    if (value === currentPage.value) {
        return;
    }

    loadItems({
        page: value,
        sortBy: parser.sort,
        itemsPerPage: perPage.value,
    });
}

function reset() {
    nextTick(() => parser.reload());
}

function apply() {
    nextTick(() => parser.reloadWith({}, props.preserveState));
}

watch(
    () => props.modelValue,
    (value) => {
        if (!equals(selected.value, value)) {
            selected.value = value;
        }
    },
);

watch(selected, (value) => {
    if (!equals(value, props.modelValue)) {
        emit('update:modelValue', value);
    }
});
</script>
<template>
    <div>
        <slot
            v-bind="{
                page,
                itemsPerPage,
                itemsTotal,
                filter,
                filters,
                activeFilters,
                search: searchValue,
                availableFilters,
                apply,
                reset,
            }"
        />
    </div>
</template>
