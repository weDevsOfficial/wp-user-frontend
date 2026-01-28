/**
 * Utility function to get optimal avatar size based on directory layout
 * 
 * @param {string} layout - The directory layout (layout-1, layout-2, etc.)
 * @returns {string} - The optimal avatar size in pixels
 */
export const getOptimalAvatarSize = (layout) => {
    const sizeMap = {
        'layout-1': '48',   // Table layout - smaller avatars
        'layout-2': '128',  // Grid layout - medium avatars
        'layout-3': '128',  // Card layout - medium avatars
        'layout-4': '192',  // Large card layout - larger avatars
        'layout-5': '128',  // Grid layout - medium avatars
        'layout-6': '265'   // Grid layout - medium avatars
    };
    return sizeMap[layout] || '48'; // Default to 48 if layout not found
};