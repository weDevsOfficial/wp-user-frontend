#!/usr/bin/env node
import fs from 'fs/promises';
import { existsSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const setupResultsPath = path.join(__dirname, '../setup/setup-results.json');
const parallelResultsPaths = [
  path.join(__dirname, '../parallel-one/parallel-lite-one-results.json'),
  path.join(__dirname, '../parallel-two/parallel-lite-two-results.json'),
  path.join(__dirname, '../parallel-one/parallel-pro-one-results.json'),
  path.join(__dirname, '../parallel-two/parallel-pro-two-results.json'),
];
const featuresMapPath = path.join(__dirname, '../features-map/features-map.yml');

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
  
  if (match) {
    return normalizeId(match[1]);
  }
  
  return searchId || null;
}

async function loadTestResults(filePath) {
  if (!existsSync(filePath)) {
    return null;
  }
  
  try {
    const data = await fs.readFile(filePath, 'utf8');
    return JSON.parse(data);
  } catch (error) {
    console.error(`Error reading ${filePath}:`, error.message);
    return null;
  }
}

async function mergeParallelResults() {
  const allResults = {
    config: {
      configFile: "merged-parallel-configs",
      rootDir: "",
      forbidOnly: false,
      fullyParallel: false,
      globalSetup: null,
      globalTeardown: null,
      globalTimeout: 0,
      grep: {},
      grepInvert: {},
      maxFailures: 0,
      metadata: {},
      preserveOutput: "always",
      reporter: [["json"]],
      reportSlowTests: { max: 5, threshold: 15000 },
      quiet: false,
      projects: [],
      shard: null,
      updateSnapshots: "missing",
      version: "1.40.0",
      workers: 2,
      webServer: null
    },
    suites: [],
    errors: [],
    stats: {
      startTime: new Date().toISOString(),
      duration: 0,
      expected: 0,
      unexpected: 0,
      flaky: 0,
      skipped: 0
    },
    // Store individual shard durations for sharded execution calculation
    parallelOneDuration: 0,
    parallelTwoDuration: 0
  };

  let totalDuration = 0;
  let totalExpected = 0;
  let totalUnexpected = 0;
  let totalFlaky = 0;
  let totalSkipped = 0;

  for (const filePath of parallelResultsPaths) {
    const results = await loadTestResults(filePath);
    if (!results) continue;

    // Store individual shard durations
    const fileName = path.basename(filePath);
    if (fileName.includes('parallel-lite-one') && results.stats?.duration) {
      allResults.parallelOneDuration = results.stats.duration;
    } else if (fileName.includes('parallel-lite-two') && results.stats?.duration) {
      allResults.parallelTwoDuration = results.stats.duration;
    }

    // Merge suites
    if (results.suites) {
      allResults.suites.push(...results.suites);
    }

    // Merge errors
    if (results.errors) {
      allResults.errors.push(...results.errors);
    }

    // Merge stats
    if (results.stats) {
      totalDuration += results.stats.duration || 0;
      totalExpected += results.stats.expected || 0;
      totalUnexpected += results.stats.unexpected || 0;
      totalFlaky += results.stats.flaky || 0;
      totalSkipped += results.stats.skipped || 0;
    }

    // Merge projects
    if (results.config && results.config.projects) {
      allResults.config.projects.push(...results.config.projects);
    }
  }

  // Update merged stats
  allResults.stats.duration = totalDuration;
  allResults.stats.expected = totalExpected;
  allResults.stats.unexpected = totalUnexpected;
  allResults.stats.flaky = totalFlaky;
  allResults.stats.skipped = totalSkipped;

  return allResults;
}

