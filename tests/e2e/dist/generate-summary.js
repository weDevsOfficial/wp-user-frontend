#!/usr/bin/env node
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';
var __filename = fileURLToPath(import.meta.url);
var __dirname = path.dirname(__filename);
var resultsPath = path.join(__dirname, '../test-results/results.json');
var featuresMapPath = path.join(__dirname, '../features-map/features-map.yml');
var htmlReportPath = 'tests/e2e/playwright-report/index.html';
function normalizeId(id) {
    // Match e.g. PF001, PF0001, LS01, LS0001, etc.
    var match = id.match(/^([A-Z]+)(\d+)(_PRO)?$/i);
    if (!match)
        return id;
    var prefix = match[1].toUpperCase();
    var num = match[2].padStart(4, '0');
    var pro = match[3] || '';
    return "".concat(prefix).concat(num).concat(pro);
}
function flattenTests(suites, parentTitle) {
    if (parentTitle === void 0) { parentTitle = ''; }
    var tests = [];
    for (var _i = 0, suites_1 = suites; _i < suites_1.length; _i++) {
        var suite = suites_1[_i];
        if (suite.specs) {
            for (var _a = 0, _b = suite.specs; _a < _b.length; _a++) {
                var spec = _b[_a];
                // Extract feature ID
                var match = spec.title.match(/([A-Z]+\d+(_PRO)?)/);
                var id = match ? match[1] : '';
                id = normalizeId(id);
                // Aggregate all results for this test
                var allResults = [];
                for (var _c = 0, _d = spec.tests; _c < _d.length; _c++) {
                    var t = _d[_c];
                    if (t.results) {
                        allResults = allResults.concat(t.results);
                    }
                }
                var duration = allResults.reduce(function (acc, r) { return acc + (r.duration || 0); }, 0);
                var statuses = allResults.map(function (r) { return r.status; });
                var status_1 = statuses.includes('failed')
                    ? 'failed'
                    : statuses.every(function (s) { return s === 'skipped'; })
                        ? 'skipped'
                        : 'passed';
                var flaky = allResults.length > 1 && statuses.includes('failed') && statuses.includes('passed');
                tests.push({
                    id: id,
                    name: spec.title,
                    status: status_1,
                    duration: duration,
                    flaky: flaky
                });
            }
        }
        if (suite.suites) {
            tests = tests.concat(flattenTests(suite.suites, suite.title));
        }
    }
    return tests;
}
function generateSummary() {
    return __awaiter(this, void 0, void 0, function () {
        var results, resultsJson, error_1, features, featuresYaml, featuresMap, error_2, allTests_1, featureRows, total, passed, failed, skipped, notCovered, flaky, totalDuration, minutes, seconds, coverage, tableHeader, tableRows, summary, error_3;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 12, , 13]);
                    results = void 0;
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 3, , 4]);
                    return [4 /*yield*/, fs.readFile(resultsPath, 'utf-8')];
                case 2:
                    resultsJson = _a.sent();
                    results = JSON.parse(resultsJson);
                    return [3 /*break*/, 4];
                case 3:
                    error_1 = _a.sent();
                    console.error('Error reading results file:', error_1);
                    console.log('Creating empty results object');
                    results = { suites: [] };
                    return [3 /*break*/, 4];
                case 4:
                    features = [];
                    _a.label = 5;
                case 5:
                    _a.trys.push([5, 7, , 8]);
                    return [4 /*yield*/, fs.readFile(featuresMapPath, 'utf-8')];
                case 6:
                    featuresYaml = _a.sent();
                    featuresMap = yaml.load(featuresYaml);
                    features = featuresMap.features.map(function (f) { return (__assign(__assign({}, f), { id: normalizeId(f.id) })); });
                    return [3 /*break*/, 8];
                case 7:
                    error_2 = _a.sent();
                    console.error('Error reading features map:', error_2);
                    console.log('Using empty features array');
                    return [3 /*break*/, 8];
                case 8:
                    allTests_1 = flattenTests(results.suites);
                    featureRows = features.map(function (feature) {
                        var test = allTests_1.find(function (t) { return t.id === feature.id; });
                        return {
                            id: feature.id,
                            name: feature.name,
                            status: test ? test.status : 'not_covered',
                            duration: test ? test.duration : 0,
                            flaky: test ? test.flaky : false
                        };
                    });
                    total = featureRows.length;
                    passed = featureRows.filter(function (f) { return f.status === 'passed'; }).length;
                    failed = featureRows.filter(function (f) { return f.status === 'failed'; }).length;
                    skipped = featureRows.filter(function (f) { return f.status === 'skipped'; }).length;
                    notCovered = featureRows.filter(function (f) { return f.status === 'not_covered'; }).length;
                    flaky = featureRows.filter(function (f) { return f.flaky; }).length;
                    totalDuration = featureRows.reduce(function (acc, f) { return acc + f.duration; }, 0);
                    minutes = Math.floor(totalDuration / 60000);
                    seconds = ((totalDuration % 60000) / 1000).toFixed(0);
                    coverage = ((total - notCovered) / total * 100).toFixed(1);
                    tableHeader = "| Feature ID | Name | Status | Duration (s) | Flaky? |\n|---|---|---|---|---|";
                    tableRows = featureRows.map(function (f) {
                        return "| ".concat(f.id, " | ").concat(f.name, " | ").concat(f.status === 'passed' ? '✅ Passed' : f.status === 'failed' ? '❌ Failed' : f.status === 'skipped' ? '⏭️ Skipped' : '⚠️ Not Covered', " | ").concat((f.duration / 1000).toFixed(1), " | ").concat(f.flaky ? '⚠️ Yes' : '', " |");
                    }).join('\n');
                    summary = "# \uD83C\uDFAD Playwright Test Summary\n\n## \uD83D\uDCCA Test Statistics\n- **Total:** ".concat(total, "\n- \u2705 **Passed:** ").concat(passed, "\n- \u274C **Failed:** ").concat(failed, "\n- \u23ED\uFE0F **Skipped:** ").concat(skipped, "\n- \u26A0\uFE0F **Not Covered:** ").concat(notCovered, "\n- \uD83D\uDD01 **Flaky:** ").concat(flaky, "\n- \uD83D\uDD52 **Duration:** ").concat(minutes, "m ").concat(seconds, "s\n- \uD83D\uDCC8 **Coverage:** ").concat(coverage, "%\n\n## \uD83D\uDCDD Test Scenario Coverage Table\n").concat(tableHeader, "\n").concat(tableRows, "\n\n## \uD83D\uDCE6 Full Report\n> \u2139\uFE0F **To see full details, screenshots, and step-by-step results, please download the `playwright-report` artifact from the next section and open `index.html` locally.**\n>\n> _This gives you a beautiful, interactive HTML report with all test evidence and logs._\n");
                    if (!process.env.GITHUB_STEP_SUMMARY) return [3 /*break*/, 10];
                    return [4 /*yield*/, fs.writeFile(process.env.GITHUB_STEP_SUMMARY, summary)];
                case 9:
                    _a.sent();
                    return [3 /*break*/, 11];
                case 10:
                    console.log(summary);
                    _a.label = 11;
                case 11: return [3 /*break*/, 13];
                case 12:
                    error_3 = _a.sent();
                    console.error('Error generating summary:', error_3);
                    process.exit(1);
                    return [3 /*break*/, 13];
                case 13: return [2 /*return*/];
            }
        });
    });
}
// Run the main function
generateSummary().catch(function (error) {
    console.error('Unhandled error:', error);
    process.exit(1);
});
