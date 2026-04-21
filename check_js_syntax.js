const fs = require('fs');

try {
    const code = fs.readFileSync('js/app.js', 'utf8');
    
    // Count braces
    let openBraces = (code.match(/{/g) || []).length;
    let closeBraces = (code.match(/}/g) || []).length;
    
    console.log('=== JavaScript Syntax Check ===');
    console.log(`Open braces: ${openBraces}`);
    console.log(`Close braces: ${closeBraces}`);
    
    if (openBraces !== closeBraces) {
        console.log(`❌ SYNTAX ERROR: Brace mismatch! ${openBraces} open vs ${closeBraces} close`);
    } else {
        console.log('✓ Braces are balanced');
    }
    
    // Check for AuraWedding object
    if (code.includes('const AuraWedding = {')) {
        console.log('✓ AuraWedding object found');
    } else {
        console.log('❌ AuraWedding object not found!');
    }
    
    // Check for simpleLogin
    if (code.includes('simpleLogin()')) {
        console.log('✓ simpleLogin() function found');
    } else {
        console.log('❌ simpleLogin() function NOT found!');
    }
    
    // Check for init
    if (code.includes('init()')) {
        console.log('✓ init() function found');
    } else {
        console.log('❌ init() function NOT found!');
    }
    
} catch(err) {
    console.error('Error:', err);
}