function extractTestsFromResults(results, resultType = 'unknown') {
  if (!results || !results.suites) {
    console.warn(`No suites found in ${resultType} results`);
    return [];
  }

  const tests = [];
  
  function traverseSuites(suites, parentTitle = '') {
    for (const suite of suites) {
      const suiteTitle = parentTitle ? `${parentTitle} › ${suite.title}` : suite.title;
      
      // Handle specs (individual test cases)
      if (suite.specs && suite.specs.length > 0) {
        for (const spec of suite.specs) {
          const testId = extractTestId(spec.title);
          
          // Get test result status and duration from the first test result
          const firstResult = spec.tests?.[0]?.results?.[0];
          let status = 'unknown';
          
          if (firstResult?.status) {
            switch (firstResult.status) {
              case 'passed':
                status = 'expected';
                break;
              case 'failed':
                status = 'failed';
                break;
              case 'timedOut':
                status = 'failed';
                break;
              case 'skipped':
                status = 'skipped';
                break;
              case 'interrupted':
                status = 'failed';
                break;
              default:
                status = firstResult.status;
            }
          } else if (spec.tests?.[0]?.outcome) {
            // Check outcome as fallback
            status = spec.tests[0].outcome;
          } else if (!spec.tests || spec.tests.length === 0) {
            // No test results means it wasn't executed
            status = 'not-covered';
          }
          
          const duration = firstResult?.duration || 0;
          
          tests.push({
            id: testId || `UNKNOWN_${tests.length}`,
            title: spec.title,
            fullTitle: `${suiteTitle} › ${spec.title}`,
            status: status,
            duration: duration,
            tags: spec.tags || [],
            type: resultType,
            error: firstResult?.errors?.[0] || null
          });
        }
      }
      
      // Continue traversing nested suites
      if (suite.suites && suite.suites.length > 0) {
        traverseSuites(suite.suites, suiteTitle);
      }
    }
  }
  
  traverseSuites(results.suites);
  return tests;
}

async function loadFeaturesMap() {
  try {
    if (!existsSync(featuresMapPath)) {
      console.warn('Features map not found, using basic categorization');
      return { validTestIds: new Set(), featuresMap: {} };
    }
    
    const yamlContent = await fs.readFile(featuresMapPath, 'utf8');
    const featuresMap = yaml.load(yamlContent);
    
    // Extract valid test IDs from the features array
    const validTestIds = new Set();
    if (featuresMap && featuresMap.features && Array.isArray(featuresMap.features)) {
      for (const feature of featuresMap.features) {
        if (feature.id) {
          validTestIds.add(feature.id);
        }
      }
    }
    
    return { validTestIds, featuresMap: featuresMap || {} };
  } catch (error) {
    console.warn('Error loading features map:', error.message);
    return { validTestIds: new Set(), featuresMap: {} };
  }
}

function getFeatureCategory(testId, featuresMap) {
  // Since we're only showing tests from features map, use fallback categorization based on test ID prefix
  const prefix = testId.match(/^([A-Z]+)/)?.[1];
  switch (prefix) {
    case 'LS': return { category: 'Setup', feature: 'Setup' };
    case 'PF': return { category: 'Post Forms', feature: 'Post Forms' };
    case 'RF': return { category: 'Registration Forms', feature: 'Registration Forms' };
    case 'PFS': return { category: 'Post Form Settings', feature: 'Post Form Settings' };
    case 'RFS': return { category: 'Registration Form Settings', feature: 'Registration Form Settings' };
    default: return { category: 'Other', feature: 'Other' };
  }
}

function formatDuration(ms) {
  if (ms < 1000) return `${ms} ms`;
  return `${(ms / 1000).toFixed(1)} s`;
}

function getStatusIcon(status) {
  switch (status) {
    case 'expected': return '✅';
    case 'passed': return '✅';
    case 'unexpected': return '❌';
    case 'failed': return '❌';
    case 'flaky': return '⚠️';
    case 'skipped': return '⏭️';
    default: return '🚫';
  }
}

