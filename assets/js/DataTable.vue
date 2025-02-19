<template>
    <div>
        <v-data-table-server v-model:items-per-page="itemsPerPage" v-bind="{
            headers,
            items,
            sortBy,
            itemsLength,
            multiSort,
            loading,
        }" @update:options="loadItems">
            <!-- Pass all slots/ -->
            <template v-for="(_, slotName) in $slots" v-slot:[slotName]="slotProps">
                <slot :name="slotName" v-bind="slotProps ?? {}" />
            </template>
            <!-- /Pass all slots -->
            <!-- Default slots -->
            <template v-for="(header, slotName) in itemSlots" v-slot:[slotName]="{ item }">
                <component v-if="header.component" :is="header.component" v-bind="{
                    header,
                    item,
                    name: header.value,
                    value: item[header.value]
                }" />
                <template v-else>{{ formatItem(header, item) }}</template>
            </template>
        </v-data-table-server>
    </div>
</template>
<script>
import { TableQueryParser } from './TableQueryParser';

export default {
    props: {
        name: { type: String, required: false },
        multiSort: { type: Boolean, default: true },
    },
    data() {
        const dataPrefix = '_tables';
        const name = this.name ?? 'default';

        const table = this.$page.props[dataPrefix][name];
        const prefix = table?.prefix ?? '';

        const parser = new TableQueryParser(name, prefix, { dataPrefix });

        return {
            table,
            parser,
        }
    },
    computed: {
        itemsPerPage: {
            get() {
                return this.perPage;
            },
            set(v) {
                this.setItemsPerPage(v);
            },
        },
        headers() {
            return (this.table?.headers || []).map(h => Object.assign({ title: h.value }, h));
        },
        items() {
            return this.table?.data || [];
        },
        filters() {
            return this.parser.filter;
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
        }
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
                this.parser.get(null, sortBy, page, itemsPerPage);
            }
        },
        formatItem(header, item) {
            const key = header.value;
            const type = header.type;
            const value = item[key];

            const typeFormatters = {
                date: (value, key, item) => value && (new Date(value).toLocaleDateString()),
                datetime: (value, key, item) => value && (new Date(value).toLocaleString()),
            };

            if (type && typeFormatters[type] !== undefined) {
                return (typeFormatters[type])(value, key, item);
            }

            return value;
        }
    }
}
</script>
