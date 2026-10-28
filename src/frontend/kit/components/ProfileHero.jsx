/**
 * The member's profile hero, shared by the account page and the
 * `[wpuf_dashboard]` page: the cover band (the member's cover photo or the
 * theme band), the avatar over its edge, the name with @username and meta
 * pills, the actions on the right. Pages add their own pieces through the
 * slots (the account's photo controls, the dashboard's bio).
 *
 * @since WPUF_SINCE
 */
import { Avatar, cx } from './primitives';

/**
 * @param {Object} props
 * @param {Object} props.person         Profile (Account_Service::profile()): display_name, username, avatar, cover.
 * @param {*}      [props.avatar]       Avatar element (default: the kit Avatar).
 * @param {*}      [props.coverActions] Element over the cover (change / remove cover).
 * @param {*}      [props.meta]         Pills after the name.
 * @param {*}      [props.actions]      Right-hand actions.
 * @param {*}      [props.children]     Under the name row.
 * @param {string} [props.as]           Name heading tag.
 * @param {string} [props.className]
 */
export default function ProfileHero( { person, avatar, coverActions, meta, actions, children, as: Heading = 'h1', className = '' } ) {
    return (
        <section className={ cx( 'wpuf-profile-section wpuf-profile-header', className ) }>
            <div className={ cx( 'wpuf-profile-cover', person.cover?.url && 'has-image' ) } aria-hidden="true">
                { person.cover?.url && <img src={ person.cover.url } alt="" /> }
            </div>
            { coverActions }
            <div className="wpuf-profile-header__main">
                { avatar || <Avatar url={ person.avatar?.url } initials={ person.avatar?.initials } alt={ person.display_name } size={ 96 } className="wpuf-profile-avatar" /> }
                <div className="wpuf-profile-header__text">
                    <div className="wpuf-profile-header__name-row">
                        <Heading className="wpuf-profile-name">{ person.display_name }</Heading>
                        { /* A handle reads left to right in an RTL page too. */ }
                        { person.username && <span className="wpuf-profile-username" dir="ltr">@{ person.username }</span> }
                        { meta }
                    </div>
                    { children }
                </div>
                { actions && <div className="wpuf-profile-header__actions">{ actions }</div> }
            </div>
        </section>
    );
}
