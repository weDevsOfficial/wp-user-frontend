<?php
/**
 * Static hook call-site scanner (contract snapshots, task 0.3).
 *
 * Lists every do_action / apply_filters call (incl. _ref_array and _deprecated)
 * with file, line, hook name and argument count, using the PHP tokenizer.
 *
 * Usage: php tests/contracts/static-hooks.php [--md] <plugin-dir> [<plugin-dir> ...]
 * Output: TSV "hook<TAB>kind<TAB>args<TAB>file:line" sorted by hook, or with
 * --md the Markdown index kept in docs/hooks/php-index.md.
 *
 * @package WP_User_Frontend
 */

if ( 'cli' !== PHP_SAPI ) {
    exit;
}

$wpuf_hook_functions = [
    'do_action'                   => 'action',
    'do_action_ref_array'         => 'action_ref',
    'do_action_deprecated'        => 'action_deprecated',
    'apply_filters'               => 'filter',
    'apply_filters_ref_array'     => 'filter_ref',
    'apply_filters_deprecated'    => 'filter_deprecated',
];

$wpuf_skip_dirs = [ 'vendor', 'node_modules', 'tests', 'lib/vendor', 'assets/vendor', 'action-scheduler', 'Appsero' ];

/**
 * Returns the source text of a token.
 *
 * @param array|string $token Token.
 *
 * @return string
 */
function wpuf_contract_token_text( $token ) {
    return is_array( $token ) ? $token[1] : $token;
}

$wpuf_rows     = [];
$wpuf_markdown = in_array( '--md', $argv, true );

foreach ( array_diff( array_slice( $argv, 1 ), [ '--md' ] ) as $wpuf_root ) {
    $wpuf_root  = rtrim( realpath( $wpuf_root ), '/' );
    $wpuf_label = basename( $wpuf_root );
    $wpuf_iter  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $wpuf_root, FilesystemIterator::SKIP_DOTS ) );

    foreach ( $wpuf_iter as $wpuf_file ) {
        if ( 'php' !== $wpuf_file->getExtension() ) {
            continue;
        }

        $wpuf_rel = substr( $wpuf_file->getPathname(), strlen( $wpuf_root ) + 1 );
        $wpuf_skip = false;

        foreach ( $wpuf_skip_dirs as $wpuf_dir ) {
            if ( 0 === strpos( $wpuf_rel, $wpuf_dir . '/' ) || false !== strpos( $wpuf_rel, '/' . $wpuf_dir . '/' ) ) {
                $wpuf_skip = true;
                break;
            }
        }

        if ( $wpuf_skip ) {
            continue;
        }

        $wpuf_tokens = token_get_all( file_get_contents( $wpuf_file->getPathname() ) );
        $wpuf_count  = count( $wpuf_tokens );

        for ( $i = 0; $i < $wpuf_count; $i++ ) {
            $wpuf_token = $wpuf_tokens[ $i ];

            if ( ! is_array( $wpuf_token ) || T_STRING !== $wpuf_token[0] || ! isset( $wpuf_hook_functions[ strtolower( $wpuf_token[1] ) ] ) ) {
                continue;
            }

            // Skip method calls and function definitions.
            $wpuf_prev = $i - 1;
            while ( $wpuf_prev >= 0 && is_array( $wpuf_tokens[ $wpuf_prev ] ) && T_WHITESPACE === $wpuf_tokens[ $wpuf_prev ][0] ) {
                $wpuf_prev--;
            }
            if ( $wpuf_prev >= 0 && is_array( $wpuf_tokens[ $wpuf_prev ] ) && in_array( $wpuf_tokens[ $wpuf_prev ][0], [ T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION ], true ) ) {
                continue;
            }

            $j = $i + 1;
            while ( $j < $wpuf_count && is_array( $wpuf_tokens[ $j ] ) && T_WHITESPACE === $wpuf_tokens[ $j ][0] ) {
                $j++;
            }
            if ( '(' !== wpuf_contract_token_text( $wpuf_tokens[ $j ] ) ) {
                continue;
            }

            $wpuf_depth = 0;
            $wpuf_args  = [ '' ];

            for ( $k = $j; $k < $wpuf_count; $k++ ) {
                $wpuf_text = wpuf_contract_token_text( $wpuf_tokens[ $k ] );

                if ( in_array( $wpuf_text, [ '(', '[', '{' ], true ) || ( is_array( $wpuf_tokens[ $k ] ) && in_array( $wpuf_tokens[ $k ][0], [ T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES ], true ) ) ) {
                    $wpuf_depth++;
                    if ( 1 === $wpuf_depth ) {
                        continue;
                    }
                } elseif ( in_array( $wpuf_text, [ ')', ']', '}' ], true ) ) {
                    $wpuf_depth--;
                    if ( 0 === $wpuf_depth ) {
                        break;
                    }
                } elseif ( ',' === $wpuf_text && 1 === $wpuf_depth ) {
                    $wpuf_args[] = '';
                    continue;
                }

                $wpuf_args[ count( $wpuf_args ) - 1 ] .= $wpuf_text;
            }

            $wpuf_args = array_map( 'trim', $wpuf_args );
            $wpuf_args = array_values( array_filter( $wpuf_args, 'strlen' ) );

            if ( empty( $wpuf_args ) ) {
                continue;
            }

            $wpuf_rows[] = [
                preg_replace( '/\s+/', ' ', $wpuf_args[0] ),
                $wpuf_hook_functions[ strtolower( $wpuf_token[1] ) ],
                count( $wpuf_args ) - 1,
                $wpuf_label . '/' . $wpuf_rel . ':' . $wpuf_token[2],
            ];
        }
    }
}

