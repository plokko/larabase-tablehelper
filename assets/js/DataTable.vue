<template>
    <v-card v-bind="{ title, }">
        <v-card-text>
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
            }" @update:options="loadItems" @click:row="(e, v) => $emit('click:row', v)">
                <template v-slot:top v-if="hasSearchBar || hasFilters">
                    <div class="align-center mx-4 ">

                        <!-- Search bar/ -->
                        <div v-if="hasSearchBar" class="datatable-search-bar d-flex justify-center align-center">
                            <div class="flex-grow-1 flex-shrink-0">
                                <SearchBar v-model="searchValue" :search="table?.search" />
                            </div>
                            <!-- Filter editor, if enabled -->
                            <div v-if="hasFilters" class="flex-grow-0 flex-shrink-1 ml-4">
                                <FiltersEditor v-model="editFilters" v-bind="{ filters, activeFilters, loading }"
                                    @apply="applyFilter" />
                            </div>
                        </div>
                        <!-- /Search bar -->
                        <!-- Filters/ -->
                        <div v-if="hasFilters" class="d-flex justify-center align-center">
                            <!-- Filter list -->
                            <div class="flex-grow-1 flex-shrink-0">

                                <FilterChip v-for="(filter, k) of activeFilters" v-model="activeFilters[k]"
                                    @click="editFilters = true;" />
                            </div>

                            <!-- Filter editor, only if not present in search bar -->
                            <div v-if="!hasSearchBar" class="flex-grow-0 flex-shrink-1">
                                <FiltersEditor v-model="editFilters" v-bind="{ filters, activeFilters, loading }"
                                    @apply="applyFilter" />
                            </div>
                        </div>
                        <!-- /Filters -->
                    </div>
                </template>
                <!-- Pass all slots/ -->
                <template v-for="(_, slotName) in $slots" v-slot:[slotName]="slotProps">
                    <slot :name="slotName" v-bind="slotProps ?? {}" />
                </template>
                <!-- /Pass all slots -->
                <!-- Default slots -->
                <template v-for="(header, slotName) in itemSlots" v-slot:[slotName]="{ item }">
                    <component :is="header.component" v-if="header.component" v-bind="{
                        header,
                        item,
                        name: header.value,
                        value: item[header.value]
                    }" />
                    <template v-else-if="['boolean', 'bool'].includes(header.format)">
                        <span v-if="item[header.value] === null">-</span>
                        <v-icon v-if="item[header.value]" color="success" icon="check_circle" />
                        <v-icon v-else color="error" icon="cancel" />
                    </template>
                    <template v-else-if="header.type === 'action'">
                        <ActionRow v-bind="{ value: item, header, }" />
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
                                //size: 'small',
                            }" />
                            <v-btn icon="refresh" size="small" variant="text" @click="reload" />
                        </div>
                    </slot>
                </template>
            </v-data-table-server>
        </v-card-text>
        <slot name="card-footer"></slot>
    </v-card>
</template>
<script>
import { TableQueryParser } from './TableQueryParser';
import ActionRow from './DataTable/ActionRow.vue';
import SearchBar from './DataTable/SearchBar.vue';
import FilterChip from './DataTable/FilterChip.vue';
import FiltersEditor from './DataTable/FiltersEditor.vue';


function equals(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
}

export default {
    events: ['update:modelValue'],
    props: {
        name: { type: String, required: false },
        multiSort: { type: Boolean, default: true },
        modelValue: { type: Array, default: () => [] },

        showSearch: { type: Boolean, default: true },
        showFilters: { type: Boolean, default: true },
        rowProps: {},
        title: {},
    },
    data() {
        const parser = new TableQueryParser(this.name, {
            dataPrefix: '_tables'
        });
        return {
            parser,
            editFilters: false,
            selected: this.modelValue,
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
            return this.parser.data;
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
        sortBy() {
            return this.parser.sort;
        },
        itemsLength() {
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
        selectionValue() {
            return this.table?.selection?.value;
        },
        showSelect() {
            return !!this.table?.selection;
        },
        returnObject() {
            return this.table?.selection?.returnObject ?? true;
        },
        selectStrategy() {
            return this.table?.selection?.strategy ?? 'page';
        },
        itemValue() {
            return this.table?.selection?.itemKey ?? 'id';
        },
        allowedPageSizes() {
            return this.parser?.allowedPageSizes;
        },
        itemsPerPageOptions() {
            return this.allowedPageSizes.map(value => ({ value, title: `${value}` }));
        }
    },
    methods: {
        setItemsPerPage(itemsPerPage) {
            const page = this.currentPage;
            const sortBy = this.parser.sort;
            this.loadItems({ page, sortBy, itemsPerPage });
        },
        loadItems({ page, itemsPerPage, sortBy }) {
            console.warn('loaditems!', { page, itemsPerPage, sortBy })
            if (JSON.stringify(sortBy) !== JSON.stringify(this.parser.sort)
                || page !== this.currentPage
                || itemsPerPage !== this.perPage
            ) {
                this.parser.reloadWith({
                    //filter
                    sort: sortBy,
                    //search
                    page,
                    //
                    itemsPerPage,
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
        },
        setPage(page) {
            console.warn('setpage', page)
            if (page === this.currentPage) {
                return;
            }
            const itemsPerPage = this.itemsPerPage;
            const sortBy = this.parser.sort;
            this.loadItems({ page, sortBy, itemsPerPage });
        },
        reload() {
            this.parser.reload();
        },

        applyFilter(filters) {
            this.filters = filters;
            //this.$emit('update:filters', filters);
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
    components: {
        ActionRow,
        SearchBar,
        FilterChip,
        FiltersEditor,
    }
}
</script>
