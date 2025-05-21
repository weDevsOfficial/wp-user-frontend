import * as fs from 'fs';
import * as path from 'path';
import * as yaml from 'js-yaml';

// Types
interface PlaywrightTest {
  title: string;
  status: string;
}
interface PlaywrightSuite {
  suites?: PlaywrightSuite[];
  tests?: PlaywrightTest[];
}
interface PlaywrightResults {
  suites: PlaywrightSuite[];
  stats?: { duration?: number };
}
interface Feature {
  id: string;
  name: string;
}

// Paths
const resultsPath = path.join(__dirname, '../test-results/results.json');
const featuresMapPath = path.join(__dirname, '../features-map/features-map.yml');
const htmlReportPath = 'tests/e2e/playwright-report/index.html';

// Read Playwright JSON results
let results: PlaywrightResults;
try {
  results = JSON.parse(fs.readFileSync(resultsPath, 'utf-8'));
} catch (e) {
  console.error('Could not read results.json:', e);
  process.exit(1);
}

// Helper to flatten all tests
function getAllTests(suites: PlaywrightSuite[] = []): PlaywrightTest[] {
  let tests: PlaywrightTest[] = [];
  for (const suite of suites) {
    if (suite.suites) tests = tests.concat(getAllTests(suite.suites));
    if (suite.tests) tests = tests.concat(suite.tests);
  }
  return tests;
}

const allTests = getAllTests(results.suites);
const total = allTests.length;
const passed = allTests.filter(t => t.status === 'expected').length;
const failed = allTests.filter(t => t.status === 'unexpected').length;
const flaky = allTests.filter(t => t.status === 'flaky').length;
const skipped = allTests.filter(t => t.status === 'skipped').length;
const durationSec = results.stats && results.stats.duration ? (results.stats.duration / 1000).toFixed(1) : 'N/A';

// Read features map
const defaultCoverage = 'N/A';
let features: Feature[] = [];
try {
  const featuresMap = yaml.load(fs.readFileSync(featuresMapPath, 'utf-8')) as { features: Feature[] };
  features = featuresMap.features || [];
} catch (e) {
  console.error('Could not read features-map.yml:', e);
}

// Extract tested feature IDs from test titles
const testedFeatureIds = new Set<string>();
for (const test of allTests) {
  // Try to extract an ID like LS0001, PF0001, RF0001, RLS0001, etc. from the test title
  const match = test.title.match(/([A-Z]{2,4}\d{4,}_?PRO?)/i) || test.title.match(/([A-Z]{2,4}\d{4,})/i);
  if (match) testedFeatureIds.add(match[1].toUpperCase());
}

let coverage: string = defaultCoverage;
if (features.length > 0) {
  const covered = features.filter(f => testedFeatureIds.has(f.id.toUpperCase())).length;
  coverage = ((covered / features.length) * 100).toFixed(2) + '%';
}

// Artifact link (relative path for HTML report)
const htmlReportLink = '[Playwright HTML Report](./tests/e2e/playwright-report/index.html)';

// Markdown summary
const summary = `
## 🧪 E2E Tests Summary

| Test Suite | Total 📊 | Passed ✅ | Failed ❌ | Flaky 🦄 | Skipped ⏭️ | Duration ⏰ | Coverage 🏁 |
|:----------:|:--------:|:--------:|:--------:|:--------:|:----------:|:----------:|:-----------:|
| E2E Tests  |   ${total}   |   ${passed}   |   ${failed}   |   ${flaky}   |    ${skipped}    |  ${durationSec}s  |   ${coverage}   |

### 📂 [HTML Report & Artifacts](${htmlReportPath})

- 📝 **Total:** ${total}
- ✅ **Passed:** ${passed}
- ❌ **Failed:** ${failed}
- 🦄 **Flaky:** ${flaky}
- ⏭️ **Skipped:** ${skipped}
- ⏰ **Duration:** ${durationSec}s
- 🏁 **Coverage:** ${coverage}

---

*Job summary generated at run-time by [generate-summary.ts](./tests/e2e/utils/generate-summary.ts)*
`;

if (process.env.GITHUB_STEP_SUMMARY) {
  fs.appendFileSync(process.env.GITHUB_STEP_SUMMARY, summary);
  console.log('Test summary written to GitHub Actions summary.');
} else {
  // Fallback for local/dev
  fs.writeFileSync(path.join(__dirname, 'test-summary.md'), summary);
  console.log('Test summary written to test-summary.md');
} 