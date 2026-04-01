// DESCRIPTION: Editor component for subscription packs block.
// Renders InspectorControls sidebar and ServerSideRender live preview.

import { InspectorControls, PanelColorSettings, useBlockProps } from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    SelectControl,
    TextControl,
    Placeholder,
    RangeControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import MultiSelect from './components/MultiSelect';

export default function Edit( { attributes, setAttributes } ) {
    const {
        include: includePacks,
        exclude: excludePacks,
        columns,
        order,
        orderby,
        showPrice,
        showFeatures,
        showDescription,
        buttonColor,
        buttonText,
        packBackgroundColor,
        packBorderColor,
        packBorderRadius,
        packPadding,
        packShadow,
        titleFontSize,
        priceFontSize,
        cardGap,
        recurringFontSize,
    } = attributes;

    const blockProps = useBlockProps();

    const packs = window.wpufSubscriptionPacks?.packs || [];
    const orderByOptions = window.wpufSubscriptionPacks?.orderByOptions || [];

    // If no packs exist, show a helpful message
    if ( packs.length === 0 ) {
        return (
            <div { ...blockProps }>
                <Placeholder
                    icon="cart"
                    label={ __( 'Subscription Packs', 'wp-user-frontend' ) }
                    instructions={ __( 'No subscription packs found. Create subscription packs in WP User Frontend → Subscription to display them here.', 'wp-user-frontend' ) }
                />
            </div>
        );
    }

    return (
        <div { ...blockProps }>
            <InspectorControls>
                <PanelBody
                    title={ __( 'Pack Selection', 'wp-user-frontend' ) }
                    initialOpen={ true }
                >
                    <div style={ { marginBottom: '16px' } }>
                        <label
                            className="components-base-control__label"
                            style={ { display: 'block', marginBottom: '8px' } }
                        >
                            { __( 'Include Packs', 'wp-user-frontend' ) }
                        </label>
                        <MultiSelect
                            options={ packs }
                            value={ includePacks }
                            onChange={ ( val ) => setAttributes( { include: val } ) }
                            placeholder={ __( 'All packs (default)', 'wp-user-frontend' ) }
                        />
                        <p className="components-base-control__help">
                            { __( 'Select specific packs to show. Leave empty to show all.', 'wp-user-frontend' ) }
                        </p>
                    </div>

                    <div style={ { marginBottom: '16px' } }>
                        <label
                            className="components-base-control__label"
                            style={ { display: 'block', marginBottom: '8px' } }
                        >
                            { __( 'Exclude Packs', 'wp-user-frontend' ) }
                        </label>
                        <MultiSelect
                            options={ packs }
                            value={ excludePacks }
                            onChange={ ( val ) => setAttributes( { exclude: val } ) }
                            placeholder={ __( 'None (default)', 'wp-user-frontend' ) }
                        />
                        <p className="components-base-control__help">
                            { __( 'Select packs to hide. Ignored when "Include" is set.', 'wp-user-frontend' ) }
                        </p>
                    </div>

                    <SelectControl
                        label={ __( 'Order By', 'wp-user-frontend' ) }
                        value={ orderby }
                        options={ orderByOptions }
                        onChange={ ( val ) => setAttributes( { orderby: val } ) }
                    />

                    <SelectControl
                        label={ __( 'Order', 'wp-user-frontend' ) }
                        value={ order }
                        options={ [
                            { label: __( 'Default', 'wp-user-frontend' ), value: '' },
                            { label: __( 'Ascending', 'wp-user-frontend' ), value: 'ASC' },
                            { label: __( 'Descending', 'wp-user-frontend' ), value: 'DESC' },
                        ] }
                        onChange={ ( val ) => setAttributes( { order: val } ) }
                    />

                    <TextControl
                        label={ __( 'Button Text', 'wp-user-frontend' ) }
                        value={ buttonText }
                        onChange={ ( val ) => setAttributes( { buttonText: val } ) }
                        placeholder={ __( 'Default (Buy Now / Sign Up / Free)', 'wp-user-frontend' ) }
                        help={ __( 'Override the default button label for all packs.', 'wp-user-frontend' ) }
                    />
                </PanelBody>

                <PanelBody
                    title={ __( 'Display Settings', 'wp-user-frontend' ) }
                    initialOpen={ false }
                >
                    <ToggleGroupControl
                        label={ __( 'Columns (Desktop)', 'wp-user-frontend' ) }
                        value={ columns }
                        onChange={ ( val ) => setAttributes( { columns: val } ) }
                        isBlock
                        help={ __( 'Mobile: 1 col → Tablet: 2 cols → Desktop: selected', 'wp-user-frontend' ) }
                    >
                        { [ 1, 2, 3, 4 ].map( ( num ) => (
                            <ToggleGroupControlOption
                                key={ num }
                                value={ num }
                                label={ String( num ) }
                            />
                        ) ) }
                    </ToggleGroupControl>

                    <RangeControl
                        label={ __( 'Gap Between Cards (px)', 'wp-user-frontend' ) }
                        value={ cardGap }
                        onChange={ ( val ) => setAttributes( { cardGap: val } ) }
                        min={ 0 }
                        max={ 64 }
                        step={ 4 }
                    />

                    <RangeControl
                        label={ __( 'Card Border Radius (px)', 'wp-user-frontend' ) }
                        value={ packBorderRadius }
                        onChange={ ( val ) => setAttributes( { packBorderRadius: val } ) }
                        min={ 0 }
                        max={ 48 }
                        step={ 2 }
                    />

                    <RangeControl
                        label={ __( 'Card Padding (px)', 'wp-user-frontend' ) }
                        value={ packPadding }
                        onChange={ ( val ) => setAttributes( { packPadding: val } ) }
                        min={ 0 }
                        max={ 64 }
                        step={ 4 }
                    />

                    <SelectControl
                        label={ __( 'Card Shadow', 'wp-user-frontend' ) }
                        value={ packShadow }
                        options={ [
                            { label: __( 'None', 'wp-user-frontend' ), value: 'none' },
                            { label: __( 'Small', 'wp-user-frontend' ), value: 'sm' },
                            { label: __( 'Medium', 'wp-user-frontend' ), value: 'md' },
                            { label: __( 'Large', 'wp-user-frontend' ), value: 'lg' },
                        ] }
                        onChange={ ( val ) => setAttributes( { packShadow: val } ) }
                    />

                    <RangeControl
                        label={ __( 'Title Font Size (px)', 'wp-user-frontend' ) }
                        value={ titleFontSize }
                        onChange={ ( val ) => setAttributes( { titleFontSize: val } ) }
                        min={ 12 }
                        max={ 48 }
                        step={ 1 }
                    />

                    <RangeControl
                        label={ __( 'Price Font Size (px)', 'wp-user-frontend' ) }
                        value={ priceFontSize }
                        onChange={ ( val ) => setAttributes( { priceFontSize: val } ) }
                        min={ 16 }
                        max={ 72 }
                        step={ 1 }
                    />

                    <RangeControl
                        label={ __( 'Recurring Info Font Size (px)', 'wp-user-frontend' ) }
                        value={ recurringFontSize }
                        onChange={ ( val ) => setAttributes( { recurringFontSize: val } ) }
                        min={ 10 }
                        max={ 32 }
                        step={ 1 }
                    />

                    <ToggleControl
                        label={ __( 'Show Price', 'wp-user-frontend' ) }
                        checked={ showPrice }
                        onChange={ ( val ) => setAttributes( { showPrice: val } ) }
                    />

                    <ToggleControl
                        label={ __( 'Show Features', 'wp-user-frontend' ) }
                        checked={ showFeatures }
                        onChange={ ( val ) => setAttributes( { showFeatures: val } ) }
                    />

                    <ToggleControl
                        label={ __( 'Show Description', 'wp-user-frontend' ) }
                        checked={ showDescription }
                        onChange={ ( val ) => setAttributes( { showDescription: val } ) }
                    />
                </PanelBody>

                <PanelColorSettings
                    title={ __( 'Color', 'wp-user-frontend' ) }
                    initialOpen={ false }
                    colorSettings={ [
                        {
                            value: buttonColor,
                            onChange: ( val ) => setAttributes( { buttonColor: val || '' } ),
                            label: __( 'Button Color', 'wp-user-frontend' ),
                        },
                        {
                            value: packBackgroundColor,
                            onChange: ( val ) => setAttributes( { packBackgroundColor: val || '#ffffff' } ),
                            label: __( 'Card Background', 'wp-user-frontend' ),
                        },
                        {
                            value: packBorderColor,
                            onChange: ( val ) => setAttributes( { packBorderColor: val || '#e5e7eb' } ),
                            label: __( 'Card Border', 'wp-user-frontend' ),
                        },
                    ] }
                />
            </InspectorControls>

            <ServerSideRender
                block="wpuf/subscription-packs"
                attributes={ attributes }
            />
        </div>
    );
}
