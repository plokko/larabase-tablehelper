<script setup>
import {computed} from 'vue';
import {trans} from 'laravel-vue-i18n';
import {router} from '@inertiajs/vue3';

const props = defineProps({
    value: {
        type: Object,
        required: true,
    },
    header: {
        type: Object,
        required: true,
    },
    location: {
        type: String,
        default: 'bottom',
    },
});

const localizations = computed(
    () => props.header?.actionsLocalization,
);

const actions = computed(() =>
    (props.header?.actions ?? []).map((action) => ({
        label: getActionLabel(action),
        icon: getActionIcon(action),
        color: getActionColor(action),
        ...action,
    })),
);

const actionBindings = computed(() =>
    (props.header?.actions ?? []).map((action) => {
        const fallback = {
            label: getActionLabel(action),
            icon: getActionIcon(action),
            color: getActionColor(action),
        };

        const isIconButton =
            action.style === 'icon' ||
            (!action.label && fallback.icon);

        const bind = isIconButton
            ? {
                class: 'ma-1',
                icon: action.icon ?? fallback.icon,
                density: action.density ?? 'comfortable',
                variant: action.variant ?? 'text',
                color: fallback.color,
                text: fallback.label,
                size: action.size ?? 'small',
            }
            : {
                class: 'mr-1 mb-1',
                prependIcon: action.icon ?? fallback.icon,
                text: fallback.label,
                color: fallback.color,
                size: action.size ?? 'small',
                density: action.density,
                variant: action.variant ?? 'tonal',
            };

        const tooltip = fallback.label
            ? {
                text: action.label ?? action.name,
            }
            : null;

        return {
            isIconButton,
            tooltip,
            action,
            bind,
        };
    }),
);

function getActionLabel(action) {
    if (action.label) {
        return action.label;
    }

    if (!localizations.value) {
        return null;
    }

    const localization = localizations.value;

    if (
        typeof localization === 'object' &&
        localization[action.name]
    ) {
        return localization[action.name];
    }

    const key = `${localization}.${action.name}`;
    const translated = trans(key);

    return translated !== key ? translated : null;
}

function getActionColor(action) {
    if (action.color) {
        return action.color;
    }

    return {
        edit: 'warning',
        destroy: 'error',
        show: 'info',
    }[action.name] ?? 'primary';
}

function getActionIcon(action) {
    if (action.icon) {
        return action.icon;
    }

    return {
        edit: 'edit',
        destroy: 'delete',
        show: 'visibility',
    }[action.name] ?? null;
}

function onAction(action) {
    const url = getActionUrl(action);
    const method = action.method ?? 'get';

    if (action.confirm && !window.confirm(action.confirm)) {
        return;
    }

    if (url) {
        router.visit(url, {method});
    }
}

function getActionUrl(action) {
    if (!action.route) {
        return action.link;
    }

    let data = {...action};

    if (action.routeBindings) {
        let bindings = action.routeBindings;

        if (typeof bindings === 'string') {
            bindings = bindings.split(',').map((key) => key.trim());
        }

        if (Array.isArray(bindings)) {
            data = bindings.map((key) => props.value[key]);
        } else {
            data = {};

            for (const [key, mapTo] of Object.entries(bindings)) {
                data[key] = props.value[mapTo];
            }
        }
    }

    return route(action.route, data);
}
</script>
<template>
    <div>
        <!-- Grouped actions -->
        <v-menu
            v-if="header.group"
            :location="location"
        >
            <template #activator="{ props: activatorProps }">
                <v-btn
                    density="comfortable"
                    icon="more_vert"
                    size="small"
                    v-bind="activatorProps"
                    variant="text"
                />
            </template>

            <v-list>
                <template
                    v-for="(action, i) in actions"
                    :key="action.name ?? i"
                >
                    <v-divider v-if="i > 0"/>

                    <v-list-item
                        :title="action.label ?? action.name"
                        :base-color="action.color"
                        :prepend-icon="action.icon ?? action.prependIcon"
                        @click="onAction(action)"
                    />
                </template>
            </v-list>
        </v-menu>

        <!-- Individual actions -->
        <template v-else>
            <template
                v-for="{ bind, tooltip, isIconButton, action } in actionBindings"
                :key="action.name"
            >
                <v-tooltip
                    v-if="isIconButton && tooltip"
                    v-bind="tooltip"
                >
                    <template #activator="{ props: tooltipProps }">
                        <v-btn
                            v-bind="{ ...tooltipProps, ...bind }"
                            @click="onAction(action)"
                        />
                    </template>
                </v-tooltip>

                <v-btn
                    v-else
                    v-bind="bind"
                    @click="onAction(action)"
                />
            </template>
        </template>
    </div>
</template>
