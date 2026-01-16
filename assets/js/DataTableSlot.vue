<template>
    <div>
        <slot v-bind="{
            page,
            itemsPerPage,
            itemsTotal,
            filter,
            filters,
            activeFilters,
            search: searchValue,
            availableFilters,
            // Apply changes to params
            apply,
            // Reload resetting filters
            reset,
        }">

        </slot>
    </div>
</template>
<script>
import { TableQueryParser } from './TableQueryParser';

function equals(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
}

export default {
    events: ['update:modelValue'],
    props: {
        name: { type: String, required: false },
        preserveState: { type: Boolean, default: false, },
    },
    data() {
        const parser = new TableQueryParser(this.name, {
            dataPrefix: '_tables'
        });
        return {
            parser,
            editFilters: false,
            selected: [],
        }
    },
    computed: {
        searchValue: {
            get() {
                return this.parser.searchValue
            },
            set(v) {
                this.parser.searchValue = v;
            }
        },
        table() {
            return this.parser.table;
        },
        page: {
            get() {
                return this.currentPage;
            },
            set(page) {
                this.setPage(page);
            },
        },
        itemsPerPage: {
            get() {
                return this.perPage;
            },
            set(v) {
                this.setItemsPerPage(v);
            },
        },
        headers() {
            return (this.parser?.headers || []).map(h => Object.assign({ title: h.name, value: h.name }, h));
        },
        items() {
            return this.table?.data || [];
        },

        filter: {
            get() {
                return this.filters.reduce((acc, item) => {
                    acc[item.name] = item
                    return acc
                }, {})
            },
            set(newObject) {
                console.warn('set filter', newObject)
                this.filters.value = Object.values(newObject)
            }
        },
        filters: {
            get() {
                return this.parser.filters;
            },
            set(v) {
                this.parser.filters = v;
            },
        },
        activeFilters() {
            return this.parser.activeFilters;
        },
        availableFilters() {
            return this.parser.availableFilters;
        },
        sortBy() {
            return this.parser.sort;
        },
        itemsTotal() {
            return this.table?.total || 0;
        },
        loading() {
            return this.parser.loading;
        },
        currentPage() {
            return this.table.current_page ?? 1;
        },
        lastPage() {
            return this.table.last_page;
        },
        perPage() {
            return this.table.per_page;
        },
        itemSlots() {
            const slots = {};

            for (const h of this.headers) {
                const slotName = `item.${h.value}`;
                if (this.$slots[slotName] === undefined) {
                    //No item prop defined
                    slots[slotName] = h;
                }
            }

            return slots;
        },
        hasSearchBar() {
            return this.showSearch && this.parser.showSearch;
        },
        hasFilters() {
            return this.showFilters && this.filters.length > 0;
        },
        showSelect() {
            return !!this.table?.selection?.selection;
        },
        returnObject() {
            return typeof this.table?.selection?.selection !== 'string';
        },
        selectStrategy() {
            return 'all';//this.table?.selection?.strategy ?? 'page';
        },
        itemValue() {
            return this.table?.selection?.selection === 'string' ?
                this.table?.selection?.selection :
                'id';
        },
    },
    methods: {
        setItemsPerPage(itemsPerPage) {
            const page = this.currentPage;
            const sortBy = this.parser.sort;
            this.loadItems({ page, sortBy, itemsPerPage });
        },
        loadItems({ page, itemsPerPage, sortBy }) {
            if (JSON.stringify(sortBy) !== JSON.stringify(this.parser.sort)
                || page !== this.currentPage
                || itemsPerPage !== this.perPage
            ) {
                this.parser.reloadWith({
                    //filter
                    sort: sortBy,
                    //search
                    page,
                });
            }
        },
        formatItem(header, item) {
            const key = header.value;
            const format = header.format;
            const value = item[key];

            const formatters = {
                date: (value, key, item) => value && (new Date(value).toLocaleDateString()),
                datetime: (value, key, item) => value && (new Date(value).toLocaleString()),
            };

            if (format && formatters[format] !== undefined) {
                return (formatters[format])(value, key, item);
            }

            return value;
        },
        setPage(page) {
            //console.warn('setpage', page)
            if (page === this.currentPage) {
                return;
            }
            const itemsPerPage = this.itemsPerPage;
            const sortBy = this.parser.sort;
            this.loadItems({ page, sortBy, itemsPerPage });
        },
        reset() {
            this.$nextTick(() => this.parser.reload());
        },
        apply() {
            this.$nextTick(() => this.parser.reloadWith({}, this.preserveState));
        },
    },
    watch: {
        modelValue() {
            if (!equals(this.selected, this.modelValue)) {
                this.selected = this.modelValue;
            }
        },
        selected() {
            if (!equals(this.selected, this.modelValue)) {
                this.$emit('update:modelValue', this.selected);
            }
        },
    },
}
</script>
