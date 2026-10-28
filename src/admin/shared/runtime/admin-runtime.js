/**
 * `admin-runtime` entry (handle wpuf-admin-runtime): the small shared layer
 * every WPUF React admin screen and Pro can use through `@wpuf/*` imports
 * (externals to window.wpuf.*). Published contract: adding is fine,
 * renaming or removing breaks add-ons.
 */
import { HOOKS, SLOTS, applyWpufFilters, doWpufAction } from '../filters';
import { parseJsonBody } from '../api/parse';
import { normalizeError, request, restPath } from '../api/request';
import { useBoot, useCan, useUnsavedGuard } from '../hooks';
import { wpufExtraTokens, wpufTokens } from '../theme';
import publish from './publish';

publish( {
    runtimeVersion: '1.0',
    filters: { HOOKS, SLOTS, applyWpufFilters, doWpufAction },
    api: { request, restPath, normalizeError, parseJsonBody },
    hooks: { useBoot, useCan, useUnsavedGuard },
    utilities: { tokens: wpufTokens, extraTokens: wpufExtraTokens },
} );
