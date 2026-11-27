/**
 * The builder marks the form clean a moment after a save (useFormSave): the
 * save's own store updates land first. Code that asks "unsaved changes?"
 * right after a save waits for that.
 *
 * @since WPUF_SINCE
 */
let pendingClean = Promise.resolve();

/**
 * Remember the save's pending "mark clean".
 *
 * @param {Promise} promise Settles once the form is marked clean.
 */
export const setPendingClean = ( promise ) => {
    pendingClean = promise;
};

/**
 * @return {Promise} Settles once a pending "mark clean" ran.
 */
export const waitForPendingClean = () => pendingClean;
