#!/usr/bin/env node
import fs from 'fs/promises';
import { existsSync } from 'fs';
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
  const match = id.match(/^([A-Z]+)(\d+)$/i);
  if (!match) return id;
  const prefix = match[1].toUpperCase();
  const num = match[2].padStart(4, '0');
  return `${prefix}${num}`;
}

function extractTestId(title, searchId) {
  // Try various formats:
  // 1. "RF0001 : Description"
  let match = title.match(/^([A-Z]+\d+)\s*:/i);
  if (!match) {
    // 2. "Description [RF0001]"
    match = title.match(/([A-Z]+\d+)/);
  }
  if (!match) {
    // 3. Remove "Here, " prefix and try again
    const titleWithoutHere = title.replace(/^Here,\s+/, '');
    match = titleWithoutHere.match(/([A-Z]+\d+)/);
  }

  if (!match) return '';

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
  
  // Check for failed tests - include timedOut, failed, and check test.ok
  const hasFailed = statuses.includes('failed') || 
                   statuses.includes('timedOut') || 
                   test.ok === false;
  
  const status = hasFailed
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
  if (featureId.startsWith('RS0')) return 'Reset';
  if (featureId.startsWith('LS0')) return 'Setup';
  if (featureId.startsWith('PF0')) return 'Form Creation';
  if (featureId.startsWith('RF0')) return 'Registration';
  if (featureId.startsWith('PFS0')) return 'Post Form Settings';
  if (featureId.startsWith('RFS0')) return 'Reg Form Settings';
  if (featureId.startsWith('FOS0')) return 'Field Option Settings';
  return 'Other';
}

// Function to style tags as pills
function formatTagAsPill(tag) {
  const tagType = tag.replace('@', '');
  
  switch(tagType) {
    case 'Basic':
      return `![Basic](https://img.shields.io/badge/Basic-4dff00?style=plastic&logoColor=white)`;
    case 'Pro':
      return `![Pro](https://img.shields.io/badge/Pro-8000ff?style=plastic&logoColor=white)`;
    case 'Lite':
      return `![Lite](https://img.shields.io/badge/Lite-ff7400?style=plastic&logoColor=white)`;
    default:
      return `![${tagType}](https://img.shields.io/badge/${tagType}-d800ff?style=plastic&logoColor=white)`;
  }
}

async function generateSummary() {
  try {
    // Read test results
    if (!existsSync(resultsPath)) {
      console.error('❌ Results file not found at:', resultsPath);
      process.exit(1);
    }
    
    const resultsData = await fs.readFile(resultsPath, 'utf8');
    const results = JSON.parse(resultsData);

    // Read features map
    const featuresData = await fs.readFile(path.join(__dirname, '../features-map/features-map.yml'), 'utf8');
    const featuresMap = yaml.load(featuresData);
    const features = Array.isArray(featuresMap) ? featuresMap : featuresMap.features || [];

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
        tags: tags || [], // Ensure tags is always an array
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
    const statHeader = `| Test 🧪 | Total 📊 | Passed ✅ | Failed ❌ | Flaky ⚠️ | Skipped ⏭️ | Not Covered 🚫 | Coverage 📈 | Duration ⏱️ | Average ⌛ | Date 📅 |
|---|---|---|---|---|---|---|---|---|---|---|
| E2E | ${total} | ${passed} | ${failed} | ${flaky} | ${skipped} | ${notCovered} | ${coverage}% | ${minutes}m ${seconds}s | ${avgDuration}s | ${lastRun} |`;

    // Remove the old stat values line since we're incorporating it in the table
    const statValues = '';

    // Markdown table with tags and type columns
    const tableHeader = `| ID | Type | Title | Status | Duration | Tags |
|---|---|---|---|---|---|`;
    const tableRows = featureRows
      .map(
        (f) =>
          `| ${f.id} | ${f.testType} | ${f.name} | ${f.status === 'passed' ? '✅' : f.status === 'failed' ? '❌' : f.status === 'skipped' ? '⏭️' : f.status === 'not_covered' ? '🚫' : '❓'} | ${(f.duration / 1000).toFixed(1)} s | ${(f.tags || []).map(formatTagAsPill).join(' ')} |`,
      )
      .join('\n');

    // Summary
    const summary = `# 🧪 Test Summary

## 📊 Final Statistics
${statHeader}

## 🎯 Covered Scenarios
${tableHeader}
${tableRows}

## 🎁 Full Report
> 📌 **To see full details, screenshots, and step-by-step results, please download the \`playwright-report\` artifact from the next section and open \`index.html\` locally.**
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