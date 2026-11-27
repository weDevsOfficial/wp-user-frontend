/**
 * Chat intent detection of the AI form builder, ported unchanged from
 * develop's FormSuccessStage.vue: decides whether a chat message needs the
 * generate API (a change request) or gets a canned answer (a question,
 * greeting or off-topic message).
 *
 * @since WPUF_SINCE
 */

const PREDEFINED_PATTERNS = [
    'paid guest post',
    'guest post',
    'portfolio',
    'classified ad',
    'classified',
    'coupon',
    'real estate',
    'property listing',
    'property',
    'news',
    'press release',
    'product listing',
    'product',
];

const ACTION_VERBS = [
    'add', 'remove', 'delete', 'change', 'modify', 'update', 'edit',
    'replace', 'insert', 'create', 'enable', 'disable', 'toggle',
    'rename', 'reorder', 'adjust', 'configure', 'customize',
];

const IMPERATIVE_PATTERNS = [
    /\bmake\s+(\w+\s+)?(\w+\s+)?(field|fields|form|all|everything|it|them|this|that|these|those)/i,
    /\bset\s+(\w+\s+)?(field|fields|all)\s+(to|as)/i,
    /\bturn\s+(\w+\s+)?(field|fields)\s+(into|to)/i,
    /\bconvert\s+(\w+\s+)?(field|fields)\s+(to|into)/i,
];

const REQUEST_PATTERNS = [
    /\b(can|could|would|will)\s+you\s+(add|remove|change|modify|update|make|set|create|delete)/i,
    /\bplease\s+(add|remove|change|modify|update|make|set|create|delete)/i,
    /\bi\s+(want|need|would like)\s+to\s+(add|remove|change|modify|update|make|set|create|delete)/i,
    /\bi\s+(want|need|would like)\s+(a|an|the|some|more|another|new)\s+(field|option|column)/i,
];

const MODIFICATION_INTENT_PATTERNS = [
    /\b(make|set|change|turn|convert)\s+.*(required|optional|mandatory|mendatory|hidden|visible)/i,
    /\b(required|optional|mandatory|mendatory)\s+(field|fields|all)/i,
    /\ball\s+(field|fields).*\b(required|optional|mandatory|mendatory)/i,
];

const REPLACEMENT_PATTERNS = [
    /\binstead\s+of/i,
    /\brather\s+than/i,
    /\bwithout\s+(the|a|an)\s+(field|option)/i,
    /\breplace\s+.+\s+with/i,
    /\bswap\s+.+\s+(for|with)/i,
];

const QUANTIFIER_PATTERNS = [
    /\b(new|more|another|additional|extra|different|custom)\s+(field|option|choice|column|section)/i,
    /\b(fewer|less|no)\s+(field|fields|option|options)/i,
    /\bwithout\s+(the|a|an)\s+(field|option)/i,
];

const QUESTION_PATTERNS = [
    /^(what|when|where|why|who|whom|whose|which|how)\s+/i,
    /\b(what|when|where|why|who|whom|whose|which|how)\s+(is|are|was|were|do|does|did|can|could|will|would|should)\b/i,
    /^(is|are|was|were|do|does|did|can|could|will|would|should|may|might|must|shall)\s+/i,
    /\b(meaning|purpose|reason|explanation|definition)\s+(of|for|behind)?\b/i,
    /\b(explain|describe|tell|show|clarify|define)\s+(me|us|about|what|how|why)?\b/i,
    /\b(need to know|want to know|wondering|curious|question about)\b/i,
    /\b(any idea|do you know|can you tell|could you explain)\b/i,
    /\b(what does .+ mean|what is .+ for|why .+ needed)\b/i,
    /\b(purpose of|use of|reason for|point of)\b/i,
    /\b(information|details|info|help me understand)\b/i,
];

const INFORMAL_QUERIES = [
    'meaning', 'purpose', 'explain', 'tell me', 'show me',
    'help me understand', 'clarify', 'describe',
    'is this', 'is that', 'are these', 'are those',
    'do i need', 'should i', 'must i', 'can i',
    'what about', 'how about', 'and the',
];

