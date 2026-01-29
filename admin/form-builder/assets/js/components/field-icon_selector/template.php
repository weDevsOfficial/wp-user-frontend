<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-icon-selector">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <div class="option-fields-section wpuf-relative">
        <div
            @click.stop="togglePicker"
            class="wpuf-w-full wpuf-mt-4 wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base wpuf-cursor-pointer"
        >
            <div class="wpuf-flex wpuf-items-center wpuf-gap-2">
                <img v-if="isImageValue" :src="value" style="width: 20px; height: 20px; object-fit: cover; border-radius: 2px;" />
                <i v-else-if="value" :class="value" class="wpuf-text-gray-600"></i>
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
            style="max-height: 350px; min-width: 320px; max-width: 400px;"
        >
            <!-- Tabs -->
            <div class="wpuf-flex wpuf-border-b wpuf-border-gray-200">
                <button
                    type="button"
                    @click.stop="switchTab('icon')"
                    :class="['wpuf-flex-1 wpuf-py-2 wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-border-b-2 wpuf-transition-colors', activeTab === 'icon' ? 'wpuf-border-blue-500 wpuf-text-blue-600' : 'wpuf-border-transparent wpuf-text-gray-500 hover:wpuf-text-gray-700']"
                >
                    <i class="fa fa-icons wpuf-mr-1"></i> <?php esc_html_e( 'Icons', 'wp-user-frontend' ); ?>
                </button>
                <button
                    type="button"
                    @click.stop="switchTab('image')"
                    :class="['wpuf-flex-1 wpuf-py-2 wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-border-b-2 wpuf-transition-colors', activeTab === 'image' ? 'wpuf-border-blue-500 wpuf-text-blue-600' : 'wpuf-border-transparent wpuf-text-gray-500 hover:wpuf-text-gray-700']"
                >
                    <i class="fa fa-image wpuf-mr-1"></i> <?php esc_html_e( 'Upload Image', 'wp-user-frontend' ); ?>
                </button>
            </div>

            <!-- Icon Tab Content -->
            <div v-if="activeTab === 'icon'">
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
                        <div style="font-size: 16px; margin-bottom: 8px;">No icons found</div>
                        <div style="font-size: 12px;">Try searching with different keywords like "user", "email", "home"</div>
                    </div>
                </div>
            </div>

            <!-- Image Tab Content -->
            <div v-if="activeTab === 'image'" class="wpuf-p-4">
                <div class="wpuf-text-center">
                    <!-- Image Preview -->
                    <div v-if="isImageValue" class="wpuf-mb-4">
                        <img :src="value" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0; margin: 0 auto;" />
                        <div class="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-2"><?php esc_html_e( 'Current custom image', 'wp-user-frontend' ); ?></div>
                    </div>

                    <!-- Upload Button -->
                    <button
                        type="button"
                        @click.stop="openMediaUploader"
                        class="wpuf-inline-flex wpuf-items-center wpuf-gap-2 wpuf-px-4 wpuf-py-2 wpuf-bg-blue-500 wpuf-text-white wpuf-rounded wpuf-text-sm wpuf-font-medium hover:wpuf-bg-blue-600 wpuf-transition-colors"
                    >
                        <i class="fa fa-upload"></i>
                        <?php esc_html_e( 'Upload an image to use as icon', 'wp-user-frontend' ); ?>
                    </button>

                    <p class="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-3">
                        <?php esc_html_e( 'Recommended size: 32x32 pixels', 'wp-user-frontend' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
