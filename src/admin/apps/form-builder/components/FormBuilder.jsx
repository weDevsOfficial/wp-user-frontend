import { useState, useEffect } from '@wordpress/element';
import { dispatch, select, useSelect } from '@wordpress/data';
import { STORE_NAME } from '../store';
import Header from './Header/Header';
import Sidebar from './Sidebar/Sidebar';
import BuilderCanvas from './Canvas/BuilderCanvas';
import BuilderDnd from './Dnd/BuilderDnd';
import FormSettings from './Settings/FormSettings';
import useDirtyState from '../hooks/useDirtyState';
import { PageFooter, PageShell } from '@wpuf/components';
import LegacySlot, { getLegacySlots } from '../common/LegacySlot';
import { foldMenu, unfoldMenu } from '../skeleton';
import { getRouteQuery, setRouteQuery } from '../../../app/client';

export default function FormBuilder() {
    const { formType } = useSelect( ( select ) => {
        return {
            formType: select( STORE_NAME ).getFormType(),
        };
    }, [] );

    // The open tab lives in the URL (`?tab=settings`), so a reload or a shared
    // link opens the same tab.
    const [ activeTab, setActiveTab ] = useState( () => ( 'settings' === getRouteQuery().tab ? 'form-settings' : 'form-editor' ) );
    const changeTab = ( tab ) => {
        setActiveTab( tab );
        setRouteQuery( 'form-settings' === tab ? { tab: 'settings' } : { tab: null, section: null } );
    };

    useDirtyState();

    // Fields that are not React state (legacy PHP slots: Pro module and
    // integration settings) mark the form dirty when the user edits them, so
    // Save (enabled only with changes) stays available for them.
    useEffect( () => {
        const onEdit = ( event ) => {
            if ( ! event.isTrusted || ! event.target.closest || ! event.target.closest( '#wpuf-form-builder [data-wpuf-legacy-slot]' ) ) {
                return;
            }

            if ( ! select( STORE_NAME ).getIsDirty() ) {
                dispatch( STORE_NAME ).markDirty();
            }
        };

        document.addEventListener( 'input', onEdit, true );
        document.addEventListener( 'change', onEdit, true );

        return () => {
            document.removeEventListener( 'input', onEdit, true );
            document.removeEventListener( 'change', onEdit, true );
        };
    }, [] );

    // The WordPress menu folds to its icon rail while the builder is open, so the
    // canvas gets the room (FlyForms builder layout); it opens again on leave
    // unless the user had it folded already.
    useEffect( () => {
        foldMenu();

        return unfoldMenu;
    }, [] );

    return (
        <PageShell className={ `wpuf-form-builder wpuf-form-builder-${ formType }` }>
            <Header activeTab={ activeTab } onTabChange={ changeTab } />

            { activeTab === 'form-editor' && (
                // One drag-and-drop context for the palette and the canvas (D16).
                <BuilderDnd>
                    { /* White card on the gray page (D26 page look, owner 2026-10-04). Same
                         width as before (mx-4 = the old mr-8) and a ring, not a border, so the
                         stage keeps develop's sizes; the inner edge borders stay as transparent. */ }
                    <div className="relative flex h-[calc(100vh-132px)] min-h-[480px] bg-white mx-4 mt-3 mb-4 rounded-lg ring-1 ring-gray-200 overflow-hidden">
                        { /* Three columns (FlyForms builder): field palette, canvas, field options.
                             Each scroller is positioned, so the visually hidden inputs of plugin-ui's
                             radios / checkboxes (position: absolute) stay inside it; unpositioned,
                             they anchored to the page and stretched its scroll (owner 2026-10-14). */ }
                        <div className="relative w-[280px] xl:w-[300px] 2xl:w-[320px] shrink-0 h-full overflow-auto border-r border-gray-200 bg-white">
                            <Sidebar panel="fields" />
                        </div>
                        <div className="relative min-w-0 flex-1 h-full px-6 py-5 overflow-auto">
                            <BuilderCanvas />
                        </div>
                        <div className="relative w-[300px] 2xl:w-[360px] shrink-0 h-full overflow-auto border-l border-gray-200">
                            <Sidebar panel="options" />
                        </div>
                    </div>
                </BuilderDnd>
            ) }

            { activeTab === 'form-settings' && (
                <FormSettings />
            ) }

            { /* Tab contents other plugins printed on wpuf-form-builder-tab-contents-{type}. */ }
            <LegacySlot id="tab-contents" html={ getLegacySlots().tabs.contents } className="m-4" />

            { /* FlyHR logo footer, as the other shared-layer screens (D26); px-5 = the
                 20px its full-bleed band reaches out by. */ }
            { /* The editor fills the window (FlyForms builder); the footer stays on Settings. */ }
            { activeTab !== 'form-editor' && <PageFooter className="px-5" /> }
        </PageShell>
    );
}
