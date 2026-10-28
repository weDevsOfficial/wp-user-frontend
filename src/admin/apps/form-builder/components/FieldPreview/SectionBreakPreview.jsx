export default function SectionBreakPreview( { field } ) {
    const divider = field.divider || 'regular';

    if ( divider === 'dashed' ) {
        return (
            <div className="wpuf-fields min-w-full">
                <div className="wpuf-section-wrap">
                    <div className="flex items-center justify-between">
                        <div className="border border-gray-200 h-0 w-2/5" />
                        <div className="wpuf-section-title text-base px-3 font-semibold">{ field.label }</div>
                        <div className="border border-gray-200 h-0 w-2/5" />
                    </div>
                    <div className="wpuf-section-details text-gray-400 text-center mt-2">{ field.description }</div>
                </div>
            </div>
        );
    }

    return (
        <div className="wpuf-fields min-w-full">
            <div className="wpuf-section-wrap">
                <h2 className="wpuf-section-title">{ field.label }</h2>
                <div className="wpuf-section-details text-sm text-gray-500">{ field.description }</div>
                <div className="border border-gray-200 h-0 w-full" />
            </div>
        </div>
    );
}
