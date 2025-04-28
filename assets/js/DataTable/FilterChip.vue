<template>
    <v-chip v-bind="{
        pill: false,
        link: true,
        size: 'small',
        variant: 'outlined',
        class: 'ma-1',
        loading,
        //color: 'secondary',
    }" @click="onClick">
        <strong>{{ title }}:</strong> {{ value }}
    </v-chip>
</template>
<script>
export default {
    props: {
        modelValue: { type: Object, required: true, },
        disabled: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
    },
    data() {
        return {
            menu: false,
            val: this.modelValue?.value,
        }
    },
    computed: {
        title() {
            return this.modelValue?.label ?? this.modelValue?.name;
        },
        value() {
            return this.modelValue?.value;
        },
    },
    methods: {
        onClick() {
            this.$emit('click');
        },
    }
}
</script>
