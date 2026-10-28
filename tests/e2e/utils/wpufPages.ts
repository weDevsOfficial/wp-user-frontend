import { wpCli } from './wpEnvCli';

/**
 * Resolve a WPUF page by the shortcode it contains, not by its title or slug.
 *
 * Other active plugins ship pages with the same titles: dokan-lite installs its own
 * "Dashboard" with the slug `dashboard`, so WordPress slugs the other one
 * `dashboard-2`. Which plugin wins the plain slug depends on activation order, so
 * neither the title nor the slug is a safe handle, and `/dashboard/` may well belong
 * to Dokan (which redirects non-vendors away from it). The shortcode a page holds is
 * the stable identity of a WPUF page.
 *
 * Ordered by ID ascending so a site whose installer ran more than once (`dashboard`,
 * `dashboard-2`, `dashboard-3`) resolves to the canonical first page deterministically
 * rather than depending on wp-cli's default date sort.
 *
 * @param shortcode e.g. `[wpuf_dashboard]`
 * @returns the page slug, or null when no published page holds the shortcode.
 */
export function wpufPageSlug( shortcode: string ): string | null {
    try {
        const rows = JSON.parse(
            wpCli( 'post list --post_type=page --post_status=publish --fields=post_name,post_content --orderby=ID --order=ASC --format=json' )
        ) as Array<{ post_name: string; post_content: string }>;
        const page = rows.find( ( r ) => ( r.post_content || '' ).includes( shortcode ) );

        return page ? page.post_name : null;
    } catch {
        return null;
    }
}

/**
 * The frontend URL of a WPUF page identified by its shortcode, e.g.
 * `wpufPageUrl( baseUrl, '[wpuf_dashboard]' )`. Falls back to `fallbackSlug` when the
 * page cannot be resolved, so callers keep their previous behaviour.
 */
export function wpufPageUrl( baseUrl: string, shortcode: string, fallbackSlug: string ): string {
    return `${ baseUrl }/${ wpufPageSlug( shortcode ) ?? fallbackSlug }/`;
}
