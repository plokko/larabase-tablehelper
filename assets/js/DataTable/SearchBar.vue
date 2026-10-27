<script setup>
import {onBeforeUnmount, ref, watch, computed} from 'vue';
import debounceFn from 'lodash/debounce';

const props = defineProps({
    label: {default: 'Ricerca'},
    prependIcon: {default: 'search'},
    variant: {default: 'underlined'},
    modelValue: {required: false},
    loading: {type: Boolean, default: false},
    search: {type: Object, required: true, default: () => ({})},
    debounce: {type: Number, default: 500},
});

const emit = defineEmits(['update:modelValue']);

const val = ref(props.modelValue);
const selectedOptions = ref([]);

const options = computed(() => props.search?.options ?? []);
const hasOptions = computed(() => options.value.length > 0);

function emitValue() {
    emit('update:modelValue', val.value);
}

let debouncedEmit = debounceFn(emitValue, props.debounce);

const model = computed({
    get: () => val.value,
    set: (value) => {
        if (val.value === value) return;
        val.value = value;
        debouncedEmit();
    },
});

function sendImmediate() {
    debouncedEmit.cancel();
    emitValue();
}

watch(
    () => props.modelValue,
    (value) => {
        if (val.value !== value && !debouncedEmit.pending?.()) {
            val.value = value;
        }
    },
);

watch(
    () => props.debounce,
    (value) => {
        debouncedEmit.cancel();
        debouncedEmit = debounceFn(emitValue, value);
    },
);

onBeforeUnmount(() => {
    debouncedEmit.cancel();
});
</script>
<template>
    <v-text-field
        v-model="model"
        v-bind="{
            prependIcon ,
            label,
            clearable: true,
            variant,
            hideDetails: 'auto',
            singleLine: true,
            loading,
            density: 'compact'
        }"
    >
        <template v-if="hasOptions" #append-inner>
            <v-menu
                v-bind="{
                    closeOnContentClick: false,
                    location: 'end',
                }"
            >
                <template #activator="{ props: activatorProps }">
                    <v-btn
                        v-bind="{
                            icon: 'filter_list',
                            variant: 'text',
                            ...activatorProps,
                        }"
                    />
                </template>

                <v-card
                    min-width="300"
                    prepend-icon="filter_list"
                    title="Filtra per"
                >
                    <v-divider/>

                    <v-list
                        v-model:selected="selectedOptions"
                        v-bind="{
                            density: 'compact',
                            selectStrategy: 'leaf',
                            slim: true,
                        }"
                        style="max-height: 200px; overflow-y: auto"
                    >
                        <template
                            v-for="option in options"
                            :key="option"
                        >
                            <v-list-item
                                :title="option"
                                :value="option"
                            >
                                <template #prepend="{ isSelected }">
                                    <v-list-item-action start>
                                        <v-checkbox-btn
                                            :model-value="isSelected"
                                        />
                                    </v-list-item-action>
                                </template>
                            </v-list-item>

                            <v-divider/>
                        </template>
                    </v-list>
                </v-card>
            </v-menu>
        </template>
        <template #append>
            <v-btn
                v-bind="{
                    icon: 'send',
                    variant: 'text',
                    density: 'compact',
                    color: 'primary',
                }"
                @click="sendImmediate"
            />
        </template>
    </v-text-field>
</template>
