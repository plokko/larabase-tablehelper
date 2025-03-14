<template>

    <v-dialog v-model="model" v-bind="{
        width: 700,
        persistent: true,
        scrollable: true,
    }">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="{
                icon: 'filter_alt',
                text: 'Filtri',
                density: 'comfortable',
                variant: 'outlined',
                ...activatorProps,
            }" />
        </template>
        <v-card v-bind="{
            title: 'Gestisci filtri',
            loading,


            prependIcon: 'filter_alt',
            //color: 'orange',
        }">
            <template v-slot:append>
                <v-btn variant="text" icon="close" size="small" @click="close" />
            </template>
            <v-data-table v-bind="{
                items,
                headers,
                loading,
                itemsPerPage: -1,
                hideDefaultFooter: true,
                fixedFooter: true,
                fixedHeader: true,
            }">

                <template v-slot:item.label="{ item }">
                    <strong>{{ item.title ?? item.label ?? item.name }}</strong>
                </template>
                <template v-slot:item.filter="{ item }">
                    <span class="font-italic font-weight-medium ">Contiene</span>
                </template>
                <template v-slot:item.value="{ item }">

                    <v-text-field v-model="item.value" v-bind="{
                        loading,
                        //label: title,
                        clearable: true,
                        density: 'compact',
                        //prependIcon: 'filter_alt',
                        variant: 'outlined',

                    }">
                        <template v-slot:append>
                            <v-btn v-bind="{ icon: 'delete', color: 'error', density: 'compact', variant: 'text' }"
                                @click="deleteRow(item)" />
                        </template>
                    </v-text-field>
                </template>

                <template v-if="canAddFilters" v-slot:tfoot="{ }">
                    <tr>
                        <td colspan="3" class="px-2">
                            <v-autocomplete v-model="addFilter" v-bind="{
                                items: addableFilters,
                                prependIcon: 'playlist_add',
                                loading,
                                //label: title,
                                density: 'compact',
                                //prependIcon: 'filter_alt',
                                variant: 'outlined',

                            }" />
                        </td>
                    </tr>
                </template>
            </v-data-table>
            <v-card-actions>
                <v-btn v-bind="{
                    prependIcon: 'close',
                    text: trans('common.cancel'),
                }" @click="close" />
                <v-spacer />
                <v-btn v-bind="{
                    color: 'primary',
                    prependIcon: 'save',
                    text: trans('common.save'),
                }" @click="apply" />

            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { h } from 'vue';

export default {
    emits: ['update:modelValue'],
    props: {
        modelValue: { default: false },
        filters: { type: Array, required: true, },
        activeFilters: { type: Array, required: true, },
        loading: { default: false },
    },
    data() {
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

        const items = JSON.parse(JSON.stringify(this.activeFilters));

        return {
            headers,
            items,
            addFilter: null,
        };
    },
    computed: {
        model: {
            get() { return this.modelValue; },
            set(v) { this.$emit('update:modelValue', v); }
        },
        selectedFilterNames() {
            return this.items.map(h => h.name);
        },
        addableFilters() {
            //todo: filter only non selected
            return this.filters
                .filter(h => !this.selectedFilterNames.includes(h.name))
                .map(h => ({
                    value: h,
                    title: h.label ?? h.name,
                }));
        },
        canAddFilters() {
            return this.addableFilters.length > 0;
        },
    },
    methods: {
        close() {
            this.model = false;
        },
        apply() {
            this.$emit('apply', this.items);
            //TODO!
            this.close();
        },
        deleteRow(item) {
            const index = this.items.indexOf(item);
            if (index !== -1) {
                this.items.splice(index, 1);
            }
        }
    },
    watch: {
        addFilter(v) {
            if (v) {
                this.items.push(v);
                this.addFilter = null;
            }
        }
    }
}
</script>
