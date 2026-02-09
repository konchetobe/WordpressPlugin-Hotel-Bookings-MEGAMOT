---
description: Update documentation after code changes
---

# Documentation Update Workflow

This workflow should be followed after making code changes to keep documentation in sync.

## Steps

1. **Identify what changed**
   - Did you modify a function signature?
   - Did you add/remove a file?
   - Did you add a new feature (endpoint, setting, payment method)?
   - Did you change data structures?

2. **Update SKELETON.md** (if signatures changed)
   - Find the class section
   - Update the function signature
   - Update return type/parameter docs
   - Update data structure examples if needed

3. **Update module README.md** (if module behavior changed)
   - `/includes/README.md` - Core class changes
   - `/admin/README.md` - Admin page/settings changes
   - `/templates/README.md` - Template variable changes
   - `/assets/README.md` - CSS/JS changes

4. **Update ARCHITECTURE.md** (if architecture changed)
   - New AJAX endpoints → AJAX Endpoints section
   - New settings → Plugin Settings section
   - New payment method → Payment Flow section
   - New shortcodes → Shortcodes section
   - New post type fields → Database Schema section

5. **Update PROJECT_STRUCTURE.md** (if files added/removed)
   - Add new files to the tree
   - Update Quick Reference table if needed

6. **Self-check**
   Ask: "If a new AI session started now with only the docs, would it understand my change?"

## Quick Reference

| Change Type | Files to Update |
|-------------|-----------------|
| Function signature | `SKELETON.md` |
| New file | `PROJECT_STRUCTURE.md` |
| AJAX endpoint | `ARCHITECTURE.md`, `includes/README.md`, `SKELETON.md` |
| New setting | `ARCHITECTURE.md`, `admin/README.md` |
| Payment method | `ARCHITECTURE.md`, `SKELETON.md` |
| Template variable | `templates/README.md` |
| CSS component | `assets/README.md` |
| JS function | `assets/README.md` |
