#!/usr/bin/env node
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const resultsPath = path.join(__dirname, '../test-results/results.json');
const featuresMapPath = path.join(__dirname, '../features-map/features-map.yml');
const htmlReportPath = 'tests/e2e/playwright-report/index.html';

function normalizeId(id) {
  // Match e.g. PF001, PF0001, LS01, LS0001, etc.
  const match = id.match(/^([A-Z]+)(\d+)(_PRO)?$/i);
  if (!match) return id;
  const prefix = match[1].toUpperCase();
  const num = match[2].padStart(4, '0');
  const pro = match[3] || '';
  return `${prefix}${num}${pro}`;
}

function extractTestId(title, searchId) {
  // Try various formats:
  // 1. "RF0001 : Description"
  let match = title.match(/^([A-Z]+\d+)(?:_PRO)?\s*:/i);
  if (!match) {
    // 2. "Description [RF0001_PRO]"
    match = title.match(/([A-Z]+\d+(?:_PRO)?)/);
  }
  if (!match) {
    // 3. Remove "Here, " prefix and try again
    const titleWithoutHere = title.replace(/^Here,\s+/, '');
    match = titleWithoutHere.match(/([A-Z]+\d+(?:_PRO)?)/);
  }

  if (!match) return '';

  // Get the base ID without _PRO suffix
  const baseId = normalizeId(match[1].replace(/_PRO$/, ''));

  // If we're searching for a PRO feature, also match the base ID
  if (searchId.endsWith('_PRO') && baseId === searchId.replace(/_PRO$/, '')) {
    return searchId;
  }

  return normalizeId(match[1]);
}

function findTestByTitle(suites, searchId) {
  for (const suite of suites) {
    if (suite.specs) {
      for (const spec of suite.specs) {
        const testId = extractTestId(spec.title, searchId);
        if (testId === searchId) {
          // Tags are at the spec level, not the test level
          const tags = spec.tags || [];
          return { ...spec, tags };
        }
      }
    }
    if (suite.suites) {
      const found = findTestByTitle(suite.suites, searchId);
      if (found) return found;
    }
  }
  return null;
}

function getTestStatus(test) {
  if (!test || !test.tests) return { status: 'not_covered', duration: 0, flaky: false, tags: [] };

  let allResults = [];
  for (const t of test.tests) {
    if (t.results) {
      allResults = allResults.concat(t.results);
    }
  }

  const duration = allResults.reduce((acc, r) => acc + (r.duration || 0), 0);
  const statuses = allResults.map((r) => r.status);
  const status = statuses.includes('failed')
    ? 'failed'
    : statuses.every((s) => s === 'skipped')
      ? 'skipped'
      : allResults.length > 0
        ? 'passed'
        : 'not_covered';
  const flaky = allResults.length > 1 && statuses.includes('failed') && statuses.includes('passed');
  const tags = test.tags || [];

  return { status, duration, flaky, tags };
}

// Function to get test type based on feature ID
function getTestType(featureId) {
  if (featureId.startsWith('LS0')) return 'Setup';
  if (featureId.startsWith('PF0')) return 'Form Creation';
  if (featureId.startsWith('RF0')) return 'Registration';
  if (featureId.startsWith('PFS0')) return 'Post Form Set';
  if (featureId.startsWith('RFS0')) return 'Reg Form Set';
  if (featureId.startsWith('FOS0')) return 'Field Option Set';
  return 'Other';
}

// Function to style tags as pills
function formatTagAsPill(tag) {
  const tagType = tag.replace('@', '');
  
  switch(tagType) {
    case 'Both':
      return `![Both](https://img.shields.io/badge/Both-orange?style=flat-square&labelColor=2d1b47&color=ffb86c)`;
    case 'Pro':
      return `![Pro](https://img.shields.io/badge/Pro-pink?style=flat-square&labelColor=3c1a33&color=ff79c6)`;
    case 'Lite':
      return `![Lite](https://img.shields.io/badge/Lite-purple?style=flat-square&labelColor=2d1b47&color=bd93f9)`;
    default:
      return `![${tagType}](https://img.shields.io/badge/${tagType}-gray?style=flat-square&labelColor=44475a&color=f8f8f2)`;
  }
}