usort(
    $wpuf_rows,
    function ( $a, $b ) {
        return strcmp( $a[0] . $a[3], $b[0] . $b[3] );
    }
);

if ( ! $wpuf_markdown ) {
    foreach ( $wpuf_rows as $wpuf_row ) {
        echo implode( "\t", $wpuf_row ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    exit;
}

$wpuf_hooks = [];

foreach ( $wpuf_rows as $wpuf_row ) {
    $wpuf_key = $wpuf_row[0] . "\t" . $wpuf_row[1];

    $wpuf_hooks[ $wpuf_key ]['args'][ $wpuf_row[2] ] = true;
    $wpuf_hooks[ $wpuf_key ]['sites'][]              = '`' . substr( $wpuf_row[3], strpos( $wpuf_row[3], '/' ) + 1 ) . '`';
}

$wpuf_lines = [
    'DESCRIPTION: Generated index of every PHP hook WPUF fires, with argument count and call sites.',
    'Read when looking up a hook; regenerate after adding or moving a hook call.',
    '',
    '# PHP hook index',
    '',
    'Generated by `php tests/contracts/static-hooks.php --md .` (do not edit by hand). Rules for these hooks: `docs/hooks/php.md`.',
    '',
    count( $wpuf_hooks ) . ' hooks, ' . count( $wpuf_rows ) . ' call sites. Quoted names are literal; names with `$` or `.` are built at runtime.',
    '',
    '| Hook | Kind | Args | Call sites |',
    '|---|---|---|---|',
];

foreach ( $wpuf_hooks as $wpuf_key => $wpuf_hook ) {
    list( $wpuf_name, $wpuf_kind ) = explode( "\t", $wpuf_key );

    $wpuf_args = array_keys( $wpuf_hook['args'] );
    sort( $wpuf_args );

    $wpuf_lines[] = '| `' . str_replace( '|', '\\|', $wpuf_name ) . '` | ' . $wpuf_kind . ' | ' . implode( '/', $wpuf_args ) . ' | ' . implode( '<br>', $wpuf_hook['sites'] ) . ' |';
}

echo implode( "\n", $wpuf_lines ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
