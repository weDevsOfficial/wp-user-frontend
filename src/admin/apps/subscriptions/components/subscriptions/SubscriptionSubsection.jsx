/**
 * DESCRIPTION: SubscriptionSubsection component for collapsible form sections
 * DESCRIPTION: Renders a collapsible section with fields inside
 */
import { Accordion, Notice, ProBadge } from '@wpuf/components';
import { CalendarClock, CreditCard, Eye, FileText, Gauge, Layers, Palette, Settings2, Tags } from 'lucide-react';
import SubscriptionField from './SubscriptionField';
import ProTooltip from './ProTooltip';
import { SubscriptionAfterSubsection } from '../../slots';
import { subscriptionsData } from '../../boot';

// develop: the first subsection of each tab starts open.
const OPEN_BY_DEFAULT = [ 'overview', 'content_limit', 'payment_details' ];

// Section icon tiles (modern card look); unknown sections get the generic one.
const ICONS = {
	overview: FileText,
	access_and_visibility: Eye,
	post_expiration: CalendarClock,
	content_limit: Gauge,
	taxonomy_restriction: Tags,
	payment_details: CreditCard,
	design_elements: Palette,
	additional: Layers,
};

const SubscriptionSubsection = ( { subSection, fields, subscription, onFieldChange } ) => {
	const wpufSubscriptions = subscriptionsData();

	return (
		<Accordion
			variant="card"
			title={ subSection.label }
			description={ subSection.sub_label }
			icon={ ( () => {
				const Icon = ICONS[ subSection.id ] || Settings2;
				return <Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />;
			} )() }
			badge={
				// develop always renders this span (8px gap even without a badge).
				<span className="pro-icon-title relative group ml-2">
					{ subSection.is_pro && ! wpufSubscriptions.isProActive && (
						<>
							<ProBadge link={ false } />
							<ProTooltip />
						</>
					) }
				</span>
			}
			defaultOpen={ OPEN_BY_DEFAULT.includes( subSection.id ) }
		>
			{ Object.entries( fields || {} ).map( ( [ fieldKey, field ] ) => (
				<SubscriptionField
					key={ fieldKey }
					field={ field }
					fieldId={ fieldKey }
					subscription={ subscription }
					onFieldChange={ onFieldChange }
				/>
			) ) }

			{/* Extension slot: Pro and third-party plugins can add UI after subsection fields */}
			<SubscriptionAfterSubsection.Slot
				fillProps={ { subSection, subscription, onFieldChange } }
			/>

			{/* Notice (server-defined text from the subsection definition) */}
			{ subSection.notice && (
				<Notice tone="warning" className="mt-4">
					<p className="m-0" dangerouslySetInnerHTML={ { __html: subSection.notice.message } } />
				</Notice>
			) }
		</Accordion>
	);
};

export default SubscriptionSubsection;