async function generateShardedSummary() {
  
  // Load setup results
  const setupResults = await loadTestResults(setupResultsPath);
  
  // Merge all parallel results
  const mergedParallelResults = await mergeParallelResults();
  
  // Load features map
  const { validTestIds, featuresMap } = await loadFeaturesMap();
  
  // Extract tests from both phases
  const setupTests = setupResults ? extractTestsFromResults(setupResults, 'Setup') : [];
  const parallelTests = extractTestsFromResults(mergedParallelResults, 'Parallel');
  
  // Filter tests to only include those in the features map
  const allTests = [...setupTests, ...parallelTests].filter(test => validTestIds.has(test.id));
  
  // Calculate overall stats
  const passed = allTests.filter(t => ['expected', 'passed'].includes(t.status)).length;
  const failed = allTests.filter(t => ['unexpected', 'failed'].includes(t.status)).length;
  const flaky = allTests.filter(t => t.status === 'flaky').length;
  const skipped = allTests.filter(t => t.status === 'skipped').length;
  
  // Count uncovered as tests that don't fall into the above categories
  const uncovered = allTests.filter(t => 
    !['expected', 'passed', 'unexpected', 'failed', 'flaky', 'skipped'].includes(t.status)
  ).length;
  
  // Calculate actual sharded execution time (wall-clock time)
  const setupDuration = setupResults?.stats?.duration || 0;
  const parallelOneDuration = mergedParallelResults.parallelOneDuration || 0;
  const parallelTwoDuration = mergedParallelResults.parallelTwoDuration || 0;
  
  // Total sharded duration is setup + max(parallel shards) since parallel shards run simultaneously
  const parallelDuration = Math.max(parallelOneDuration, parallelTwoDuration);
  const totalWallClockDuration = setupDuration + parallelDuration;
  
  // Calculate average based on sum of all individual test durations
  const totalTestDuration = allTests.reduce((sum, test) => sum + test.duration, 0);
  const averageDuration = totalTestDuration / allTests.length;
  const coverage = ((passed / allTests.length) * 100).toFixed(1);
  
  // Format duration for display (convert ms to minutes and seconds)
  const formatTotalDuration = (ms) => {
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${minutes}m ${seconds}s`;
  };
  
  const formatAverageDuration = (ms) => {
    return `${(ms / 1000).toFixed(1)}s`;
  };
  
  // Get current date
  const currentDate = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  
  // Final Statistics table
  console.log('📊 Final Statistics');
  console.log('');
  console.log('| Test | Total | Passed | Failed | Flaky | Skipped | Not Covered | Coverage | Duration | Average | Date |');
  console.log('|------|-------|--------|--------|-------|---------|-------------|----------|----------|---------|------|');
  console.log(`| E2E  | ${allTests.length} | ${passed} | ${failed} | ${flaky} | ${skipped} | ${uncovered} | ${coverage}% | ${formatTotalDuration(totalWallClockDuration)} | ${formatAverageDuration(averageDuration)} | ${currentDate} |`);
  console.log('');
  
  // Covered Scenarios table
  console.log('🎯 Covered Scenarios');
  console.log('');
  console.log('| ID | Type | Title | Status | Duration | Tags |');
  console.log('|----|------|-------|--------|----------|------|');
  
  for (const test of allTests.sort((a, b) => a.id.localeCompare(b.id))) {
    const { category } = getFeatureCategory(test.id, featuresMap);
    const statusIcon = getStatusIcon(test.status);
    const duration = formatDuration(test.duration);
    const tags = test.tags.join(' ');
    
    console.log(`| ${test.id} | ${category} | ${test.title} | ${statusIcon} | ${duration} | ${tags} |`);
  }
  
  // Save merged results
  const mergedResultsPath = path.join(__dirname, '../test-results/merged-results.json');
  const mergedResults = {
    setup: setupResults,
    parallel: mergedParallelResults,
    summary: {
      totalTests: allTests.length,
      passed,
      failed,
      flaky,
      skipped,
      totalWallClockDuration,
      totalTestDuration,
      successRate: ((passed / allTests.length) * 100).toFixed(1)
    }
  };
  
  await fs.writeFile(mergedResultsPath, JSON.stringify(mergedResults, null, 2));
}

// Run the summary generation
generateShardedSummary().catch(console.error); 
