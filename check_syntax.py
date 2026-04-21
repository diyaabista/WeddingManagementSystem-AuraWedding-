import re

# Read the file
with open(r'c:\xampp\htdocs\AuraWedding\js\app.js', 'r', encoding='utf-8') as f:
    content = f.read()
    lines = content.split('\n')

# Check for basic syntax issues
issues = []

# Count braces
open_braces = content.count('{')
close_braces = content.count('}')
if open_braces != close_braces:
    issues.append(f"Brace mismatch: {open_braces} open, {close_braces} close")

# Count brackets
open_brackets = content.count('[')
close_brackets = content.count(']')
if open_brackets != close_brackets:
    issues.append(f"Bracket mismatch: {open_brackets} open, {close_brackets} close")

# Count parentheses
open_parens = content.count('(')
close_parens = content.count(')')
if open_parens != close_parens:
    issues.append(f"Parenthesis mismatch: {open_parens} open, {close_parens} close")

if issues:
    print("ISSUES FOUND:")
    for issue in issues[:10]:  # Show first 10 issues
        print(f"  - {issue}")
else:
    print("No obvious syntax issues found!")
    print(f"File has {len(lines)} lines")
    print(f"Braces: {open_braces} open, {close_braces} close")
    print(f"Brackets: {open_brackets} open, {close_brackets} close")
    print(f"Parentheses: {open_parens} open, {close_parens} close")
