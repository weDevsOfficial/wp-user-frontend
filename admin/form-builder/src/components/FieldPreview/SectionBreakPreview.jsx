export default function SectionBreakPreview( { field } ) {
    const divider = field.divider || 'regular';

    if ( divider === 'dashed' ) {
        return (
            <div className="wpuf-fields wpuf-min-w-full">
                <div className="wpuf-section-wrap">
                    <div className="wpuf-flex wpuf-items-center wpuf-justify-between">
                        <div className="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-2/5" />
                        <div className="wpuf-section-title wpuf-text-base text-gray-900 wpuf-px-3 wpuf-font-semibold">{ field.label }</div>
                        <div className="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-2/5" />
                    </div>
                    <div className="wpuf-section-details wpuf-text-gray-400 wpuf-text-center wpuf-mt-2">{ field.description }</div>
                </div>
            </div>
        );
    }

    return (
        <div className="wpuf-fields wpuf-min-w-full">
            <div className="wpuf-section-wrap">
                <h2 className="wpuf-section-title">{ field.label }</h2>
                <div className="wpuf-section-details wpuf-text-sm wpuf-text-gray-500">{ field.description }</div>
                <div className="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-full" />
            </div>
        </div>
    );
}
