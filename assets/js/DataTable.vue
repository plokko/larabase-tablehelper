<template>
  <div>
    <SearchBar v-if="hasSearchBar" v-model="searchValue" :search="table?.search"/>

    <Filters v-if="hasFilters" v-model:filters="filters" v-bind="{ activeFilters, loading, }"/>
    <v-data-table-server v-model:items-per-page="itemsPerPage" v-bind="{
            headers,
            items,
            sortBy,
            itemsLength,
            multiSort,
            page: currentPage,
            loading,
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
import Filters from './DataTable/Filters.vue';

export default {
  props: {
    name: {type: String, required: false},
    multiSort: {type: Boolean, default: true},
  },
  data() {
    const parser = new TableQueryParser(this.name, {
      dataPrefix: '_tables'
    });
    return {
      parser,
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
      return this.parser.showSearch;
    },
    hasFilters() {
      return this.filters.length > 0;
    }
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
      console.warn('setpage', page)
      if (page === this.currentPage) {
        return;
      }
      const itemsPerPage = this.itemsPerPage;
      const sortBy = this.parser.sort;
      this.loadItems({page, sortBy, itemsPerPage});
    },
    reload() {
      this.parser.reload();
    }
  },
  components: {
    ActionRow,
    SearchBar,
    Filters,
  }
}
</script>
