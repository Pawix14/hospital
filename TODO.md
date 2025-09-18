# Nurse Panel Design Update Plan

## Information Gathered
- **admin-panel.php**: Modern glass-morphism design with gradients, blur effects, glass-card classes, nav-pills sidebar, tab-content with glass-card wrappers, custom CSS with :root variables for gradients, welcome-header, stat-cards with gradients.
- **nurse-panel.php**: Basic Bootstrap design with list-group sidebar, tab-content, existing PHP functions for patient registration, medicine management, patient rounds scheduling.

## Plan
- Replace nurse-panel.php HTML structure and CSS to match admin-panel.php design
- Preserve all PHP logic, functions, and database operations
- Update navbar to glass-morphism style with "Nurse" branding
- Convert sidebar from list-group to nav-pills with glass styling
- Wrap tab-content in glass-card containers
- Apply admin's CSS styles (gradients, glass effects)
- Update dashboard cards to use glass-card styling
- Keep all existing functionality intact

## Dependent Files to be Edited
- nurse-panel.php (full redesign)

## Followup Steps
- Test all nurse functions still work (patient registration, medicine management, rounds scheduling)
- Verify responsive design
- Check modal functionality

## Confirmation Needed
Please confirm if I can proceed with applying the admin-panel design to nurse-panel.php while preserving all functions and important codes.

---

# Doctor Panel Code Fix Plan

## Information Gathered
- doctor-panel.php has syntax errors in diagnostics query (invalid PHP block inside SQL string).
- SQL injection vulnerabilities due to direct variable insertion in queries.
- Missing error handling for mysqli_query calls.
- Potential issues with billtb updates if columns are missing.

## Plan
- [ ] Fix malformed diagnostics_query by removing invalid PHP block and properly closing the query.
- [ ] Implement prepared statements for all database queries to prevent SQL injection.
- [ ] Add error handling for mysqli_query calls.
- [ ] Verify and fix billtb updates.
- [ ] Check for any other syntax errors.

## Dependent Files to be Edited
- doctor-panel.php

## Followup Steps
- [ ] Test the page for runtime errors.
- [ ] Verify all functionalities work correctly.
- [ ] Check database operations.
