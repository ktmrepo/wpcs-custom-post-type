const fs = require('fs');
const path = require('path');

class WordPressPluginDebugger {
    constructor(pluginPath) {
        this.pluginPath = pluginPath;
        this.issues = [];
        this.warnings = [];
        this.suggestions = [];
    }

    analyzePlugin() {
        console.log('🔍 Starting WordPress Plugin Analysis...\n');
        
        this.checkFileStructure();
        this.analyzeMainFile();
        this.analyzeClassFiles();
        this.checkSecurity();
        this.checkWordPressStandards();
        
        this.generateReport();
    }

    checkFileStructure() {
        console.log('📁 Checking File Structure...');
        
        const requiredFiles = [
            'custom-content-blocks.php',
            'includes/class-post-type.php',
            'includes/class-shortcode-handler.php',
            'includes/class-admin-interface.php'
        ];

        requiredFiles.forEach(file => {
            const filePath = path.join(this.pluginPath, file);
            if (fs.existsSync(filePath)) {
                console.log(`✅ ${file} - Found`);
            } else {
                console.log(`❌ ${file} - Missing`);
                this.issues.push(`Missing required file: ${file}`);
            }
        });
        console.log('');
    }

    analyzeMainFile() {
        console.log('🔧 Analyzing Main Plugin File...');
        
        const mainFile = path.join(this.pluginPath, 'custom-content-blocks.php');
        if (!fs.existsSync(mainFile)) {
            this.issues.push('Main plugin file not found');
            return;
        }

        const content = fs.readFileSync(mainFile, 'utf8');
        
        // Check for plugin header
        if (!content.includes('Plugin Name:')) {
            this.issues.push('Missing plugin header information');
        } else {
            console.log('✅ Plugin header found');
        }

        // Check for security measures
        if (!content.includes("if (!defined('ABSPATH'))")) {
            this.issues.push('Missing ABSPATH security check');
        } else {
            console.log('✅ ABSPATH security check found');
        }

        // Check for proper file includes
        if (!content.includes('require_once')) {
            this.warnings.push('No file includes found - plugin may not load properly');
        } else {
            console.log('✅ File includes found');
        }

        // Check for activation/deactivation hooks
        if (!content.includes('register_activation_hook')) {
            this.suggestions.push('Consider adding activation hook for proper plugin setup');
        }

        console.log('');
    }

    analyzeClassFiles() {
        console.log('🏗️  Analyzing Class Files...');
        
        const classFiles = [
            'includes/class-post-type.php',
            'includes/class-shortcode-handler.php',
            'includes/class-admin-interface.php'
        ];

        classFiles.forEach(file => {
            const filePath = path.join(this.pluginPath, file);
            if (fs.existsSync(filePath)) {
                this.analyzeClassFile(filePath, file);
            }
        });
        console.log('');
    }

    analyzeClassFile(filePath, fileName) {
        const content = fs.readFileSync(filePath, 'utf8');
        
        // Check for class definition
        if (!content.includes('class ')) {
            this.issues.push(`${fileName}: Missing class definition`);
            return;
        }

        // Check for constructor
        if (!content.includes('__construct') && !content.includes('function ' + this.getClassName(fileName))) {
            this.warnings.push(`${fileName}: No constructor found`);
        }

        // Check for WordPress hooks
        if (!content.includes('add_action') && !content.includes('add_filter')) {
            this.warnings.push(`${fileName}: No WordPress hooks found`);
        }

        console.log(`✅ ${fileName} - Basic structure OK`);
    }

    getClassName(fileName) {
        const matches = fileName.match(/class-(.+)\.php/);
        if (matches) {
            return matches[1].replace(/-/g, '_').toUpperCase();
        }
        return '';
    }

    checkSecurity() {
        console.log('🔐 Checking Security Measures...');
        
        const files = [
            'custom-content-blocks.php',
            'includes/class-post-type.php',
            'includes/class-shortcode-handler.php',
            'includes/class-admin-interface.php'
        ];

        files.forEach(file => {
            const filePath = path.join(this.pluginPath, file);
            if (fs.existsSync(filePath)) {
                const content = fs.readFileSync(filePath, 'utf8');
                
                // Check for nonce verification
                if (content.includes('$_POST') && !content.includes('wp_verify_nonce')) {
                    this.issues.push(`${file}: POST data used without nonce verification`);
                }

                // Check for capability checks
                if (content.includes('admin') && !content.includes('current_user_can')) {
                    this.warnings.push(`${file}: Admin functionality without capability checks`);
                }

                // Check for data sanitization
                if (content.includes('$_GET') || content.includes('$_POST')) {
                    if (!content.includes('sanitize_') && !content.includes('wp_kses')) {
                        this.warnings.push(`${file}: User input may not be properly sanitized`);
                    }
                }
            }
        });
        
        console.log('✅ Security check completed');
        console.log('');
    }

    checkWordPressStandards() {
        console.log('📋 Checking WordPress Coding Standards...');
        
        // This is a simplified check - in real scenarios you'd use PHP_CodeSniffer
        const mainFile = path.join(this.pluginPath, 'custom-content-blocks.php');
        if (fs.existsSync(mainFile)) {
            const content = fs.readFileSync(mainFile, 'utf8');
            
            // Check for proper prefixing
            if (!content.includes('wpcs_') && !content.includes('WPCS_')) {
                this.suggestions.push('Consider using consistent function/class prefixes to avoid conflicts');
            }
            
            // Check for proper hook naming
            if (content.includes('add_action') || content.includes('add_filter')) {
                console.log('✅ WordPress hooks found');
            }
        }
        
        console.log('✅ WordPress standards check completed');
        console.log('');
    }

    generateReport() {
        console.log('📊 DEBUGGING REPORT');
        console.log('='.repeat(50));
        
        if (this.issues.length > 0) {
            console.log('\n🚨 CRITICAL ISSUES:');
            this.issues.forEach((issue, index) => {
                console.log(`${index + 1}. ${issue}`);
            });
        }
        
        if (this.warnings.length > 0) {
            console.log('\n⚠️  WARNINGS:');
            this.warnings.forEach((warning, index) => {
                console.log(`${index + 1}. ${warning}`);
            });
        }
        
        if (this.suggestions.length > 0) {
            console.log('\n💡 SUGGESTIONS:');
            this.suggestions.forEach((suggestion, index) => {
                console.log(`${index + 1}. ${suggestion}`);
            });
        }
        
        if (this.issues.length === 0 && this.warnings.length === 0) {
            console.log('\n✅ No critical issues found! Plugin structure looks good.');
        }
        
        console.log('\n📋 COMMON DEBUGGING STEPS:');
        console.log('1. Enable WordPress debug mode (WP_DEBUG = true)');
        console.log('2. Check error logs for PHP errors');
        console.log('3. Verify plugin activation in WordPress admin');
        console.log('4. Test custom post type registration');
        console.log('5. Test shortcode functionality');
        console.log('6. Check admin interface rendering');
        
        console.log('\n🔧 DEBUGGING COMMANDS:');
        console.log('- Add error_log() statements for debugging');
        console.log('- Use var_dump() to inspect variables');
        console.log('- Check WordPress hooks with add_action("wp_loaded", "debug_function")');
    }
}

// Run the plugin analyzer
const pluginAnalyzer = new WordPressPluginDebugger('./wpcs-custom-post-type');
pluginAnalyzer.analyzePlugin();