import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '../store';
import Header from './Header/Header';
import Sidebar from './Sidebar/Sidebar';
import BuilderCanvas from './Canvas/BuilderCanvas';
import FormSettings from './Settings/FormSettings';
import useDirtyState from '../hooks/useDirtyState';

export default function FormBuilder() {
    const { formType } = useSelect( ( select ) => {
        return {
            formType: select( STORE_NAME ).getFormType(),
        };
    }, [] );

    const [ activeTab, setActiveTab ] = useState( 'form-editor' );

    useDirtyState();

    return (
        <div className={ `wpuf-form-builder wpuf-form-builder-${ formType }` }>
            <Header activeTab={ activeTab } onTabChange={ setActiveTab } />

            { activeTab === 'form-editor' && (
                <div className="wpuf-flex wpuf-bg-white wpuf-mr-8">
                    <div className="wpuf-w-2/3 wpuf-min-h-screen wpuf-max-h-screen wpuf-px-13 wpuf-py-4 wpuf-border-t wpuf-border-l wpuf-border-gray-200 wpuf-overflow-auto">
                        <BuilderCanvas />
                    </div>
                    <div className="wpuf-w-1/3 wpuf-max-h-screen wpuf-overflow-auto wpuf-rounded-tr-lg wpuf-border wpuf-border-b-0 wpuf-border-gray-200">
                        <Sidebar />
                    </div>
                </div>
            ) }

            { activeTab === 'form-settings' && (
                <FormSettings />
            ) }
        </div>
    );
}