const IRRELEVANT_PATTERNS = [
    /^(tell me a joke|sing a song|write a poem|tell a story)/i,
    /\b(weather|news|sports|movie|music|recipe|game)\b/i,
    /\b(math|calculate|solve|equation)\b/i,
    /\b(translate|language|french|spanish|german)\b/i,
];

const FORM_KEYWORDS = [ 'form', 'field', 'input', 'submit', 'add', 'remove', 'modify', 'change', 'update', 'portfolio', 'email', 'required' ];

/**
 * Whether a prompt matches one of the predefined template topics.
 *
 * @param {string} prompt Prompt.
 *
 * @return {boolean} Predefined.
 */
export function isPredefinedPrompt( prompt ) {
    const lower = String( prompt || '' ).toLowerCase();

    return PREDEFINED_PATTERNS.some( ( pattern ) => lower.includes( pattern ) );
}

/**
 * Whether a chat message asks to change the form.
 *
 * @param {string} message Message.
 *
 * @return {boolean} Change request.
 */
export function isModificationRequest( message ) {
    if ( ! message || 'string' !== typeof message ) {
        return false;
    }

    const lower = message.toLowerCase().trim();
    const isLikelyQuestion = message.includes( '?' ) || /^(what|when|where|why|who|how|is|are|do|does|can)\s+/i.test( lower );
    const hasActionVerb = ! isLikelyQuestion && ACTION_VERBS.some( ( verb ) => new RegExp( `\\b${ verb }\\b`, 'i' ).test( lower ) );
    const hasImperativePattern = IMPERATIVE_PATTERNS.some( ( pattern ) => pattern.test( message ) );
    const hasRequestPattern = REQUEST_PATTERNS.some( ( pattern ) => pattern.test( message ) );
    const hasModificationIntent = MODIFICATION_INTENT_PATTERNS.some( ( pattern ) => pattern.test( message ) );
    const hasReplacementPattern = REPLACEMENT_PATTERNS.some( ( pattern ) => pattern.test( message ) );
    const hasQuantifierPattern = QUANTIFIER_PATTERNS.some( ( pattern ) => pattern.test( message ) );
    const mentionsField = /\b(field|fields)\b/i.test( message );

    return hasImperativePattern || hasRequestPattern || hasModificationIntent ||
        ( hasActionVerb && mentionsField ) ||
        ( hasActionVerb && ( hasReplacementPattern || hasQuantifierPattern ) ) ||
        hasReplacementPattern || hasQuantifierPattern;
}

/**
 * Whether a chat message is a question about the form.
 *
 * @param {string} message Message.
 *
 * @return {boolean} Question.
 */
export function isInformationalQuery( message ) {
    if ( ! message || 'string' !== typeof message ) {
        return false;
    }

    if ( isModificationRequest( message ) ) {
        return false;
    }

    const lower = message.toLowerCase().trim();

    return message.includes( '?' ) ||
        QUESTION_PATTERNS.some( ( pattern ) => pattern.test( lower ) ) ||
        INFORMAL_QUERIES.some( ( phrase ) => lower.includes( phrase ) );
}

/**
 * Whether a chat message has nothing to do with forms.
 *
 * @param {string} message Message.
 *
 * @return {boolean} Off topic.
 */
export function isIrrelevantQuery( message ) {
    if ( ! message || 'string' !== typeof message ) {
        return false;
    }

    const lower = message.toLowerCase().trim();

    if ( FORM_KEYWORDS.some( ( keyword ) => lower.includes( keyword ) ) ) {
        return false;
    }

    return IRRELEVANT_PATTERNS.some( ( pattern ) => pattern.test( lower ) );
}

/**
 * Whether a chat message goes to the generate API.
 *
 * @param {string} message Message.
 *
 * @return {boolean} Call the API.
 */
export function shouldMakeAPICall( message ) {
    if ( isModificationRequest( message ) ) {
        return true;
    }

    return ! isInformationalQuery( message );
}
