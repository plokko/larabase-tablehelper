<template>
  <v-text-field v-model="model" v-bind="{
        prependIcon: 'search',
        label: 'Ricerca',
        clearable: true,
        variant: 'outlined',
        density: 'comfortable',
        hideDetails: 'auto',
    }">
    <template v-if="hasOptions" v-slot:append-inner="slots">
      <v-menu v-bind="{
                closeOnContentClick: false,
                location: 'end',
            }">
        <template v-slot:activator="{ props }">
          <v-btn icon="filter_list" v-bind="props" variant="text"/>
        </template>
        <v-card min-width="300" prepend-icon="filter_list" title="Filtra per">
          <v-divider/>
          <v-list v-model:selected="selectedOptions" style="max-height: 200px;" v-bind="{
                        density: 'compact',
                        selectStrategy: 'leaf',
                        slim: true,
                    }">
            <template v-for="option in options">
              <v-list-item v-bind="{
                                title: option,
                                key: option,
                                value: option,
                            }">
                <template v-slot:prepend="{ isSelected }">
                  <v-list-item-action start>
                    <v-checkbox-btn :model-value="isSelected"/>
                  </v-list-item-action>
                </template>
              </v-list-item>
              <v-divider/>
            </template>
          </v-list>
        </v-card>
      </v-menu>
    </template>
  </v-text-field>
</template>

<script>
import _ from 'lodash';

export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: {required: false},
    search: {type: Object, required: true, default: () => ({})},
    debounce: {type: Number, default: 300},
  },
  data() {

    return {
      val: this.modelValue,
      selectedOptions: []
    };
  },
  computed: {
    options() {
      return this.search?.options
    },
    hasOptions() {
      return false
    },//TODO: !!this.options
    model: {
      get() {
        return this.val
      },
      set(v) {
        if (this.val !== v) {
          this.val = v;
          this.debounced();
        }
      }
    }
  },
  mounted() {
    this.updateDebounce();
  },
  methods: {
    updateDebounce() {
      // Updates debounce value
      this.debounced?.cancel();
      this.debounced = _.debounce(() => {
        this.$emit('update:modelValue', this.val);
      }, this.debounce);
    }
  },
  watch: {
    modelValue() {
      this.val = this.modelValue;
      this.updateDebounce();
    },
    debounce() {
      this.updateDebounce();
    },
  }
}
</script>
