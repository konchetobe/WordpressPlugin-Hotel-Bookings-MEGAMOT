# AI Agent Instructions

> **IMPORTANT**: Read this file before making any code changes.

## 🔴 Mandatory Documentation Updates

When you modify code in this project, you **MUST** also update the relevant documentation files. This is not optional.

### Update Triggers

| If you change... | Update these files... |
|------------------|----------------------|
| Any function signature | `SKELETON.md` |
| Add/remove a file | `PROJECT_STRUCTURE.md` |
| Add AJAX endpoint | `CLAUDE.md` (AJAX section), `includes/README.md` |
| Add shortcode | `CLAUDE.md` (Shortcodes section) |
| Add settings option | `CLAUDE.md` (Settings section), `admin/README.md` |
| Add payment method | `CLAUDE.md` (Payment Flow section) |
| Modify booking fields | `CLAUDE.md` (Database Schema section), `SKELETON.md` |
| Add CSS component | `assets/README.md` |
| Add JavaScript function | `assets/README.md` |
| Add template variable | `templates/README.md` |
| Add admin page | `admin/README.md` |
| Change class relationships | `includes/README.md` |

### Checklist Before Completing Any Task

```
□ Code changes complete
□ Tested/verified working
□ SKELETON.md updated (if signatures changed)
□ Relevant README.md updated (if module changed)
□ CLAUDE.md updated (if architecture changed)
□ PROJECT_STRUCTURE.md updated (if files added/removed)
```

## 📝 Documentation Style Guide

### SKELETON.md
- Only function signatures, no implementation
- Include parameter types and return types
- Add data structure examples where helpful

### Module README.md files
- Keep under 100 lines
- Use tables for quick reference
- Include "Common Tasks" section
- Update flow diagrams if relationships change

### CLAUDE.md
- Comprehensive but organized
- Use tables for reference data
- Include code examples
- Keep step-by-step flows up to date

## 🚨 Red Flags (Stop and Update Docs)

If you find yourself:
- Adding a new class → Update `PROJECT_STRUCTURE.md` and `SKELETON.md`
- Adding a public function → Update `SKELETON.md`
- Changing how data flows → Update `CLAUDE.md` and module `README.md`
- Adding a new feature → Consider if any docs need updating

## Example: Adding a New Payment Method

When adding a payment method (e.g., "Pay at Hotel"), update:

1. **`SKELETON.md`** - Add any new function signatures
2. **`CLAUDE.md`** - 
   - Add to Settings section
   - Add to Payment Flow section
   - Update AJAX section if new endpoint
3. **`admin/README.md`** - If new settings added
4. **`templates/README.md`** - If template variables change
5. **`includes/README.md`** - If class relationships change

## Self-Check Question

Before marking a task complete, ask yourself:

> "If a new AI session started right now with only the documentation files, 
> would it understand the change I just made?"

If no → update the docs.
