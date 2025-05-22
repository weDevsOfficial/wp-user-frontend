#!/usr/bin/env node
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

interface PlaywrightError {
  message: string;
  stack?: string;
  value?: unknown;
}

interface PlaywrightTestInstanceResult {
  status: string;
  duration: number;
  retry: number;
  errors: PlaywrightError[];
}
interface PlaywrightTestInstance {
  results: PlaywrightTestInstanceResult[];
}
interface PlaywrightTest {
  title: string;
  ok: boolean;
  tests: PlaywrightTestInstance[];
}
interface PlaywrightSuite {
  title: string;
  specs?: PlaywrightTest[];
  suites?: PlaywrightSuite[];
}
interface PlaywrightResults {
  suites: PlaywrightSuite[];
  stats?: { duration?: number };
}
interface Feature {
  id: string;
  name: string;
}

const resultsPath = path.join(__dirname, '../test-results/results.json');
const featuresMapPath = path.join(__dirname, '../features-map/features-map-pro.yml');
const htmlReportPath = 'tests/e2e/playwright-report/index.html';

function normalizeId(id: string): string {
  // Match e.g. PF001, PF0001, LS01, LS0001, etc.
  const match = id.match(/^([A-Z]+)(\d+)(_PRO)?$/i);
  if (!match) return id;
  const prefix = match[1].toUpperCase();
  const num = match[2].padStart(4, '0');
  const pro = match[3] || '';
  return `${prefix}${num}${pro}`;
}

function flattenTests(suites: PlaywrightSuite[], parentTitle = ''): { id: string, name: string, status: string, duration: number, flaky: boolean }[] {
  let tests: { id: string, name: string, status: string, duration: number, flaky: boolean }[] = [];
  for (const suite of suites) {
    if (suite.specs) {
      for (const spec of suite.specs) {
        // Extract feature ID
        const match = spec.title.match(/([A-Z]+\d+(_PRO)?)/);
        let id = match ? match[1] : '';
        id = normalizeId(id);

        // Aggregate all results for this test
        let allResults: PlaywrightTestInstanceResult[] = [];
        for (const t of spec.tests) {
          if (t.results) {
            allResults = allResults.concat(t.results);
          }
        }
        const duration = allResults.reduce((acc, r) => acc + (r.duration || 0), 0);
        const statuses = allResults.map(r => r.status);
        const status = statuses.includes('failed')
          ? 'failed'
          : statuses.every(s => s === 'skipped')
            ? 'skipped'
            : 'passed';
        const flaky = allResults.length > 1 && statuses.includes('failed') && statuses.includes('passed');

        tests.push({
          id,
          name: spec.title,
          status,
          duration,
          flaky
        });
      }
    }
    if (suite.suites) {
      tests = tests.concat(flattenTests(suite.suites, suite.title));
    }
  }
  return tests;
}

async function generateSummary() {
  try {
    // Read test results
    const resultsJson = await fs.readFile(resultsPath, 'utf-8');
    const results: PlaywrightResults = JSON.parse(resultsJson);

    // Read features map
    const featuresYaml = await fs.readFile(featuresMapPath, 'utf-8');
    const featuresMap = yaml.load(featuresYaml) as { features: Feature[] };
    // Normalize feature IDs
    const features = featuresMap.features.map(f => ({ ...f, id: normalizeId(f.id) }));

    // Flatten all test results
    const allTests = flattenTests(results.suites);

    // Map feature coverage
    const featureRows = features.map(feature => {
      const test = allTests.find(t => t.id === feature.id);
      return {
        id: feature.id,
        name: feature.name,
        status: test ? test.status : 'not_covered',
        duration: test ? test.duration : 0,
        flaky: test ? test.flaky : false
      };
    });

    // Calculate stats
    const total = featureRows.length;
    const passed = featureRows.filter(f => f.status === 'passed').length;
    const failed = featureRows.filter(f => f.status === 'failed').length;
    const skipped = featureRows.filter(f => f.status === 'skipped').length;
    const notCovered = featureRows.filter(f => f.status === 'not_covered').length;
    const flaky = featureRows.filter(f => f.flaky).length;
    const totalDuration = featureRows.reduce((acc, f) => acc + f.duration, 0);
    const minutes = Math.floor(totalDuration / 60000);
    const seconds = ((totalDuration % 60000) / 1000).toFixed(0);
    const coverage = ((total - notCovered) / total * 100).toFixed(1);

    // Markdown table
    const tableHeader = `| Feature ID | Name | Status | Duration (s) | Flaky? |
|---|---|---|---|---|`;
    const tableRows = featureRows.map(f =>
      `| ${f.id} | ${f.name} | ${f.status === 'passed' ? '✅ Passed' : f.status === 'failed' ? '❌ Failed' : f.status === 'skipped' ? '⏭️ Skipped' : '⚠️ Not Covered'} | ${(f.duration / 1000).toFixed(1)} | ${f.flaky ? '⚠️ Yes' : ''} |`
    ).join('\n');

    // Summary
    const summary = `# 🎭 Playwright Test Summary

## 📊 Test Statistics
- **Total:** ${total}
- ✅ **Passed:** ${passed}
- ❌ **Failed:** ${failed}
- ⏭️ **Skipped:** ${skipped}
- ⚠️ **Not Covered:** ${notCovered}
- 🔁 **Flaky:** ${flaky}
- 🕒 **Duration:** ${minutes}m ${seconds}s
- 📈 **Coverage:** ${coverage}%

## 📝 Test Scenario Coverage Table
${tableHeader}
${tableRows}

## 📦 Full Report
> ℹ️ **To see full details, screenshots, and step-by-step results, please download the \`playwright-report\` artifact from the next section and open \`index.html\` locally.**
>
> _This gives you a beautiful, interactive HTML report with all test evidence and logs._
`;

    // Write to GITHUB_STEP_SUMMARY if in CI
    if (process.env.GITHUB_STEP_SUMMARY) {
      await fs.writeFile(process.env.GITHUB_STEP_SUMMARY, summary);
    } else {
      console.log(summary);
    }
  } catch (error) {
    console.error('Error generating summary:', error);
  }
}

generateSummary();