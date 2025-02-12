<div class="wpuf-fields wpuf-min-w-full">
    <div
        v-if="!field.divider || field.divider === 'regular'"
        class="wpuf-section-wrap">
        <h2 class="wpuf-section-title">{{ field.label }}</h2>
        <div class="wpuf-section-details wpuf-text-sm wpuf-text-gray-500">{{ field.description }}</div>
        <div class="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-full"></div>
    </div>
    <div
        v-else-if="field.divider === 'dashed'"
        class="wpuf-section-wrap">
        <div class="wpuf-flex wpuf-items-center wpuf-justify-between">
            <div class="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-2/5"></div>
            <div class="wpuf-section-title wpuf-text-base text-gray-900 wpuf-px-3 wpuf-font-semibold">{{ field.label }}</div>
            <div class="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-2/5"></div>
        </div>
        <div class="wpuf-section-details wpuf-text-gray-400 wpuf-text-center wpuf-mt-2">{{ field.description }}</div>
    </div>
</div>
