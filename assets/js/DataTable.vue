<template>
  <div>
    <v-row v-if="hasSearchBar" class="align-center">
      <v-col class="flex-grow-1 flex-shrink-0">
        <SearchBar v-model="searchValue" :search="table?.search"/>
      </v-col>
      <v-col v-if="hasFilters" class="flex-grow-0 flex-shrink-1">
        <FiltersEditor v-model="editFilters" v-bind="{ filters, activeFilters, loading }"
                       @apply="applyFilter"/>
      </v-col>
    </v-row>
    <v-row v-if="hasFilters" class="align-center" no-gutters>
      <v-col class="flex-grow-1 flex-shrink-0">
        <FilterChip v-for="(filter, k) of activeFilters" v-model="activeFilters[k]"
                    @click="editFilters = true;"/>
      </v-col>
      <v-col v-if="!hasSearchBar" class="flex-grow-0 flex-shrink-1">
        <FiltersEditor v-model="editFilters" v-bind="{ filters, activeFilters, loading }"
                       @apply="applyFilter"/>
      </v-col>
    </v-row>
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
        }" @update:options="loadItems">
      <!-- Pass all slots/ -->
      <template v-for="(_, slotName) in $slots" v-slot:[slotName]="slotProps">
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
            <v-pagination v-model="page" :length="lastPage" :total-visible="7"/>
            <v-btn icon="refresh" size="small" variant="text" @click="reload"/>
          </div>
        </slot>
      </template>
    </v-data-table-server>
  </div>
</template>
<script>
import {TableQueryParser} from './TableQueryParser';
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
    name: {type: String, required: false},
    multiSort: {type: Boolean, default: true},
    modelValue: {type: Array, default: () => []},

    showSearch: {type: Boolean, default: true},
    showFilters: {type: Boolean, default: true},
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
      return (this.parser?.headers || []).map(h => Object.assign({title: h.name, value: h.name}, h));
    },
    items() {
      return this.table?.data || [];
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
    showSelect() {
      return !!this.table?.selection?.selection;
    },
    returnObject() {
      return typeof this.table?.selection?.selection !== 'string';
    },
    selectStrategy() {
      return this.table?.selection?.strategy ?? 'page';
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
      this.loadItems({page, sortBy, itemsPerPage});
    },
    loadItems({page, itemsPerPage, sortBy}) {
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
      this.loadItems({page, sortBy, itemsPerPage});
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
