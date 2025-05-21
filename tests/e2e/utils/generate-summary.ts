#!/usr/bin/env node
import * as fs from 'fs/promises';
import * as path from 'path';
import { fileURLToPath } from 'url';
import * as yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

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

async function generateSummary(): Promise<void> {
  try {
    function extractTests(suite: PlaywrightSuite): void {
      if (suite.tests) {
        allTests.push(...suite.tests.map(t => ({ title: t.title, status: t.status })));
      }
      if (suite.suites) {
        suite.suites.forEach(extractTests);
      }
    }

    // Read test results
    const resultsJson = await fs.readFile(resultsPath, 'utf-8');
    const results: PlaywrightResults = JSON.parse(resultsJson);

    // Read features map
    const featuresYaml = await fs.readFile(featuresMapPath, 'utf-8');
    const featuresMap = yaml.load(featuresYaml) as { features: Feature[] };

    // Extract all test titles and statuses
    const allTests: { title: string; status: string }[] = [];

    results.suites.forEach(extractTests);

    // Calculate statistics
    const totalTests = allTests.length;
    const passedTests = allTests.filter(t => t.status === 'passed').length;
    const failedTests = allTests.filter(t => t.status === 'failed').length;
    const skippedTests = allTests.filter(t => t.status === 'skipped').length;

    // Calculate duration
    const duration = results.stats?.duration || 0;
    const minutes = Math.floor(duration / 60000);
    const seconds = ((duration % 60000) / 1000).toFixed(0);

    // Generate summary markdown
    const summary = `## 🎭 Playwright Test Summary
### 📊 Test Statistics
- ✅ **${passedTests}** tests passed
- ❌ **${failedTests}** tests failed
- ⏭️ **${skippedTests}** tests skipped
- 🕒 Duration: **${minutes}m ${seconds}s**
### 📝 Test Details
${allTests.map(test => `- ${test.status === 'passed' ? '✅' : test.status === 'failed' ? '❌' : '⏭️'} ${test.title}`).join('\n')}
### 📎 Artifacts
- [HTML Report](${htmlReportPath})
`;

    // Write to GITHUB_STEP_SUMMARY if in CI
    if (process.env.GITHUB_STEP_SUMMARY) {
      await fs.writeFile(process.env.GITHUB_STEP_SUMMARY, summary);
    } else {
      console.log(summary);
    }
  } catch (error) {
    console.error('Error generating summary:', error);
    process.exit(1);
  }
}

generateSummary();