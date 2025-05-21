#!/usr/bin/env node
import * as fs from 'fs/promises';
import * as path from 'path';
import { fileURLToPath } from 'url';
import * as yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Adjust these paths as needed
const resultsPath = path.join(__dirname, '../test-results/results.json');
const featuresMapPath = path.join(__dirname, '../features-map/features-map.yml');
const htmlReportPath = 'tests/e2e/playwright-report/index.html';

async function generateSummary(): Promise<void> {
  try {
    // Detailed logging for debugging
    console.log('Attempting to read results from:', resultsPath);
    
    // Check if results file exists
    try {
      await fs.access(resultsPath);
    } catch (accessError) {
      console.error('Results file does not exist.');
      
      // List contents of the directory for debugging
      const testResultsDir = path.dirname(resultsPath);
      console.log('Contents of test-results directory:');
      try {
        const files = await fs.readdir(testResultsDir);
        console.log('Files:', files);
      } catch (dirError) {
        console.error('Could not list directory contents:', dirError);
      }
      
      // Create a default summary
      const fallbackSummary = `## 🎭 Playwright Test Summary
### ❌ No test results found
- Unable to generate test summary
- Please check test execution logs
`;
      
      if (process.env.GITHUB_STEP_SUMMARY) {
        await fs.writeFile(process.env.GITHUB_STEP_SUMMARY, fallbackSummary);
      } else {
        console.log(fallbackSummary);
      }
      return;
    }

    // Read test results
    const resultsJson = await fs.readFile(resultsPath, 'utf-8');
    const results = JSON.parse(resultsJson);

    // Validate results structure
    if (!results || !results.suites) {
      throw new Error('Invalid results structure');
    }

    // Extract tests recursively
    const allTests: { title: string; status: string }[] = [];
    
    function extractTests(suite: any): void {
      if (suite.tests) {
        allTests.push(...suite.tests.map((t: any) => ({ 
          title: t.title, 
          status: t.status 
        })));
      }
      if (suite.suites) {
        suite.suites.forEach(extractTests);
      }
    }

    results.suites.forEach(extractTests);

    // Calculate statistics
    const passedTests = allTests.filter(t => t.status === 'passed').length;
    const failedTests = allTests.filter(t => t.status === 'failed').length;
    const skippedTests = allTests.filter(t => t.status === 'skipped').length;

    // Generate summary markdown
    const summary = `## 🎭 Playwright Test Summary
### 📊 Test Statistics
- ✅ **${passedTests}** tests passed
- ❌ **${failedTests}** tests failed
- ⏭️ **${skippedTests}** tests skipped

### 📝 Test Details
${allTests.map(test => `- ${
  test.status === 'passed' ? '✅' : 
  test.status === 'failed' ? '❌' : 
  '⏭️'
} ${test.title}`).join('\n')}

### 📎 Artifacts
- [HTML Report](${htmlReportPath})
`;

    // Write to GITHUB_STEP_SUMMARY
    if (process.env.GITHUB_STEP_SUMMARY) {
      await fs.writeFile(process.env.GITHUB_STEP_SUMMARY, summary);
    } else {
      console.log(summary);
    }

  } catch (error) {
    console.error('Comprehensive error generating summary:', error);
    
    // Fallback summary in case of any error
    const errorSummary = `## 🎭 Playwright Test Summary
### ❌ Error Generating Summary
- An unexpected error occurred while processing test results
- Please check the workflow logs for more details
`;

    if (process.env.GITHUB_STEP_SUMMARY) {
      await fs.writeFile(process.env.GITHUB_STEP_SUMMARY, errorSummary);
    } else {
      console.log(errorSummary);
    }

    process.exit(1);
  }
}

generateSummary();