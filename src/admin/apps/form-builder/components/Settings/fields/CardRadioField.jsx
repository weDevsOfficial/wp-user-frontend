import { RadioGroup, RadioGroupItem } from '@wedevs/plugin-ui';
import SettingLabel from './SettingLabel';

/**
 * Card radio setting (develop type="card-radio", registration form "User
 * Notification Type"): one bordered card per option with its label and
 * sub-label; stores the option key (4.5a, B15). The radio keeps plugin-ui's
 * default look (owner rule).
 */
export default function CardRadioField( { field, name, value, onChange } ) {
    const options = field.options || {};

    return (
        <>
            <SettingLabel field={ field } />
            <RadioGroup
                id={ name }
                value={ '' === value || undefined === value ? null : String( value ) }
                onValueChange={ ( next ) => onChange( name, next ) }
                className="flex flex-wrap gap-y-3"
            >
                { Object.keys( options ).map( ( key ) => (
                    <div key={ key } className="shadow-sm border border-gray-300 rounded-[6px] p-4 mr-5 max-w-[250px]">
                        <label htmlFor={ `${ name }-${ key }` } className="flex cursor-pointer">
                            <RadioGroupItem id={ `${ name }-${ key }` } value={ key } data-value={ key } className="mt-0 mr-2 shrink-0 cursor-pointer" />
                            <div className="leading-none text-sm text-gray-700">
                                { options[ key ].label }
                                { options[ key ].sub_label && (
                                    <span className="text-sm text-gray-500 block leading-5 mt-[5px]">{ options[ key ].sub_label }</span>
                                ) }
                            </div>
                        </label>
                    </div>
                ) ) }
            </RadioGroup>
        </>
    );
}
