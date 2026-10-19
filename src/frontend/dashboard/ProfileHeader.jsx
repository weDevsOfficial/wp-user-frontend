/**
 * The dashboard header: the kit's ProfileHero (the account page's hero:
 * cover band, avatar over its edge, name, @username, meta pills) with the
 * dashboard's facts (member since, posts, plan), the bio, and the quick
 * actions (Submit new post, Edit profile, My account) beside the role.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Pill, ProfileHero, icons } from '@wpuf/frontend-kit';

const { BadgeDollarSign, CalendarDays, FileText, Mail, Plus } = icons;

/**
 * @param {Object}  props
 * @param {Object}  props.profile   Profile (Dashboard_Overview::profile()).
 * @param {Array}   props.actions   Quick actions.
 * @param {number}  props.total     Posts in the list.
 * @param {string}  props.labels    Post type labels.
 * @param {boolean} props.showCount Show the post count pill.
 */
export default function ProfileHeader( { profile, actions = [], total, labels, showCount } ) {
    const meta = (
        <>
            { profile.member_since && (
                <span className="wpuf-meta-pill">
                    <CalendarDays size={ 14 } aria-hidden="true" />
                    { /* translators: %s: date */ }
                    { sprintf( __( 'Member since %s', 'wp-user-frontend' ), profile.member_since ) }
                </span>
            ) }
            { profile.email && (
                <span className="wpuf-meta-pill">
                    <Mail size={ 14 } aria-hidden="true" /> { profile.email }
                </span>
            ) }
            { showCount && labels && (
                <a className="wpuf-meta-pill post_count" href={ profile.author_url }>
                    <FileText size={ 14 } aria-hidden="true" />
                    { sprintf( /* translators: 1: number of posts, 2: post type labels */ __( '%1$d %2$s', 'wp-user-frontend' ), total, labels ) }
                </a>
            ) }
            { profile.plan && (
                <span className="wpuf-meta-pill">
                    <BadgeDollarSign size={ 14 } aria-hidden="true" /> { profile.plan }
                </span>
            ) }
        </>
    );

    return (
        <div className="wpuf-dashboard-hero">
            <ProfileHero
                person={ profile }
                as="h2"
                meta={ meta }
                actions={
                    <>
                        { profile.role_label && <Pill tone="green" className="wpuf-profile-role">{ profile.role_label }</Pill> }
                        { actions.map( ( action ) => (
                            <Button key={ action.key } href={ action.url } variant={ action.primary ? 'primary' : 'secondary' }>
                                { 'new_post' === action.key && <Plus size={ 14 } aria-hidden="true" /> }
                                { action.label }
                            </Button>
                        ) ) }
                    </>
                }
            >
                { profile.bio && <p className="wpuf-dashboard-hero__bio">{ profile.bio }</p> }
            </ProfileHero>
        </div>
    );
}
