import { parse } from "@babel/parser";
import _traverse from "@babel/traverse";
import _generate from "@babel/generator";

// Handle CommonJS/ES Module compatibility
const traverse = _traverse.default || _traverse;
const generate = _generate.default || _generate;

export default function wpufPrefixPlugin() {
    return {
        name: "wpuf-prefix-plugin",
        apply: "build",
        generateBundle(options, bundle) {
            Object.keys(bundle).forEach((fileName) => {
                const asset = bundle[fileName];

                // Only process JS files
                if (asset.type === "chunk" && fileName.endsWith(".js")) {
                    let code = asset.code;

                    // List of reserved keywords and built-in identifiers to avoid renaming
                    const reservedWords = new Set([
                        "if",
                        "else",
                        "for",
                        "while",
                        "do",
                        "switch",
                        "case",
                        "break",
                        "continue",
                        "function",
                        "return",
                        "var",
                        "let",
                        "const",
                        "class",
                        "extends",
                        "super",
                        "this",
                        "new",
                        "try",
                        "catch",
                        "throw",
                        "finally",
                        "typeof",
                        "instanceof",
                        "in",
                        "of",
                        "with",
                        "delete",
                        "void",
                        "null",
                        "undefined",
                        "true",
                        "false",
                        "import",
                        "export",
                        "from",
                        "as",
                        "default",
                        "async",
                        "await",
                        "yield",
                        "get",
                        "set",
                        "static",
                        "public",
                        "private",
                        "protected",
                        "readonly",
                        // Common single-letter variables that shouldn't be renamed
                        "i",
                        "j",
                        "k",
                        "x",
                        "y",
                        "z",
                        "e",
                        "t",
                        "r",
                        "n",
                        "m",
                        "l",
                        "s",
                        "c",
                        "v",
                        "w",
                        "h",
                        // Common abbreviations
                        "el",
                        "fn",
                        "cb",
                        "err",
                        "res",
                        "req",
                        "obj",
                        "arr",
                        "str",
                        "num",
                        "bool",
                        "url",
                        "uri",
                        "id",
                        "api",
                        "dom",
                        "css",
                        "xhr",
                        "json",
                        "xml",
                        "sql",
                        // jQuery and common library shortcuts
                        "$",
                        "_",
                        "jQuery",
                    ]);

                    // AST-based variable renaming for better accuracy and maintainability
                    try {
                        // Validate that required functions are available
                        if (typeof traverse !== "function") {
                            throw new Error(
                                "Babel traverse function is not available",
                            );
                        }
                        if (typeof generate !== "function") {
                            throw new Error(
                                "Babel generate function is not available",
                            );
                        }
                        // Parse the code into an AST
                        const ast = parse(code, {
                            sourceType: "module",
                            allowImportExportEverywhere: true,
                            allowReturnOutsideFunction: true,
                            plugins: [
                                "jsx",
                                "typescript",
                                "decorators-legacy",
                                "classProperties",
                                "asyncGenerators",
                                "functionBind",
                                "exportDefaultFrom",
                                "exportNamespaceFrom",
                                "dynamicImport",
                                "nullishCoalescingOperator",
                                "optionalChaining",
                            ],
                        });

                        // Track variables that need to be renamed
                        const variablesToRename = new Map();
                        const scopeStack = [];

                        // First pass: collect variables that should be renamed
                        traverse(ast, {
                            enter(path) {
                                // Track scope entry
                                if (path.isScope()) {
                                    scopeStack.push(path.scope);
                                }
                            },
                            exit(path) {
                                // Track scope exit
                                if (path.isScope()) {
                                    scopeStack.pop();
                                }
                            },
                            Identifier(path) {
                                const name = path.node.name;

                                // Skip reserved words and common variables
                                if (reservedWords.has(name)) {
                                    return;
                                }

                                // Check if this identifier matches our renaming criteria
                                const shouldRename =
                                    // Single letters
                                    (name.length === 1 &&
                                        /^[a-z]$/.test(name)) ||
                                    // Two letter variables
                                    (name.length === 2 &&
                                        /^[a-z]{2}$/.test(name)) ||
                                    // Three letter variables
                                    (name.length === 3 &&
                                        /^[a-z]{3}$/.test(name)) ||
                                    // Underscore + letters
                                    /^_[a-z]{1,3}$/.test(name);

                                if (!shouldRename) {
                                    return;
                                }

                                // Only rename if this is a binding (declaration) or a reference to a local binding
                                const binding = path.scope.getBinding(name);
                                if (!binding) {
                                    return; // Not a local variable, likely a global
                                }

                                // Check if this is a declaration or reference that we should rename
                                const isRenameable =
                                    path.isReferencedIdentifier() ||
                                    path.isBindingIdentifier() ||
                                    (path.isIdentifier() &&
                                        (path.isFunctionDeclaration() ||
                                            path.isVariableDeclarator() ||
                                            path.isClassDeclaration() ||
                                            path.isFunctionExpression() ||
                                            path.isArrowFunctionExpression()));

                                if (
                                    isRenameable &&
                                    !variablesToRename.has(name)
                                ) {
                                    const newName = name.startsWith("_")
                                        ? `wpuf${name}`
                                        : `wpuf_${name}`;
                                    variablesToRename.set(name, newName);
                                }
                            },
                        });

                        // Second pass: perform the renaming
                        traverse(ast, {
                            Identifier(path) {
                                const name = path.node.name;
                                if (variablesToRename.has(name)) {
                                    const binding = path.scope.getBinding(name);
                                    if (binding) {
                                        // Use Babel's built-in rename functionality for proper scope handling
                                        binding.referencePaths.forEach(
                                            (refPath) => {
                                                refPath.node.name =
                                                    variablesToRename.get(name);
                                            },
                                        );
                                        if (binding.path.isIdentifier()) {
                                            binding.path.node.name =
                                                variablesToRename.get(name);
                                        }
                                    }
                                }
                            },
                        });

                        // Generate the new code from the modified AST
                        const output = generate(ast, {
                            retainLines: true,
                            compact: false,
                            concise: false,
                        });

                        // Update the asset code
                        asset.code = output.code;
                    } catch (error) {
                        // If AST parsing fails, fall back to original code
                        console.warn(
                            `WPUF Prefix Plugin: Failed to parse JavaScript for ${fileName}:`,
                            error.message,
                        );
                        // Keep original code unchanged
                    }
                }
            });
        },
    };
}