async function generateSummary() {
  try {
    // Read test results
    let results;
    try {
      const resultsJson = await fs.readFile(resultsPath, 'utf-8');
      results = JSON.parse(resultsJson);
    } catch (error) {
      console.error('Error reading results file:', error);
      console.log('Creating empty results object');
      results = { suites: [] };
    }

    // Read features map
    let features = [];
    try {
      const featuresYaml = await fs.readFile(featuresMapPath, 'utf-8');
      const featuresMap = yaml.load(featuresYaml);
      features = featuresMap.features.map((f) => ({ ...f, id: normalizeId(f.id) }));
    } catch (error) {
      console.error('Error reading features map:', error);
      console.log('Using empty features array');
    }

    // Map feature coverage
    const featureRows = features.map((feature) => {
      const test = findTestByTitle(results.suites, feature.id);
      const { status, duration, flaky, tags } = getTestStatus(test);
      const testType = getTestType(feature.id);
      
      return {
        id: feature.id,
        name: feature.name,
        status,
        duration,
        flaky,
        tags: tags || [],
        testType
      };
    });

    // Calculate stats
    const total = featureRows.length;
    const passed = featureRows.filter((f) => f.status === 'passed').length;
    const failed = featureRows.filter((f) => f.status === 'failed').length;
    const skipped = featureRows.filter((f) => f.status === 'skipped').length;
    const notCovered = featureRows.filter((f) => f.status === 'not_covered').length;
    const flaky = featureRows.filter((f) => f.flaky).length;
    const totalDuration = featureRows.reduce((acc, f) => acc + f.duration, 0);
    const minutes = Math.floor(totalDuration / 60000);
    const seconds = ((totalDuration % 60000) / 1000).toFixed(0);
    const coverage = (((total - notCovered) / total) * 100).toFixed(1);
    const avgDuration = total > 0 ? (totalDuration / total / 1000).toFixed(1) : '0';
    const browser = 'Chromium'; // Default browser from your setup
    const lastRun = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

    // Create a table header and row for statistics
    const statHeader = `| Test 📝 | Total 📊 | Passed ✅ | Failed ❌ | Flaky ⚠️ | Skipped ⏭️ | Duration ⏱️ | Avg Duration ⏱️ | Browser 🌐 | Run Date 📅 | Coverage 📈 |
|---|---|---|---|---|---|---|---|---|---|---|
| E2E Tests | ${total} | ${passed} | ${failed} | ${flaky} | ${skipped} | ${minutes}m ${seconds}s | ${avgDuration}s | ${browser} | ${lastRun} | ${coverage}% |`;

    // Remove the old stat values line since we're incorporating it in the table
    const statValues = '';

    // Markdown table with tags and type columns
    const tableHeader = `| Feature ID | Name | Status | Duration (s) | Flaky? | Type | Tags |
|---|---|---|---|---|---|---|`;
    const tableRows = featureRows
      .map(
        (f) =>
          `| ${f.id} | ${f.name} | ${f.status === 'passed' ? '✅ Passed' : f.status === 'failed' ? '❌ Failed' : f.status === 'skipped' ? '⏭️ Skipped' : '⚠️ Not Covered'} | ${(f.duration / 1000).toFixed(1)} | ${f.flaky ? '⚠️ Yes ' : '👍 No'} | ${f.testType} | ${(f.tags || []).map(formatTagAsPill).join(' ')} |`,
      )
      .join('\n');

    // Summary
    const summary = `# 🎭 Playwright Test Summary

## 📊 Test Statistics
${statHeader}

## 📝 Scenario Coverage Table
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
    process.exit(1);
  }
}

// Run the main function
generateSummary().catch((error) => {
  console.error('Unhandled error:', error);
  process.exit(1);
});