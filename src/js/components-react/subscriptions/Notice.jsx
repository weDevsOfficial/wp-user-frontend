import { __ } from '@wordpress/i18n';

export default function Notice({ message, type, onRemove }) {
    return (
        <div className={`wpuf-notice wpuf-notice-${type}`}>
            <p>{message}</p>
            <button onClick={onRemove}>x</button>
        </div>
    );
}
