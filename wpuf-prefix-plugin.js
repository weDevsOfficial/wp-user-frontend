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

                    // Ultra-comprehensive patterns for all potential variable conflicts
                    const replacementMap = new Map();

                    // 1. Match and collect all potential conflicting variables
                    const variablePatterns = [
                        // Single letters as standalone variables/functions (but exclude reserved)
                        /\b([a-z])(\s*(?:[=\(:]|\s*=>))/g,
                        // Two letter variables
                        /\b([a-z]{2})(\s*(?:[=\(:]|\s*=>))/g,
                        // Three letter variables
                        /\b([a-z]{3})(\s*(?:[=\(:]|\s*=>))/g,
                        // Underscore + letters
                        /\b(_[a-z]{1,3})(\s*(?:[=\(:]|\s*=>))/g,
                    ];

                    // First pass: identify all variables to rename
                    variablePatterns.forEach((pattern) => {
                        let match;
                        while ((match = pattern.exec(code)) !== null) {
                            const varName = match[1];

                            // Skip reserved words and common variables
                            if (reservedWords.has(varName)) {
                                continue;
                            }

                            if (!replacementMap.has(varName)) {
                                if (varName.startsWith("_")) {
                                    replacementMap.set(
                                        varName,
                                        `wpuf${varName}`,
                                    );
                                } else {
                                    replacementMap.set(
                                        varName,
                                        `wpuf_${varName}`,
                                    );
                                }
                            }
                        }
                    });

                    // Second pass: perform the actual replacements
                    replacementMap.forEach((newName, oldName) => {
                        // Escape special regex characters in the old variable name
                        const escapedOldName = oldName.replace(
                            /[.*+?^${}()|[\]\\]/g,
                            "\\$&",
                        );

                        // Multiple replacement patterns for comprehensive coverage
                        const replacementPatterns = [
                            // Variable declarations: const/let/var oldName =
                            new RegExp(
                                `\\b(const|let|var)\\s+${escapedOldName}\\s*=`,
                                "g",
                            ),
                            // Function declarations: function oldName(
                            new RegExp(
                                `\\bfunction\\s+${escapedOldName}\\s*\\(`,
                                "g",
                            ),
                            // Class declarations: class oldName
                            new RegExp(`\\bclass\\s+${escapedOldName}\\b`, "g"),
                            // Method definitions: oldName() {
                            new RegExp(
                                `\\b${escapedOldName}\\s*\\(\\s*[^)]*\\)\\s*\\{`,
                                "g",
                            ),
                            // Arrow functions: oldName =>
                            new RegExp(`\\b${escapedOldName}\\s*=>`, "g"),
                            // Function calls: oldName(
                            new RegExp(`\\b${escapedOldName}\\s*\\(`, "g"),
                            // Property access: .oldName
                            new RegExp(`\\.${escapedOldName}\\b`, "g"),
                            // Object properties: {oldName:
                            new RegExp(`\\{\\s*${escapedOldName}\\s*:`, "g"),
                            // Object shorthand: {oldName}
                            new RegExp(`\\{\\s*${escapedOldName}\\s*[,}]`, "g"),
                            // Function parameters: (oldName,
                            new RegExp(`\\(\\s*${escapedOldName}\\s*[,)]`, "g"),
                            // For loop variables: for(let oldName
                            new RegExp(
                                `\\bfor\\s*\\(\\s*(let|const|var)\\s+${escapedOldName}\\b`,
                                "g",
                            ),
                            // For...in/of loops: for(oldName in/of
                            new RegExp(
                                `\\bfor\\s*\\(\\s*${escapedOldName}\\s+(in|of)\\b`,
                                "g",
                            ),
                            // Catch variables: catch(oldName)
                            new RegExp(
                                `\\bcatch\\s*\\(\\s*${escapedOldName}\\s*\\)`,
                                "g",
                            ),
                            // Destructuring: {oldName}
                            new RegExp(`\\{\\s*${escapedOldName}\\s*[,}]`, "g"),
                            // Array destructuring: [oldName]
                            new RegExp(
                                `\\[\\s*${escapedOldName}\\s*[,\\]]`,
                                "g",
                            ),
                            // Import/export: import {oldName}
                            new RegExp(
                                `\\b(import|export)\\s*\\{[^}]*\\b${escapedOldName}\\b[^}]*\\}`,
                                "g",
                            ),
                            // Assignment operations: oldName +=
                            new RegExp(
                                `\\b${escapedOldName}\\s*[+\\-*/%&|^]?=`,
                                "g",
                            ),
                            // Increment/decrement: ++oldName, oldName++
                            new RegExp(
                                `(\\+\\+|\\-\\-)\\s*${escapedOldName}\\b|\\b${escapedOldName}\\s*(\\+\\+|\\-\\-)`,
                                "g",
                            ),
                            // Template literals: ${oldName}
                            new RegExp(
                                `\\$\\{[^}]*\\b${escapedOldName}\\b[^}]*\\}`,
                                "g",
                            ),
                            // General variable references (most permissive, applied last)
                            new RegExp(
                                `\\b${escapedOldName}\\b(?![a-zA-Z0-9_])`,
                                "g",
                            ),
                        ];

                        replacementPatterns.forEach((pattern, index) => {
                            if (index === 0) {
                                // const/let/var declarations
                                code = code.replace(pattern, `$1 ${newName}=`);
                            } else if (index === 1) {
                                // function declarations
                                code = code.replace(
                                    pattern,
                                    `function ${newName}(`,
                                );
                            } else if (index === 2) {
                                // class declarations
                                code = code.replace(
                                    pattern,
                                    `class ${newName}`,
                                );
                            } else if (index === 3) {
                                // method definitions
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 4) {
                                // arrow functions
                                code = code.replace(pattern, `${newName} =>`);
                            } else if (index === 5) {
                                // function calls
                                code = code.replace(pattern, `${newName}(`);
                            } else if (index === 6) {
                                // property access
                                code = code.replace(pattern, `.${newName}`);
                            } else if (index === 7) {
                                // object properties
                                code = code.replace(pattern, `{${newName}:`);
                            } else if (index === 8) {
                                // object shorthand
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 9) {
                                // function parameters
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 10) {
                                // for loop variables
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 11) {
                                // for...in/of loops
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 12) {
                                // catch variables
                                code = code.replace(
                                    pattern,
                                    `catch(${newName})`,
                                );
                            } else if (index === 13) {
                                // destructuring objects
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 14) {
                                // destructuring arrays
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 15) {
                                // import/export
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 16) {
                                // assignment operations
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 17) {
                                // increment/decrement
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else if (index === 18) {
                                // template literals
                                code = code.replace(pattern, (match) =>
                                    match.replace(oldName, newName),
                                );
                            } else {
                                // general references
                                code = code.replace(pattern, newName);
                            }
                        });
                    });

                    // Update the asset code
                    asset.code = code;
                }
            });
        },
    };
}
