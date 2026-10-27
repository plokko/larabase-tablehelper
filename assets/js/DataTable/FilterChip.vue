<script setup>
import {computed} from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const title = computed(
    () => props.modelValue?.label ?? props.modelValue?.name,
);

const value = computed(() => props.modelValue?.value);

const text = computed(() => {
    if (Array.isArray(value.value)) {
        return value.value.join(', ');
    }

    return value.value;
});

function onClick() {
    emit('click');
}
</script>
<template>
    <v-chip
        v-bind="{
            pill: false,
            link: true,
            size: 'small',
            variant: 'outlined',
            class: 'ma-1',
            loading,
            disabled,
        }"
        @click="onClick"
    >
        <strong class="mr-1">{{ title }}:</strong>
        {{ text }}
    </v-chip>
</template>
