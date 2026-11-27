/**
 * Builder URLs: the builder page (`admin.php?page=wpuf-post-forms&action=edit&id=N`)
 * or, with the single admin app, its route (`admin.php?page=wp-user-frontend#/post-forms/N/edit`,
 * `#/registration-forms/N/edit`). Old builder URLs redirect to the route.
 */

/** Matches a builder URL of either shape. */
export const BUILDER_URL = /action=edit&id=\d+|#\/(post|registration)-forms\/\d+\/edit/;

/** Form ID of a builder URL of either shape ('' when none). */
export function builderFormId(url: string): string {
    const parsed = new URL(url);

    return parsed.searchParams.get('id') || (parsed.hash.match(/^#\/(?:post|registration)-forms\/(\d+)\/edit/) || [])[1] || '';
}
