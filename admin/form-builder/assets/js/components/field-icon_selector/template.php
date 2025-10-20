<div class="panel-field-opt panel-field-opt-icon-selector">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <div class="option-fields-section wpuf-relative">
        <div
            @click.stop="togglePicker"
            class="wpuf-w-full wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base wpuf-cursor-pointer"
        >
            <div class="wpuf-flex wpuf-items-center wpuf-gap-2">
                <i v-if="value" :class="value" class="wpuf-text-gray-600"></i>
                <span>{{ selectedIconDisplay }}</span>
            </div>
            <div class="wpuf-flex wpuf-items-center wpuf-gap-1">
                <i v-if="value" @click.stop="clearIcon" class="fa fa-times wpuf-text-gray-500 hover:wpuf-text-red-500 wpuf-cursor-pointer wpuf-p-1"></i>
                <i :class="showIconPicker ? 'fa-angle-up' : 'fa-angle-down'" class="fa wpuf-text-base"></i>
            </div>
        </div>

        <div
            v-if="showIconPicker"
            @click.stop
            class="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-50 wpuf-mt-1 wpuf-shadow-lg wpuf-right-0"
            style="max-height: 300px; min-width: 320px; max-width: 400px;"
        >
            <!-- Search -->
            <div class="wpuf-p-3 wpuf-border-b wpuf-border-gray-200">
                <input 
                    v-model="searchTerm"
                    type="text" 
                    placeholder="Search icons... (e.g., user, email, home)"
                    class="wpuf-w-full !wpuf-px-4 !wpuf-py-1.5 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-text-sm wpuf-text-gray-900 placeholder:wpuf-text-gray-400 wpuf-shadow focus:!wpuf-shadow-none"
                >
                <div class="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-1">
                    {{ filteredIcons.length }} icons {{ searchTerm ? 'found' : 'available' }}
                </div>
            </div>

            <!-- Icons Grid -->
            <div class="wpuf-icon-grid-container" style="max-height: 210px; overflow-y: auto; padding: 10px;">
                <!-- Icons Grid -->
                <div v-if="filteredIcons.length > 0" class="wpuf-icon-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                    <div
                        v-for="icon in filteredIcons"
                        :key="icon.class"
                        @click="selectIcon(icon.class)"
                        :class="['wpuf-icon-grid-item', { 'selected': value === icon.class }]"
                        :title="icon.name + ' - ' + icon.keywords"
                        style="padding: 10px 5px; text-align: center; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer; transition: all 0.2s; min-height: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center;"
                    >
                        <i :class="icon.class" style="font-size: 18px; margin-bottom: 4px; color: #555;"></i>
                        <div style="font-size: 10px; color: #666; line-height: 1.2; word-break: break-word; max-width: 100%;">{{ icon.name }}</div>
                    </div>
                </div>

                <!-- No Results -->
                <div v-else class="wpuf-text-center wpuf-py-8 wpuf-text-gray-500">
                    <div style="font-size: 16px; margin-bottom: 8px;">🔍 No icons found</div>
                    <div style="font-size: 12px;">Try searching with different keywords like "user", "email", "home"</div>
                </div>
            </div>
        </div>
    </div>
</div>