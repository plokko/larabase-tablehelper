<template>
  <div>
    <template v-for="action in actions">
      <v-btn v-bind="{
                class: 'mr-1',
                prependIcon: action.icon,
                color: action.color,
                size: action.size ?? 'small',
                text: action.label,
                icon: !action.label && action.icon ? action.icon : null,
            }" @click="onAction(action)"/>
    </template>
  </div>
</template>

<script>
import {trans} from 'laravel-vue-i18n';
import {router} from '@inertiajs/vue3';

export default {
  props: {
    /// Value = header definition
    value: {type: Object, required: true,},
    header: {type: Object, required: true,},
  },
  computed: {
    actions() {
      return (this.header?.actions ?? []).map(action => Object.assign({}, action, {
        label: this.getActionLabel(action),
        icon: this.getActionIcon(action),
        color: this.getActionColor(action),
      }));
    },

    localizations() {
      return this.header?.actionsLocalization;
    },
  },
  methods: {
    getActionLabel(action) {
      if (action.label)
        return action.label;
      if (!this.localizations)
        return action.name;


      if (this.localizations[action.name])
        return this.localizations[action.name];


      const tr = trans(`${this.localizations}.${action.name}`);
      return tr;
      return (tr !== `${this.localizations}.${action.name}`) ? tr : null;
    },
    getActionColor(action) {
      if (action.color)
        return action.color;
      return {
        edit: 'warning',
        delete: 'error',
        show: 'info',
      }[action.name] ?? 'primary';
    },
    getActionIcon(action) {
      if (action.icon)
        return action.icon;
      return {
        edit: 'edit',
        delete: 'delete',
        show: 'visibility',
      }[action.name] ?? null;
    },
    onAction(action) {

      const url = action.route ? route(action.route, this.value) : action.url;
      console.log('onAction', url);
      if (url)
        router.visit(url);

    }
  }
}
</script>
